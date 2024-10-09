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
	 * Get min and max depth.
	 *
	 * @param mixed $data
	 *        	Input data
	 * @return integer[] [min, max] depth
	 */
	public function getDepthRange($data)
	{
		if (!Container::isTraversable($data))
			return [
				0,
				0
			];
		$min = -1;
		$max = 1;

		foreach ($data as $value)
		{
			list ($dmin, $dmax) = $this->getDepthRange($value);
			if ($min < 0)
				$min = $dmin;
			else
				$min = \min($min, $dmin);
			$max = \max($max, $dmax + 1);
		}

		return [
			\max(0, $min) + 1,
			$max
		];
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

	const DIMENSION_CLASS_MODE_INTERSECT = 0;

	const DIMENSION_CLASS_MODE_COMBINE = 4;

	/**
	 * Get per-depth collection class
	 *
	 * @param \Traversable|array $data
	 *        	Input data
	 * @param array $options
	 *        	Options
	 *        	<ul>
	 *        	<li>depth (int): Limit container traversal to $depth level</li>
	 *        	<li>mode: Indicates how to merge results from different subtree.</li>
	 *        	</ul>
	 * @return array Per depth collection classes
	 */
	public function getDimensionCollectionClasss($data,
		$options = array())
	{
		if (\is_integer($options))
			$options = [
				'depth' => $options,
				'mode' => self::DIMENSION_CLASS_MODE_INTERSECT
			];
		$mode = Container::keyValue($options, 'mode',
			self::DIMENSION_CLASS_MODE_INTERSECT);
		$depth = Container::keyValue($options, 'depth', null);
		if ($depth === null)
		{
			if ($mode == self::DIMENSION_CLASS_MODE_COMBINE)
				$depth = $this->getMaxDepth($data);
			else
				$depth = $this->getMinDepth($data);
		}

		if ($depth == 0)
			return [];
		if ($depth == 1)
			return [
				$this->getCollectionClass($data)
			];

		$collectionClass = [];
		$visited = Container::keyValue($options, 'visited', []);
		$visited[] = $data;
		foreach ($data as $element)
		{
			$c = [];
			if (Container::isTraversable($element))
			{
				if (\in_array($element, $visited))
					continue;

				$c = $this->getDimensionCollectionClasss($element,
					[
						'depth' => $depth - 1,
						'mode' => $mode,
						'visited' => $visited
					]);
			}
			if ($mode == self::DIMENSION_CLASS_MODE_COMBINE)
				$collectionClass = self::array_bitwise_or(
					$collectionClass, $c);
			else
				$collectionClass = self::array_bitwise_and(
					$collectionClass, $c);
		}
		\array_unshift($collectionClass,
			$this->getCollectionClass($data));

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

	private static function array_bitwise_or($a, $b)
	{
		$c = \max(\count($a), \count($b));
		for ($i = 0; $i < $c; $i++)
		{

			$a[$i] = Container::keyValue($a, $i, 0) |
				Container::keyValue($b, $i, 0);
		}
		return $a;
	}

	private static function array_bitwise_and($a, $b)
	{
		$c = \max(\count($a), \count($b));
		for ($i = 0; $i < $c; $i++)
		{
			if (Container::keyExists($a, $i))
			{
				if (Container::keyExists($b, $i))
				{
					$a[$i] &= $b[$i];
				}
			}
			elseif (Container::keyExists($b, $i))
				$a[$i] = $b[$i];
		}
		return $a;
	}
}
