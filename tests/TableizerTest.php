<?php
use NoreSources\Container\Container;
use NoreSources\Container\ContainerPropertyInterface;
use NoreSources\Data\Tableizer;
use NoreSources\Type\TypeDescription;

class TraversableEntity implements ContainerPropertyInterface
{

	public $key = 'value';

	public $foo = 'bar';

	public function getContainerProperties()
	{
		return Container::properties($this, true) |
			Container::TRAVERSABLE;
	}

	private $sercret = "I'm a teapot";
}

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
class TableizerTest extends \PHPUnit\Framework\TestCase
{

	public function testLiteral()
	{
		$tableizer = new Tableizer();

		foreach ([
			'string',
			42,
			3.14159,
			true,
			false,
			null
		] as $value)
		{
			$expected = [
				[
					$value
				]
			];
			$actual = $tableizer($value);
			$this->assertEquals($expected, $actual,
				'Tablelize ' . TypeDescription::getName($value) .
				' literal');
		}
	}

	public function testArray()
	{
		$input = [
			'A',
			'B',
			'C'
		];
		$expected = [
			[
				'A'
			],
			[
				'B'
			],
			[
				'C'
			]
		];
		$this->tableize($input, $expected, 'Tableize 11 array');
	}

	public function testAssociative()
	{
		$input = [
			'One' => 1,
			'Two' => 2,
			'Three' => 3
		];
		$expected = [
			[
				'One',
				1
			],
			[
				'Two',
				2
			],
			[
				'Three',
				3
			]
		];
		$this->tableize($input, $expected, 'Tableize associative array');
	}

	public function testTraversableObject()
	{
		$input = new TraversableEntity();
		$expected = [
			[
				'key',
				'value'
			],
			[
				'foo',
				'bar'
			]
		];
		$this->tableize($input, $expected, 'Tableize traversable object');
	}

	public function testArrayOfArray()
	{
		$input = [
			[
				'One',
				'Two',
				'Threa'
			],
			[
				'Un',
				'Deux',
				'Trois',
				'Quatre'
			],
			[
				'Ichi',
				'Ni',
				'San'
			]
		];
		$expected = $input;
		$this->tableize($input, $expected, 'Tableize array of array');
	}

	public function testArrayOfObject()
	{
		$input = [
			new TraversableEntity(),
			[
				'integer' => 42,
				'foo' => 'baz',
				'key' => 'Clé'
			]
		];
		$expected = [
			[
				'key',
				'foo',
				'integer'
			],
			[
				'value',
				'bar',
				null
			],
			[
				'Clé',
				'baz',
				42
			]
		];
		$this->tableize($input, $expected, 'Tableize array of object');
	}

	public function testObjectOfArray()
	{
		$input = [
			'numbers' => [
				1,
				2,
				3
			],
			'ducks' => [
				'Bugs',
				'Daffy'
			],
			'musketeers' => [
				'Athos',
				'Portos',
				'Aramis',
				'Bob'
			]
		];
		$expected = [
			[
				'numbers',
				1,
				2,
				3
			],
			[
				'ducks',
				'Bugs',
				'Daffy'
			],
			[
				'musketeers',
				'Athos',
				'Portos',
				'Aramis',
				'Bob'
			]
		];
		$this->tableize($input, $expected, 'Object of array');
	}

	public function testObjectOfObject()
	{
		$input = [
			'first' => [
				'first-name' => 'John',
				'last-name' => 'Doe',
				'age' => 'undefined'
			],
			'average' => [
				'age' => 42,
				'last-name' => 'Leblanc',
				'color' => 'white'
			],
			'last' => [
				'salary' => 0.001,
				'last-name' => 'Dumb',
				'first-name' => 'Donald'
			]
		];
		$expected = [
			[
				null,
				'first-name',
				'last-name',
				'age',
				'color',
				'salary'
			],
			[
				'first',
				'John',
				'Doe',
				'undefined',
				null,
				null
			],
			[
				'average',
				null,
				'Leblanc',
				42,
				'white',
				null
			],
			[
				'last',
				'Donald',
				'Dumb',
				null,
				null,
				0.001
			]
		];
		$this->tableize($input, $expected, 'Object of objects');
	}

	protected function tableize($input, $expected, $label)
	{
		$tableizer = new Tableizer();
		$actual = $tableizer($input);
		$this->assertEquals($actual, $expected, $label);
	}
}
