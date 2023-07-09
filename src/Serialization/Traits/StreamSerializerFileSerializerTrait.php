<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Serialization\SerializableContentInterface;
use NoreSources\Data\Serialization\SerializableMediaTypeInterface;
use NoreSources\Data\Serialization\SerializationException;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Implementation of FileSerializerInterface using StreamSerializerInterface methods
 */
trait StreamSerializerFileSerializerTrait
{

	public function getFileStreamFooter()
	{
		return null;
	}

	public function isSerializableToFile($filename, $data,
		MediaTypeInterface $mediaType = null)
	{
		return $this->defaultIsSerializableToFile($filename, $data,
			$mediaType);
	}

	private final function defaultIsSerializableToFile($filename, $data,
		MediaTypeInterface $mediaType = null)
	{
		if (($this instanceof SerializableContentInterface) &&
			!$this->isContentSerializable($data))
			return false;

		$testExtension = $this instanceof FileExtensionListInterface &&
			\is_string($filename);
		if ($testExtension && !$mediaType)
		{
			return $this->matchFileExtension(
				\pathinfo($filename, PATHINFO_EXTENSION));
		}

		if (($this instanceof MediaTypeListInterface) ||
			($this instanceof SerializableMediaTypeInterface))
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
			{
				if (($this instanceof SerializableMediaTypeInterface) &&
					!$this->isMediaTypeSerializable($mediaType))
					return false;

				if ($this instanceof MediaTypeListInterface)
				{
					// return $this->matchMediaType($mediaType);
				}
			}
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
