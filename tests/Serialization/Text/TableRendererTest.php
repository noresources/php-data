<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\TestCase\Serialization\Text;

use NoreSources\Data\Serialization\Text\TableRenderer;
use NoreSources\Data\Serialization\Text\Utf8TableRenderer;
use NoreSources\Test\DerivedFileTestTrait;

class TableRendererTestString
{

	private $data;

	public function __construct($text)
	{
		$this->data = $text;
	}

	public function __toString()
	{
		return $this->data;
	}
}

class TableRendererTest extends \PHPUnit\Framework\TestCase
{

	use DerivedFileTestTrait;

	public function setUp(): void
	{
		$this->setUpDerivedFileTestTrait(__DIR__ . '/../..');
	}

	public function tearDown(): void
	{
		$this->tearDownDerivedFileTestTrait();
	}

	public function testHeadingMode()
	{
		$tests = [
			'empty' => [
				'input' => [],
				'expected' => 0
			],
			'table' => [
				'input' => [
					[
						'one',
						'two'
					],
					[
						'un',
						'deux'
					]
				],
				'expected' => 0
			],
			'dictionary of array' => [
				'input' => [
					'en' => [
						'one',
						'two'
					],
					'fr' => [
						'un',
						'deux'
					]
				],
				'expected' => TableRenderer::HEADING_ROW
			],
			'collection' => [
				'input' => [
					[
						'first' => 'one',
						'second' => 'two'
					],
					[
						'first' => 'un',
						'second' => 'deux'
					]
				],
				'expected' => TableRenderer::HEADING_COLUMN
			],
			'collection' => [
				'input' => [
					'fr' => [
						'first' => 'one',
						'second' => 'two'
					],
					'fr' => [
						'first' => 'un',
						'second' => 'deux'
					]
				],
				'expected' => TableRenderer::HEADING_BOTH
			]
		];

		foreach ($tests as $label => $test)
		{
			$actual = TableRenderer::guessHeadingMode($test['input']);
			$this->assertEquals($test['expected'], $actual, $label);
		}
	}

	public function testRender()
	{
		$method = __METHOD__;
		$extension = 'ascii';
		$rows = [
			[
				'id' => 1,
				'name' => 'Bob',
				'role' => 'Sponge'
			],
			[
				'role' => 'Super hero',
				'id' => 2,
				'name' => 'Batman'
			],
			[
				'id' => 1954,
				'role' => 'Book',
				'name' => new TableRendererTestString(
					'The Lord Of The Ring')
			]
		];

		$renderer = new Utf8TableRenderer();
		$actual = $renderer->render($rows);

		$this->assertDerivedFile($actual, $method, 'basic', $extension);

		$name = $rows[2]['name'];
		$this->assertInstanceOf(TableRendererTestString::class, $name,
			'Stringification process does not modify original data');
	}

	public function testRenderLine()
	{
		$renderer = new Utf8TableRenderer();
		$renderer->heading = TableRenderer::HEADING_COLUMN;
		$renderer->setMargin(0);

		$tests = [
			'top' => [
				'verticalPosition' => TableRenderer::VERTICAL_POSITION_TOP,
				'expected' => '┌─────┬───┬─┐'
			],
			'inter' => [
				'verticalPosition' => TableRenderer::VERTICAL_POSITION_INTER,
				'expected' => '├─────┼───┼─┤'
			],
			'bottom' => [
				'verticalPosition' => TableRenderer::VERTICAL_POSITION_BOTTOM,
				'expected' => '└─────┴───┴─┘'
			]
		];

		$columnSizes = [
			5,
			3,
			1
		];

		$expectedLength = \array_sum($columnSizes) + \count(
			$columnSizes) + 1;

		foreach ($tests as $label => $test)
		{
			$verticalPosition = $test['verticalPosition'];
			$line = $renderer->renderBorderLine($verticalPosition,
				$columnSizes);
			$length = \mb_strlen($line);
			$this->assertEquals($expectedLength, $length,
				$label . ' line length');

			$this->assertEquals($test['expected'], $line, $label);
		}
	}
}
