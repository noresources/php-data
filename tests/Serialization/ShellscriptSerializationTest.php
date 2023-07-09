<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\TestCase\Serialization;

use NoreSources\Data\Serialization\DataSerializerInterface;
use NoreSources\Data\Serialization\FileSerializerInterface;
use NoreSources\Data\Serialization\SerializableMediaTypeInterface;
use NoreSources\Data\Serialization\ShellscriptSerializer;
use NoreSources\Data\Serialization\StreamSerializerInterface;
use NoreSources\Data\Serialization\Shellscript\ShellscriptWriter;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeFactory;

final class ShellscriptSerializationTest extends SerializerTestCaseBase
{

	const CLASS_NAME = ShellscriptSerializer::class;

	const MEDIA_TYPE = 'text/x-shellscript';

	const EXTENSION = 'sh';

	public function testImplements()
	{
		$serializer = $this->createSerializer();
		$this->assertImplements(
			[
				SerializableMediaTypeInterface::class,
				// Helpers
				MediaTypeListInterface::class,
				FileExtensionListInterface::class,
				// Stream interfaces
				StreamSerializerInterface::class,
				// Data interfaces
				DataSerializerInterface::class,
				// File interface
				FileSerializerInterface::class
			], $serializer);
	}

	public function testSerialization()
	{
		$data = [
			'null' => null,
			'true' => true,
			'false' => false,
			'The answer to life, the universe and everything' => 42,
			'franc to euro' => 6.55957,
			'string' => "The \"good\", the 'bad', and Bob-Wan Kenobi",
			'list' => [
				'one',
				2,
				'Free'
			],
			'dictionary' => [
				'one' => 'un',
				'two' => 'deux',
				'three' => 'trois'
			],
			'map' => [
				[
					'one',
					'un'
				],
				'ducks' => [
					'disney' => [
						'Donald',
						'Huey'
					],
					'warner' => 'Daffy'
				],
				[
					'Rabbit' => 'Bugs'
				]
			]
		];

		$serializer = $this->createSerializer();

		foreach ([
			null,
			ShellscriptSerializer::VARIABLE_CASE_CAMEL,
			ShellscriptSerializer::VARIABLE_CASE_MACRO,
			ShellscriptSerializer::VARIABLE_CASE_PASCAL,
			ShellscriptSerializer::VARIABLE_CASE_SNAKE,
			'NoStyle'
		] as $case)
		{
			foreach ([
				null,
				'bash',
				'zsh'
			] as $interpreter)
			{
				$suffix = [];
				$mediaType = MediaTypeFactory::getInstance()->createFromString(
					self::MEDIA_TYPE);
				if ($case)
				{
					$suffix[] = $case;
					$mediaType->getParameters()->offsetSet(
						ShellscriptSerializer::PARAMETER_VARIABLE_CASE,
						$case);
				}
				if ($interpreter)
				{
					$suffix[] = $interpreter;
					$mediaType->getParameters()->offsetSet(
						ShellscriptSerializer::PARAMETER_INTERPRETER,
						$interpreter);
				}
				$suffix = \count($suffix) ? \implode('_', $suffix) : null;
				$label = 'Serialize for ' .
					($interpreter ? $interpreter : 'default') .
					' interpreter with ' . ($case ? $case : 'default') .
					' variable code case';
				$serialized = $serializer->serializeData($data,
					$mediaType);
				$this->assertDataEqualsReferenceFile($serialized,
					__METHOD__, $suffix, self::EXTENSION, $label,
					ShellscriptWriter::EOL);
			}
		}
	}

	public function testIsCollection()
	{
		$withoutParameters = MediaTypeFactory::getInstance()->createFromString(
			self::MEDIA_TYPE);
		$withCollectionParameter = clone $withoutParameters;
		$withCollectionParameter->getParameters()->offsetSet(
			'collection', '');
		$withKeyPropertyParameter = clone $withoutParameters;
		$withKeyPropertyParameter->getParameters()->offsetSet(
			'key-property', 'first');

		$tests = [
			'POD' => [
				'Hello',
				false,
				false,
				false
			],
			'List' => [
				[
					'Hello',
					'world'
				],
				false,
				false,
				false
			],
			'Dictionary' => [
				[
					'first' => 'Hello',
					'last' => 'World'
				],
				true,
				true,
				true
			],
			'list of list' => [
				[
					[
						'Hello',
						'world'
					],
					[
						'Good',
						'bye'
					]
				],
				false,
				false,
				false
			],
			'list of dictionary' => [
				[
					[
						'first' => 'Hello',
						'last' => 'world'
					],
					[
						'first' => 'Good',
						'last' => 'bye'
					]
				],
				false,
				true,
				true
			],
			'dictionary of dictionary' => [
				[
					'begin' => [
						'first' => 'Hello',
						'last' => 'world'
					],
					'end' => [
						'first' => 'Good',
						'last' => 'bye'
					]
				],
				true,
				true,
				true
			]
		];

		$serializer = $this->createSerializer();

		foreach ($tests as $label => $test)
		{
			$data = $test[0];
			foreach ([
				$withoutParameters,
				$withCollectionParameter,
				$withKeyPropertyParameter
			] as $offset => $mediaType)
			{
				$mediaTypeText = $mediaType->jsonSerialize();
				$expected = $test[$offset + 1];
				$actual = $serializer->isSerializableTo($data,
					$mediaType);
				$text = $label . ' is ';
				if (!$expected)
					$text .= 'not ';
				$text .= 'serializable with ' . $mediaTypeText;
				$this->assertEquals($expected, $actual, $text);
			}
		}
	}

	public function testCollection()
	{
		$serializer = $this->createSerializer();
		$mediaType = MediaTypeFactory::getInstance()->createFromString(
			self::MEDIA_TYPE);
		$method = __METHOD__;
		foreach ([
			'collection'
		] as $source)
		{
			$filename = __DIR__ . '/../reference/' . $source . '.json';
			$data = \json_decode(\file_get_contents($filename), true);
			foreach ([
				[],
				[
					'key-property' => 'id'
				],
				[
					'variable-case' => 'camel'
				],
				[
					'collection' => '',
					'variable-case' => 'camel'
				],
				[
					'variable-case' => 'Pascal',
					'key-property' => 'id'
				]
			] as $parameters)
			{
				$suffix = \implode('_', \array_keys($parameters));
				$mt = clone $mediaType;
				foreach ($parameters as $key => $value)
					$mt->getParameters()->offsetSet($key, $value);
				$serialized = $serializer->serializeData($data, $mt);

				$this->assertDataEqualsReferenceFile($serialized,
					$method, $suffix, 'sh');
			}
		}
	}

	public function testParameters()
	{
		$this->assertSupportsMediaTypeParameter(
			[
				[
					true,
					'variable-case'
				],
				[
					true,
					'collection'
				],
				[
					true,
					'key-property',
					'foo'
				],
				[
					true,
					'interpreter',
					'bash'
				],
				[
					false,
					'just-no'
				]
			]);
	}
}
