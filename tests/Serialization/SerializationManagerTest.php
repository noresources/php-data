<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\TestCase\Serialization;

use NoreSources\Container\Container;
use NoreSources\Data\Serialization\JsonSerializer;
use NoreSources\Data\Serialization\SerializationManager;
use NoreSources\Data\Serialization\UrlEncodedSerializer;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\Type\TypeDescription;

final class SerializationManagerTest extends \PHPUnit\Framework\TestCase
{

	public function testDeserializerSelection()
	{
		$tests = [
			'URL encoded (text)' => [
				'input' => \urlencode('Hello world!'),
				'expected' => 'Hello world!',
				'type' => 'application/x-www-form-urlencoded',
				'select' => UrlEncodedSerializer::class
			],
			'JSON' => [
				'if' => JsonSerializer::prerequisites(),
				'input' => \json_encode('Hello world!'),
				'expected' => 'Hello world!',
				'type' => 'application/json',
				'select' => JsonSerializer::class
			],
			'URL encoded params' => [
				'input' => \http_build_query([
					'hello' => 'world'
				]),
				'expected' => [
					'hello' => 'world'
				],
				'type' => 'application/x-www-form-urlencoded',
				'select' => UrlEncodedSerializer::class
			]
		];

		$manager = new SerializationManager();
		foreach ($tests as $label => $test)
		{
			$available = Container::keyValue($test, 'if', true);
			if (!$available)
			{
				$this->assertFalse($available, $label . ' not supported');
				continue;
			}
			$input = Container::keyValue($test, 'input');
			$expected = Container::keyValue($test, 'expected', $input);
			$mediaType = Container::keyValue($test, 'type');
			if ($mediaType)
				$mediaType = MediaTypeFactory::getInstance()->createFromString(
					$mediaType);

			$deserializers = $manager->getDataUnserializersFor($input,
				$mediaType);

			$deserializerNameList = Container::implodeValues(
				\array_map([
					TypeDescription::class,
					'getLocalName'
				], $deserializers),
				[
					Container::IMPLODE_BETWEEN => ', ',
					Container::IMPLODE_BETWEEN_LAST => ' or '
				]);
			if (empty($deserializerNameList))
				$deserializerNameList = 'None';

			$deserializersText = \array_map(
				[
					TypeDescription::class,
					'getName'
				], $deserializers);

			if (($select = Container::keyValue($test, 'select')))
			{
				$this->assertContains($select, $deserializersText,
					$label . ' using ' . $deserializerNameList);
			}

			$actual = null;
			$errorMessage = '';
			try
			{
				$actual = $manager->unserializeData($input, $mediaType);
			}
			catch (\Exception $e)
			{
				$errorMessage = PHP_EOL . $e->getMessage();
				$actual = TypeDescription::getName($e);
			}
			$this->assertEquals($expected, $actual,
				$label . ' deserialization value using ' .
				$deserializerNameList . $errorMessage);
		}
	}

	public function testManagerFile()
	{
		$manager = new SerializationManager();

		foreach ([
			'getSerializableMediaRanges',
			'getUnserializableMediaRanges'
		] as $method)
		{
			$result = \call_user_func([
				$manager,
				$method
			]);
			$this->assertEquals('array',
				TypeDescription::getName($result),
				'Check if ' . $method . ' returns array');
		}

		foreach ([
			'a' => 'A file'
		] as $key => $label)
		{
			foreach ([
				'json',
				'yaml'
			] as $extension)
			{
				if (!\extension_loaded($extension))
					continue;
				$filename = __DIR__ . '/../reference/' . $key . '.' .
					$extension;
				$derivedFilename = __DIR__ . '/../derived/' . $key . '.' .
					$extension;
				$this->assertFileExists($filename,
					$extension . ' file for test ' . $label);

				$this->assertTrue(
					$manager->isUnserializableFromFile($filename),
					$label . ' can unserialize .' . $extension);

				$data = $manager->unserializeFromFile($filename);

				$this->assertTrue($manager->isSerializableTo($data),
					$label .
					' - Can serialize the result of file deserialization');

				$serialized = $manager->serializeData($data);
				$this->assertEquals('string',
					TypeDescription::getName($serialized),
					'Re-serialize');

				$this->assertTrue(
					$manager->isUnserializableFrom($serialized),
					$label . ' can re-unserialize serialized data');

				$data = $manager->unserializeData($serialized);

				$this->assertTrue(
					$manager->isSerializableToFile($derivedFilename,
						$data),
					$label . ' - ca re-serialize data to file');

				$manager->serializeToFile($derivedFilename, $data);
				$this->assertFileExists($derivedFilename,
					$label . ' ' . $extension . ' serialized file');
				$data2 = $manager->unserializeFromFile($derivedFilename);
				$this->assertEquals($data, $data2,
					$label . ' serialization cycle to ' . $extension);
			}
		}
	}

	public function testFileExtensions()
	{
		$manager = new SerializationManager();
		$extensions = $manager->getFileExtensions();
		$this->assertEquals('array',
			TypeDescription::getName($extensions),
			'Extension list is array');

		$this->assertContains('ini', $extensions);

		$this->assertTrue($manager->matchFileExtension('csv'),
			'Manager supports csv file extension');
	}

	public function testBuildSerialiableMediaTypeListMatchingMediaRanges()
	{
		$tests = [
			'Basic JSON' => [
				'accept' => [
					'application/json'
				],
				'expected' => [
					'application/json'
				]
			],
			'Basic JSON or CSV' => [
				'accept' => [
					'application/json',
					'text/csv'
				],
				'expected' => [
					'application/json',
					'text/csv'
				]
			],
			'Pretty JSON' => [
				'accept' => [
					'application/json; style=pretty'
				],
				'expected' => [
					'application/json; style=pretty'
				]
			],
			'Pretty JSON and some unsupported parameter' => [
				'accept' => [
					'application/json; foo=bar; style=pretty'
				],
				'expected' => [
					'application/json; style=pretty'
				]
			],
			'Multiple cases' => [
				'accept' => [
					'application/json; foo=bar; style=pretty',
					'text/csv; eol=EOL',
					'text/csv; escape=!; exists=nope'
				],
				'expected' => [
					'application/json; style=pretty',
					'text/csv; eol=EOL',
					'text/csv; escape=!'
				]
			]
		];
		$manager = new SerializationManager();
		foreach ($tests as $label => $test)
		{
			$accept = Container::map($test['accept'],
				function ($k, $v) {
					return MediaTypeFactory::createFromString($v, true);
				});

			$actual = $manager->buildSerialiableMediaTypeListMatchingMediaRanges(
				$accept);
			$actual = Container::map($actual,
				function ($k, $v) {
					return $v->jsonSerialize();
				});
			$expected = $test['expected'];
			sort($actual);
			sort($expected);
			$this->assertEquals($expected, $actual, $label);
		}
	}
}
