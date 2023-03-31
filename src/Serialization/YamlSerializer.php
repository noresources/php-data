<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Data\Serialization\Traits\StreamSerializerDataSerializerTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerFileSerializerTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerMediaTypeTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerDataUnserializerTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerFileUnserializerTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerMediaTypeTrait;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\Data\Utility\Traits\FileExtensionListTrait;
use NoreSources\Data\Utility\Traits\MediaTypeListTrait;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * YAML content and file (de)serialization.
 *
 * Require the yaml extension
 */
class YamlSerializer implements DataUnserializerInterface,
	DataSerializerInterface, FileSerializerInterface,
	FileUnserializerInterface, StreamSerializerInterface,
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

	/**
	 * Default encoding
	 *
	 * @var string
	 */
	public $encoding = YAML_ANY_ENCODING;

	public function __construct()
	{}

	public static function prerequisites()
	{
		return \extension_loaded('yaml');
	}

	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		\fwrite($stream, $this->serializeData($data, $mediaType));
	}

	public function serializeToFile($filename, $data,
		MediaTypeInterface $mediaType = null)
	{
		$encoding = $this->encoding;
		if ($mediaType &&
			$mediaType->getParameters()->offsetExists('charset'))
		{
			$charset = $mediaType->getParameters()->offsetGet('charset');
			if (\strcasecmp($charset, 'utf-8') == 0)
				$encoding = YAML_UTF8_ENCODING;
		}
		return \yaml_emit_file($filename, $data, $encoding);
	}

	public function unserializeFromStream($stream,
		MediaTypeInterface $mediaType = null)
	{
		return \yaml_parse(\stream_get_contents($stream));
	}

	public function serializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$encoding = $this->encoding;
		if ($mediaType &&
			$mediaType->getParameters()->offsetExists('charset'))
		{
			$charset = $mediaType->getParameters()->offsetGet('charset');
			if (\strcasecmp($charset, 'utf-8') == 0)
				$encoding = YAML_UTF8_ENCODING;
		}

		return \yaml_emit($data, $encoding);
	}

	public function unserializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		return \yaml_parse($data);
	}

	protected function getMediaTypeFactoryFlagsForFile()
	{
		return MediaTypeFactory::FROM_ALL |
			MediaTypeFactory::FROM_EXTENSION_FIRST;
	}

	public function matchMediaType(MediaTypeInterface $mediaType)
	{
		$syntax = $mediaType->getStructuredSyntax();
		if (\strcasecmp($syntax, 'yaml') == 0)
			return true;
		return $this->matchMediaTypeList($mediaType);
	}

	public function buildMediaTypeList()
	{
		$factory = MediaTypeFactory::getInstance();
		return [
			$factory->createFromString('text/x-yaml'),
			$factory->createFromString('text/yaml'),
			$factory->createFromString('application/yaml')
		];
	}

	public function buildFileExtensionList()
	{
		return [
			'yaml',
			'yml'
		];
	}
}
