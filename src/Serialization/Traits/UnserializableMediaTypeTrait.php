<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\MediaType\MediaTypeMatcher;

/**
 * Generic implementation of UnserializableMediaTypeInterface
 */
trait UnserializableMediaTypeTrait
{

	public function isMediaTypeUnserializable(
		MediaTypeInterface $mediaType)
	{
		$list = $this->getUnserializableMediaRanges();
		$matcher = new MediaTypeMatcher($mediaType);
		return $matcher->match($list);
	}

	public function getUnserializableMediaRanges()
	{
		if ($this instanceof MediaTypeListInterface)
			return $this->getMediaTypes();
		return [];
	}
}
