<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\MediaType\MediaTypeInterface;

/**
 * Provide data deserialization from given file format(s)
 */
interface FileUnserializerInterface
{

	/**
	 *
	 * @param string $filename
	 *        	Input file path
	 * @param MediaTypeInterface $mediaType
	 *        	File content type
	 * @return boolean TRUE if instance can unserialize file type
	 */
	function isUnserializableFromFile($filename,
		?MediaTypeInterface $mediaType = null);

	/**
	 *
	 * @param string $filename
	 *        	File to UnserializeExceptionArrayObjectAsset
	 * @param MediaTypeInterface $mediaType
	 *        	File content tyep
	 * @throws SerializationException::
	 * @return mixed
	 */
	function unserializeFromFile($filename,
		?MediaTypeInterface $mediaType = null);
}
