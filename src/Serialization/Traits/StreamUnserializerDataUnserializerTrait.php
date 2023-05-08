<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Serialization\SerializationException;
use NoreSources\Data\Serialization\UnserializableMediaTypeInterface;
use NoreSources\Data\Unserialization\UnserializableContentInterface;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Implementation of DataUnserializerInterface that uses the new StreamUnserializer API
 */
trait StreamUnserializerDataUnserializerTrait
{

	public function isUnserializableFrom($data,
		MediaTypeInterface $mediaType = null)
	{
		if ($mediaType &&
			($this instanceof UnserializableMediaTypeInterface) &&
			!$this->isMediaTypeUnserializable($mediaType))
			return false;
		if ($this instanceof UnserializableContentInterface)
			return $this->isContentUnserializable($mediaType);
		return true;
	}

	public function unserializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$mt = '';
		if ($mediaType)
			$mt = \strval($mediaType);

		$stream = @\fopen('data://' . $mt . ',' . \urlencode($data), 'r');
		if ($stream === false)
		{
			$error = error_get_last();
			throw new SerializationException(
				'Failed to create data stream: ' . $error['message']);
		}
		try
		{
			$data = $this->unserializeFromStream($stream, $mediaType);
		}
		catch (\Exception $e)
		{
			\fclose($stream);
			throw $e;
		}

		\fclose($stream);
		return $data;
	}
}
