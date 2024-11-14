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
 * Implementation of FileSerializerInterface using StreamSerializerInterface methods
 */
trait StreamSerializerFileSerializerTrait
{

	use FileSerializerTrait;

	public function getFileStreamFooter()
	{
		return null;
	}

	public function serializeToFile($filename, $data,
		MediaTypeInterface $mediaType = null)
	{
		$stream = @\fopen($filename, 'wb');
		if ($stream === false)
		{
			$error = \error_get_last();
			throw new SerializationException($error['message']);
		}

		@\flock($stream, LOCK_EX);

		try
		{
			$this->serializeToStream($stream, $data, $mediaType);
			$foorter = $this->getFileStreamFooter();
			if (!empty($foorter))
				@\fwrite($stream, $foorter);
		}
		catch (\Exception $e)
		{
			@\flock($stream, LOCK_UN);
			@\fclose($stream);
			throw $e;
		}

		@\flock($stream, LOCK_UN);
		@\fclose($stream);
	}
}
