<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Container\Container;
use NoreSources\Data\Serialization\Traits\StreamSerializerFileSerializerTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerDataSerializerTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerMediaTypeTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerFileUnserializerTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerDataUnserializerTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerMediaTypeTrait;
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
class PlainTextSerializer implements DataUnserializerInterface,
	DataSerializerInterface, FileUnserializerInterface,
	FileSerializerInterface, StreamSerializerInterface,
	StreamUnserializerInterface, FileExtensionListInterface,
	MediaTypeListInterface
{
	use MediaTypeListTrait;
	use FileExtensionListTrait;

	use StreamSerializerMediaTypeTrait;
	use StreamSerializerDataSerializerTrait;
	use StreamSerializerFileSerializerTrait;

	use StreamUnserializerMediaTypeTrait;
	use StreamUnserializerDataUnserializerTrait;
	use StreamUnserializerFileUnserializerTrait;

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
			MediaTypeFactory::getInstance()->createFromString('text/plain')
		];
	}

	protected function buildFileExtensionList()
	{
		return [
			'txt',
			'plain'
		];
	}
}

