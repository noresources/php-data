<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Container\Container;
use NoreSources\Data\Primitifier;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\Type\TypeConversion;

/**
 * Reference method to transform any kind of value to a POD value
 */
trait PrimitifyTrait
{

	/**
	 *
	 * @param mixed $data
	 *        	Data to convert to POD value
	 * @param MediaTypeInterface $mediaType
	 * @return mixed|unknown|unknown[]|unknown
	 */
	public function primitifyData($data,
		MediaTypeInterface $mediaType = null)
	{
		if (!$mediaType)
			return $this->primitifyDataFallback($data);

		$key = 'preprocess-depth';
		$p = $mediaType->getParameters();
		if (!$p->has($key))
			return $this->primitifyDataFallback($data);

		$maxDepth = TypeConversion::toInteger($p->get($key));
		$primitifier = $this->createPrimitifier();
		if ($maxDepth == 0)
			return $data;
		$primitifier->setRecursionLimit($maxDepth);
		return $primitifier($data);
	}

	/**
	 * Default data primitification method when media type is not provided or does not gave relevant
	 * informations.
	 *
	 * @param unknown $data
	 *        	Data to convert to POD value
	 * @return mixed
	 */
	public function primitifyDataFallback($data)
	{
		if (!Container::isTraversable($data))
			return $data;
		$map = [];
		foreach ($data as $key => $value)
			$map[$key] = $value;
		return $map;
	}

	/**
	 *
	 * @return Primitifier
	 */
	protected function createPrimitifier()
	{
		return new Primitifier();
	}
}
