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
 * JSON content and file (de)serialization
 *
 * Supported media type parameters
 * <ul>
 * <li>style=pretty</li>
 * <ul>
 *
 * Require json PHP extension.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc3986
 *
 */
class JsonSerializer implements UnserializableMediaTypeInterface,
	SerializableMediaTypeInterface, DataUnserializerInterface,
	DataSerializerInterface, FileUnserializerInterface,
	FileSerializerInterface, StreamSerializerInterface,
	StreamUnserializerInterface, MediaTypeListInterface,
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

	const PARAMETER_STYLE = 'style';

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
			throw new SerializationException(json_last_error_msg());

		$written = @\fwrite($stream, $serialized);
		if ($written === false)
		{
			$error = \error_get_last();
			throw new SerializationException($error['message']);
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
			throw new SerializationException(json_last_error_msg());
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
			throw new SerializationException(json_last_error_msg());
		return $data;
	}

	public function isMediaTypeSerializable(
		MediaTypeInterface $mediaType)
	{
		$syntax = $mediaType->getStructuredSyntax();
		if (\strcasecmp($syntax, 'json') === 0)
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

	protected function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				'application/json')
		];
	}

	protected function getSupportedMediaTypeParameterValues()
	{
		if (!isset(self::$supportedMediaTypeParameters))
		{
			self::$supportedMediaTypeParameters = [
				'application/json' => [
					self::PARAMETER_STYLE => self::STYLE_PRETTY
				]
			];
		}

		return self::$supportedMediaTypeParameters;
	}

	private static $supportedMediaTypeParameters;

	protected function buildFileExtensionList()
	{
		return [
			'json',
			'jsn'
		];
	}
}
