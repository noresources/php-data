<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\MediaType\MediaTypeInterface;

interface StreamSerializerInterface
{

	/**
	 * Indicates if the given data can be serialized
	 *
	 * Implementations MAY check check the data type.
	 * Implementations MUST return FALSE if the provided media type is not compatible with the
	 * serialier supported media types.
	 *
	 * @param resource $stream
	 *        	T.arget stream
	 * @param mixed $data
	 *        	Input data.
	 * @param MediaTypeInterface|NULL $mediaType
	 *        	Target media type
	 * @return TRUE if the given data can be serialized to the given data type
	 */
	function isSerializableToStream($stream, $data,
		?MediaTypeInterface $mediaType = null);

	/**
	 * Serialize data to the given stream
	 *
	 * @param resource $stream
	 *        	Stream to write into
	 * @param mixed $data
	 *        	Data to write to stream
	 * @param MediaTypeInterface|NULL $mediaType
	 *        	Target media type
	 */
	function serializeToStream($stream, $data,
		?MediaTypeInterface $mediaType = null);
}
