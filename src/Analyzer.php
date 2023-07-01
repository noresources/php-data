<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
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

	/**
	 * Data min depth
	 *
	 * @var string
	 */
	const MIN_DEPTH = 'min-depth';

	/**
	 * Data max depth
	 *
	 * @var string
	 */
	const MAX_DEPTH = 'max-depth';

	/**
	 * Content organization
	 *
	 * @var string
	 */
	const COLLECTION_CLASS = 'collection-class';

	/**
	 * Data container properties
	 *
	 * @var string
	 */
	const CONTAINER_PROPERTIES = 'container-properties';

	/**
	 * Gather all properties of input data.
	 *
	 * @param mixed $data
	 *        	Input data
	 * @return number[]|string[] Data properties
	 */
	public function __invoke($data)
	{
		$depth = $this->getMinDepth($data);
		$collectionClass = $this->getCollectionClass($data, $depth);
		$properties = 0;
		if ($depth)
			$properties = Container::properties($data);

		return [
			self::MIN_DEPTH => $depth,
			self::MAX_DEPTH => $this->getMaxDepth($data),
			self::COLLECTION_CLASS => $collectionClass,
			self::CONTAINER_PROPERTIES => $properties
		];
	}

	/**
	 * Get input data container nature.
	 *
	 * @param mixed $data
	 *        	Input data
	 * @param number|NULL $depth
	 *        	Pre-computed data depth
	 * @return number Input data container nature flags
	 */
	public function getCollectionClass($data, $depth = null)
	{
		if ($depth === null)
			$depth = $this->getMinDepth($data);
		$collectionClass = 0;
		if ($depth == 0)
			return $collectionClass;

		if (Container::isAssociative($data, true, false))
		{
			$collectionClass |= CollectionClass::ASSOCIATIVE;
			$dictionary = false;
			foreach ($data as $key => $_)
			{
				if (!\is_string($key))
				{
					$dictionary = false;
					break;
				}
				$dictionary = true;
			}
			if ($dictionary)
				$collectionClass |= CollectionClass::DICTIONARY;
		}
		elseif (Container::isIndexed($data, true, false))
			$collectionClass |= CollectionClass::INDEXED;

		if ($depth > 1)
		{
			$min = null;
			$max = null;
			foreach ($data as $columns)
			{
				$p = Container::properties($columns);
				if (($p & Container::COUNTABLE) == 0)
					continue;
				$c = Container::count($columns);
				if ($min === null || $c < $min)
					$min = $c;
				if ($max === null || $c > $max)
					$max = $c;
			}
			if ($min && $max && $min == $max)
				$collectionClass |= CollectionClass::TABLE;
		}

		return $collectionClass;
	}

	/**
	 *
	 * @param mixed $data
	 *        	Input data
	 * @return number
	 */
	public function getMinDepth($data)
	{
		if (!Container::isTraversable($data))
			return 0;
		$depth = -1;
		foreach ($data as $value)
		{
			$d = $this->getMinDepth($value);
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
	 *        	Input data
	 * @return integer Max depth of data
	 */
	public function getMaxDepth($data)
	{
		if (!Container::isTraversable($data))
			return 0;
		$depth = 1;
		foreach ($data as $value)
		{
			$d = $this->getMaxDepth($value);
			$depth = \max($depth, $d + 1);
		}

		return $depth;
	}

	/**
	 *
	 * @deprecated Use getMinDepth()
	 */
	public function getDataMinDepth($data)
	{
		return $this->getMinDepth($data);
	}

	public function getDimensionCollectionClasss($data, $depth = null)
	{
		if ($depth === null)
			$depth = $this->getMinDepth($data);
		$collectionClass = [];
		$level = $data;
		while ($depth--)
		{
			$collectionClass[] = $this->getCollectionClass($level);
			$level = Container::firstValue($level);
		}

		return $collectionClass;
	}

	/**
	 *
	 * @deprecated Use getDimensionCollectionClasss()
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
		$depth = $this->getMinDepth($data);
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
