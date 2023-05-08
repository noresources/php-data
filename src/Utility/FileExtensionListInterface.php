<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Utility;

interface FileExtensionListInterface
{

	/**
	 * Get a list of supportee file extenions
	 *
	 * Extension names MUST be lowercase
	 *
	 * @return string[] List of file extensions
	 */
	function getFileExtensions();

	/**
	 * Extension matching MUST be case insensitive.
	 *
	 * @return boolean TRUE if $extension is one of the supported file extensions
	 */
	function matchFileExtension($extension);
}
