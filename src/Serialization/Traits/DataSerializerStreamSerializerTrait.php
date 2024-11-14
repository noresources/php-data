<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Serialization\SerializationException;
use NoreSources\MediaType\MediaTypeInterface;

trait DataSerializerStreamSerializerTrait
{

	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		$serialized = $this->serializeData($data, $mediaType);
		$error = \json_last_error();

		$written = @\fwrite($stream, $serialized);
		if ($written === false)
		{
			$error = \error_get_last();
			throw new SerializationException($error['message']);
		}
	}
}
