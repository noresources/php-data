<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\TestCase\Serialization;

use NoreSources\Data\Serialization\DataUnserializerInterface;
use NoreSources\Data\Serialization\FileUnserializerInterface;
use NoreSources\Data\Serialization\IniSerializer;
use NoreSources\Data\Serialization\StreamUnserializerInterface;
use NoreSources\Data\Serialization\UnserializableMediaTypeInterface;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeFactory;

final class IniSerializationTest extends SerializerTestCaseBase
{

	const CLASS_NAME = IniSerializer::class;

	const EXTENSION = 'ini';

	const MEDIA_TYPE = 'text/x-ini';

	public function testImplements()
	{
		if (!$this->canTestSerializer())
			return;

		$serializer = $this->createSerializer();
		$this->assertImplements(
			[
				UnserializableMediaTypeInterface::class,
				MediaTypeListInterface::class,
				FileExtensionListInterface::class,

				StreamUnserializerInterface::class,
				DataUnserializerInterface::class,
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
			'bad mediatype' => [
				'mediaType' => 'application/yaml',
				'isUnserializable' => false
			]
		];

		foreach ([
			'string',
			'integer',
			'float',
			'boolean'
		] as $typename)
		{
			foreach ($tests as $options)
			{
				$this->assertTypeSerialization($typename, $options);
			}
		}
	}

	public function testAgainstPhpBuiltin()
	{
		$directory = __DIR__ . '/../reference';
		$ini = new IniSerializer();

		foreach ([
			'a'
		] as $name)
		{
			$filename = $directory . '/' . $name . '.' . self::EXTENSION;

			$this->assertTrue($ini->isUnserializableFromFile($filename),
				'Can unserialize ' .
				\pathinfo($filename, PATHINFO_FILENAME));

			try
			{
				$actual = $ini->unserializeFromFile($filename);
			}
			catch (\Exception $e)
			{
				$actual = [
					\get_class($e) => $e->getMessage()
				];
				foreach ($e->getTrace() as $e)
				{
					$actual[] = $e['function'] . ' ' .
						\basename($e['file']) . ' ' . $e['line'];
				}
			}

			$this->assertEquals(
				\parse_ini_file($filename, INI_SCANNER_TYPED), $actual,
				$name . 'file');
		}
	}

	public function testSystemdUnit()
	{
		$pathUnitFilename = __DIR__ . '/../reference/ini/systemd.path';
		$this->assertFileExists($pathUnitFilename,
			'systemd path unit file exists');
		$serializer = $this->createSerializer();

		$mediaType = MediaTypeFactory::getInstance()->createFromString(
			IniSerializer::MEDIA_TYPE_SYSTEMD_UNIT);
		$pathUnit = $serializer->unserializeFromFile($pathUnitFilename,
			$mediaType);
		$this->assertArrayHasKey('Path', $pathUnit,
			'Data has Path section');
		$path = $pathUnit['Path'];
		$this->assertArrayHasKey('PathExists', $path,
			'Path section has PathExists');
		$this->assertEquals([
			'/tmp/bar',
			'/tmp/baz'
		], $path['PathExists'],
			'Duplicated PathExists becames array value');

		$suffix = null;
		$extension = 'path';
		$method = __METHOD__;
		$derivedFilename = $this->getDerivedFilename($method, $suffix,
			$extension);
		$this->assertCreateFileDirectoryPath($derivedFilename,
			'systemd.path re-serialization');
		$serializer->serializeToFile($derivedFilename, $pathUnit,
			$mediaType);
		$this->assertFileEquals($pathUnitFilename, $derivedFilename,
			'systemd.path re-serialization');
	}

	public function testParameters()
	{
		$this->assertSupportsMediaTypeParameter(
			[
				[
					true,
					'indent'
				],
				[
					true,
					'list-separator'
				],
				[
					true,
					'null-string'
				],
				[
					true,
					'single-value-key'
				],
				[
					true,
					'section-glue'
				]
			]);
	}
}
