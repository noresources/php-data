<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Lua;

use NoreSources\Container\Stack;
use NoreSources\Data\Serialization\SerializationException;
use NoreSources\Type\TypeConversion;

class GenericLuaUnserializerVisitor
{

	public function __construct()
	{
		$this->stack = new Stack();
		$this->stack->push(new LuaUnserializerVisitorState());
	}

	public function finalize()
	{
		if ($this->stack->count() != 1)
			throw new SerializationException('Incomplete lua data.');
		return $this->stack->value;
	}

	public function exitFloatValue($context)
	{
		$this->stack->value = TypeConversion::toFloat(
			$context->getText());
	}

	public function exitIntegerValue($context)
	{
		$this->stack->value = TypeConversion::toInteger(
			$context->getText());
	}

	public function exitStringValue($context)
	{
		$text = $context->getText();
		$length = \strlen($text);
		$text = \substr($text, 1, $length - 2);
		$text = \stripslashes($text);
		$this->stack->value = $text;
	}

	public function exitStringKey($context)
	{
		$this->stack->key = $context->getText();
	}

	public function exitProtectedKeyContent($context)
	{
		$text = $context->getText();

		if (\ctype_digit($text))
		{
			$this->stack->key = \intval($text);
			return;
		}

		$length = \strlen($text);
		$text = \substr($text, 1, $length - 2);
		$text = \stripslashes($text);
		$this->stack->key = $text;
	}

	public function exitKeywordConstantValue($context)
	{
		static $map = [
			'true' => true,
			'false' => false,
			'nil' => null
		];
		$text = $context->getText();
		$this->stack->value = $map[$text];
	}

	public function enterTable($context)
	{
		$this->stack->value = [];
	}

	public function enterTableEntry($context)
	{
		$this->stack->push(new LuaUnserializerVisitorState());
	}

	public function exitTableEntry($context)
	{
		$state = $this->stack->pop();
		$array = $this->stack->value;
		if (!\is_array($array))
			throw new \RuntimeException(
				'Current value is not an array.');

		if (empty($state->key))
			$array[] = $state->value;
		elseif (\is_integer($state->key))
			$array[$state->key - 1] = $state->value;
		else
			$array[$state->key] = $state->value;
		$this->stack->value = $array;
	}

	/**
	 *
	 * @var Stack
	 */
	private $stack;
}
