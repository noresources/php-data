<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\TestCase\Serialization;

use NoreSources\Data\Serialization\ApplePropertyListSerializer;
use NoreSources\Data\Serialization\SerializableMediaTypeInterface;
use NoreSources\Data\Serialization\StreamSerializerInterface;
use NoreSources\Data\Serialization\StreamUnserializerInterface;
use NoreSources\Data\Serialization\UnserializableMediaTypeInterface;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;

final class ApplePropertyListSerializationTest extends SerializerTestCaseBase
{

	const CLASS_NAME = ApplePropertyListSerializer::class;

	const MEDIA_TYPE = 'application/x-plist';

	const EXTENSION = 'plist';

	public static $REFERENCE_LIST_DATA = [
		self::REFERENCE_STRING_DATA,
		self::REFERENCE_INTEGER_DATA,
		self::REFERENCE_FLOAT_DATA,
		self::REFERENCE_BOOLEAN_DATA
	];

	public static $REFERENCE_DICTIONARY_DATA = [
		'string' => self::REFERENCE_STRING_DATA,
		'integer' => self::REFERENCE_INTEGER_DATA,
		'float' => self::REFERENCE_FLOAT_DATA,
		'boolean' => self::REFERENCE_BOOLEAN_DATA
	];

	public function testImplements()
	{
		$serializer = $this->createSerializer();
		$this->assertImplements(
			[
				SerializableMediaTypeInterface::class,
				UnserializableMediaTypeInterface::class,
				MediaTypeListInterface::class,
				FileExtensionListInterface::class,
				StreamSerializerInterface::class,
				StreamUnserializerInterface::class
			], $serializer);
	}

	public function testPOD()
	{
		if (!$this->canTestSerializer())
			return;

		$tests = [
			'auto' => [],
			'force mediatype' => [
				'mediaType' => self::MEDIA_TYPE
			],
			'bad mediatype' => [
				'mediaType' => 'application/yaml',
				'isUnserializable' => false
			]
		];

		$this->assertTrue(true);
		foreach ([
			'string',
			'integer',
			'float',
			'boolean',
			'list',
			'dictionary'
		] as $typename)
		{
			foreach ($tests as $options)
			{
				$this->assertTypeSerialization($typename, $options);
			}
		}
	}
}
