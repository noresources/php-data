<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\TestCase\Serialization;

use NoreSources\Data\Serialization\DataSerializerInterface;
use NoreSources\Data\Serialization\DataUnserializerInterface;
use NoreSources\Data\Serialization\FileSerializerInterface;
use NoreSources\Data\Serialization\FileUnserializerInterface;
use NoreSources\Data\Serialization\PlainTextSerializer;
use NoreSources\Data\Serialization\SerializableMediaTypeInterface;
use NoreSources\Data\Serialization\StreamSerializerInterface;
use NoreSources\Data\Serialization\StreamUnserializerInterface;
use NoreSources\Data\Serialization\UnserializableMediaTypeInterface;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\Type\TypeDescription;

final class PlainTextSerializationTest extends SerializerTestCaseBase
{

	const CLASS_NAME = PlainTextSerializer::class;

	const MEDIA_TYPE = PlainTextSerializer::MEDIA_TYPE;

	public function testImplements()
	{
		if (!$this->canTestSerializer())
			return;

		$serializer = $this->createSerializer();
		$this->assertImplements(
			[
				UnserializableMediaTypeInterface::class,
				SerializableMediaTypeInterface::class,
				// Helpers
				MediaTypeListInterface::class,
				FileExtensionListInterface::class,
				// Stream interfaces
				StreamSerializerInterface::class,
				StreamUnserializerInterface::class,
				// Data interfaces
				DataSerializerInterface::class,
				DataUnserializerInterface::class,
				// File interface
				FileSerializerInterface::class,
				FileUnserializerInterface::class
			], $serializer);
	}

	public function testPOD()
	{
		if (!$this->canTestSerializer())
			return;

		$tests = [
			'auto' => [],
			'force mediatype' => [
				'mediaType' => 'text/plain'
			],
			'bad mediatype' => [
				'mediaType' => 'application/json',
				'isUnserializable' => false
			]
		];

		foreach ([
			'string',
			'integer',
			'float'
		] as $typename)
		{
			foreach ($tests as $options)
			{
				$this->assertTypeSerialization($typename, $options);
			}
		}
	}

	public function testUnserialize()
	{
		$s = new PlainTextSerializer();
		$tests = [
			'text' => [
				'input' => 'Hello',
				'expected' => 'Hello'
			],
			'int' => [
				'input' => '42',
				'expected' => 42
			],
			'float' => [
				'input' => '6.55957',
				'expected' => 6.55957
			],
			'LF' => [
				"input" => "first\nsecond\nthird",
				'expected' => [
					'first',
					'second',
					'third'
				]
			],
			'Mixed CR, LF and CRLF' => [
				"input" => "first\nInner\rValues\r\nsecond\nthird",
				'expected' => [
					'first',
					'Inner',
					'Values',
					'second',
					'third'
				]
			]
		];

		foreach ($tests as $label => $test)
		{
			$i = $test['input'];
			$expected = $test['expected'];
			$actual = $s->unserializeData($i);

			$this->assertEquals(TypeDescription::getName($expected),
				TypeDescription::getName($actual),
				$label . ' to ' . TypeDescription::getName($expected));

			$this->assertEquals($expected, $actual, $label . ' value');
		}
	}

	public function testSerialize()
	{
		$s = new PlainTextSerializer();
		$tests = [
			'basic' => [
				'input' => 'Hello',
				'expected' => 'Hello'
			],
			'int' => [
				'input' => 42,
				'expected' => '42'
			],
			'boolean false' => [
				'input' => false,
				'expected' => ''
			],
			'boolean true' => [
				'input' => true,
				'expected' => '1'
			],
			'list' => [
				'input' => [
					'a',
					'b'
				],
				'expected' => "a\nb"
			],
			'map' => [
				'input' => [
					'First' => 'a',
					'Second' => 'b'
				],
				'expected' => "a\nb"
			],
			'deep map' => [
				'input' => [
					'First' => 'a',
					'Second' => [
						's1' => 'b1',
						'b2' => [
							'c1',
							'c2' => [
								'foo' => 'bar'
							],
							'c3'
						]
					]
				],
				'expected' => "a\nb1\nc1\nbar\nc3"
			]
		];

		foreach ($tests as $label => $test)
		{
			$i = $test['input'];
			$expected = $test['expected'];
			$actual = $s->serializeData($i);
			$this->assertEquals($expected, $actual, $label);
		}
	}

	public function testParameters()
	{
		$serializer = $this->createSerializer();

		$mediaType = MediaTypeFactory::createFromString(
			self::MEDIA_TYPE);

		$this->assertSupportsMediaTypeParameter(
			[

				'pre-transform parameter' => [
					true,
					'preprocess-depth'
				]
			], $serializer, $mediaType);
	}
}
