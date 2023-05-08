<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Container\Container;
use NoreSources\Data\Tableizer;
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

	const PARAMETER_SEPARATOR = 'separator';

	const PARAMETER_ENCLOSURE = 'enclosure';

	const PARAMETER_ESCAPE = 'escape';

	const PARAMETER_EOL = 'eol';

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
			self::PARAMETER_FLATTEN => false
		];
		if (!$mediaType)
			return $options;

		foreach ([
			self::PARAMETER_FLATTEN
		] as $b)
		{
			if ($mediaType->getParameters()->has($b))
				$options[$b] = true;
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

		$flatten = Container::keyValue($options, self::PARAMETER_FLATTEN);
		if ($flatten && Container::count($lines) == 1)
		{
			$f = Container::firstValue($lines);
			if (Container::count($f) == 1)
				return Container::firstValue($f);
		}
		return $lines;
	}

	protected function prepareSerialization($data)
	{
		$tableizer = new Tableizer();
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
				'text/csv'),
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

	/**
	 *
	 * @var callable
	 */
	private $stringifier;
}
