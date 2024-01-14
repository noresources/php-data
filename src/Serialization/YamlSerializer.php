<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Container\Container;
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
use NoreSources\MediaType\MediaTypeMatcher;

/**
 * YAML content and file (de)serialization.
 *
 * Require the yaml extension
 */
class YamlSerializer implements UnserializableMediaTypeInterface,
	SerializableMediaTypeInterface, DataUnserializerInterface,
	DataSerializerInterface, FileSerializerInterface,
	FileUnserializerInterface, StreamSerializerInterface,
	StreamUnserializerInterface, FileExtensionListInterface,
	MediaTypeListInterface
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

	const MEDIA_TYPE = 'text/yaml';

	const MEDIA_TYPE_UNREGISTERED = 'text/x-yaml';

	const MEDIA_TYPE_ALTERNATIVE = 'application/yaml';

	/**
	 * Encoding
	 *
	 * @var string
	 */
	const PARAMETER_ENCODING = 'charset';

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
		$result = @\fwrite($stream,
			$this->serializeData($data, $mediaType));
		if ($result === false)
		{
			$error = \error_get_last();
			throw new SerializationException($error['message']);
		}
	}

	public function serializeToFile($filename, $data,
		MediaTypeInterface $mediaType = null)
	{
		$encoding = $this->getEncoding($mediaType);
		$result = @\yaml_emit_file($filename, $data, $encoding);
		if ($result !== true)
			throw new SerializationException('Failed to emit YAML file');
	}

	public function unserializeFromStream($stream,
		MediaTypeInterface $mediaType = null)
	{
		return \yaml_parse(\stream_get_contents($stream));
	}

	public function serializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$encoding = $this->getEncoding($mediaType);
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

	public function isMediaTypeSerializable(
		MediaTypeInterface $mediaType)
	{
		$syntax = $mediaType->getStructuredSyntax(false);
		if (\is_string($syntax) && \strcasecmp($syntax, 'yaml') === 0)
			return true;
		$list = $this->getSerializableMediaRanges();
		$matcher = new MediaTypeMatcher($mediaType);
		return $matcher->match($list);
	}

	public function isMediaTypeUnserializable(
		MediaTypeInterface $mediaType)
	{
		return $this->isMediaTypeSerializable($mediaType);
	}

	public function buildMediaTypeList()
	{
		$factory = MediaTypeFactory::getInstance();
		return [
			$factory->createFromString(self::MEDIA_TYPE),
			$factory->createFromString(self::MEDIA_TYPE_UNREGISTERED),
			$factory->createFromString(self::MEDIA_TYPE_ALTERNATIVE)
		];
	}

	protected function getSupportedMediaTypeParameterValues()
	{
		if (!isset(self::$supportedMediaTypeParameters))
		{
			self::$supportedMediaTypeParameters = [];
			foreach ([
				self::MEDIA_TYPE,
				self::MEDIA_TYPE_UNREGISTERED,
				self::MEDIA_TYPE_ALTERNATIVE
			] as $mediaType)
			{
				self::$supportedMediaTypeParameters[$mediaType] = [
					self::PARAMETER_ENCODING => [
						'utf-8',
						'utf-16',
						'utf-16le',
						'utf-16be'
					]
				];
			}
		}

		return self::$supportedMediaTypeParameters;
	}

	public function buildFileExtensionList()
	{
		return [
			'yaml',
			'yml'
		];
	}

	protected function getEncoding(MediaTypeInterface $mediaType = null)
	{
		$encoding = $this->encoding;
		$charset = null;
		if (!($mediaType &&
			($charset = Container::keyValue($mediaType->getParameters(),
				self::PARAMETER_ENCODING))))
			return $encoding;

		switch (\strtolower($charset))
		{
			case 'utf-8':
				$encoding = YAML_UTF8_ENCODING;
			case 'utf-16':
			case 'utf-16-be':
				$encoding = YAML_UTF16BE_ENCODING;
			break;
			case 'utf-16-le':
				$encoding = YAML_UTF16LE_ENCODING;
			break;
		}
		return $encoding;
	}

	private static $supportedMediaTypeParameters;
}
