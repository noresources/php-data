<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Utility;

use NoreSources\MediaType\MediaRange;
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
}
