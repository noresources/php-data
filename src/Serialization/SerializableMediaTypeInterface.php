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

	/**
	 *
	 * @param MediaTypeInterface|string $mediaType
	 *        	A media type supported by the class
	 * @param string $parameter
	 *        	Media type parameter name
	 * @param string|null $value
	 *        	The parameter value
	 *
	 * @return TRUE if class supports the Media type parameter $paramter with (if set) the $value
	 *         value.
	 */
	function isMediaTypeSerializableWithParameter(
		MediaTypeInterface $mediaType, $parameter, $value = null);

	const LIST_MEDIA_RANGE = 0x01;

	/**
	 *
	 * @param MediaTypeInterface[] $expectedMediaRanges
	 *        	A list of expected media ranges. For example, a list of media ranges contained in
	 *        	a HTTP Accept header value.
	 * @param number $flags
	 *        	Result option flags
	 */
	function buildSerialiableMediaTypeListMatchingMediaRanges(
		$expectedMediaRanges, $flags = 0);
}
