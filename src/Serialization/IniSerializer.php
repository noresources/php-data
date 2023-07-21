<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Container\Container;
use NoreSources\Data\Analyzer;
use NoreSources\Data\CollectionClass;
use NoreSources\Data\Parser\IniParser;
use NoreSources\Data\Parser\ParserException;
use NoreSources\Data\Serialization\Traits\SerializableMediaTypeTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerBaseTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerDataSerializerTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerFileSerializerTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerBaseTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerDataUnserializerTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerFileUnserializerTrait;
use NoreSources\Data\Serialization\Traits\UnserializableMediaTypeTrait;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\Data\Utility\Traits\FileExtensionListTrait;
use NoreSources\Data\Utility\Traits\MediaTypeListTrait;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * INI file format serialization and deserialization.
 *
 * Since INI files does not have a normative syntax, this serializer may failed to deserialize some
 * file types.
 *
 * Supported media type parameters
 * <ul>
 * <li>indent : Indent key-value line when serializing. Accept white space before key-value when
 * deserializing</li>
 * <li>list-separator : Use this glue to implode leaf key values that are indexed array</li>
 * <li>section-glue : Use this glue to create sub section key when serializing data with a depth >
 * 1. This is also used to restore data tree when deserializing</li>
 * <li>null-string : String representing NULL</li>
 * <li>escape : Character to use to escape quotes. If not set, use the same strategy as PHP built-in
 * parser</li>
 * <li>single-value-key : Use this string as key when serializing POD value</li>
 * <li>duplicated-key=override|concatenate|list</li>
 * <li>list=duplicate-key|concatenate</li>
 * </ul>
 */
class IniSerializer implements UnserializableMediaTypeInterface,
	DataUnserializerInterface, FileUnserializerInterface,
	StreamUnserializerInterface, SerializableMediaTypeInterface,
	StreamSerializerInterface, FileSerializerInterface,
	DataSerializerInterface, MediaTypeListInterface,
	FileExtensionListInterface
{
	use MediaTypeListTrait;
	use FileExtensionListTrait;

	use UnserializableMediaTypeTrait;
	use SerializableMediaTypeTrait;

	use StreamUnserializerBaseTrait;
	use StreamUnserializerDataUnserializerTrait;
	use StreamUnserializerFileUnserializerTrait;

	use StreamSerializerBaseTrait;
	use StreamSerializerDataSerializerTrait;
	use StreamSerializerFileSerializerTrait;

	const MEDIA_TYPE = 'text/x-ini';

	const MEDIA_TYPE_WINE = 'application/x-wine-extension-ini';

	const MEDIA_TYPE_DBUS_SERVICE = 'text/x-dbus-service';

	const MEDIA_TYPE_SYSTEMD_UNIT = 'text/x-systemd-unit';

	/**
	 * Key-value line inside section MAY be indented
	 *
	 * @var string
	 */
	const PARAMETER_INDENT = 'indent';

	/**
	 * Value list separator
	 *
	 * @var string
	 */
	const PARAMETER_LIST_SEPARATOR = 'list-separator';

	/**
	 * Sub section stringification glue
	 *
	 * @var string
	 */
	const PARAMETER_SECTION_GLUE = 'section-glue';

	/**
	 * Text value corresponding to the null value
	 *
	 * The built-in PHP ini function use "null".
	 *
	 * @var string
	 */
	const PARAMETER_NULL_STRING = 'null-string';

	/**
	 * Escape character
	 *
	 * Possible values are
	 * <ul>
	 * <li>quote: Quote-swaping mode. Single and double quotes are escpaed using the "quote swap"
	 * method.
	 * Ex: "The \"quoted\" string" -> "The "'"'"quoted"'"'" string"</li>
	 * <li>none: Do not escape.</li>
	 * <li>"" (empty string): Same as "quote"</li>
	 * <li>any character: A character to prepend before any meta character</li>
	 * </ul>
	 *
	 *
	 * @var string
	 */
	const PARAMETER_ESCAPE = 'escape';

	const ESCAPE_NONE = 'none';

	const ESCAPE_QUOTE_SWAP = 'quote';

	/**
	 * Key used to indication INI contains a single value
	 *
	 * @var string
	 */
	const PARAMETER_SINGLE_VALUE_KEY = 'single-value-key';

	/**
	 * Duplicated key processing mode
	 *
	 * Expected values are
	 * <ul>
	 * <li>override</li>
	 * <li>concatenate</li>
	 * <li>list</li>
	 * </ul>
	 *
	 * <ul>
	 * <li>
	 * <code>duplicated-key=list</code> is equivalent to <code>list=duplicate-key</code>
	 * </li>
	 * <li>
	 * <code>duplicated-key=concatenate</code> is equivalent to <code>list=concatenate</code>
	 * </li>
	 * </ul>
	 *
	 * @var string
	 */
	const PARAMETER_DUPLICATED_KEY = 'duplicated-key';

	/**
	 * Value list serialization mode
	 *
	 * Expected values are
	 * <ul>
	 * <li>concatenate</li>
	 * <li>duplicate-key</li>
	 * </ul>
	 *
	 * See also <code>PARAMETER_DUPLICATED_KEY</code>
	 *
	 * @var string
	 */
	const PARAMETER_LIST = 'list';

	const DUPLICATED_KEY_OVERRIDE = 'override';

	const DUPLICATED_KEY_CONCATENATE = 'concatenate';

	const LIST_CONCATENATE = self::DUPLICATED_KEY_CONCATENATE;

	const DUPLICATED_KEY_ARRAY = 'list';

	const LIST_DUPLICATE_KEY = 'duplicate-key';

	public function __construct()
	{}

	public function unserializeData($text,
		MediaTypeInterface $mediaType = null)
	{
		$options = $this->getOptions($mediaType);
		$parser = new IniParser();
		if (($mode = $this->getParserDuplicatedKeyMode($options)) !==
			null)
			$parser->duplicatedKeyMode = $mode;
		$parser->valueConcatenationGlue = Container::keyValue($options,
			self::PARAMETER_LIST_SEPARATOR);
		$parserFlags = $this->getParserFlags($options, $mediaType);
		$parser->initialize($parserFlags);
		$data = $parser($text, $parserFlags);

		if (!Container::isTraversable($data))
			throw new SerializationException('Failed to parse INI text');

		return $this->postprocessDeserialization($data, $options);
	}

	public function unserializeFromStream($stream,
		MediaTypeInterface $mediaType = null)
	{
		$options = $this->getOptions($mediaType);
		$parser = new IniParser();
		if (($mode = $this->getParserDuplicatedKeyMode($options)) !==
			null)
			$parser->duplicatedKeyMode = $mode;
		$parser->valueConcatenationGlue = Container::keyValue($options,
			self::PARAMETER_LIST_SEPARATOR);
		$parserFlags = $this->getParserFlags($options, $mediaType);
		$parser->initialize($parserFlags);
		while (($text = \fgets($stream)))
		{
			$trimmed = \rtrim($text, "\r\n");
			try
			{
				$parser->parseLine($trimmed,
					\substr($text, \strlen($trimmed)));
			}
			catch (ParserException $e)
			{
				throw new SerializationException($e->getMessage());
			}
		}
		$data = $parser->finalize();
		if (!Container::isTraversable($data))
			throw new SerializationException(
				'Failed to parse INI stream');

		return $this->postprocessDeserialization($data, $options);
	}

	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		$options = $this->getOptions($mediaType);
		if (!Container::isTraversable($data))
		{
			$key = Container::keyValue($options,
				self::PARAMETER_SINGLE_VALUE_KEY);
			$data = [
				$key => $data
			];
		}
		$this->serializeSectionToStream($stream, [], $data, $options);
	}

	protected function serializeSectionToStream($stream, $keys, $data,
		$options)
	{
		$analyzer = Analyzer::getInstance();
		if (Container::count($keys))
		{
			$glue = Container::keyValue($options,
				self::PARAMETER_SECTION_GLUE, ',');
			$section = Container::implodeValues($keys, $glue);
			$p = \ftell($stream);
			if ($p)
				\fwrite($stream, PHP_EOL);

			\fwrite($stream, '[' . $section . ']' . PHP_EOL);
		}

		$standalone = Container::filterValues($data,
			function ($e) use ($analyzer) {
				$d = $analyzer->getMaxDepth($e);
				if ($d == 0)
					return true;
				if ($d > 1)
					return false;
				$class = $analyzer->getCollectionClass($e);
				return (($class & CollectionClass::LIST) ==
				CollectionClass::LIST);
			});
		$standaloneKeys = Container::keys($standalone);
		$inSections = Container::filter($data,
			function ($k, $e) use ($standaloneKeys) {
				return !\in_array($k, $standaloneKeys);
			});
		foreach ($standalone as $key => $value)
			$this->serializeEntryToStream($stream, $key, $value,
				$options, $keys);
		foreach ($inSections as $section => $entry)
		{
			$this->serializeSectionToStream($stream,
				\array_merge($keys, [
					$section
				]), $entry, $options);
		}
	}

	protected function serializeEntryToStream($stream, $key, $data,
		$options, $sectionKeys = array())
	{
		$this->serializeEntryKeyToStream($stream, $key, $options);
		\fwrite($stream, '=');
		if (Container::isTraversable($data))
		{
			$analyzer = Analyzer::getInstance();
			$class = $analyzer->getCollectionClass($data);
			$depth = $analyzer->getMaxDepth($data);

			$separator = Container::keyValue($options,
				self::PARAMETER_LIST_SEPARATOR, '');
			$list = Container::keyValue($options, self::PARAMETER_LIST);
			if (($depth == 1) && $list &&
				($class & CollectionClass::LIST) == CollectionClass::LIST)
			{
				if ($list == self::LIST_CONCATENATE)
				{
					$s = false;
					foreach ($data as $value)
					{
						if ($s)
							\fwrite($stream, $separator);
						$s = true;
						$this->serializeEntryValueToStream($stream,
							$value, $options);
					}
					\fwrite($stream, PHP_EOL);
				}
				elseif ($list == self::LIST_DUPLICATE_KEY)
				{
					$follow = false;
					foreach ($data as $value)
					{
						if ($follow)
						{
							$this->serializeEntryKeyToStream($stream,
								$key, $options);
							\fwrite($stream, '=');
						}
						$follow = true;
						$this->serializeEntryValueToStream($stream,
							$value, $options);
						\fwrite($stream, PHP_EOL);
					}
				}
				return;
			}
			else
			{
				$this->serializeSectionToStream($stream,
					\array_merge($sectionKeys, [
						$key
					]), $data, $options);
				return;
			}
		}

		$this->serializeEntryValueToStream($stream, $data, $options);
		\fwrite($stream, PHP_EOL);
	}

	protected function serializeEntryKeyToStream($stream, $key, $options)
	{
		foreach ([
			'true',
			'false',
			Container::keyValue($options, self::PARAMETER_NULL_STRING,
				'')
		] as $keyword)
		{
			if (\strcasecmp($keyword, $key) == 0)
			{
				$key = '_' . $key;
				break;
			}
		}
		\fwrite($stream, $key);
	}

	protected function serializeEntryValueToStream($stream, $value,
		$options)
	{
		if (\is_null($value))
			$value = Container::keyValue($options,
				self::PARAMETER_NULL_STRING, '');
		elseif (\is_bool($value))
			$value = ($value ? 'true' : 'false');
		elseif (\is_string($value))
		{
			return $this->serializeEntryStringValueToStream($stream,
				$value, $options);
		}

		\fwrite($stream, $value);
	}

	protected function serializeEntryStringValueToStream($stream, $value,
		$options)
	{
		$escape = Container::keyValue($options, self::PARAMETER_ESCAPE);
		if (\strcasecmp($escape, self::ESCAPE_NONE) == 0)
		{
			\fwrite($stream, $value);
			return;
		}

		if (\strcasecmp($escape, self::ESCAPE_QUOTE_SWAP) == 0)
			$escape = null;

		$characters = [
			// PHP built-in parser special characters
			'!',
			'$',
			// The quote
			'"',
			// New lines
			"\r",
			"\n"
		];
		if (!(empty($escape) || \in_array($escape, $characters)))
			$characters[] = $escape;

		$simple = true;
		foreach ($characters as $c)
		{
			if (\str_contains($value, $c))
			{
				$simple = false;
				break;
			}
		}

		if ($simple)
		{
			\fwrite($stream, $value);
			return;
		}

		$length = \strlen($value);
		$v = '"';

		if (empty($escape))
		{
			for ($i = 0; $i < $length; $i++)
			{
				$c = $value[$i];
				if ($c == '"')
				{
					$v .= '"' . "'" . '"' . "'" . '"';
					continue;
				}

				$v .= $c;
			}
		}
		else
		{
			for ($i = 0; $i < $length; $i++)
			{
				$c = $value[$i];
				if ($c == '"' || $c == $escape)
				{
					$v .= $escape;
				}
				$v .= $c;
			}
		}
		$v .= '"';
		\fwrite($stream, $v);
	}

	public function buildMediaTypeList()
	{
		$factory = MediaTypeFactory::getInstance();
		return Container::mapValues(
			[
				self::MEDIA_TYPE_DBUS_SERVICE,
				self::MEDIA_TYPE_SYSTEMD_UNIT,
				self::MEDIA_TYPE_WINE,
				self::MEDIA_TYPE
			], [
				$factory,
				'createFromString'
			]);
	}

	protected function getSupportedMediaTypeParameterValues()
	{
		if (!isset(self::$supportedMediaTypeParameters))
		{
			self::$supportedMediaTypeParameters = [];
			foreach ([
				self::MEDIA_TYPE,
				self::MEDIA_TYPE_WINE,
				self::MEDIA_TYPE_SYSTEMD_UNIT,
				self::MEDIA_TYPE_DBUS_SERVICE
			] as $mediaType)
			{
				self::$supportedMediaTypeParameters[$mediaType] = [
					self::PARAMETER_INDENT => true,
					self::PARAMETER_DUPLICATED_KEY => [
						self::DUPLICATED_KEY_ARRAY,
						self::DUPLICATED_KEY_CONCATENATE,
						self::DUPLICATED_KEY_OVERRIDE
					],
					self::PARAMETER_LIST => [
						self::LIST_CONCATENATE,
						self::LIST_DUPLICATE_KEY
					],
					self::PARAMETER_LIST_SEPARATOR => true,
					self::PARAMETER_SECTION_GLUE => true,
					self::PARAMETER_NULL_STRING => true,
					self::PARAMETER_SINGLE_VALUE_KEY => true
				];
			}
		}
		return self::$supportedMediaTypeParameters;
	}

	public function buildFileExtensionList()
	{
		return [
			'ini'
		];
	}

	protected function postprocessDeserialization($data, $options)
	{
		$parsed = [];
		$analyzer = Analyzer::getInstance();
		$sectionGlue = Container::keyValue($options,
			self::PARAMETER_SECTION_GLUE);
		foreach ($data as $key => $value)
		{
			$parts = [
				$key
			];
			if ($sectionGlue)
				$parts = \explode($sectionGlue, $key, 2);
			if (\count($parts) == 1)
			{
				$class = $analyzer->getCollectionClass($value);
				$dictionary = ($class & CollectionClass::DICTIONARY) ==
					CollectionClass::DICTIONARY;
				if ($dictionary)
				{
					foreach ($value as $k => $v)
					{
						$data[$key][$k] = $this->postprocessValue($v,
							$options);
					}
				}
				else
					$data[$key] = $this->postprocessValue($value,
						$options);
				continue;
			}
			list ($k, $s) = $parts;
			$parsed[$key] = [
				$k,
				$s,
				$value
			];
		}

		foreach ($parsed as $key => $info)
		{
			list ($k, $s, $d) = $info;
			if (!Container::keyExists($data, $k))
				$data[$k] = [];
			$data[$k][$s] = $d;
			unset($data[$key]);
			$data[$k] = $this->postprocessDeserialization($data[$k],
				$options);
		}

		if (Container::count($data) == 1)
		{
			list ($k, $v) = Container::first($data);
			if ($k ==
				Container::keyValue($options,
					self::PARAMETER_SINGLE_VALUE_KEY))
				return $v;
		}

		return $data;
	}

	protected function postprocessValue($value, $options = [])
	{
		if (!\is_string($value))
			return $value;
		if (\strpos($value, PHP_EOL) !== false)
			return $value;

		/**
		 *
		 * @todo use duplicated-key/list params
		 * @var Ambiguous $separator
		 */

		$separator = Container::keyValue($options,
			self::PARAMETER_LIST_SEPARATOR);
		$listMode = Container::keyValue($options, self::PARAMETER_LIST);
		$isPlain = \strcasecmp($listMode, self::LIST_CONCATENATE) ||
			empty($separator);
		if ($isPlain)
			return $this->parseTextValue($value, $options);

		$list = Container::mapValues(\str_getcsv($value, $separator),
			function ($v) {
				return \trim($v, " \t");
			});
		if (\count($list) < 2)
			return $this->parseTextValue($value, $options);
		;

		return \array_map(
			function ($v) use ($options) {
				return $this->parseTextValue($v, $options);
			}, $list);
	}

	protected function parseTextValue($value, $options)
	{
		if (\ctype_digit($value))
			return \intval($value);
		elseif (\is_numeric($value))
			return \floatval($value);
		elseif (\strcasecmp($value, 'true') === 0)
			return true;
		elseif (\strcasecmp($value, 'false') === 0)
			return false;
		elseif (\strcasecmp($value,
			Container::keyValue($options, self::PARAMETER_NULL_STRING)) ===
			0)
			return null;
		return $value;
	}

	protected function getOptions(MediaTypeInterface $mediaType = null)
	{
		$options = [
			self::PARAMETER_INDENT => false,
			self::PARAMETER_LIST_SEPARATOR => null,
			self::PARAMETER_SECTION_GLUE => null,
			self::PARAMETER_NULL_STRING => '',
			self::PARAMETER_ESCAPE => null,
			self::PARAMETER_SINGLE_VALUE_KEY => '_',
			self::PARAMETER_DUPLICATED_KEY => self::DUPLICATED_KEY_OVERRIDE,
			self::PARAMETER_LIST => self::LIST_CONCATENATE
		];
		if ($mediaType)
		{
			$s = \strval($mediaType);
			if (\strcasecmp($s, self::MEDIA_TYPE_WINE) == 0)
			{
				$options[self::PARAMETER_NULL_STRING] = 'null';
			}
			elseif (\strcasecmp($s, self::MEDIA_TYPE_DBUS_SERVICE) == 0 ||
				\strcasecmp($s, self::MEDIA_TYPE_SYSTEMD_UNIT) == 0)
			{
				$options[self::PARAMETER_ESCAPE] = self::ESCAPE_NONE;
				$options[self::PARAMETER_DUPLICATED_KEY] = self::DUPLICATED_KEY_ARRAY;
				$options[self::PARAMETER_LIST] = self::LIST_DUPLICATE_KEY;
			}

			$p = $mediaType->getParameters();
			foreach ($options as $key => $value)
			{
				if (!$p->has($key))
					continue;
				if (\is_bool($options[$key]))
					$options[$key] = !$options[$key];
				else
					$options[$key] = $p->get($key);
			}
		}

		return $options;
	}

	protected function getParserDuplicatedKeyMode($options)
	{
		$mode = Container::keyValue($options,
			self::PARAMETER_DUPLICATED_KEY);
		switch (\strtolower($mode))
		{
			case self::DUPLICATED_KEY_ARRAY:
				return IniParser::DUPLICATED_KEY_ARRAY;
			case self::DUPLICATED_KEY_CONCATENATE:
				return IniParser::DUPLICATED_KEY_CONCATENATE;
			case self::DUPLICATED_KEY_OVERRIDE:
				return IniParser::DUPLICATED_KEY_OVERRIDE;
		}
		return null;
	}

	protected function getParserFlags($options,
		MediaTypeInterface $mediaType = null)
	{
		$flags = 0;
		if ($options[self::PARAMETER_INDENT])
			$flags |= IniParser::KEY_VALUE_INDENTED;

		if (!$mediaType)
			return $flags;

		$s = \strval($mediaType);
		if (\strcasecmp($s, self::MEDIA_TYPE_SYSTEMD_UNIT) == 0 ||
			\strcasecmp($s, self::MEDIA_TYPE_DBUS_SERVICE) == 0)
		{
			$flags |= IniParser::UNQUOTED_VALUE_BACKSLASH_CONTINUE;
		}

		return $flags;
	}

	private static $supportedMediaTypeParameters;
}
