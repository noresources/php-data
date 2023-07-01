<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Shellscript;

use NoreSources\Container\Container;
use NoreSources\Data\Analyzer;
use NoreSources\Data\CollectionClass;
use NoreSources\Text\Text;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeConversionException;

/**
 * Shellscript variable definition writer
 */
class ShellscriptWriter
{

	const EOL = "\n";

	/**
	 *
	 * @param callable $variableNameTransformer
	 *        	Variable name formatter function
	 * @param callable $stringifier
	 *        	Stringifier function
	 */
	public function __construct($variableNameTransformer = null,
		$stringifier = null)
	{
		if (\is_callable($variableNameTransformer))
			$this->variableNameTransformer = $variableNameTransformer;
		else
			$this->variableNameTransformer = [
				self::class,
				'defaultVariableCaseTransformer'
			];
		if (\is_callable($stringifier))
			$this->stringifier = $stringifier;
		else
			$this->stringifier = [
				self::class,
				'defaultStringifier'
			];
	}

	/**
	 * Get the first index of a variable containing a list of values.
	 *
	 * @return number
	 */
	public function getListStartIndex()
	{
		return 0;
	}

	/**
	 *
	 * @param resource $stream
	 *        	Output stream
	 * @param string $name
	 *        	Variable name
	 * @param mixed $value
	 *        	Variable value
	 */
	public function writeVariableDefinition($stream, $name, $value)
	{
		\fwrite($stream, $this->getVariableDefinition($name, $value));
	}

	public function getVariableDefinition($name, $value)
	{
		$variable = $this->normalizeVariableName($name);

		if (\is_array($value) ||
			(\is_object($value) && Container::isTraversable($value)))
			return $this->getArrayVariableDefinition($variable, $value);

		return $variable . '=' . $this->getValue($value) . self::EOL;
	}

	protected function getArrayVariableDefinition($variable, $values)
	{
		$analyzer = Analyzer::getInstance();
		$maxDepth = $analyzer->getMaxDepth($values);
		$class = $analyzer->getCollectionClass($values);
		if ($maxDepth == 1)
		{
			if ($class == CollectionClass::LIST)
				return $this->getListVariableDefinition($variable,
					$values);
			return $this->getMapVariableDefinition($variable, $values);
		}

		$text = '';
		foreach ($values as $key => $value)
		{
			$subMaxDepth = $analyzer->getMaxDepth($value);
			if ($subMaxDepth == 0)
			{
				$text .= $variable . '[' .
					$this->normalizeVariableName($key) . ']=' .
					$this->getValue($value) . self::EOL;
				continue;
			}
			$subName = $variable . ' ' . $key;
			$text .= $this->getVariableDefinition($subName, $value);
		}
		return $text;
	}

	protected function getListVariableDefinition($variable, $values)
	{
		$text = $variable . '=(\\' . self::EOL;
		foreach ($values as $value)
			$text .= "\t" . $this->getValue($value) . '\\' . self::EOL;
		return $text .= ')' . self::EOL;
	}

	protected function getMapVariableDefinition($variable, $values)
	{
		$text = '';
		foreach ($values as $key => $value)
		{
			$text .= $variable . '[' . $this->normalizeVariableName(
				$key) . ']=' . $this->getValue($value) . self::EOL;
		}
		return $text;
	}

	public function getValue($value)
	{
		if (\is_null($value))
			return $this->getNullValue();
		elseif (\is_bool($value))
			return $this->getBooleanValue($value);
		elseif (\is_numeric($value))
			return $this->getNumberValue($value);
		return $this->getStringValue($value);
	}

	public function getStringValue($value)
	{
		if (!\is_string($value))
			$value = \call_user_func($this->stringifier, $value);
		return \escapeshellarg($value);
	}

	public function getNumberValue($value)
	{
		return TypeConversion::toString($value);
	}

	public function getBooleanValue($value)
	{
		return ($value ? 'true' : 'false');
	}

	public function getNullValue()
	{
		return $this->getStringValue('');
	}

	public function normalizeVariableName($name)
	{
		$name = \call_user_func($this->variableNameTransformer, $name);
		if (\strlen($name) == 0)
			$name = '_';
		elseif (\ctype_digit(\substr($name, 0, 1)))
			$name = '_' . $name;
		return $name;
	}

	/**
	 *
	 * @param callable $variableNameTransformer
	 *        	Transform function
	 * @throws \InvalidArgumentException
	 */
	public function setVariableNameTransformer($variableNameTransformer)
	{
		if (!\is_callable($variableNameTransformer))
			throw new \InvalidArgumentException(
				'Variable name transformer must be a callable');
		$this->variableNameTransformer = $variableNameTransformer;
	}

	/**
	 * Set the strigification function
	 *
	 * @param callable $callback
	 *        	Stringification function
	 * @throws \InvalidArgumentException
	 */
	public function setStringifier($callback)
	{
		if (!\is_callable($callback))
			throw new \InvalidArgumentException(
				'Stringifier must be a callable');
		$this->stringifier = $callback;
	}

	public static function defaultVariableCaseTransformer($text)
	{
		return Text::toSnakeCase($text);
	}

	public static function defaultStringifier($value)
	{
		try
		{
			return TypeConversion::toString($value);
		}
		catch (TypeConversionException $e)
		{}
		return serialize($value);
	}

	/**
	 *
	 * @var callable
	 */
	private $variableNameTransformer;

	/**
	 *
	 * @var callable
	 */
	private $stringifier;
}
