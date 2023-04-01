<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\MediaType\MediaTypeInterface;

/**
 * Media type related unserializer capabilities
 */
interface UnserializableMediaTypeInterface
{

	/**
	 * Indicates if the unserializer supports data unserialization to the given media type.
	 *
	 * @param MediaTypeInterface $mediaType
	 *        	Target media type.
	 * @return boolean TRUE if t the given media type can be unserialized
	 */
	function isMediaTypeUnserializable(MediaTypeInterface $mediaType);

	/**
	 * Get the list of media ranges supported by the unserializer class.
	 *
	 * @return MediaTypeInterface[]
	 */
	function getUnserializableMediaRanges();
}
