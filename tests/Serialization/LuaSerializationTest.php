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
use NoreSources\Data\Serialization\LuaSerializer;
use NoreSources\Data\Serialization\SerializableMediaTypeInterface;
use NoreSources\Data\Serialization\StreamSerializerInterface;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\Type\TypeDescription;

final class LuaSerializationTest extends SerializerTestCaseBase
{

	const CLASS_NAME = LuaSerializer::class;

	public function testLua()
	{
		$directory = __DIR__ . '/../reference';
		$tests = [
			'nil' => null,
			'true' => true,
			'false' => false,
			'pi' => 3.14,
			'answer' => 42,
			'table' => [
				"key" => "value",
				"implicitely indexed",
				"subtree" => [
					5,
					6,
					7
				],
				'Not an identifier' => 'Somthing "in" the air',
				"05" => "It's not '5'"
			]
		];

		$serializer = new LuaSerializer();
		$mediaType = MediaTypeFactory::getInstance()->createFromString(
			'text/x-lua');

		foreach ($tests as $key => $data)
		{
			$filename = $directory . '/' . $key . '.data.lua';

			$this->assertTrue($serializer->isSerializableTo($data),
				'Can serialize ' . TypeDescription::getName($data) . ' ' .
				$key);

			$serialized = $serializer->serializeData($data, $mediaType);
			if (!\is_file($filename))
				\file_put_contents($filename, $serialized);
			$reference = file_get_contents($filename);

			$this->assertEquals($reference, $serialized,
				$key . ': Compare serialized data with reference file');
		}
	}

	public function testImplements()
	{
		if (!$this->canTestSerializer())
			return;

		$serializer = $this->createSerializer();
		$this->assertImplements(
			[
				SerializableMediaTypeInterface::class,
				MediaTypeListInterface::class,
				StreamSerializerInterface::class,
				DataSerializerInterface::class,
				FileSerializerInterface::class,
				FileExtensionListInterface::class
			], $serializer);
	}
}
