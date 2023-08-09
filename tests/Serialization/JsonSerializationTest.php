<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\TestCase\Serialization;

use NoreSources\Container\CaseInsensitiveKeyMapTrait;
use NoreSources\Container\Container;
use NoreSources\Container\ContainerPropertyInterface;
use NoreSources\Data\Serialization\DataSerializerInterface;
use NoreSources\Data\Serialization\DataUnserializerInterface;
use NoreSources\Data\Serialization\FileSerializerInterface;
use NoreSources\Data\Serialization\FileUnserializerInterface;
use NoreSources\Data\Serialization\JsonSerializer;
use NoreSources\Data\Serialization\SerializableMediaTypeInterface;
use NoreSources\Data\Serialization\StreamSerializerInterface;
use NoreSources\Data\Serialization\StreamUnserializerInterface;
use NoreSources\Data\Serialization\UnserializableMediaTypeInterface;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaTypeFactory;

class SomeJsonSerializableClass implements \JsonSerializable
{

	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return "My name is JSON";
	}
}

class OpaqueClass
{

	public $secret = 'I am not a secret';
}

class SomeTraversableClass implements ContainerPropertyInterface
{

	public $property = 'value';

	private $privateProperty = 'shht';

	public function getContainerProperties()
	{
		return Container::properties($this, true) |
			Container::TRAVERSABLE;
	}
}

class SomeTraversableContainer implements \ArrayAccess, \Countable,
	\IteratorAggregate
{
	use CaseInsensitiveKeyMapTrait;

	public function __construct($array = array())
	{
		$this->initializeCaseInsensitiveKeyMapTrait($array);
	}
}

final class JsonSerializationTest extends SerializerTestCaseBase
{

	const CLASS_NAME = JsonSerializer::class;

	const MEDIA_TYPE = 'application/json';

	const EXTENSION = 'json';

	public function testImplements()
	{
		$serializer = $this->createSerializer();
		$this->assertImplements(
			[
				UnserializableMediaTypeInterface::class,
				SerializableMediaTypeInterface::class,
				// Helpers
				MediaTypeListInterface::class,
				FileExtensionListInterface::class,
				// Stream interfaces
				StreamSerializerInterface::class,
				StreamUnserializerInterface::class,
				// Data interfaces
				DataSerializerInterface::class,
				DataUnserializerInterface::class,
				// File interface
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
				'mediaType' => 'application/vnd.ns.php.data+' .
				self::EXTENSION
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

	public function testCanUnserializeFile()
	{
		$supported = JsonSerializer::prerequisites();
		if (!$supported)
			return $this->assertFalse($supported, 'Not supported');

		$serializer = new JsonSerializer();
		$tests = [
			'by extension' => [
				'arguments' => [
					'foo.' . self::EXTENSION
				],
				'expected' => true
			],
			'by media type' => [
				'arguments' => [
					null,
					MediaTypeFactory::getInstance()->createFromString(
						'application/json')
				],
				'expected' => true
			]
		];

		foreach ($tests as $label => $test)
		{
			$args = $test['arguments'];
			$expected = $test['expected'];
			$actual = \call_user_func_array(
				[
					$serializer,
					'isUnserializableFromFile'
				], $args);
			$this->assertEquals($expected, $actual, $label);
		}
	}

	public function testParameters()
	{
		if (!$this->canTestSerializer())
			return;

		$serializer = $this->createSerializer();

		$mediaType = MediaTypeFactory::createFromString(
			self::MEDIA_TYPE);

		$this->assertSupportsMediaTypeParameter(
			[

				'style parameter' => [
					true,
					'style'
				],
				'unexpected parameter' => [
					false,
					'unholy'
				],
				'pretty style' => [
					true,
					'style',
					'pretty'
				],
				'ugly style' => [
					false,
					'style',
					'ugly'
				]
			], $serializer, $mediaType);
	}

	public function testTransform()
	{
		if (!$this->canTestSerializer())
			return;
		$serializer = $this->createSerializer();
		$mediaType = MediaTypeFactory::getInstance()->createFromString(
			self::MEDIA_TYPE);
		$mediaType->getParameters()->offsetSet('style', 'pretty');

		$data = new \ArrayObject(
			[
				'description' => 'ArrayObject is traversable.',
				'inner-object' => new SomeTraversableContainer(
					[
						'hidden' => 'value',
						'reason' => 'If depth parameter is set to 0, this will be hidden'
					]),
				'traversable-object' => new SomeTraversableClass(),
				'opaque-object' => new OpaqueClass(),
				'date-time' => new \DateTime('@0')
			]);
		$method = __METHOD__;
		$extension = 'json';
		foreach ([

			'zero' => 0,
			'one' => 1,
			'two' => 2,
			'infinite' => -1
		] as $suffix => $depth)
		{
			$label = 'Serialize object to JSON ';
			if ($depth == 0)
				$label .= 'without pre-transformation';
			elseif ($depth < 1)
				$label .= 'with full pre-transformation';
			else
				$label .= 'with pre-transformation limited to ' . $depth .
					' recursion';

			$mediaType->getParameters()->offsetSet('preprocess-depth',
				$depth);
			$data['media-type'] = $mediaType->jsonSerialize();
			$filename = $this->getDerivedFilename($method, $suffix,
				$extension, $label);
			$serializer->serializeToFile($filename, $data, $mediaType);
			$this->assertDerivedFileEqualsReferenceFile($method, $suffix,
				$extension, $label);
		}
	}
}
