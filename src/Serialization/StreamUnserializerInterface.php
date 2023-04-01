<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\MediaType\MediaTypeInterface;

interface StreamUnserializerInterface
{

	/**
	 * Indicates if the given stream can be deserialized by this deserializer.
	 *
	 * @param resource|NULL $stream
	 *        	Input data stream
	 * @param MediaTypeInterface|NULL $mediaType
	 *        	Target media type
	 *
	 * @return TRUE if the given data can be deserialized to the given data type
	 *
	 */
	function isUnserializableFromStream($stream,
		MediaTypeInterface $mediaType = null);

	/**
	 *
	 * @param resource $stream
	 *        	Input stream
	 * @param MediaTypeInterface $mediaType
	 *        	Target media type
	 * @return Deserialized data
	 */
	function unserializeFromStream($stream,
		MediaTypeInterface $mediaType = null);
}
