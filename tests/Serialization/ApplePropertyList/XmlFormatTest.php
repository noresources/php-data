<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\TestCase\Serialization\ApplePropertyList;

use NoreSources\Data\Serialization\ApplePropertyList\XmlFormat;
use NoreSources\Test\DerivedFileTestTrait;
use DateTime;

class XmlFormatTest extends \PHPUnit\Framework\TestCase
{
	use DerivedFileTestTrait;

	function setUp(): void
	{
		$this->setUpDerivedFileTestTrait(__DIR__ . '/../..');
	}

	function tearDown(): void
	{
		$this->tearDownDerivedFileTestTrait();
	}

	public function testNullValue()
	{
		$xml = new XmlFormat();
		$this->expectException(\InvalidArgumentException::class);
		$xml->createDocumentWithContent(null);
	}

	public function testArrayWithNullValue()
	{
		$xml = new XmlFormat();
		$this->expectException(\InvalidArgumentException::class);
		$xml->createDocumentWithContent([
			'foo',
			null,
			'bar'
		]);
	}

	public function testDictionaryWithNullEntry()
	{
		$method = __METHOD__;
		$suffix = null;
		$extension = 'plist';

		$xml = new XmlFormat();
		$document = $xml->createDocumentWithContent(
			[
				'foo' => 'bar',
				'null' => null
			]);
		$document->formatOutput = true;
		$this->assertDerivedFile($document->saveXML(), $method, $suffix,
			$extension);
	}

	public function testSaveLoad()
	{
		$method = __METHOD__;
		$extension = 'plist';
		$suffix = null;

		foreach ([
			'bool' => true,
			'int' => 42,
			'date' => DateTime::createFromFormat(DateTime::ISO8601,
				'2013-12-11T10:09:08+0700'),
			'real' => 3.14,
			'string' => 'Hello world!',
			'array' => [
				1,
				3,
				5,
				7,
				11
			],
			'dictionary' => [
				'key' => 'value',
				'foo' => 'bar'
			],
			'collection',
			//'dictionary-of-collections',
			//'dictionary-of-objects',
			'sparse',
			'table'
		] as $label => $data)
		{
			if (\is_integer($label) && \is_string($data))
			{
				$filename = $this->getReferenceFileDirectory() . '/' .
					$data . '.json';
				$this->assertFileExists($filename,
					$data . ' input file exists');
				$label = $data;
				$data = \json_decode(\file_get_contents($filename), true);
			}
			$derived = $this->getDerivedFilename($method, $label,
				$extension);
			$xml = new XmlFormat();
			$xml->writeFile($derived, $data);
			$actual = \file_get_contents($derived);
			//if (false)
			$this->assertDerivedFileEqualsReferenceFile($method, $label,
				$extension, $label . ' file content');
			$actual = $xml->readFile($derived);
			$this->assertEquals($data, $actual, $label . ' - reloaded');
		}
	}
}