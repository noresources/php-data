<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Utility\Traits;

/**
 * Implements interface FileExtensionListInterface
 */
trait FileExtensionListTrait
{

	public function getFileExtensions()
	{
		if (!isset($this->fileExtensions))
			$this->fileExtensions = $this->buildFileExtensionList();
		return $this->fileExtensions;
	}

	public function matchFileExtension($extension)
	{
		return \in_array(\strtolower($extension),
			$this->getFileExtensions());
	}

	protected function buildFileExtensionList()
	{
		throw new \LogicException(
			__METHOD__ . ' must be re-implemented');
	}

	/**
	 *
	 * @var string[]
	 */
	private $fileExtensions;
}
