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
use NoreSources\Data\Serialization\StreamSerializerInterface;
use NoreSources\Data\Serialization\StreamUnserializerInterface;
use NoreSources\Data\Serialization\UrlEncodedSerializer;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\Type\TypeDescription;

final class UrlEncodedSerializationTest extends SerializerTestCaseBase
{

	const CLASS_NAME = UrlEncodedSerializer::class;

	public function testImplements()
	{
		if (!$this->canTestSerializer())
			return;

		$serializer = $this->createSerializer();
		$this->assertImplements(
			[
				// Helpers
				MediaTypeListInterface::class,
				// FileExtensionListInterface::class,
				// Stream interfaces
				StreamSerializerInterface::class,
				StreamUnserializerInterface::class,
				// Data interfaces
				DataSerializerInterface::class,
				DataUnserializerInterface::class
				// File interface
				// FileSerializerInterface::class,
				// FileUnserializerInterface::class
			], $serializer);
	}

	public function testUrlEncoded()
	{
		$serializer = new UrlEncodedSerializer();
		$mediaType = MediaTypeFactory::getInstance()->createFromString(
			'application/x-www-form-urlencoded');

		foreach ([
			'Text' => 'text',
			'A text with space' => 'A text with space',
			'Key-values' => [
				'key' => 'value',
				'Complex' => 'A more "tricky" string'
			]
		] as $label => $value)
		{
			$this->assertTrue(
				$serializer->isSerializableTo($value, $mediaType),
				'Can serialize ' . $label . ' with media type');
			$this->assertTrue($serializer->isSerializableTo($value),
				'Can serialize ' . $label . ' without media type');

			$serialized = $serializer->serializeData($value);

			$this->assertTrue(
				$serializer->isUnserializableFrom($serialized, $mediaType),
				'Can unserialize ' . $label . ' ' .
				TypeDescription::getName($serialized) .
				' with media type');

			$this->assertTrue(
				$serializer->isUnserializableFrom($serialized),
				'Can unserialize ' . $label . ' ' .
				TypeDescription::getName($serialized) .
				' without media type');

			$unserialized = $serializer->unserializeData($serialized);

			$this->assertEquals($value, $unserialized,
				'Serialization/Deserialization cycle');
		}
	}
}
