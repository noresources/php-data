<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\MediaType\MediaTypeInterface;

/**
 * Provide data serialization for one or mode content type
 */
interface DataSerializerInterface
{

	/**
	 * Indicate if the given data can be unserialized to the given media type
	 *
	 * @param mixed $data
	 *        	Data to serialize
	 * @param MediaTypeInterface $mediaType
	 *        	Data output format
	 * @return boolean TRUE if the instance can serialize $data to $mediaType format
	 */
	function isSerializableTo($data,
		?MediaTypeInterface $mediaType = null);

	/**
	 * Serialize data to a given media type
	 *
	 * @param mixed $data
	 *        	Data to serialize
	 * @param MediaTypeInterface $mediaType
	 *        	Serialization content type
	 * @thros SerializationException
	 * @return string
	 */
	function serializeData($data, ?MediaTypeInterface $mediaType = null);
}
