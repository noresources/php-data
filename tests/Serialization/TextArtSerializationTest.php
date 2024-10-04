<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\TestCase\Serialization;

use NoreSources\Data\Serialization\DataSerializerInterface;
use NoreSources\Data\Serialization\FileSerializerInterface;
use NoreSources\Data\Serialization\SerializableMediaTypeInterface;
use NoreSources\Data\Serialization\StreamSerializerInterface;
use NoreSources\Data\Serialization\TextArtTableSerializer;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\Test\DerivedFileTestTrait;

class TextArtSerializationTest extends SerializerTestCaseBase
{
	use DerivedFileTestTrait;

	public function setUp(): void
	{
		$basePath = __DIR__ . '/..';
		$this->setUpDerivedFileTestTrait($basePath);
		$this->initializeSerializerAssertions(self::CLASS_NAME,
			$basePath);
	}

	public function tearDown(): void
	{
		$this->tearDownDerivedFileTestTrait();
	}

	const CLASS_NAME = TextArtTableSerializer::class;

	const MEDIA_TYPE = 'text/vnd.ascii-art';

	public function testParameters()
	{
		if (!$this->canTestSerializer())
			return;

		$serializer = $this->createSerializer();

		$mediaType = MediaTypeFactory::createFromString(
			self::MEDIA_TYPE);

		$this->assertSupportsMediaTypeParameter(
			[

				'heading parameter' => [
					true,
					'heading'
				],
				'unexpected parameter' => [
					false,
					'unholy'
				],
				'column heading' => [
					true,
					'heading',
					'column'
				],
				'ugly heading' => [
					false,
					'heading',
					'ugly'
				]
			], $serializer, $mediaType);
	}

	public function testImplements()
	{
		$serializer = $this->createSerializer();
		$this->assertImplements(
			[
				SerializableMediaTypeInterface::class,
				MediaTypeListInterface::class,
				FileExtensionListInterface::class,
				StreamSerializerInterface::class,
				DataSerializerInterface::class,
				FileSerializerInterface::class
			], $serializer);
	}

	public function testSerialize()
	{
		/**
		 *
		 * @var TextArtTableSerializer $serializer
		 */
		$serializer = $this->createSerializer();
		$mediaType = MediaTypeFactory::getInstance()->createFromString(
			'text/vnd.ascii-art');
		$method = __METHOD__;
		$extension = 'ascii';

		foreach ([
			'a' => false,
			'collection' => 'column',
			'dictionary-of-objects' => 'both',
			'spare' => 'column',
			'table' => 'none',
			'tree.data' => false
		] as $filename => $heading)
		{
			$serializable = ($heading === false) ? false : true;
			$path = $this->getReferenceFileDirectory() . '/' . $filename .
				'.json';
			$data = \json_decode(\file_get_contents($path), true);
			$actual = $serializer->isContentSerializable($data);
			$this->assertEquals($serializable, $actual,
				'Is ' . $filename . ' serializable ?');

			if (!$actual)
				continue;

			foreach ([
				'ascii',
				'utf-8'
			] as $charset)
			{
				$mediaType->getParameters()->offsetSet('heading',
					$heading);
				$mediaType->getParameters()->offsetSet('charset',
					$charset);
				$suffix = $filename . '_' . $charset;
				$actual = $serializer->serializeData($data, $mediaType);
				$this->assertDerivedFile($actual, $method, $suffix,
					$extension, 'Serialized with forced heading');

				$mediaType->getParameters()->offsetUnset('heading');

				$actual = $serializer->serializeData($data, $mediaType);
				$this->assertDerivedFile($actual, $method, $suffix,
					$extension,
					'Serialized with auto heading should match ' .
					$heading);
			}
		}
	}
}
