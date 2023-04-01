<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\TestCase\Serialization;

use NoreSources\Data\Serialization\DataUnserializerInterface;
use NoreSources\Data\Serialization\FileUnserializerInterface;
use NoreSources\Data\Serialization\IniSerializer;
use NoreSources\Data\Serialization\StreamUnserializerInterface;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;

final class IniSerializationTest extends SerializerTestCaseBase
{

	const CLASS_NAME = IniSerializer::class;

	public function testImplements()
	{
		if (!$this->canTestSerializer())
			return;

		$serializer = $this->createSerializer();
		$this->assertImplements(
			[
				MediaTypeListInterface::class,
				FileExtensionListInterface::class,

				StreamUnserializerInterface::class,
				DataUnserializerInterface::class,
				FileUnserializerInterface::class
			], $serializer);
	}

	public function testIni()
	{
		$directory = __DIR__ . '/../data';
		$ini = new IniSerializer();

		foreach ([
			'a'
		] as $name)
		{
			$filename = $directory . '/' . $name . '.ini';

			$this->assertTrue($ini->isUnserializableFromFile($filename),
				'Can unserialize ' .
				\pathinfo($filename, PATHINFO_FILENAME));

			$data = $ini->unserializeFromFile($filename);
		}
	}
}
