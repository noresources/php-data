<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Unserialization;

/**
 */
interface UnserializableContentInterface
{

	/**
	 * Indicates if the unserializer can unserialize the given data type and/or structure.
	 *
	 * @param mixed $data
	 *        	Target content.
	 * @return boolean TRUE if t the given content can be unserialized
	 */
	function isContentUnserializable($data);
}
