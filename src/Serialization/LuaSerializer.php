<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Container\Container;
use NoreSources\Data\Serialization\Traits\PrimitifyTrait;
use NoreSources\Data\Serialization\Traits\SerializableMediaTypeTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerBaseTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerDataSerializerTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerFileSerializerTrait;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\Data\Utility\Traits\FileExtensionListTrait;
use NoreSources\Data\Utility\Traits\MediaTypeListTrait;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\Type\TypeConversion;

/**
 * Lua primitive serialization
 *
 * Supported parameters
 * <ul>
 * <li>preprocess-depth=non-zero (serializer only): Primitify input data to ensure better
 * serialization</li>
 * </ul>
 *
 * @see https://datatracker.ietf.org/doc/html/rfc3986
 */
class LuaSerializer implements SerializableMediaTypeInterface,
	DataSerializerInterface, FileSerializerInterface,
	StreamSerializerInterface, MediaTypeListInterface,
	FileExtensionListInterface
{
	use MediaTypeListTrait;
	use FileExtensionListTrait;

	use SerializableMediaTypeTrait;

	use StreamSerializerBaseTrait;
	use StreamSerializerDataSerializerTrait;
	use StreamSerializerFileSerializerTrait;

	use PrimitifyTrait;

	/**
	 * Unregisted media type
	 *
	 * @var string
	 */
	const MEDIA_TYPE = 'text/x-lua';

	/**
	 * Data presentation mode
	 *
	 * @var string
	 */
	const PARAMETER_MODE = 'mode';

	/**
	 * Export value "as is"
	 *
	 * This is the default behavior of the serializeData() method
	 *
	 * @var string
	 */
	const MODE_RAW = 'raw';

	/**
	 * Export value prefixed by a "return" keyword
	 *
	 * This is the default behavior of the serializeDataToFile() method
	 *
	 * @var string
	 */
	const MODE_MODULE = 'module';

	/**
	 * Indentation character(s)
	 *
	 * @var string
	 */
	const PARAMETER_INDENT = 'indent';

	public $indentation = ' ';

	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		$options = [
			self::PARAMETER_INDENT => $this->indentation,
			self::PARAMETER_MODE => self::MODE_RAW
		];

		$meta = \stream_get_meta_data($stream);
		switch (Container::keyValue($meta, 'wrapper_type', 'undefined'))
		{
			case 'file':
			case 'plainfile':
				$options[self::PARAMETER_MODE] = self::MODE_MODULE;
			default:
			break;
		}

		if ($mediaType)
		{
			$indentation = Container::keyValue(
				$mediaType->getParameters(), self::PARAMETER_INDENT,
				$this->indentation);
			switch (\mb_strtolower($indentation))
			{
				case 'tab':
					$indentation = "\t";
				break;
				case 'space':
					$indentation = ' ';
				break;
			}

			$options[self::PARAMETER_INDENT] = $indentation;
			$options[self::PARAMETER_MODE] = Container::keyValue(
				$mediaType->getParameters(), self::PARAMETER_MODE,
				$options[self::PARAMETER_MODE]);
		}

		$data = $this->primitifyData($data, $mediaType);

		if (\strcasecmp($options[self::PARAMETER_MODE],
			self::MODE_MODULE) == 0)
			fwrite($stream, 'return ');

		if (Container::isTraversable($data))
			return $this->serializeTable($stream, $data, $options);

		$this->serializeLiteral($stream, $data, $options);
	}

	protected function serializeTableKey($stream, $key, $options)
	{
		if (\preg_match(chr(1) . self::LUA_IDENTIFIER_PATTERN . chr(1),
			$key))
			return \fwrite($stream, $key);
		elseif (\is_integer($key))
			return \fwrite($stream, '[' . $key . ']');
		\fwrite($stream,
			'["' . \addslashes(TypeConversion::toString($key)) . '"]');
	}

	protected function serializeTable($stream, $table, $options,
		$level = 0)
	{
		$first = true;
		\fwrite($stream, "{\n");
		$pad = \str_repeat($options[self::PARAMETER_INDENT], $level);
		if (Container::isIndexed($table))
		{
			foreach ($table as $value)
			{
				if (!$first)
					\fwrite($stream, ",\n");
				\fwrite($stream, $options[self::PARAMETER_INDENT] . $pad);
				if (Container::isTraversable($value))
					$this->serializeTable($stream, $value, $options,
						$level + 1);
				else
					$this->serializeLiteral($stream, $value, $options);
				$first = false;
			}
		}
		else
		{
			foreach ($table as $key => $value)
			{
				if (!$first)
					\fwrite($stream, ",\n");

				\fwrite($stream, $options[self::PARAMETER_INDENT] . $pad);
				$this->serializeTableKey($stream, $key, $options);
				\fwrite($stream, ' = ');
				if (Container::isTraversable($value))
					$this->serializeTable($stream, $value, $options,
						$level + 1);
				else
					$this->serializeLiteral($stream, $value, $options);
				$first = false;
			}
		}

		\fwrite($stream, "\n" . $pad . '}');
	}

	protected function serializeLiteral($stream, $value, $options)
	{
		if (\is_null($value))
			return \fwrite($stream, 'nil');
		if (\is_bool($value))
			return \fwrite($stream, ($value) ? 'true' : 'false');
		if (\is_numeric($value))
			return \fwrite($stream, \strval($value));

		\fwrite($stream, '"' . \addslashes($value) . '"');
	}

	protected function getSupportedMediaTypeParameterValues()
	{
		if (!isset(self::$supportedMediaTypeParameters))
		{
			self::$supportedMediaTypeParameters = [
				SerializationParameter::PRE_TRANSFORM_RECURSION_LIMIT => true,
				self::MEDIA_TYPE => [
					self::PARAMETER_MODE => [
						self::MODE_RAW,
						self::MODE_MODULE
					],
					self::PARAMETER_INDENT => true
				]
			];
		}

		return self::$supportedMediaTypeParameters;
	}

	public function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				self::MEDIA_TYPE)
		];
	}

	public function getFileExtensions()
	{
		return [
			'lua'
		];
	}

	private static $supportedMediaTypeParameters;

	const INTEGER_PATTERN = '^[1-9][0-9]*$';

	const LUA_IDENTIFIER_PATTERN = '^[a-zA-Z_][a-zA-Z0-9_]*$';
}
