<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Data\Primitifier;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\Type\TypeConversion;

trait PrimitifyTrait
{

	public function primitifyData($data,
		MediaTypeInterface $mediaType = null)
	{
		if (!$mediaType)
			return $data;

		$key = 'preprocess-depth';
		$p = $mediaType->getParameters();
		if (!$p->has($key))
			return $data;

		$maxDepth = TypeConversion::toInteger($p->get($key));
		$primitifier = $this->createPrimitifier();
		if ($maxDepth == 0)
			return $data;
		$primitifier->setRecursionLimit($maxDepth);
		return $primitifier($data);
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
