<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\TestCase\Parser;

use NoreSources\Container\Container;
use NoreSources\Data\Parser\IniParser;
use NoreSources\Data\Parser\ParserException;

class IniParserTest extends \PHPUnit\Framework\TestCase
{

	public function testLine()
	{
		$defaultFlags = IniParser::VALUE_UNQUOTED_MULTILINE;
		$parser = new IniParser();
		$tests = [
			'section' => [
				'lines' => '[section]',
				'expected' => [
					'section' => []
				]
			],
			'entry' => [
				'lines' => 'key=value',
				'expected' => [
					'key' => 'value'
				]
			],
			'quoted entry' => [
				'lines' => 'key="value"',
				'expected' => [
					'key' => 'value'
				]
			],
			'innere-qutes' => [
				'lines' => "string=\"The \"'\"'\"good\"'\"'\", the bad, and Kil'Jaiden\"",
				'expected' => [
					'string' => 'The "good", the bad, and Kil\'Jaiden'
				]
			],
			'multiline quoted entry' => [
				'lines' => [
					'key="value',
					' and value"'
				],
				'expected' => [
					'key' => "value\n and value"
				]
			],
			'line continuation' => [
				'lines' => [
					'key=La la la',
					'     li la lo'
				],
				'expected' => [
					'key' => "La la la\nli la lo"
				]
			],
			'out-and-in-section' => [
				'lines' => [
					'outside=Poors',
					'[inside]',
					'gender=Male',
					'color=White'
				],
				'expected' => [
					'outside' => 'Poors',
					'inside' => [
						'gender' => 'Male',
						'color' => 'White'
					]
				]
			],
			'continue' => [
				'flags' => ($defaultFlags |
				IniParser::VALUE_UNQUOTED_BACKSLASH_CONTINUE),
				'lines' => [
					'foo=bar \\',
					'baz'
				],
				'expected' => [
					'foo' => 'bar baz'
				]
			]
		];

		foreach ($tests as $label => $test)
		{
			$flags = Container::keyValue($test, 'flags', $defaultFlags);
			$lines = $test['lines'];
			if (\is_string($lines))
				$lines = [
					$lines
				];
			$expected = $test['expected'];

			try
			{
				$parser->initialize($flags);
				foreach ($lines as $offset => $text)
				{
					$this->assertTrue($parser->parseLine($text),
						'Parse line ' . ($offset + 1));
				}
				$actual = $parser->finalize();
			}
			catch (ParserException $e)
			{
				$actual = $e->getMessage();
			}

			$this->assertEquals($expected, $actual, $label);
		}
	}
}
