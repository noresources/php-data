<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Utility\Traits;

use NoreSources\Container\Container;
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

	public function supportsMediaTypeParameter(
		MediaTypeInterface $mediaType, $parameter, $value = null)
	{
		$map = $this->getSupportedMediaTypeParameterValues();

		if (!Container::isTraversable($map))
			return false;
		$key = \strtolower(\strval($mediaType));
		if (!Container::keyExists($map, $key))
			return false;
		$map = $map[$key];

		$parameter = \strtolower($parameter);
		if (!Container::keyExists($map, $parameter))
			return false;
		if ($value === null)
			return true;

		$list = $map[$parameter];
		if ($list === true)
			return true;

		$value = \strtolower($value);
		if (\is_string($list))
			return ($list === $value);

		if (!Container::isTraversable($list))
			return false;
		return Container::valueExists($list, $value);
	}

	protected function getSupportedMediaTypeParameterValues()
	{
		return null;
	}

	/**
	 *
	 * @var array<string, MediaTypeInterface>
	 */
	private $mediaTypes;
}
