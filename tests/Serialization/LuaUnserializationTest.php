<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\TestCase\Serialization;

use NoreSources\Data\Serialization\DataUnserializerInterface;
use NoreSources\Data\Serialization\FileUnserializerInterface;
use NoreSources\Data\Serialization\LuaUnserializer;
use NoreSources\Data\Serialization\StreamUnserializerInterface;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\Text\Text;

class LuaUnserializationTest extends SerializerTestCaseBase
{

	const MEDIA_TYPE = 'text/x-lua';

	const CLASS_NAME = LuaUnserializer::class;

	public function testImplements()
	{
		if (!$this->canTestSerializer())
			return;
		$serializer = new LuaUnserializer();
		$this->assertImplements(
			[
				DataUnserializerInterface::class,
				FileUnserializerInterface::class,
				StreamUnserializerInterface::class
			], $serializer);
	}

	public function testUnserialization()
	{
		if (!$this->canTestSerializer())
			return;

		$directory = __DIR__ . '/../reference/lua';
		$tests = [
			'nil' => null,
			'true' => true,
			'int' => 42,
			'float' => 3.14,
			'table-map-tree' => [
				'foo' => 'bar',
				'list' => [
					'foo',
					'bar',
					'baz'
				],
				'map' => [
					'key' => 'value',
					'int' => 42
				],
				'tree' => [
					'branch' => [
						'leaf' => 'lady bug'
					],
					'root' => [
						'size' => 'big',
						'color' => '#404000'
					]
				]
			],
			'table-list' => [
				'one',
				2,
				'A third value'
			],
			'character-string-with-escaped-content' => 'Some "more" \'complex\' example',
			'table-empty' => [],
			'normal-string-with-escaped-quotes' => 'A \'double\' "quoted" normal string',
			'normal-string' => "I'm a poor lonesome string",
			'table-with-mixed-and-overrided-keys',
			'table-map' => [
				'foo' => 'bar',
				'The response' => 42,
				'true' => true,
				'pi' => 3.14,
				41 => 'Forty two'
			],
			'character-string' => 'Character string are more or less like normal strings',
			'module-table-with-comments' => [
				'boolean' => true,
				'integer' => 42,
				'number' => 3.14,
				'0123' => 'protected key',
				'text' => "It's a \"quite\" complex text",
				'null' => null,
				'list' => [
					'one',
					'two',
					3
				],
				'map' => [
					'key' => 'value',
					'tree' => [
						'leaf' => 'green',
						'root' => 'brown'
					]
				]
			]
		];

		$mediaType = MediaTypeFactory::getInstance()->createFromString(
			self::MEDIA_TYPE);
		$serializer = new LuaUnserializer();

		foreach ($tests as $name => $expected)
		{
			if (\is_integer($name))
				continue;
			$label = Text::toHumanCase($name);
			$filename = $directory . '/' . $name . '.lua';
			$actual = $serializer->unserializeFromFile($filename,
				$mediaType);
			$this->assertEquals($expected, $actual, $label);
		}
	}
}
