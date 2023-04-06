<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\TestCase\Serialization;

use NoreSources\Data\Serialization\DataSerializerInterface;
use NoreSources\Data\Serialization\DataUnserializerInterface;
use NoreSources\Data\Serialization\FileSerializerInterface;
use NoreSources\Data\Serialization\FileUnserializerInterface;
use NoreSources\Data\Serialization\SerializableMediaTypeInterface;
use NoreSources\Data\Serialization\StreamSerializerInterface;
use NoreSources\Data\Serialization\StreamUnserializerInterface;
use NoreSources\Data\Serialization\UnserializableMediaTypeInterface;
use NoreSources\Data\Serialization\YamlSerializer;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;

final class YamlSerializationTest extends SerializerTestCaseBase
{

	const CLASS_NAME = YamlSerializer::class;

	const MEDIA_TYPE = 'text/yaml';

	public function testImplements()
	{
		if (!$this->canTestSerializer())
			return;

		$serializer = $this->createSerializer();
		$this->assertImplements(
			[
				SerializableMediaTypeInterface::class,
				UnserializableMediaTypeInterface::class,
				MediaTypeListInterface::class,
				FileExtensionListInterface::class,
				StreamSerializerInterface::class,
				StreamUnserializerInterface::class,
				DataSerializerInterface::class,
				DataUnserializerInterface::class,
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
				'mediaType' => self::MEDIA_TYPE
			],
			'force mediatype with a structured syntax' => [
				'mediaType' => 'application/vnd.ns.php.data+yaml'
			],
			'bad mediatype' => [
				'mediaType' => 'application/json',
				'isUnserializable' => false
			]
		];

		foreach ([
			'string',
			'integer',
			// Result depends on PHP version
			// 'float',
			'boolean'
		] as $typename)
		{
			foreach ($tests as $options)
			{
				$this->assertTypeSerialization($typename, $options);
			}
		}
	}
}
