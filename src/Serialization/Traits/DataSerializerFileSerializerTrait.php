<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\MediaType\MediaTypeInterface;

trait DataSerializerFileSerializerTrait
{

	use FileSerializerTrait;

	public function isSerializableToFile($filename, $data,
		?MediaTypeInterface $mediaType = null)
	{
		return $this->defaultIsSerializableToFile($filename, $data,
			$mediaType);
	}

	public function serializeToFile($filename, $data,
		?MediaTypeInterface $mediaType = null)
	{}
}
