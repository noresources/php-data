<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\MediaType\MediaTypeInterface;

/**
 * Media type related serializer capabilities
 */
interface SerializableMediaTypeInterface
{

	/**
	 * Indicates if the serializer supports data serialization to the given media type.
	 *
	 * @param MediaTypeInterface $mediaType
	 *        	Target media type.
	 * @return boolean TRUE if t the given media type can be serialized
	 */
	function isMediaTypeSerializable(MediaTypeInterface $mediaType);

	/**
	 * Get the list of media ranges supported by the serializer class.
	 *
	 * @return MediaTypeInterface[]
	 */
	function getSerializableMediaRanges();
}
