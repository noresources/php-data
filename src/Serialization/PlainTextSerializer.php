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

/**
 * Plain text serialization
 */
class PlainTextSerializer implements UnserializableMediaTypeInterface,
	SerializableMediaTypeInterface, DataUnserializerInterface,
	DataSerializerInterface, FileUnserializerInterface,
	FileSerializerInterface, StreamSerializerInterface,
	StreamUnserializerInterface, FileExtensionListInterface,
	MediaTypeListInterface, SerializableContentInterface
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

	const MEDIA_TYPE = 'text/plain';

	public function __construct()
	{}

	public function unserializeFromStream($stream,
		MediaTypeInterface $mediaType = null)
	{
		$list = [];
		while (($line = \fgets($stream)) !== false)
		{
			$line = \rtrim($line, "\n");
			if (empty($line))
				continue;
			$values = \explode("\r", $line);
			foreach ($values as $value)
			{
				if (empty($value))
					continue;

				if (\is_numeric($value))
				{
					if (\ctype_digit($value))
						$value = TypeConversion::toInteger($value);
					else
						$value = TypeConversion::toFloat($value);
				}

				$list[] = $value;
			}
		}

		$c = \count($list);
		if ($c == 0)
			return '';
		elseif ($c == 1)
			return $list[0];
		return $list;
	}

	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		if (!Container::isTraversable($data))
			return \fwrite($stream, TypeConversion::toString($data));

		$count = 0;
		foreach ($data as $value)
		{
			$visited = [];
			$this->recursiveSerializeData($stream, $count, $visited,
				$value, $mediaType);
		}
	}

	public function getFileStreamFooter()
	{
		return "\n";
	}

	public function isContentSerializable($data)
	{
		$analyzer = Analyzer::getInstance();
		$depth = $analyzer->getMaxDepth($data);

		if ($depth == 0)
			return true;
		if ($depth > 1)
			return false;
		$collectionClass = $analyzer->getCollectionClass($data, 1);

		return ($collectionClass & CollectionClass::ASSOCIATIVE) == 0;
	}

	protected function recursiveSerializeData(&$stream, &$count,
		&$visited, $data, MediaTypeInterface $mediaType = null)
	{
		if (\in_array($data, $visited))
			return;

		if (!Container::isTraversable($data))
		{
			if ($count)
				\fwrite($stream, PHP_EOL);
			$count++;
			\fwrite($stream, TypeConversion::toString($data));
			return;
		}

		$visited[] = $data;
		foreach ($data as $value)
		{
			$this->recursiveSerializeData($stream, $count, $visited,
				$value, $mediaType);
		}
	}

	protected function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				self::MEDIA_TYPE)
		];
	}

	protected function getSupportedMediaTypeParameterValues()
	{
		if (!isset(self::$supportedMediaTypeParameters))
		{
			self::$supportedMediaTypeParameters = [
				self::MEDIA_TYPE => [
					SerializationParameter::PRE_TRANSFORM_RECURSION_LIMIT => true
				]
			];
		}

		return self::$supportedMediaTypeParameters;
	}

	protected function buildFileExtensionList()
	{
		return [
			'txt',
			'plain'
		];
	}

	private static $supportedMediaTypeParameters;
}

