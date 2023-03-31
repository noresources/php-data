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
 * Generic implementation of the basic methods of StreamSerializerInterface
 */
trait StreamSerializerBaseTrait
{

	/**
	 *
	 * @return MediaTypeInterface[]
	 */
	public function getSerializableMediaTypes()
	{
		if ($this instanceof MediaTypeListInterface)
			return $this->getMediaTypes();
		return [];
	}

	/**
	 *
	 * @param MediaTypeInterface $mediaType
	 *        	Media type to check
	 * @return void|boolean TRUE if media type match with at least one of the supported media range
	 *         or if the structured syntax suffix of the media type is present in at least one of
	 *         the supported media range
	 */
	public function isMediaTypeSerializable(
		MediaTypeInterface $mediaType)
	{
		$list = $this->getSerializableMediaTypes();
		if (!empty($syntax))
		{
			foreach ($list as $mediaRange)
			{
				$s = $mediaRange->getStructuredSyntax(true);
				if ($syntax == $s)
					return true;
			}
		}
		$matcher = new MediaTypeMatcher($mediaType);
		return $matcher->match($list);
	}

	public function isSerializable($data,
		MediaTypeInterface $mediaType = null)
	{
		if ($mediaType)
			return $this->isMediaTypeSerializable($mediaType);
		return true;
	}
}
