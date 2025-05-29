<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Container\Container;
use NoreSources\Data\Primitifier;
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
use NoreSources\MediaType\MediaTypeMatcher;

/**
 * JSON content and file (de)serialization
 *
 * Supported media type parameters
 * <ul>
 * <li>style=pretty: (serializer only) Use pretty print output</li>
 * <li>preprocess-depth=int: (serializer only) Define if data should be pre-transformed before
 * invoking json_encode function.
 * <ul>
 * <li>0: Do not transform data</li>
 * <li>&gt; 0: Transform data recursively. The given value define the recursion depth limit.</li>
 * <li>&lt 0: Transform data recursively without recursion limit.</li>
 * </ul>
 * </li>
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

	use PrimitifyTrait;

	/**
	 * Default JSON media type.
	 * This serializer suppots any media type with a +json syntax suffix
	 *
	 * @var string
	 */
	const MEDIA_TYPE = 'application/json';

	/**
	 * Controls the maximum depth of serialization preprocess
	 *
	 * Default is unlimited
	 *
	 * @var string
	 */
	const PARAMETER_DEPTH = SerializationParameter::PRE_TRANSFORM_RECURSION_LIMIT;

	const PARAMETER_STYLE = SerializationParameter::PRESENTATION_STYLE;

	const STYLE_PRETTY = SerializationParameter::PRESENTATION_STYLE_PRETTY;

	public function __construct()
	{}

	public static function prerequisites()
	{
		return \extension_loaded('json');
	}

	public static function preprocessEntry($entry)
	{
		if ($entry instanceof \JsonSerializable)
			return $entry->jsonSerialize();
		return $entry;
	}

	///////////////////////////////////////////////////
	// StreamSerializer
	public function serializeToStream($stream, $data,
		?MediaTypeInterface $mediaType = null)
	{
		$flags = 0;
		if ($mediaType)
		{
			$p = $mediaType->getParameters();

			if (($style = Container::keyValue($p, 'style')) &&
				(\strcasecmp($style, self::STYLE_PRETTY) == 0))
				$flags |= JSON_PRETTY_PRINT;
		}

		$data = $this->primitifyData($data, $mediaType);

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
		?MediaTypeInterface $mediaType = null)
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
		?MediaTypeInterface $mediaType = null)
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
		if (\is_string($syntax) && \strcasecmp($syntax, 'json') === 0)
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

	protected function preprocessDataForEncoding($data, $depth)
	{
		if ($data instanceof \DateTimeInterface)
			return $data->format(\DateTime::ISO8601);

		if ($depth == 0)
			return $data;

		if ($data instanceof \JsonSerializable)
			$data = $data->jsonSerialize();

		if (!Container::isTraversable($data))
			return $data;

		return Container::mapValues($data,
			function ($v) use ($depth) {
				return $this->preprocessDataForEncoding($v,
					\max(-1, $depth - 1));
			});
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
					self::PARAMETER_STYLE => self::STYLE_PRETTY,
					self::PARAMETER_DEPTH => true
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

	protected function createPrimitifier()
	{
		$primitifier = new Primitifier();
		$primitifier->setEntryPreprocessor(
			[
				self::class,
				'preprocessEntry'
			]);
		return $primitifier;
	}
}
