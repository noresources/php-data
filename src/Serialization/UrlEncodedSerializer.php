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
use NoreSources\Data\Serialization\Traits\StreamUnserializerBaseTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerDataUnserializerTrait;
use NoreSources\Data\Serialization\Traits\UnserializableMediaTypeTrait;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\Data\Utility\Traits\MediaTypeListTrait;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\Type\TypeConversion;

/**
 * URL-encoded query parameter (de)serialization
 *
 * @see https://datatracker.ietf.org/doc/html/rfc3986
 */
class UrlEncodedSerializer implements UnserializableMediaTypeInterface,
	SerializableMediaTypeInterface, DataUnserializerInterface,
	DataSerializerInterface, StreamSerializerInterface,
	StreamUnserializerInterface, MediaTypeListInterface
{
	use MediaTypeListTrait;

	use UnserializableMediaTypeTrait;
	use SerializableMediaTypeTrait;

	use StreamSerializerBaseTrait;
	use StreamSerializerDataSerializerTrait;

	use StreamUnserializerBaseTrait;
	use StreamUnserializerDataUnserializerTrait;

	public function unserializeFromStream($stream,
		MediaTypeInterface $mediaType = null)
	{
		return $this->unserializeData(\stream_get_contents($stream),
			$mediaType);
	}

	public function unserializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$e = \strpos($data, '=');

		if ($e !== false && $e > 0)
		{
			$params = [];
			\parse_str($data, $params);
			return $params;
		}

		return \urldecode($data);
	}

	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		return \fwrite($stream, $this->serializedata($data, $mediaType));
	}

	public function serializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		if (Container::isArray($data))
			return \http_build_query(Container::createArray($data));

		return \urlencode(TypeConversion::toString($data));
	}

	public function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				'application/x-www-form-urlencoded')
		];
	}
}
