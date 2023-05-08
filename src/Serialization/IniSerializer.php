<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

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
 * INI deserialization.
 */
class IniSerializer implements UnserializableMediaTypeInterface,
	DataUnserializerInterface, FileUnserializerInterface,
	StreamUnserializerInterface, MediaTypeListInterface,
	FileExtensionListInterface
{
	use MediaTypeListTrait;
	use FileExtensionListTrait;

	use UnserializableMediaTypeTrait;

	use StreamUnserializerBaseTrait;
	use StreamUnserializerDataUnserializerTrait;
	use StreamUnserializerFileUnserializerTrait;

	public function __construct()
	{}

	public function unserializeFromFile($filename,
		MediaTypeInterface $mediaType = null)
	{
		$data = @\parse_ini_file($filename, true);
		if ($data === false)
		{
			$error = \error_get_last();
			throw new SerializationException($error['message']);
		}
		return $data;
	}

	public function unserializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$data = @parse_ini_string($data, true);
		if ($data === false)
		{
			$error = \error_get_last();
			throw new SerializationException($error['message']);
		}
		return $data;
	}

	public function unserializeFromStream($stream,
		MediaTypeInterface $mediaType = null)
	{
		return $this->isUnserializableFrom(\stream_get_contents($stream),
			$mediaType);
	}

	public function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				'text/x-ini'),
			MediaTypeFactory::getInstance()->createFromString(
				'application/x-wine-extension-ini')
		];
	}

	public function buildFileExtensionList()
	{
		return [
			'ini'
		];
	}
}
