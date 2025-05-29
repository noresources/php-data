<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Serialization\SerializableContentInterface;
use NoreSources\Data\Serialization\SerializableMediaTypeInterface;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Implements DataSerializerInterface usin g
 * StreamSerializerInterface API
 */
trait StreamSerializerDataSerializerTrait
{

	public function isSerializableTo($data,
		?MediaTypeInterface $mediaType = null)
	{
		return $this->defaultIsSerializableTo($data, $mediaType);
	}

	private function defaultIsSerializableTo($data,
		?MediaTypeInterface $mediaType = null)
	{
		if ($mediaType &&
			($this instanceof SerializableMediaTypeInterface) &&
			!$this->isMediaTypeSerializable($mediaType))
			return false;
		if ($this instanceof SerializableContentInterface)
			return $this->isContentSerializable($data);
		return true;
	}

	public function serializeData($data,
		?MediaTypeInterface $mediaType = null)
	{
		$stream = @\fopen('php://memory', 'w');
		try
		{
			$this->serializeToStream($stream, $data, $mediaType);
		}
		catch (\Exception $e)
		{
			\fclose($stream);
			throw $e;
		}

		@\fseek($stream, 0);
		$serialized = \stream_get_contents($stream);
		\fclose($stream);
		return $serialized;
	}
}
