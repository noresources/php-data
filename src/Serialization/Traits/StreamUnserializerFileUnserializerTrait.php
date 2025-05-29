<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Serialization\SerializationException;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Implements FileUnserializerInterface methods using StreamUnserializerInterface
 */
trait StreamUnserializerFileUnserializerTrait
{
	use FileUnserializerTrait;

	public function unserializeFromFile($filename,
		?MediaTypeInterface $mediaType = null)
	{
		$stream = @\fopen($filename, 'rb');
		if ($stream === false)
		{
			$error = \error_get_last();
			throw new SerializationException($error['message']);
		}

		$data = null;
		\flock($stream, LOCK_SH);
		try
		{
			$data = $this->unserializeFromStream($stream, $mediaType);
		}
		catch (\Exception $e)
		{
			\flock($stream, LOCK_UN);
			@\fclose($stream);
			throw $e;
		}

		\flock($stream, LOCK_UN);
		@\fclose($stream);
		return $data;
	}
}
