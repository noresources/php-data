<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Utility\Traits;

use NoreSources\MediaType\MediaTypeInterface;

/**
 * Implements MediaTypeListInterface
 */
trait MediaTypeListTrait
{

	public function getMediaTypes()
	{
		if (!isset($this->mediaTypes))
		{
			$list = $this->mediaTypes = $this->buildMediaTypeList();
			$this->mediaTypes = [];
			foreach ($list as $type)
				$this->mediaTypes[\strval($type)] = $type;
		}
		return $this->mediaTypes;
	}

	public function matchMediaType(MediaTypeInterface $mediaType)
	{
		return $this->matchMediaTypeList($mediaType);
	}

	protected function matchMediaTypeList(MediaTypeInterface $mediaType)
	{
		$mediaTypes = $this->getMediaTypes();
		foreach ($mediaTypes as $type)
		{
			if ($mediaType->match($type))
				return true;
		}

		return false;
	}

	protected function buildMediaTypeList()
	{
		throw new \LogicException('Not implementaed');
	}

	/**
	 *
	 * @var array<string, MediaTypeInterface>
	 */
	private $mediaTypes;
}
