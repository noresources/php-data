<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Container\Container;
use NoreSources\Data\Tableifier;
use NoreSources\Data\Serialization\Traits\PrimitifyTrait;
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
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeConversionException;

/**
 * CSV (comma separated value) (de)serializer
 *
 * Supported media type parameters
 * <ul>
 * <li><code>separator</code></li>
 * <li><code>enclosure</code></li>
 * <li><code>escape</code></li>
 * <li><code>eol</code> (serializer and PHP 8.1+ for serializer)</li>
 * <li><code>flatten</code> (unserializer only)</li>
 * <li><code>heading=none|auto|row|column</code> (unserialize only) Transforms table to object or
 * collection of objects</li>
 * <li>preprocess-depth=non-zero (serializer only): Primitify input data to ensure better
 * serialization</li>
 * </ul>
 */
class CsvSerializer implements UnserializableMediaTypeInterface,
	SerializableMediaTypeInterface, DataUnserializerInterface,
	DataSerializerInterface, FileUnserializerInterface,
	FileSerializerInterface, StreamUnserializerInterface,
	StreamSerializerInterface, MediaTypeListInterface,
	FileExtensionListInterface
{
	use MediaTypeListTrait;
	use FileExtensionListTrait;

	use UnserializableMediaTypeTrait;
	use SerializableMediaTypeTrait;

	use StreamSerializerBaseTrait;
	use StreamSerializerDataSerializerTrait;
	use StreamSerializerFileSerializerTrait;

	use StreamUnserializerBaseTrait;
	use StreamUnserializerDataUnserializerTrait;
	use StreamUnserializerFileUnserializerTrait;

	use PrimitifyTrait;

	const MEDIA_TYPE = 'text/csv';

	/**
	 * Field separamter
	 *
	 * @var string
	 */
	const PARAMETER_SEPARATOR = 'separator';

	/**
	 * Text enclosing character
	 *
	 * @var string
	 */
	const PARAMETER_ENCLOSURE = 'enclosure';

	/**
	 * Escape character
	 *
	 * @var string
	 */
	const PARAMETER_ESCAPE = 'escape';

	/**
	 * End of line character
	 *
	 * @var string
	 */
	const PARAMETER_EOL = 'eol';

	/**
	 * Row and column heading mode
	 *
	 * @var string
	 */
	const PARAMETER_HEADING = SerializationParameter::TABLE_HEADING;

	/**
	 * No headings
	 *
	 * @var string
	 * @deprecated Use SerializationParameter::TABLE_HEADING_*
	 */
	const HEADING_NONE = SerializationParameter::TABLE_HEADING_NONE;

	/**
	 * Auto-detect headings
	 *
	 * @var string
	 * @deprecated Use SerializationParameter::TABLE_HEADING_*
	 */
	const HEADING_AUTO = SerializationParameter::TABLE_HEADING_AUTO;

	/**
	 * Row heading only
	 *
	 * @var string
	 * @deprecated Use SerializationParameter::TABLE_HEADING_*
	 */
	const HEADING_ROW = SerializationParameter::TABLE_HEADING_ROW;

	/**
	 * Column heading only
	 *
	 * @var string
	 * @deprecated Use SerializationParameter::TABLE_HEADING_*
	 */
	const HEADING_COLUMN = SerializationParameter::TABLE_HEADING_COLUMN;

	/**
	 * Both row and column headings
	 *
	 * @var string
	 * @deprecated Use SerializationParameter::TABLE_HEADING_*
	 */
	const HEADING_BOTH = SerializationParameter::TABLE_HEADING_BOTH;

	/**
	 * Reduce table dimension when top level array contains only one element.
	 *
	 * @var string
	 */
	const PARAMETER_FLATTEN = 'flatten';

	/**
	 * Default field separator
	 *
	 * @var string
	 */
	public $separator = ',';

	/**
	 * Default field enclosure
	 *
	 * @var string
	 */
	public $enclosure = '"';

	/**
	 * Default escape character
	 *
	 * @var string
	 */
	public $escape = '\\';

	/**
	 * Default End of line
	 *
	 * @var string
	 */
	public $eol = "\n";

	public function __construct()
	{
		$this->stringifier = [
			self::class,
			'defaultStringifier'
		];
	}

	/**
	 * Set the strigification function
	 *
	 * @param callable $callback
	 *        	Stringification function
	 * @throws \InvalidArgumentException
	 */
	public function setStringifier($callback)
	{
		if (!\is_callable($callback))
			throw new \InvalidArgumentException(
				'Stringifier must be a callable');
		$this->stringifier = $callback;
	}

	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		$data = $this->prepareSerialization($data);
		$this->writeLinesToStream($stream, $data, $mediaType);
	}

	public function unserializeFromStream($stream,
		MediaTypeInterface $mediaType = null)
	{
		$presentation = $this->retrievePresentationParameters(
			$mediaType);
		$separator = $enclosure = $escape = $eol = null;
		$this->retrieveFormatParameters($separator, $enclosure, $escape,
			$eol, $mediaType);
		$lines = [];
		while ($line = @\fgetcsv($stream, 0, $separator, $enclosure,
			$escape))
		{
			$lines[] = Container::map($line,
				function ($k, $v) {
					if (ctype_digit($v))
						return \intval($v);
					if (\is_numeric($v))
						return \floatval($v);
					return $v;
				});
		}

		return $this->finalizeDeserialization($lines, $presentation);
	}

	public function unserializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$presentation = $this->retrievePresentationParameters(
			$mediaType);
		$separator = $enclosure = $escape = $eol = null;
		$this->retrieveFormatParameters($separator, $enclosure, $escape,
			$eol, $mediaType);

		$lines = \explode($eol, $data);
		$csv = [];

		foreach ($lines as $line)
		{
			if (empty($line))
				continue;
			$csv[] = \str_getcsv($line, $separator, $enclosure, $escape);
		}

		return $this->finalizeDeserialization($csv, $presentation);
	}

	public static function defaultStringifier($value)
	{
		try
		{
			return TypeConversion::toString($value);
		}
		catch (TypeConversionException $e)
		{}
		return serialize($value);
	}

	protected function retrievePresentationParameters(
		MediaTypeInterface $mediaType = null)
	{
		$options = [
			self::PARAMETER_FLATTEN => false,
			self::PARAMETER_HEADING => SerializationParameter::TABLE_HEADING_NONE,
			SerializationParameter::COLLECTION => false
		];
		if (!$mediaType)
			return $options;

		foreach ($options as $h => $dflt)
		{
			if (!$mediaType->getParameters()->has($h))
				continue;
			$value = $mediaType->getParameters()->get($h);
			$value = \strtolower($value);
			if (empty($value) && $dflt === false)
				$value = true;
			$options[$h] = $value;
		}

		return $options;
	}

	protected function finalizeDeserialization(&$lines,
		$options = array())
	{
		if (Container::count($lines) > 0)
		{
			$last = \array_pop($lines);
			if (!((Container::count($last) == 1) &&
				Container::firstValue($last) === null))
			{
				$lines[] = $last;
			}
		}

		$lineCount = Container::count($lines);
		if ($lineCount == 0)
			return $lines;
		$columnCount = Container::count($lines[0]);
		if ($columnCount == 0)
			return $lines;

		$heading = Container::keyValue($options, self::PARAMETER_HEADING);
		if ($heading == SerializationParameter::TABLE_HEADING_AUTO)
		{
			$pivot = $lines[0][0];
			if (empty($pivot))
				$heading = SerializationParameter::TABLE_HEADING_BOTH;
		}

		$isCollection = Container::keyValue($options,
			SerializationParameter::COLLECTION, false);

		if ($heading == SerializationParameter::TABLE_HEADING_COLUMN)
		{
			$fields = [];
			$object = [];
			foreach ($lines[0] as $field)
			{
				$fields[] = $field;
				$object[$field] = [];
			}
			$fieldCount = Container::count($fields);

			if ($isCollection)
			{
				$collection = [];
				for ($a = 1; $a < $lineCount; $a++)
				{
					$object = [];
					for ($b = 0; $b < $fieldCount; $b++)
						$object[$fields[$b]] = $lines[$a][$b];
					$collection[] = $object;
				}

				return $collection;
			}

			for ($a = 1; $a < $lineCount; $a++)
			{
				for ($b = 0; $b < $fieldCount; $b++)
					$object[$fields[$b]][] = $lines[$a][$b];
			}
			return $object;
		}

		if ($heading == SerializationParameter::TABLE_HEADING_ROW)
		{
			$fields = [];
			$object = [];
			foreach ($lines as $line)
			{
				$field = $line[0];
				$fields[] = $field;
				$object[$field] = [];
			}

			$fieldCount = Container::count($fields);

			if ($isCollection)
			{
				$collection = [];
				for ($b = 1; $b < $columnCount; $b++)
				{
					$object = [];
					for ($a = 0; $a < $lineCount; $a++)
						$object[$fields[$a]] = $lines[$a][$b];
					$collection[] = $object;
				}

				return $collection;
			}

			for ($a = 0; $a < $lineCount; $a++)
			{
				for ($b = 1; $b < $columnCount; $b++)
					$object[$fields[$a]][] = $lines[$a][$b];
			}
			return $object;
		}

		if ($heading == SerializationParameter::TABLE_HEADING_BOTH)
		{
			$collection = [];
			$fieldCount = Container::count($lines[0]);
			for ($a = 1; $a < $lineCount; $a++)
			{
				$object = [];
				for ($b = 1; $b < $fieldCount; $b++)
					$object[$lines[0][$b]] = $lines[$a][$b];
				$collection[$lines[$a][0]] = $object;
			}
			return $collection;
		}

		$flatten = Container::keyValue($options, self::PARAMETER_FLATTEN);
		if ($flatten && Container::count($lines) == 1)
		{
			$f = Container::firstValue($lines);
			if (Container::count($f) == 1)
				return Container::firstValue($f);
		}

		return $lines;
	}

	protected function prepareSerialization($data,
		MediaTypeInterface $mediaType = null)
	{
		$data = $this->primitifyData($data, $mediaType);
		$tableizer = new Tableifier();
		$tableizer->setCellNormalizer(
			[
				$this,
				'prepareFieldSerialization'
			]);

		return $tableizer($data);
	}

	public function prepareFieldSerialization($data)
	{
		return \call_user_func($this->stringifier, $data);
	}

	protected function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				self::MEDIA_TYPE),
			/*
			 *  application/csv is not a registered media type but
			 *  finfo_type / mime_content_type may return this one
			 */
			MediaTypeFactory::getInstance()->createFromString(
				'application/csv')
		];
	}

	protected function retrieveFormatParameters(&$separator, &$enclosure,
		&$escape, &$eol, $mediaType)
	{
		foreach ([
			self::PARAMETER_SEPARATOR,
			self::PARAMETER_ENCLOSURE,
			self::PARAMETER_ESCAPE,
			self::PARAMETER_EOL
		] as $v)
		{
			$$v = $this->$v;
			if ($mediaType instanceof MediaTypeInterface)
				$$v = Container::keyValue($mediaType->getParameters(),
					$v, $$v);
		}
	}

	protected function writeLinesToStream($stream, $lines, $mediaType)
	{
		$separator = $enclosure = $escape = $eol = null;
		$this->retrieveFormatParameters($separator, $enclosure, $escape,
			$eol, $mediaType);

		$hasEOL = (version_compare(PHP_VERSION, '8.1.0', '>='));
		foreach ($lines as $line)
		{
			$args = [
				$stream,
				$line,
				$separator,
				$enclosure
			];
			if ($hasEOL)
				$args[] = $eol;

			$result = @\call_user_func_array('\fputcsv', $args);
			if ($result === false)
			{
				$error = \error_get_last();
				throw new SerializationException(
					'Failed to write CSV line: ' . $error['message']);
			}
		}
	}

	protected function buildFileExtensionList()
	{
		return [
			'csv'
		];
	}

	protected function getSupportedMediaTypeParameterValues()
	{
		if (!isset(self::$supportedMediaTypeParameters))
		{
			self::$supportedMediaTypeParameters = [
				self::MEDIA_TYPE => [
					SerializationParameter::PRE_TRANSFORM_RECURSION_LIMIT => true,
					SerializationParameter::COLLECTION => true,
					self::PARAMETER_ENCLOSURE => true,
					self::PARAMETER_EOL => true,
					self::PARAMETER_ESCAPE => true,
					self::PARAMETER_FLATTEN => true,
					self::PARAMETER_SEPARATOR => true,
					self::PARAMETER_HEADING => [
						SerializationParameter::TABLE_HEADING_NONE,
						SerializationParameter::TABLE_HEADING_AUTO,
						SerializationParameter::TABLE_HEADING_ROW,
						SerializationParameter::TABLE_HEADING_COLUMN,
						SerializationParameter::TABLE_HEADING_BOTH
					]
				]
			];
		}

		return self::$supportedMediaTypeParameters;
	}

	private static $supportedMediaTypeParameters;

	/**
	 *
	 * @var callable
	 */
	private $stringifier;
}
