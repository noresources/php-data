<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Serialization\DataSerializationException;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Implementation of legacy DataUnserializerInterface that uses the new StreamUnserializer API
 */
trait StreamUnserializerDataUnserializerTrait
{

	/**
	 *
	 * @deprecated Use $this->getUnserializableMediaTypes
	 */
	public function getUnserializableDataMediaTypes()
	{
		return $this->getUnserializableMediaTypes();
	}

	/**
	 *
	 * @deprecated Use isUnserializable($data, $mediaType);
	 */
	public function canUnserializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		return $this->isUnserializable($data, $mediaType);
	}

	public function unserializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$mt = '';
		if ($mediaType)
			$mt = \strval($mediaType);

		$stream = @\fopen('data://' . $mt . ',' . $data, 'r');
		if ($stream === false)
		{
			$error = error_get_last();
			throw new DataSerializationException(
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
