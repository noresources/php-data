<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\MediaType\MediaTypeInterface;

trait DataUnserializerStreamUnserializerTrait
{
	use FileUnserializerTrait;

	public function unserializeFromStream($stream,
		?MediaTypeInterface $mediaType = null)
	{
		return $this->unserializeData(\stream_get_contents($stream),
			$mediaType);
	}
}
