<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Container\Container;
use NoreSources\Data\Analyzer;
use NoreSources\Data\CollectionClass;
use NoreSources\Data\Serialization\Shellscript\ShellscriptWriter;
use NoreSources\Data\Serialization\Traits\SerializableMediaTypeTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerBaseTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerDataSerializerTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerFileSerializerTrait;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\Data\Utility\Traits\FileExtensionListTrait;
use NoreSources\Data\Utility\Traits\MediaTypeListTrait;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\Text\Text;
use NoreSources\Type\TypeDescription;

/**
 * Serialize data as a set of shell script variables.
 *
 * Supported media type parameters
 * <ul>
 * <li>variable-case=camel|macro|pascal|snake : Variable name code case</li>
 * <li>interpreter=string : Target shellscriptdialect</li>
 * <li>interpreter-version=semver : Target interpreter version</li>
 * <li>collection=* : Indicates the given content is a collection of object</li>
 * <li>key-property=string : When serializing collection of object. Indicates which element property
 * to use as variable name prefix</li>
 * </ul>
 */
class ShellscriptSerializer implements SerializableMediaTypeInterface,
	SerializableContentInterface, DataSerializerInterface,
	FileSerializerInterface, StreamSerializerInterface,
	MediaTypeListInterface, FileExtensionListInterface
{
	use MediaTypeListTrait;
	use FileExtensionListTrait;

	use SerializableMediaTypeTrait;

	use StreamSerializerBaseTrait;
	use StreamSerializerDataSerializerTrait;
	use StreamSerializerFileSerializerTrait;

	const MEDIA_TYPE = 'text/x-shellscript';

	/**
	 * Variable and key name style
	 *
	 * @var string
	 */
	const PARAMETER_VARIABLE_CASE = 'variable-case';

	/**
	 * camelCase variable names
	 *
	 * @var string
	 */
	const VARIABLE_CASE_CAMEL = 'camel';

	/**
	 * MACRO_CASE variable names
	 *
	 * @var string
	 */
	const VARIABLE_CASE_MACRO = 'macro';

	/**
	 * PascalCase variable names
	 *
	 * @var string
	 */
	const VARIABLE_CASE_PASCAL = 'pascal';

	const VARIABLE_CASE_SNAKE = 'snake';

	/**
	 * Target interpreter
	 *
	 * @var string
	 */
	const PARAMETER_INTERPRETER = 'interpreter';

	/**
	 * Target interpreter version
	 *
	 * @var string
	 */
	const PARAMETER__INTERPRETER_VERSION = 'interpreter-version';

	/**
	 * Indicates input data is a collection of object/array
	 *
	 * @var string
	 */
	const PARAMETER_COLLECTION = 'collection';

	/**
	 * Collection element key property to use as variable name prefix
	 *
	 * @var string
	 */
	const PARAMETER_KEY_PROPERTY = 'key-property';

	public function isContentSerializable($data)
	{
		return Container::isTraversable($data);
	}

	public function isSerializableToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		return $this->defaultIsSerializableToStream($stream, $data,
			$mediaType) && $this->isSerializableTo($data, $mediaType);
	}

	public function isSerializableToFile($filename, $data,
		MediaTypeInterface $mediaType = null)
	{
		return $this->defaultIsSerializableToFile($filename, $data,
			$mediaType) && $this->isSerializableTo($data, $mediaType);
	}

	public function isSerializableTo($data,
		MediaTypeInterface $mediaType = null)
	{
		if (!$this->defaultIsSerializableTo($data, $mediaType))
			return false;

		if (!Container::isTraversable($data))
			return false;

		$analyzer = Analyzer::getInstance();
		$class = $analyzer->getCollectionClass($data);

		if (($class & CollectionClass::DICTIONARY) ==
			CollectionClass::DICTIONARY)
			return true;

		if (!$this->dataIsCollection($mediaType))
			return false;

		$minDepth = $analyzer->getMinDepth($data);
		if ($minDepth < 2)
			return false;

		$first = Container::firstValue($data);
		$class = $analyzer->getCollectionClass($first);

		return (($class & CollectionClass::DICTIONARY) ==
			CollectionClass::DICTIONARY);
	}

	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		$variableNameTransformer = null;
		$interpreter = null;
		$interpreterVersion = null;
		$dataIsCollection = false;
		$keyProperty = null;
		if ($mediaType)
		{
			$p = $mediaType->getParameters();

			$keyProperty = Container::keyValue($p,
				self::PARAMETER_KEY_PROPERTY, $keyProperty);
			$dataIsCollection = Container::keyExists($p,
				self::PARAMETER_COLLECTION) || ($keyProperty !== null);

			$interpreter = Container::keyValue($p,
				self::PARAMETER_INTERPRETER);
			$interpreterVersion = Container::keyValue($p,
				self::PARAMETER__INTERPRETER_VERSION);

			if (($style = Container::keyValue($p,
				self::PARAMETER_VARIABLE_CASE)) &&
				($method = 'to' . $style . 'Case') &&
				\method_exists(Text::class, $method))
			{
				$variableNameTransformer = [
					Text::class,
					$method
				];
			}
		}

		$writer = $this->getInterpreterWriter($interpreter,
			$interpreterVersion);
		if ($variableNameTransformer)
			$writer->setVariableNameTransformer(
				$variableNameTransformer);

		if ($dataIsCollection)
		{
			foreach ($data as $key => $entry)
			{
				if ($keyProperty)
					$key = Container::keyValue($entry, $keyProperty,
						$key);
				foreach ($entry as $name => $value)
				{
					if (\is_numeric($key))
						$name .= '_' . $key;
					else
						$name = $key . '_' . $name;
					$writer->writeVariableDefinition($stream, $name,
						$value);
				}
			}
		}
		else
		{
			foreach ($data as $name => $value)
				$writer->writeVariableDefinition($stream, $name, $value);
		}
	}

	public function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				self::MEDIA_TYPE)
		];
	}

	/**
	 *
	 * @param string $interpreter
	 *        	Interpreter name
	 * @param string|number $version
	 *        	Interpreter version
	 * @return ShellscriptWriter
	 */
	protected function getInterpreterWriter($interpreter = null,
		$version = null)
	{
		$className = ShellscriptWriter::class;
		$localName = TypeDescription::getLocalName($className, true);
		$namespace = TypeDescription::getNamespaces($className, true);

		if (\is_string($interpreter))
		{
			$n = \implode('\\', $namespace) . '\\' .
				Text::toPascalCase($interpreter) . $localName;

			if (\class_exists($n))
				$className = $n;
		}

		$cls = new \ReflectionClass($className);
		return $cls->newInstance();
	}

	protected function getSupportedMediaTypeParameterValues()
	{
		if (!isset(self::$supportedMediaTypeParameters))
		{
			self::$supportedMediaTypeParameters = [
				self::MEDIA_TYPE => [
					self::PARAMETER_VARIABLE_CASE => [
						self::VARIABLE_CASE_CAMEL,
						self::VARIABLE_CASE_MACRO,
						self::VARIABLE_CASE_PASCAL,
						self::VARIABLE_CASE_SNAKE
					],
					self::PARAMETER__INTERPRETER_VERSION => true,
					self::PARAMETER_INTERPRETER => true,
					self::PARAMETER_COLLECTION => true,
					self::PARAMETER_KEY_PROPERTY => true
				]
			];
		}

		return self::$supportedMediaTypeParameters;
	}

	public function getFileExtensions()
	{
		return [
			'sh'
		];
	}

	private function dataIsCollection(
		MediaTypeInterface $mediaType = null)
	{
		if (!$mediaType)
			return false;
		$p = $mediaType->getParameters();
		return $p->has(self::PARAMETER_COLLECTION) ||
			$p->has(self::PARAMETER_KEY_PROPERTY);
	}

	private static $supportedMediaTypeParameters;
}


