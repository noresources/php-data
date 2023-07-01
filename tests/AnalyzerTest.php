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

final class AnalyzerTest extends \PHPUnit\Framework\TestCase
{

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
			$actualTypes = $analyzer->getDataDimensionTypes($data);

			if ($minDepth >= 0)
				$this->assertEquals($minDepth, $actualMinDepth,
					$label . ' min depth');
			if ($maxDepth >= 0)
				$this->assertEquals($maxDepth, $actualMaxDepth,
					$label . ' max depth');
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
