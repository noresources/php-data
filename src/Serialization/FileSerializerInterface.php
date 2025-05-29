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
 * Provide data serialization to a given file format
 */
interface FileSerializerInterface
{

	/**
	 * Indicates if the class can serialize the given content to the target file format given by
	 * file name or media type.
	 *
	 * @param string $filename
	 *        	Output file path
	 *        	* @param mixed $data
	 *        	Data to srialize
	 * @param MediaTypeInterface $mediaType
	 *        	Target content type
	 *
	 */
	function isSerializableToFile($filename, $data,
		?MediaTypeInterface $mediaType = null);

	/**
	 *
	 * @param string $filename
	 *        	Target file path
	 * @param MediaTypeInterface $mediaType
	 *        	File content type
	 */
	function serializeToFile($filename, $data,
		?MediaTypeInterface $mediaType = null);
}
