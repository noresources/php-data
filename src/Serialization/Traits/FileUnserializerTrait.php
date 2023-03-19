<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Partially implements FileUnserializerInterface
 */
trait FileUnserializerTrait
{

	public function canUnserializeFromFile($filename,
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
						$filename,
						$this->getMediaTypeFactoryFlagsForFile());
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

	protected function getMediaTypeFactoryFlagsForFile()
	{
		return MediaTypeFactory::FROM_ALL;
	}
}
