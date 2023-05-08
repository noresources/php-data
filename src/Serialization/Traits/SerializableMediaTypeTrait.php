<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\MediaType\MediaTypeMatcher;

/**
 * Generic implementation of SerializableMediaTypeInterface
 */
trait SerializableMediaTypeTrait
{

	public function isMediaTypeSerializable(
		MediaTypeInterface $mediaType)
	{
		$list = $this->getSerializableMediaRanges();
		$matcher = new MediaTypeMatcher($mediaType);
		return $matcher->match($list);
	}

	public function getSerializableMediaRanges()
	{
		if ($this instanceof MediaTypeListInterface)
			return $this->getMediaTypes();
		return [];
	}
}
