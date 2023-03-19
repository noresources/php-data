<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Serialization\DataSerializationException;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Implements DataUnserializerInterface using
 * StreamSerializerInterface API
 */
trait StreamSerializerFileSerializerTrait
{

	/**
	 *
	 * @deprecated Use $this->getSerializableMediaTypes
	 */
	public function getSerializableFileMediaTypes()
	{
		return $this->getSerializableMediaTypes();
	}

	public function canSerializeToFile($filename, $data,
		MediaTypeInterface $mediaType = null)
	{
		$testExtension = $this instanceof FileExtensionListInterface &&
			\is_string($filename);
		if ($testExtension && !$mediaType)
		{
			return $this->matchFileExtension(
				\pathinfo($filename, PATHINFO_EXTENSION));
		}

		if ($this instanceof MediaTypeListInterface)
		{
			if (!$mediaType)
			{
				try
				{
					$mediaType = MediaTypeFactory::getInstance()->createFromMedia(
						$filename, MediaTypeFactory::FROM_EXTENSION);
				}
				catch (MediaTypeException $e)
				{}
			}

			if ($mediaType)
				return $this->matchMediaType($mediaType);
		}

		if ($testExtension)
			return $this->matchFileExtension(
				\pathinfo($filename, PATHINFO_EXTENSION));

		// No restrictions, assumes OK
		return true;
	}

	public function serializeToFile($filename, $data,
		MediaTypeInterface $mediaType = null)
	{
		$stream = @\fopen($filename, 'wb');
		if ($stream === false)
		{
			$error = \error_get_last();
			throw new DataSerializationException($error['message']);
		}

		@\flock($stream, LOCK_EX);

		try
		{
			$this->serializeToStream($stream, $data, $mediaType);
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
