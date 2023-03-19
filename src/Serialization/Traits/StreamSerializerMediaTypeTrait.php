<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Utility\MediaTypeListInterface;

/**
 * Assumes class using this trait also use MediaTypeListTrait
 * or a compatible feature implementation
 */
trait StreamSerializerMediaTypeTrait
{

	public function getSerializableMediaTypes()
	{
		return $this->getMediaTypes();
	}

	public function isSerializable($data, $mediaType = null)
	{
		if ($mediaType && ($this instanceof MediaTypeListInterface))
			return $this->matchMediaType($mediaType);

		return true;
	}
}
