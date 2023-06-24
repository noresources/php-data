<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Utility;

use NoreSources\MediaType\MediaTypeInterface;

interface MediaTypeListInterface
{

	/**
	 *
	 * @return MediaRange[]
	 */
	function getMediaTypes();

	/**
	 *
	 * @param MediaTypeInterface $mediaType
	 *        	Media type ty match
	 * @return TRUE if $mediaType match with at least one media type of the list
	 */
	function matchMediaType(MediaTypeInterface $mediaType);

	/**
	 *
	 * @param MediaTypeInterface $mediaType
	 *        	A media type supported by the class
	 * @param string $parameter
	 *        	Media type parameter name
	 * @param string|null $value
	 *        	The parameter value
	 *
	 * @return TRUE if class supports the Media type parameter $paramter with (if set) the $value
	 *         value.
	 */
	function supportsMediaTypeParameter(MediaTypeInterface $mediaType,
		$parameter, $value = null);
}
