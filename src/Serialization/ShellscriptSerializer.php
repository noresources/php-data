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

	/**
	 * Variable and key name style
	 *
	 * @var string
	 */
	const PARAMETER_STYLE = 'style';

	/**
	 * camelCase variable names
	 *
	 * @var string
	 */
	const STYLE_CAMEL = 'camel';

	/**
	 * MACRO_CASE variable names
	 *
	 * @var string
	 */
	const STYLE_MACRO = 'macro';

	/**
	 * PascalCase variable names
	 *
	 * @var string
	 */
	const STYLE_PASCAL = 'pascal';

	const STYLE_SNAKE = 'snake';

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

	public function isContentSerializable($data)
	{
		$analyzer = Analyzer::getInstance();
		$class = $analyzer->getCollectionClass($data);
		return ($class & CollectionClass::DICTIONARY) ==
			CollectionClass::DICTIONARY;
	}

	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		$variableNameTransformer = null;
		$interpreter = null;
		$interpreterVersion = null;
		if ($mediaType)
		{
			$p = $mediaType->getParameters();
			$interpreter = Container::keyValue($p,
				self::PARAMETER_INTERPRETER);
			$interpreterVersion = Container::keyValue($p,
				self::PARAMETER__INTERPRETER_VERSION);

			if (($style = Container::keyValue($p, self::PARAMETER_STYLE)) &&
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
		foreach ($data as $name => $value)
		{
			$writer->writeVariableDefinition($stream, $name, $value);
		}
	}

	public function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				'text/x-shellscript')
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
				'text/x-shellscript' => [
					self::PARAMETER_STYLE => [
						self::STYLE_CAMEL,
						self::STYLE_MACRO,
						self::STYLE_PASCAL,
						self::STYLE_SNAKE
					]
				],
				self::PARAMETER__INTERPRETER_VERSION => true,
				self::PARAMETER_INTERPRETER => true
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

	private static $supportedMediaTypeParameters;
}


