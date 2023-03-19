<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\MediaType\MediaTypeInterface;

/**
 * Implements DataSerializerInterface usin g
 * StreamSerializerInterface API
 */
trait StreamSerializerDataSerializerTrait
{

	/**
	 *
	 * @deprecated Use $this->getSerializableMediaTypes
	 */
	public function getSerializableDataMediaTypes()
	{
		return $this->getSerializableMediaTypes();
	}

	/**
	 *
	 * @deprecated Use isSerializable($data, $mediaType);
	 */
	public function canSerializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		return $this->isSerializable($data, $mediaType);
	}

	public function serializeData($data,
		MediaTypeInterface $mediaType = null)
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
