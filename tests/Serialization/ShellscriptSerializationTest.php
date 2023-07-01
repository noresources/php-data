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
			ShellscriptSerializer::STYLE_CAMEL,
			ShellscriptSerializer::STYLE_MACRO,
			ShellscriptSerializer::STYLE_PASCAL,
			ShellscriptSerializer::STYLE_SNAKE,
			'NoStyle'
		] as $style)
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
				if ($style)
				{
					$suffix[] = $style;
					$mediaType->getParameters()->offsetSet(
						ShellscriptSerializer::PARAMETER_STYLE, $style);
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
					' interpreter with ' . ($style ? $style : 'default') .
					' style';
				$serialized = $serializer->serializeData($data,
					$mediaType);
				$this->assertDataEqualsReferenceFile($serialized,
					__METHOD__, $suffix, self::EXTENSION, $label,
					ShellscriptWriter::EOL);
			}
		}
	}
}
