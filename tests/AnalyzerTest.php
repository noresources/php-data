<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Test;

use NoreSources\Container\Container;
use NoreSources\Data\Analyzer;
use NoreSources\Data\CollectionClass;
use NoreSources\Type\TypeDescription;

final class AnalyzerTest extends \PHPUnit\Framework\TestCase
{

	public function testCollectionClass()
	{
		$analyzer = Analyzer::getInstance();
		$inconsistent = [
			'string',
			[
				'int' => 42,
				'float' => 3.14
			]
		];

		list ($min, $max) = $analyzer->getDepthRange($inconsistent);
		$this->assertEquals(1, $min, 'Min depth');
		$this->assertEquals(2, $max, 'Max depth');

		$actual = $analyzer->getDimensionCollectionClasss($inconsistent);
		$this->assertEquals('array', TypeDescription::getName($actual),
			'getDimensionClass() result type');
		$this->assertCount(1, $actual,
			'Number of levels (default options)');
		$this->assertEquals(CollectionClass::LIST, $actual[0],
			'First level class (default options)');

		$actual = $analyzer->getDimensionCollectionClasss($inconsistent,
			0);
		$this->assertCount(0, $actual,
			'Number of classes with depth = 0');

		$actual = $analyzer->getDimensionCollectionClasss($inconsistent,
			[
				'depth' => 1,
				'mode' => Analyzer::DIMENSION_CLASS_MODE_COMBINE
			]);
		$this->assertCount(1, $actual,
			'Deep search with depth = min depth returns the same result as default.');
		$actual = $analyzer->getDimensionCollectionClasss($inconsistent,
			[
				'depth' => 2,
				'mode' => Analyzer::DIMENSION_CLASS_MODE_COMBINE
			]);
		$this->assertCount(2, $actual,
			'Number of levels with deep depth = 2');
		$this->assertEquals(CollectionClass::LIST, $actual[0],
			'First level class');
		$this->assertEquals(CollectionClass::DICTIONARY, $actual[1],
			'Second level class');
	}

	public function testAnalyzer()
	{
		$tests = [
			'literal' => [
				'data' => 'Foo',
				Analyzer::MIN_DEPTH => 0,
				Analyzer::MAX_DEPTH => 0
			],
			'empty array' => [
				'data' => [],
				Analyzer::MIN_DEPTH => 1,
				Analyzer::MAX_DEPTH => 1,
				Analyzer::COLLECTION_CLASS => 0,
				'types' => [
					'array'
				]
			],
			'indexed array' => [
				'data' => [
					1,
					2,
					3
				],
				Analyzer::MIN_DEPTH => 1,
				Analyzer::MAX_DEPTH => 1,
				Analyzer::COLLECTION_CLASS => CollectionClass::LIST,
				'types' => [
					'array'
				]
			],
			'associative array' => [
				'data' => [
					'key' => 'value',
					'foo' => 'Bar'
				],
				Analyzer::MIN_DEPTH => 1,
				Analyzer::MAX_DEPTH => 1,
				Analyzer::COLLECTION_CLASS => CollectionClass::DICTIONARY,
				'types' => [
					'object'
				]
			],
			'associative array with some array values' => [
				'data' => [
					'literal' => 'Foo',
					'array' => [
						'bar',
						'baz'
					],
					42 => 'The answer to life, the universe and everything'
				],
				Analyzer::MIN_DEPTH => 1,
				Analyzer::MAX_DEPTH => 2,
				Analyzer::COLLECTION_CLASS => CollectionClass::MAP,
				'types' => [
					'object'
				]
			],
			'associative array of indexed array' => [
				'data' => [
					"Foo" => [
						'bar',
						'baz'
					],
					'docks' => [
						'Daffy',
						'Bugs'
					]
				],
				Analyzer::COLLECTION_CLASS => (CollectionClass::DICTIONARY |
				CollectionClass::TABLE),
				Analyzer::MIN_DEPTH => 2,
				Analyzer::MAX_DEPTH => 2,
				'types' => [
					'object',
					'array'
				]
			]
		];

		$analyzer = Analyzer::getInstance();

		foreach ($tests as $label => $test)
		{
			$data = Container::keyValue($test, 'data');
			$minDepth = Container::keyValue($test, Analyzer::MIN_DEPTH,
				-1);
			$maxDepth = Container::keyValue($test, Analyzer::MAX_DEPTH,
				-1);
			$collectionClass = Container::keyValue($test,
				Analyzer::COLLECTION_CLASS);
			$types = Container::keyValue($test, 'types', []);

			$actualMinDepth = $analyzer->getDataMinDepth($data);
			$actualMaxDepth = $analyzer->getMaxDepth($data);
			$actualRange = $analyzer->getDepthRange($data);
			$actualTypes = $analyzer->getDataDimensionTypes($data);

			if ($minDepth >= 0)
				$this->assertEquals($minDepth, $actualMinDepth,
					$label . ' min depth');
			if ($maxDepth >= 0)
				$this->assertEquals($maxDepth, $actualMaxDepth,
					$label . ' max depth');
			$this->assertEquals($actualMaxDepth, $actualRange[1],
				'Max depth rom getDepthRange');
			$this->assertEquals($types, $actualTypes,
				$label . ' level types');

			if ($collectionClass !== null)
			{
				$collectionClass = CollectionClass::getCollectionClassNames(
					$collectionClass);
				$collectionClass = \implode(', ', $collectionClass);
				$actual = $analyzer->getCollectionClass($data);
				$actual = CollectionClass::getCollectionClassNames(
					$actual);
				$actual = \implode(', ', $actual);

				$this->assertEquals($collectionClass, $actual,
					$label . ' collection classes');
			}
		}
	}
}
