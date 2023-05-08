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
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Generic implementation of the basic methods of StreamSerializerInterface
 */
trait StreamSerializerBaseTrait
{

	public function isSerializableToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		if (($this instanceof SerializableContentInterface) &&
			!$this->isContentSerializable($data))
			return false;

		if (!$mediaType)
		{
			try
			{
				$factory = MediaTypeFactory::getInstance();
				$mediaType = $factory->createFromMedia($stream);
			}
			catch (MediaTypeException $e)
			{}
		}

		if ($mediaType &&
			($this instanceof SerializableMediaTypeInterface))
			return $this->isMediaTypeSerializable($mediaType);

		return true;
	}
}
