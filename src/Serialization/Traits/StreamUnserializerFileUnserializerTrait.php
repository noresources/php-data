<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Serialization\DataSerializationException;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Use the new StreamUnserializerInterface to implement the legacy FileUnserializerInterface
 */
trait StreamUnserializerFileUnserializerTrait
{
	use FileUnserializerTrait;

	/**
	 *
	 * @deprecated Use $this->getUnserializableMediaTypes
	 */
	public function getUnserializableFileMediaTypes()
	{
		return $this->getUnserializableMediaTypes();
	}

	public function unserializeFromFile($filename,
		MediaTypeInterface $mediaType = null)
	{
		$stream = @\fopen($filename, 'rb');
		if ($stream === false)
		{
			$error = \error_get_last();
			throw new DataSerializationException($error['message']);
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
