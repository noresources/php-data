<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Assumes class using this trait also use MediaTypeListTrait
 * or a compatible feature implementation
 */
trait StreamUnserializerMediaTypeTrait
{

	public function getUnserializableMediaTypes()
	{
		return $this->getMediaTypes();
	}

	public function isUnserializable($stream,
		MediaTypeInterface $mediaType = null)
	{
		if (!($this instanceof MediaTypeListInterface))
			return true;

		if (!$mediaType)
		{
			try
			{
				$mediaType = MediaTypeFactory::getInstance()->createFromMedia($stream);
			}
			catch (MediaTypeException $e)
			{}
		}

		if ($mediaType)
			return $this->matchMediaType($mediaType);

		return true;
	}
}
