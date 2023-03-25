<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Test;

use NoreSources\Container\Container;
use NoreSources\Data\Analyzer;

final class AnalyzerTest extends \PHPUnit\Framework\TestCase
{

	public function testAnalyzer()
	{
		$tests = [
			'literal' => [
				'data' => 'Foo',
				'min-depth' => 0
			],
			'empty array' => [
				'data' => [],
				'min-depth' => 1,
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
				'min-depth' => 1,
				'types' => [
					'array'
				]
			],
			'associative array' => [
				'data' => [
					'key' => 'value',
					'foo' => 'Bar'
				],
				'min-depth' => 1,
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
					]
				],
				'min-depth' => 1,
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
				'min-depth' => 2,
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
			$depth = Container::keyValue($test, 'min-depth', 0);
			$types = Container::keyValue($test, 'types', []);

			$actualDepth = $analyzer->getDataMinDepth($data);
			$actualTypes = $analyzer->getDataDimensionTypes($data);

			$this->assertEquals($depth, $actualDepth,
				$label . ' min depth');
			$this->assertEquals($types, $actualTypes,
				$label . ' level types');
		}
	}
}
