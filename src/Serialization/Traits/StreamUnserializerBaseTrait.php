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
use NoreSources\MediaType\MediaTypeMatcher;

/**
 * Generic implementation of StreamUnserializerInterface basic methods.
 */
trait StreamUnserializerBaseTrait
{

	/**
	 *
	 * @return MediaTypeInterface[]
	 */
	public function getUnserializableMediaTypes()
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
	public function isMediaTypeUnserializable(
		MediaTypeInterface $mediaType)
	{
		$list = $this->getUnserializableMediaTypes();

		$syntax = $mediaType->getStructuredSyntax(false);
		if (!empty($syntax))
		{
			foreach ($list as $mediaRange)
			{
				$s = $mediaRange->getStructuredSyntax(false);
				if ($syntax == $s)
					return true;
			}
		}
		$matcher = new MediaTypeMatcher($mediaType);
		return $matcher->match($list);
	}

	public function isUnserializable($stream,
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

		if ($mediaType)
		{
			return $this->isMediaTypeUnserializable($mediaType);
		}

		return true;
	}
}
