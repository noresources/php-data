<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\TestCase\Serialization;

use NoreSources\Container\Container;
use NoreSources\Data\Serialization\CsvSerializer;
use NoreSources\Data\Serialization\DataSerializerInterface;
use NoreSources\Data\Serialization\DataUnserializerInterface;
use NoreSources\Data\Serialization\FileSerializerInterface;
use NoreSources\Data\Serialization\FileUnserializerInterface;
use NoreSources\Data\Serialization\StreamSerializerInterface;
use NoreSources\Data\Serialization\StreamUnserializerInterface;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeFactory;

final class CsvSerializationTest extends SerializerTestCaseBase
{

	const CLASS_NAME = CsvSerializer::class;

	const MEDIA_TYPE = 'text/csv; flatten=yes';

	const FILE_EXTENSION = 'csv';

	public function testImplements()
	{
		$serializer = $this->createSerializer();
		$this->assertImplements(
			[
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
			'using flatten media type parameter' => [
				'mediaType' => self::MEDIA_TYPE . '; ' .
				CsvSerializer::PARAMETER_FLATTEN . '=anything'
			],
			'bad mediatype' => [
				'mediaType' => 'application/yaml',
				'canUnserialize' => false
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

	public function testRegularCsv()
	{
		$input = [
			[
				'ID',
				'name',
				'followers',
				'haters'
			],
			[
				1,
				'Bob',
				42,
				314159
			],
			[
				2,
				'Alice',
				123456,
				1
			],
			[
				666,
				'John Carmack',
				918700,
				0
			]
		];

		$serializer = $this->createSerializer();

		$valid = $serializer->isSerializableTo($input);
		if (!$valid)
		{
			$this->assertFalse($valid);
			return;
		}

		$serialized = $serializer->serializeData($input);
		$this->assertTrue(\is_string($serialized),
			'Serialization is string');
		$deserialized = $serializer->unserializeData($serialized);

		$this->assertEquals($deserialized, $input,
			'Serialization/Deserialization cycle');

		$filename = __DIR__ . '/../data/table.' . self::FILE_EXTENSION;
		$deserializedFile = $serializer->unserializeFromFile($filename);

		$this->assertEquals($input, $deserializedFile,
			'Deserialize file');
	}

	public function testTransform()
	{
		$tests = [
			'literal' => [
				'input' => 123,
				'serialized' => "123\n",
				'output' => [
					[
						123
					]
				]
			],
			'object' => [
				'input' => [
					'id' => 5,
					'name' => 'Bob',
					'age' => 42
				],
				'serialized' => "id,5\nname,Bob\nage,42\n",
				'output' => [
					[
						'id',
						5
					],
					[
						'name',
						'Bob'
					],
					[
						'age',
						42
					]
				]
			],
			'object with media type parameters' => [
				'input' => [
					'id' => 5,
					'name' => 'Bob',
					'age' => 42
				],
				'mediaType' => MediaTypeFactory::getInstance()->createFromString(
					self::MEDIA_TYPE . ';separator=";"', true),
				'serialized' => "id;5\nname;Bob\nage;42\n"
			],
			'collection' => [
				'input' => [
					[
						'id' => 5,
						'name' => 'Bob',
						'age' => 42
					],
					[
						'name' => 'Alice',
						'sex' => 'F'
					],
					[
						'foo' => 'bar'
					]
				],
				'output' => [
					[
						'id',
						'name',
						'age',
						'sex',
						'foo'
					],
					[
						5,
						'Bob',
						42,
						null,
						null
					],
					[
						null,
						'Alice',
						null,
						'F',
						null
					],
					[
						null,
						null,
						null,
						null,
						'bar'
					]
				]
			]
		];

		$serializer = new CsvSerializer();

		foreach ($tests as $label => $test)
		{
			$input = $test['input'];
			$mediaType = Container::keyValue($test, 'mediaType', null);
			$valid = Container::keyValue($test, 'valid', true);

			$this->assertEquals($valid,
				$serializer->isSerializableTo($input, $mediaType),
				'Can serialize ' . $label);

			if (!$valid)
				continue;

			$serialized = $serializer->serializeData($input, $mediaType);
			if (($expected = Container::keyValue($test, 'serialized')))
			{
				$this->assertEquals($expected, $serialized,
					'Serialized ' . $label);
			}

			if (Container::keyExists($test, 'output'))
			{
				$deserialized = $serializer->unserializeData(
					$serialized, $mediaType);
				$output = $test['output'];
				$this->assertEquals($output, $deserialized,
					$label . ' serialization/deserialization');
			}
		}
	}
}
