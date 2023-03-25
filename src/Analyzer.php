<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data;

use NoreSources\SingletonTrait;
use NoreSources\Container\Container;

/**
 * Data structure analyzer utility
 *
 * @author renaud
 *
 */
class Analyzer
{
	use SingletonTrait;

	public function getDataMinDepth($data)
	{
		if (!Container::isTraversable($data))
			return 0;
		$depth = -1;
		foreach ($data as $value)
		{
			$d = $this->getDataMinDepth($value);
			if ($depth < 0)
				$depth = $d;
			else
				$depth = \min($depth, $d);
		}

		return \max(0, $depth) + 1;
	}

	/**
	 *
	 * @param mixed $data
	 *        	Data to analyze
	 * @return string[] Nature of container for each data depth. Values are one of
	 *         <ul>
	 *         <li><code>object</code> for associative array or traversable objects</li>
	 *         <li><code>array</code> for indexed arrays</li>
	 *         </ul>
	 */
	public function getDataDimensionTypes($data)
	{
		$depth = $this->getDataMinDepth($data);
		$types = [];
		$level = $data;
		while ($depth--)
		{
			$types[] = Container::isIndexed($level) ? 'array' : 'object';
			$level = Container::firstValue($level);
		}

		return $types;
	}
}
