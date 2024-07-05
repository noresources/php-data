<?php

/*
 * Generated from /home/renaud/Projects/ns-php-data/resources/antlr/Lua/Lua.g4 by ANTLR 4.11.1
 */

namespace NoreSources\Data\Parser\ANTLR\v41100\Lua;
use Antlr\Antlr4\Runtime\Tree\ParseTreeListener;

/**
 * This interface defines a complete listener for a parse tree produced by
 * {@see LuaParser}.
 */
interface LuaListener extends ParseTreeListener {
	/**
	 * Enter a parse tree produced by {@see LuaParser::data()}.
	 * @param $context The parse tree.
	 */
	public function enterData(Context\DataContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::data()}.
	 * @param $context The parse tree.
	 */
	public function exitData(Context\DataContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::value()}.
	 * @param $context The parse tree.
	 */
	public function enterValue(Context\ValueContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::value()}.
	 * @param $context The parse tree.
	 */
	public function exitValue(Context\ValueContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::keywordConstantValue()}.
	 * @param $context The parse tree.
	 */
	public function enterKeywordConstantValue(Context\KeywordConstantValueContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::keywordConstantValue()}.
	 * @param $context The parse tree.
	 */
	public function exitKeywordConstantValue(Context\KeywordConstantValueContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::numberValue()}.
	 * @param $context The parse tree.
	 */
	public function enterNumberValue(Context\NumberValueContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::numberValue()}.
	 * @param $context The parse tree.
	 */
	public function exitNumberValue(Context\NumberValueContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::floatValue()}.
	 * @param $context The parse tree.
	 */
	public function enterFloatValue(Context\FloatValueContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::floatValue()}.
	 * @param $context The parse tree.
	 */
	public function exitFloatValue(Context\FloatValueContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::integerValue()}.
	 * @param $context The parse tree.
	 */
	public function enterIntegerValue(Context\IntegerValueContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::integerValue()}.
	 * @param $context The parse tree.
	 */
	public function exitIntegerValue(Context\IntegerValueContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::stringValue()}.
	 * @param $context The parse tree.
	 */
	public function enterStringValue(Context\StringValueContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::stringValue()}.
	 * @param $context The parse tree.
	 */
	public function exitStringValue(Context\StringValueContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::table()}.
	 * @param $context The parse tree.
	 */
	public function enterTable(Context\TableContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::table()}.
	 * @param $context The parse tree.
	 */
	public function exitTable(Context\TableContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::tableEntry()}.
	 * @param $context The parse tree.
	 */
	public function enterTableEntry(Context\TableEntryContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::tableEntry()}.
	 * @param $context The parse tree.
	 */
	public function exitTableEntry(Context\TableEntryContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::keyValuePair()}.
	 * @param $context The parse tree.
	 */
	public function enterKeyValuePair(Context\KeyValuePairContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::keyValuePair()}.
	 * @param $context The parse tree.
	 */
	public function exitKeyValuePair(Context\KeyValuePairContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::key()}.
	 * @param $context The parse tree.
	 */
	public function enterKey(Context\KeyContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::key()}.
	 * @param $context The parse tree.
	 */
	public function exitKey(Context\KeyContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::stringKey()}.
	 * @param $context The parse tree.
	 */
	public function enterStringKey(Context\StringKeyContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::stringKey()}.
	 * @param $context The parse tree.
	 */
	public function exitStringKey(Context\StringKeyContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::protectedKey()}.
	 * @param $context The parse tree.
	 */
	public function enterProtectedKey(Context\ProtectedKeyContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::protectedKey()}.
	 * @param $context The parse tree.
	 */
	public function exitProtectedKey(Context\ProtectedKeyContext $context): void;
	/**
	 * Enter a parse tree produced by {@see LuaParser::protectedKeyContent()}.
	 * @param $context The parse tree.
	 */
	public function enterProtectedKeyContent(Context\ProtectedKeyContentContext $context): void;
	/**
	 * Exit a parse tree produced by {@see LuaParser::protectedKeyContent()}.
	 * @param $context The parse tree.
	 */
	public function exitProtectedKeyContent(Context\ProtectedKeyContentContext $context): void;
}