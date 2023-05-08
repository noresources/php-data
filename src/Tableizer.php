<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data;

use NoreSources\Container\Container;

/**
 * Transform data structure to a 2D table
 */
class Tableizer
{

	public function __invoke($data)
	{
		$analyzer = new Analyzer();
		$dimensions = $analyzer->getDataDimensionTypes($data);
		$depth = Container::count($dimensions);

		if ($depth == 0)
			return [
				[
					$this->normalizeCellValue($data)
				]
			];

		if ($depth == 1)
		{
			if ($dimensions[0] == 'array')
				return Container::map($data,
					function ($k, $v) {
						return [
							$this->normalizeCellValue($v)
						];
					});
			// 	Object
			$normalized = [];
			foreach ($data as $property => $value)
			{
				$normalized[] = [
					$property,
					$this->normalizeCellValue($value)
				];
			}

			return $normalized;
		}

		// 2D or more
		$normalized = [];
		if ($dimensions[0] == 'array')
		{
			if ($dimensions[1] == 'array')
			{
				foreach ($data as $line)
				{
					$n = [];
					foreach ($line as $column)
						$n[] = $this->normalizeCellValue($column);
					$normalized[] = $n;
				}
			}
			else
			{
				$keys = [];
				foreach ($data as $object)
				{
					$keys = \array_unique(
						\array_merge($keys, Container::keys($object)));
				}
				$normalized[] = $keys;
				foreach ($data as $object)
				{
					$r = [];
					foreach ($keys as $key)
					{
						$r[] = $this->normalizeCellValue(
							Container::keyValue($object, $key));
					}
					$normalized[] = $r;
				}
			}
		}
		else // objet of ...
		{
			if ($dimensions[1] == 'array')
			{
				foreach ($data as $property => $array)
				{
					$r = Container::map($array,
						function ($k, $v) {
							return $this->normalizeCellValue($v);
						});
					\array_unshift($r, $property);
					$normalized[] = $r;
				}
			}
			else // object of object
			{
				$keys = [];
				foreach ($data as $object)
				{
					$keys = \array_unique(
						\array_merge($keys, Container::keys($object)));
				}

				$normalized[] = \array_merge([
					null
				], $keys);
				foreach ($data as $property => $object)
				{
					$r = [
						$property
					];

					foreach ($keys as $key)
					{
						$r[] = $this->normalizeCellValue(
							Container::keyValue($object, $key));
					}

					$normalized[] = $r;
				}
			}
		}

		return $normalized;
	}

	/**
	 *
	 * @param callable $normalizer
	 *        	Cell normalization callback
	 */
	public function setCellNormalizer($normalizer)
	{
		$this->cellNormalizer = $normalizer;
	}

	private function normalizeCellValue($value)
	{
		if (isset($this->cellNormalizer))
			return \call_user_func($this->cellNormalizer, $value);
		return $value;
	}

	/**
	 *
	 * @var callable
	 */
	private $cellNormalizer;
}
