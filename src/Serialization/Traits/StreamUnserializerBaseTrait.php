<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Serialization\UnserializableMediaTypeInterface;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Generic implementation of StreamUnserializerInterface basic methods.
 */
trait StreamUnserializerBaseTrait
{

	public function isUnserializableFromStream($stream,
		MediaTypeInterface $mediaType = null)
	{
		if (!$mediaType)
		{
			try
			{
				$mediaType = MediaTypeFactory::getInstance()->createFromMedia(
					$stream);
			}
			catch (MediaTypeException $e)
			{}
		}

		if ($mediaType &&
			($this instanceof UnserializableMediaTypeInterface))
			return $this->isMediaTypeUnserializable($mediaType);

		return true;
	}
}
