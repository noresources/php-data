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
use NoreSources\Data\Serialization\Traits\StreamSerializerBaseTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerFileUnserializerTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerDataUnserializerTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerBaseTrait;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\Data\Utility\Traits\FileExtensionListTrait;
use NoreSources\Data\Utility\Traits\MediaTypeListTrait;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * JSON content and file (de)serialization
 *
 * Supported media type parameters
 * - style=pretty
 *
 * Require json PHP extension.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc3986
 *
 */
class JsonSerializer implements DataUnserializerInterface,
	DataSerializerInterface, FileUnserializerInterface,
	FileSerializerInterface, StreamSerializerInterface,
	StreamUnserializerInterface, MediaTypeListInterface,
	FileExtensionListInterface
{
	use MediaTypeListTrait;
	use FileExtensionListTrait;

	use StreamSerializerBaseTrait;
	use StreamSerializerDataSerializerTrait;
	use StreamSerializerFileSerializerTrait;

	use StreamUnserializerBaseTrait;
	use StreamUnserializerDataUnserializerTrait;
	use StreamUnserializerFileUnserializerTrait;

	const STYLE_PRETTY = 'pretty';

	public function __construct()
	{}

	public static function prerequisites()
	{
		return \extension_loaded('json');
	}

	///////////////////////////////////////////////////
	// StreamSerializer
	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		$flags = 0;
		if ($mediaType &&
			($style = Container::keyValue($mediaType->getParameters(),
				'style')) &&
			(\strcasecmp($style, self::STYLE_PRETTY) == 0))
		{
			$flags |= JSON_PRETTY_PRINT;
		}

		$serialized = @\json_encode($data, $flags);
		$error = \json_last_error();
		if ($error != JSON_ERROR_NONE)
			throw new DataSerializationException(json_last_error_msg());

		$written = @\fwrite($stream, $serialized);
		if ($written === false)
		{
			$error = \error_get_last();
			throw new DataSerializationException($error['message']);
		}
	}

	/////////////////////////////////////////////////
	// StreamUnserializer
	public function unserializeFromStream($stream,
		MediaTypeInterface $mediaType = null)
	{
		$data = @\json_decode(\stream_get_contents($stream), true);
		$error = \json_last_error();
		if ($error != JSON_ERROR_NONE)
			throw new DataSerializationException(json_last_error_msg());
		return $data;
	}

	///////////////////////////////////////////////////
	// DataSerializer

	/**
	 * A more straight forward implementation than the default on in traits.
	 *
	 * {@inheritdoc}
	 * @see \NoreSources\Data\Serialization\DataUnserializerInterface::unserializeData()
	 */
	public function unserializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$data = @\json_decode($data, true);
		$error = \json_last_error();
		if ($error != JSON_ERROR_NONE)
			throw new DataSerializationException(json_last_error_msg());
		return $data;
	}

	/**
	 * Accept any media type with the "json" structured syntax declaration
	 *
	 * @param MediaTypeInterface $mediaType
	 * @return boolean
	 */
	public function matchMediaType(MediaTypeInterface $mediaType)
	{
		$syntax = $mediaType->getStructuredSyntax();
		if (\strcasecmp($syntax, 'json') == 0)
			return true;
		return $this->matchMediaTypeList($mediaType);
	}

	protected function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString('application/json')
		];
	}

	protected function buildFileExtensionList()
	{
		return [
			'json',
			'jsn'
		];
	}
}
