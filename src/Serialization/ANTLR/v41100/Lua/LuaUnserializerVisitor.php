<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\ANTLR\v41100\Lua;

use NoreSources\Data\Parser\ANTLR\v41100\Lua\LuaBaseListener;
use NoreSources\Data\Parser\ANTLR\v41100\Lua\Context\FloatValueContext;
use NoreSources\Data\Parser\ANTLR\v41100\Lua\Context\IntegerValueContext;
use NoreSources\Data\Parser\ANTLR\v41100\Lua\Context\KeywordConstantValueContext;
use NoreSources\Data\Parser\ANTLR\v41100\Lua\Context\ProtectedKeyContentContext;
use NoreSources\Data\Parser\ANTLR\v41100\Lua\Context\StringKeyContext;
use NoreSources\Data\Parser\ANTLR\v41100\Lua\Context\StringValueContext;
use NoreSources\Data\Parser\ANTLR\v41100\Lua\Context\TableContext;
use NoreSources\Data\Parser\ANTLR\v41100\Lua\Context\TableEntryContext;
use NoreSources\Data\Serialization\Lua\GenericLuaUnserializerVisitor;

/**
 *
 * @internal
 *
 */
class LuaUnserializerVisitor extends LuaBaseListener

{

	public function __construct(
		GenericLuaUnserializerVisitor $genericVisitor)
	{
		$this->genericVisitor = $genericVisitor;
	}

	public function finalize()
	{
		return $this->genericVisitor->finalize();
	}

	public function exitFloatValue(FloatValueContext $context): void
	{
		$this->genericVisitor->exitFloatValue($context);
	}

	public function exitIntegerValue(IntegerValueContext $context): void
	{
		$this->genericVisitor->exitIntegerValue($context);
	}

	public function exitStringValue(StringValueContext $context): void
	{
		$this->genericVisitor->exitStringValue($context);
	}

	public function exitStringKey(StringKeyContext $context): void
	{
		$this->genericVisitor->exitStringKey($context);
	}

	public function exitProtectedKeyContent(
		ProtectedKeyContentContext $context): void
	{
		$this->genericVisitor->exitProtectedKeyContent($context);
	}

	public function exitKeywordConstantValue(
		KeywordConstantValueContext $context): void
	{
		$this->genericVisitor->exitKeywordConstantValue($context);
	}

	public function enterTable(TableContext $context): void
	{
		$this->genericVisitor->enterTable($context);
	}

	public function enterTableEntry(TableEntryContext $context): void
	{
		$this->genericVisitor->enterTableEntry($context);
	}

	public function exitTableEntry(TableEntryContext $context): void
	{
		$this->genericVisitor->exitTableEntry($context);
	}

	/**
	 *
	 * @var GenericLuaUnserializerVisitor
	 */
	private $genericVisitor;
}
