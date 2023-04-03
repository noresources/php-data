<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\TestCase\Serialization;

use NoreSources\Container\Container;
use NoreSources\Data\Serialization\DataSerializerInterface;
use NoreSources\Data\Serialization\DataUnserializerInterface;
use NoreSources\Data\Serialization\FileSerializerInterface;
use NoreSources\Data\Serialization\FileUnserializerInterface;
use NoreSources\Data\Serialization\StreamSerializerInterface;
use NoreSources\Data\Serialization\StreamUnserializerInterface;
use NoreSources\Data\Test\SerializerAssertionTrait;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\Type\TypeDescription;

class SerializerTestCaseBase extends \PHPUnit\Framework\TestCase
{
	use SerializerAssertionTrait;

	const REFERENCE_STRING_DATA = 'Hello world';

	const REFERENCE_INTEGER_DATA = 42;

	const REFERENCE_FLOAT_DATA = 3.14159;

	const REFERENCE_BOOLEAN_DATA = true;

	const CLASS_NAME = 'MustOverride';

	public function setUp(): void
	{
		$this->initializeSerializerAssertions(static::CLASS_NAME,
			__DIR__ . '/..');
	}

	public function assertImplements($list, $serializer, $message = null)
	{
		if (empty($message))
			$message = TypeDescription::getLocalName($serializer) .
				' interfaces';
		foreach ($list as $i)
		{
			$this->assertInstanceOf($i, $serializer, $message);
		}
	}

	public function assertTypeSerialization($typename,
		$options = array())
	{
		/**
		 *
		 * @var StreamSerializerInterface|StreamUnserializerInterface|DataSerializerInterface|DataUnserializerInterface|FileSerializerInterface|FileSerializerInterface $serializer
		 */
		$serializer = Container::keyValue($options, 'serializer');
		$mediaType = Container::keyValue($options, 'mediaType');
		if (\is_string($mediaType))
			$mediaType = MediaTypeFactory::getInstance()->createFromString(
				$mediaType);

		$method = Container::keyValue($options, 'method', __METHOD__);
		$suffix = Container::keyValue($options, 'suffix', $typename);

		$isUnserializable = Container::keyValue($options,
			'isUnserializable', true);
		$extension = Container::keyValue($options, 'extension');

		if (!$serializer)
			$serializer = static::createSerializer();

		$serializeName = TypeDescription::getLocalName($serializer);
		$serializeShortName = \preg_replace('/Serializer$/', '',
			$serializeName);

		if (!$extension)
		{
			if ($serializer instanceof FileExtensionListInterface)
			{
				$extension = Container::firstValue(
					$serializer->getFileExtensions());
			}
		}

		$filename = __DIR__ . '/../data/' . $typename . '/' .
			$serializeName . '.' . $extension;
		$this->assertFileExists($filename,
			'Reference file for ' . $serializeShortName . ' exists');

		$mediaTypeMessagePart = (($mediaType) ? ' with ' .
			$mediaType->jsonSerialize() : ' with undefined') .
			' media type';

		$actual = false;
		$stream = null;
		if ($serializer instanceof FileUnserializerInterface)
		{
			$actual = $serializer->isUnserializableFromFile($filename,
				$mediaType);

			$this->assertEquals($isUnserializable, $actual,
				$serializeName . ' can unserialize file ' .
				\basename($filename) . $mediaTypeMessagePart);
		}

		if ($serializer instanceof StreamUnserializerInterface)
		{
			$stream = \fopen($filename, 'rb');
			$actual = $serializer->isUnserializableFromStream($stream,
				$mediaType);
			$this->assertEquals($isUnserializable, $actual,
				$serializeName . ' can unserialize stream' .
				$mediaTypeMessagePart);
		}

		if (!$isUnserializable)
		{
			if ($stream)
				\fclose($stream);
			return;
		}

		$hasExpected = false;
		if (Container::keyExists($options, 'expected'))
		{
			$expected = Container::keyValue($options, 'expected');
			$hasExpected = true;
		}
		else
		{
			$cls = new \ReflectionClass($this);
			$referenceConstantName = 'REFERENCE_' .
				\strtoupper($typename) . '_DATA';
			$hasExpected = $cls->hasConstant($referenceConstantName);
			if ($hasExpected)
				$expected = $cls->getConstant($referenceConstantName);
		}

		if ($hasExpected)
		{
			if ($serializer instanceof FileUnserializerInterface)
			{
				$actual = $serializer->unserializeFromStream($stream,
					$mediaType);
				$this->assertEquals($expected, $actual,
					$serializeName . ' unserialize ' . $typename .
					' data from file ' . \basename($filename) .
					$mediaTypeMessagePart);
			}

			if ($serializer instanceof StreamUnserializerInterface)
			{
				try
				{
					// ?
					\fseek($stream, 0);
					$actual = $serializer->unserializeFromStream(
						$stream, $mediaType);
				}
				catch (\Exception $e)
				{
					$actual = $e->getMessage();
				}
				$this->assertEquals($expected, $actual,
					$serializeName . ' unserialize data from stream' .
					$mediaTypeMessagePart);
			}
		}

		if ($stream)
			\fclose($stream);

		if ($hasExpected)
		{
			$derived = $this->getDerivedFilename($method, $suffix,
				$extension, $typename);
			$data = $expected;
			if ($serializer instanceof FileSerializerInterface)
			{
				$this->assertTrue(
					$serializer->isSerializableToFile($derived, $data,
						$mediaType),
					$serializeName . ' can serialize file ' .
					$mediaTypeMessagePart);
				$serializer->serializeToFile($derived, $data, $mediaType);
				$this->assertFileEquals($filename, $derived,
					'Compare ' . $serializeName .
					' serialized file content with reference');
			}
		}
	}

	/**
	 *
	 * @return StreamSerializerInterface|StreamUnserializerInterface|DataSerializerInterface|DataUnserializerInterface|FileSerializerInterface|FileSerializerInterface
	 */
	protected function createSerializer()
	{
		$cls = new \ReflectionClass(static::CLASS_NAME);
		return \call_user_func_array([
			$cls,
			'newInstanceArgs'
		], \func_get_args());
	}
}
