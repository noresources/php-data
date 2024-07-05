<?php

/*
 * Generated from /home/renaud/Projects/ns-php-data/resources/antlr/Lua/Lua.g4 by ANTLR 4.11.1
 */

namespace NoreSources\Data\Parser\ANTLR\v41100\Lua {
	use Antlr\Antlr4\Runtime\Atn\ATN;
	use Antlr\Antlr4\Runtime\Atn\ATNDeserializer;
	use Antlr\Antlr4\Runtime\Atn\ParserATNSimulator;
	use Antlr\Antlr4\Runtime\Dfa\DFA;
	use Antlr\Antlr4\Runtime\Error\Exceptions\FailedPredicateException;
	use Antlr\Antlr4\Runtime\Error\Exceptions\NoViableAltException;
	use Antlr\Antlr4\Runtime\PredictionContexts\PredictionContextCache;
	use Antlr\Antlr4\Runtime\Error\Exceptions\RecognitionException;
	use Antlr\Antlr4\Runtime\RuleContext;
	use Antlr\Antlr4\Runtime\Token;
	use Antlr\Antlr4\Runtime\TokenStream;
	use Antlr\Antlr4\Runtime\Vocabulary;
	use Antlr\Antlr4\Runtime\VocabularyImpl;
	use Antlr\Antlr4\Runtime\RuntimeMetaData;
	use Antlr\Antlr4\Runtime\Parser;

	final class LuaParser extends Parser
	{
		public const SEMI = 1, EQ = 2, COMMA = 3, RETURN = 4, NIL = 5, FALSE = 6, 
               TRUE = 7, DOT = 8, OP = 9, CP = 10, OCU = 11, CCU = 12, OB = 13, 
               CB = 14, DD = 15, NAME = 16, NORMALSTRING = 17, CHARSTRING = 18, 
               LONGSTRING = 19, INT = 20, HEX = 21, FLOAT = 22, HEX_FLOAT = 23, 
               LINE_COMMENT = 24, BLOCK_COMMENT = 25, COMMENT = 26, WS = 27, 
               NL = 28;

		public const RULE_data = 0, RULE_value = 1, RULE_keywordConstantValue = 2, 
               RULE_numberValue = 3, RULE_floatValue = 4, RULE_integerValue = 5, 
               RULE_stringValue = 6, RULE_table = 7, RULE_tableEntry = 8, 
               RULE_keyValuePair = 9, RULE_key = 10, RULE_stringKey = 11, 
               RULE_protectedKey = 12, RULE_protectedKeyContent = 13;

		/**
		 * @var array<string>
		 */
		public const RULE_NAMES = [
			'data', 'value', 'keywordConstantValue', 'numberValue', 'floatValue', 
			'integerValue', 'stringValue', 'table', 'tableEntry', 'keyValuePair', 
			'key', 'stringKey', 'protectedKey', 'protectedKeyContent'
		];

		/**
		 * @var array<string|null>
		 */
		private const LITERAL_NAMES = [
		    null, "';'", "'='", "','", "'return'", "'nil'", "'false'", "'true'", 
		    "'.'", "'('", "')'", "'{'", "'}'", "'['", "']'", "'..'"
		];

		/**
		 * @var array<string>
		 */
		private const SYMBOLIC_NAMES = [
		    null, "SEMI", "EQ", "COMMA", "RETURN", "NIL", "FALSE", "TRUE", "DOT", 
		    "OP", "CP", "OCU", "CCU", "OB", "CB", "DD", "NAME", "NORMALSTRING", 
		    "CHARSTRING", "LONGSTRING", "INT", "HEX", "FLOAT", "HEX_FLOAT", "LINE_COMMENT", 
		    "BLOCK_COMMENT", "COMMENT", "WS", "NL"
		];

		private const SERIALIZED_ATN =
			[4, 1, 28, 88, 2, 0, 7, 0, 2, 1, 7, 1, 2, 2, 7, 2, 2, 3, 7, 3, 2, 4, 
		    7, 4, 2, 5, 7, 5, 2, 6, 7, 6, 2, 7, 7, 7, 2, 8, 7, 8, 2, 9, 7, 9, 
		    2, 10, 7, 10, 2, 11, 7, 11, 2, 12, 7, 12, 2, 13, 7, 13, 1, 0, 1, 0, 
		    3, 0, 31, 8, 0, 1, 0, 1, 0, 3, 0, 35, 8, 0, 1, 1, 1, 1, 1, 1, 1, 1, 
		    3, 1, 41, 8, 1, 1, 2, 1, 2, 1, 3, 1, 3, 3, 3, 47, 8, 3, 1, 4, 1, 4, 
		    1, 5, 1, 5, 1, 6, 1, 6, 1, 7, 1, 7, 1, 7, 1, 7, 5, 7, 59, 8, 7, 10, 
		    7, 12, 7, 62, 9, 7, 3, 7, 64, 8, 7, 1, 7, 1, 7, 1, 8, 1, 8, 3, 8, 
		    70, 8, 8, 1, 9, 1, 9, 1, 9, 1, 9, 1, 10, 1, 10, 3, 10, 78, 8, 10, 
		    1, 11, 1, 11, 1, 12, 1, 12, 1, 12, 1, 12, 1, 13, 1, 13, 1, 13, 0, 
		    0, 14, 0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 0, 5, 1, 
		    0, 5, 7, 1, 0, 22, 23, 1, 0, 20, 21, 1, 0, 17, 18, 2, 0, 17, 18, 20, 
		    20, 83, 0, 30, 1, 0, 0, 0, 2, 40, 1, 0, 0, 0, 4, 42, 1, 0, 0, 0, 6, 
		    46, 1, 0, 0, 0, 8, 48, 1, 0, 0, 0, 10, 50, 1, 0, 0, 0, 12, 52, 1, 
		    0, 0, 0, 14, 54, 1, 0, 0, 0, 16, 69, 1, 0, 0, 0, 18, 71, 1, 0, 0, 
		    0, 20, 77, 1, 0, 0, 0, 22, 79, 1, 0, 0, 0, 24, 81, 1, 0, 0, 0, 26, 
		    85, 1, 0, 0, 0, 28, 29, 5, 4, 0, 0, 29, 31, 5, 27, 0, 0, 30, 28, 1, 
		    0, 0, 0, 30, 31, 1, 0, 0, 0, 31, 32, 1, 0, 0, 0, 32, 34, 3, 2, 1, 
		    0, 33, 35, 5, 1, 0, 0, 34, 33, 1, 0, 0, 0, 34, 35, 1, 0, 0, 0, 35, 
		    1, 1, 0, 0, 0, 36, 41, 3, 4, 2, 0, 37, 41, 3, 6, 3, 0, 38, 41, 3, 
		    12, 6, 0, 39, 41, 3, 14, 7, 0, 40, 36, 1, 0, 0, 0, 40, 37, 1, 0, 0, 
		    0, 40, 38, 1, 0, 0, 0, 40, 39, 1, 0, 0, 0, 41, 3, 1, 0, 0, 0, 42, 
		    43, 7, 0, 0, 0, 43, 5, 1, 0, 0, 0, 44, 47, 3, 8, 4, 0, 45, 47, 3, 
		    10, 5, 0, 46, 44, 1, 0, 0, 0, 46, 45, 1, 0, 0, 0, 47, 7, 1, 0, 0, 
		    0, 48, 49, 7, 1, 0, 0, 49, 9, 1, 0, 0, 0, 50, 51, 7, 2, 0, 0, 51, 
		    11, 1, 0, 0, 0, 52, 53, 7, 3, 0, 0, 53, 13, 1, 0, 0, 0, 54, 63, 5, 
		    11, 0, 0, 55, 60, 3, 16, 8, 0, 56, 57, 5, 3, 0, 0, 57, 59, 3, 16, 
		    8, 0, 58, 56, 1, 0, 0, 0, 59, 62, 1, 0, 0, 0, 60, 58, 1, 0, 0, 0, 
		    60, 61, 1, 0, 0, 0, 61, 64, 1, 0, 0, 0, 62, 60, 1, 0, 0, 0, 63, 55, 
		    1, 0, 0, 0, 63, 64, 1, 0, 0, 0, 64, 65, 1, 0, 0, 0, 65, 66, 5, 12, 
		    0, 0, 66, 15, 1, 0, 0, 0, 67, 70, 3, 2, 1, 0, 68, 70, 3, 18, 9, 0, 
		    69, 67, 1, 0, 0, 0, 69, 68, 1, 0, 0, 0, 70, 17, 1, 0, 0, 0, 71, 72, 
		    3, 20, 10, 0, 72, 73, 5, 2, 0, 0, 73, 74, 3, 2, 1, 0, 74, 19, 1, 0, 
		    0, 0, 75, 78, 3, 22, 11, 0, 76, 78, 3, 24, 12, 0, 77, 75, 1, 0, 0, 
		    0, 77, 76, 1, 0, 0, 0, 78, 21, 1, 0, 0, 0, 79, 80, 5, 16, 0, 0, 80, 
		    23, 1, 0, 0, 0, 81, 82, 5, 13, 0, 0, 82, 83, 3, 26, 13, 0, 83, 84, 
		    5, 14, 0, 0, 84, 25, 1, 0, 0, 0, 85, 86, 7, 4, 0, 0, 86, 27, 1, 0, 
		    0, 0, 8, 30, 34, 40, 46, 60, 63, 69, 77];
		protected static $atn;
		protected static $decisionToDFA;
		protected static $sharedContextCache;

		public function __construct(TokenStream $input)
		{
			parent::__construct($input);

			self::initialize();

			$this->interp = new ParserATNSimulator($this, self::$atn, self::$decisionToDFA, self::$sharedContextCache);
		}

		private static function initialize(): void
		{
			if (self::$atn !== null) {
				return;
			}

			RuntimeMetaData::checkVersion('4.11.1', RuntimeMetaData::VERSION);

			$atn = (new ATNDeserializer())->deserialize(self::SERIALIZED_ATN);

			$decisionToDFA = [];
			for ($i = 0, $count = $atn->getNumberOfDecisions(); $i < $count; $i++) {
				$decisionToDFA[] = new DFA($atn->getDecisionState($i), $i);
			}

			self::$atn = $atn;
			self::$decisionToDFA = $decisionToDFA;
			self::$sharedContextCache = new PredictionContextCache();
		}

		public function getGrammarFileName(): string
		{
			return "Lua.g4";
		}

		public function getRuleNames(): array
		{
			return self::RULE_NAMES;
		}

		public function getSerializedATN(): array
		{
			return self::SERIALIZED_ATN;
		}

		public function getATN(): ATN
		{
			return self::$atn;
		}

		public function getVocabulary(): Vocabulary
        {
            static $vocabulary;

			return $vocabulary = $vocabulary ?? new VocabularyImpl(self::LITERAL_NAMES, self::SYMBOLIC_NAMES);
        }

		/**
		 * @throws RecognitionException
		 */
		public function data(): Context\DataContext
		{
		    $localContext = new Context\DataContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 0, self::RULE_data);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(30);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::RETURN) {
		        	$this->setState(28);
		        	$this->match(self::RETURN);
		        	$this->setState(29);
		        	$this->match(self::WS);
		        }
		        $this->setState(32);
		        $this->value();
		        $this->setState(34);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::SEMI) {
		        	$this->setState(33);
		        	$this->match(self::SEMI);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function value(): Context\ValueContext
		{
		    $localContext = new Context\ValueContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 2, self::RULE_value);

		    try {
		        $this->setState(40);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::NIL:
		            case self::FALSE:
		            case self::TRUE:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(36);
		            	$this->keywordConstantValue();
		            	break;

		            case self::INT:
		            case self::HEX:
		            case self::FLOAT:
		            case self::HEX_FLOAT:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(37);
		            	$this->numberValue();
		            	break;

		            case self::NORMALSTRING:
		            case self::CHARSTRING:
		            	$this->enterOuterAlt($localContext, 3);
		            	$this->setState(38);
		            	$this->stringValue();
		            	break;

		            case self::OCU:
		            	$this->enterOuterAlt($localContext, 4);
		            	$this->setState(39);
		            	$this->table();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function keywordConstantValue(): Context\KeywordConstantValueContext
		{
		    $localContext = new Context\KeywordConstantValueContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 4, self::RULE_keywordConstantValue);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(42);

		        $_la = $this->input->LA(1);

		        if (!((($_la) & ~0x3f) === 0 && ((1 << $_la) & 224) !== 0)) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function numberValue(): Context\NumberValueContext
		{
		    $localContext = new Context\NumberValueContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 6, self::RULE_numberValue);

		    try {
		        $this->setState(46);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::FLOAT:
		            case self::HEX_FLOAT:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(44);
		            	$this->floatValue();
		            	break;

		            case self::INT:
		            case self::HEX:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(45);
		            	$this->integerValue();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function floatValue(): Context\FloatValueContext
		{
		    $localContext = new Context\FloatValueContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 8, self::RULE_floatValue);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(48);

		        $_la = $this->input->LA(1);

		        if (!($_la === self::FLOAT || $_la === self::HEX_FLOAT)) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function integerValue(): Context\IntegerValueContext
		{
		    $localContext = new Context\IntegerValueContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 10, self::RULE_integerValue);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(50);

		        $_la = $this->input->LA(1);

		        if (!($_la === self::INT || $_la === self::HEX)) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function stringValue(): Context\StringValueContext
		{
		    $localContext = new Context\StringValueContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 12, self::RULE_stringValue);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(52);

		        $_la = $this->input->LA(1);

		        if (!($_la === self::NORMALSTRING || $_la === self::CHARSTRING)) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function table(): Context\TableContext
		{
		    $localContext = new Context\TableContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 14, self::RULE_table);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(54);
		        $this->match(self::OCU);
		        $this->setState(63);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ((($_la) & ~0x3f) === 0 && ((1 << $_la) & 16197856) !== 0) {
		        	$this->setState(55);
		        	$this->tableEntry();
		        	$this->setState(60);
		        	$this->errorHandler->sync($this);

		        	$_la = $this->input->LA(1);
		        	while ($_la === self::COMMA) {
		        		$this->setState(56);
		        		$this->match(self::COMMA);
		        		$this->setState(57);
		        		$this->tableEntry();
		        		$this->setState(62);
		        		$this->errorHandler->sync($this);
		        		$_la = $this->input->LA(1);
		        	}
		        }
		        $this->setState(65);
		        $this->match(self::CCU);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function tableEntry(): Context\TableEntryContext
		{
		    $localContext = new Context\TableEntryContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 16, self::RULE_tableEntry);

		    try {
		        $this->setState(69);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::NIL:
		            case self::FALSE:
		            case self::TRUE:
		            case self::OCU:
		            case self::NORMALSTRING:
		            case self::CHARSTRING:
		            case self::INT:
		            case self::HEX:
		            case self::FLOAT:
		            case self::HEX_FLOAT:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(67);
		            	$this->value();
		            	break;

		            case self::OB:
		            case self::NAME:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(68);
		            	$this->keyValuePair();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function keyValuePair(): Context\KeyValuePairContext
		{
		    $localContext = new Context\KeyValuePairContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 18, self::RULE_keyValuePair);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(71);
		        $this->key();
		        $this->setState(72);
		        $this->match(self::EQ);
		        $this->setState(73);
		        $this->value();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function key(): Context\KeyContext
		{
		    $localContext = new Context\KeyContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 20, self::RULE_key);

		    try {
		        $this->setState(77);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::NAME:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(75);
		            	$this->stringKey();
		            	break;

		            case self::OB:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(76);
		            	$this->protectedKey();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function stringKey(): Context\StringKeyContext
		{
		    $localContext = new Context\StringKeyContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 22, self::RULE_stringKey);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(79);
		        $this->match(self::NAME);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function protectedKey(): Context\ProtectedKeyContext
		{
		    $localContext = new Context\ProtectedKeyContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 24, self::RULE_protectedKey);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(81);
		        $this->match(self::OB);
		        $this->setState(82);
		        $this->protectedKeyContent();
		        $this->setState(83);
		        $this->match(self::CB);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function protectedKeyContent(): Context\ProtectedKeyContentContext
		{
		    $localContext = new Context\ProtectedKeyContentContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 26, self::RULE_protectedKeyContent);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(85);

		        $_la = $this->input->LA(1);

		        if (!((($_la) & ~0x3f) === 0 && ((1 << $_la) & 1441792) !== 0)) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}
	}
}

namespace NoreSources\Data\Parser\ANTLR\v41100\Lua\Context {
	use Antlr\Antlr4\Runtime\ParserRuleContext;
	use Antlr\Antlr4\Runtime\Token;
	use Antlr\Antlr4\Runtime\Tree\ParseTreeVisitor;
	use Antlr\Antlr4\Runtime\Tree\TerminalNode;
	use Antlr\Antlr4\Runtime\Tree\ParseTreeListener;
	use NoreSources\Data\Parser\ANTLR\v41100\Lua\LuaParser;
	use NoreSources\Data\Parser\ANTLR\v41100\Lua\LuaListener;

	class DataContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_data;
	    }

	    public function value(): ?ValueContext
	    {
	    	return $this->getTypedRuleContext(ValueContext::class, 0);
	    }

	    public function RETURN(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::RETURN, 0);
	    }

	    public function WS(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::WS, 0);
	    }

	    public function SEMI(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::SEMI, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterData($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitData($this);
		    }
		}
	} 

	class ValueContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_value;
	    }

	    public function keywordConstantValue(): ?KeywordConstantValueContext
	    {
	    	return $this->getTypedRuleContext(KeywordConstantValueContext::class, 0);
	    }

	    public function numberValue(): ?NumberValueContext
	    {
	    	return $this->getTypedRuleContext(NumberValueContext::class, 0);
	    }

	    public function stringValue(): ?StringValueContext
	    {
	    	return $this->getTypedRuleContext(StringValueContext::class, 0);
	    }

	    public function table(): ?TableContext
	    {
	    	return $this->getTypedRuleContext(TableContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterValue($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitValue($this);
		    }
		}
	} 

	class KeywordConstantValueContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_keywordConstantValue;
	    }

	    public function TRUE(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::TRUE, 0);
	    }

	    public function FALSE(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::FALSE, 0);
	    }

	    public function NIL(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::NIL, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterKeywordConstantValue($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitKeywordConstantValue($this);
		    }
		}
	} 

	class NumberValueContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_numberValue;
	    }

	    public function floatValue(): ?FloatValueContext
	    {
	    	return $this->getTypedRuleContext(FloatValueContext::class, 0);
	    }

	    public function integerValue(): ?IntegerValueContext
	    {
	    	return $this->getTypedRuleContext(IntegerValueContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterNumberValue($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitNumberValue($this);
		    }
		}
	} 

	class FloatValueContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_floatValue;
	    }

	    public function FLOAT(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::FLOAT, 0);
	    }

	    public function HEX_FLOAT(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::HEX_FLOAT, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterFloatValue($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitFloatValue($this);
		    }
		}
	} 

	class IntegerValueContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_integerValue;
	    }

	    public function INT(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::INT, 0);
	    }

	    public function HEX(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::HEX, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterIntegerValue($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitIntegerValue($this);
		    }
		}
	} 

	class StringValueContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_stringValue;
	    }

	    public function NORMALSTRING(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::NORMALSTRING, 0);
	    }

	    public function CHARSTRING(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::CHARSTRING, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterStringValue($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitStringValue($this);
		    }
		}
	} 

	class TableContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_table;
	    }

	    public function OCU(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::OCU, 0);
	    }

	    public function CCU(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::CCU, 0);
	    }

	    /**
	     * @return array<TableEntryContext>|TableEntryContext|null
	     */
	    public function tableEntry(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(TableEntryContext::class);
	    	}

	        return $this->getTypedRuleContext(TableEntryContext::class, $index);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function COMMA(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(LuaParser::COMMA);
	    	}

	        return $this->getToken(LuaParser::COMMA, $index);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterTable($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitTable($this);
		    }
		}
	} 

	class TableEntryContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_tableEntry;
	    }

	    public function value(): ?ValueContext
	    {
	    	return $this->getTypedRuleContext(ValueContext::class, 0);
	    }

	    public function keyValuePair(): ?KeyValuePairContext
	    {
	    	return $this->getTypedRuleContext(KeyValuePairContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterTableEntry($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitTableEntry($this);
		    }
		}
	} 

	class KeyValuePairContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_keyValuePair;
	    }

	    public function key(): ?KeyContext
	    {
	    	return $this->getTypedRuleContext(KeyContext::class, 0);
	    }

	    public function EQ(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::EQ, 0);
	    }

	    public function value(): ?ValueContext
	    {
	    	return $this->getTypedRuleContext(ValueContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterKeyValuePair($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitKeyValuePair($this);
		    }
		}
	} 

	class KeyContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_key;
	    }

	    public function stringKey(): ?StringKeyContext
	    {
	    	return $this->getTypedRuleContext(StringKeyContext::class, 0);
	    }

	    public function protectedKey(): ?ProtectedKeyContext
	    {
	    	return $this->getTypedRuleContext(ProtectedKeyContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterKey($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitKey($this);
		    }
		}
	} 

	class StringKeyContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_stringKey;
	    }

	    public function NAME(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::NAME, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterStringKey($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitStringKey($this);
		    }
		}
	} 

	class ProtectedKeyContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_protectedKey;
	    }

	    public function OB(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::OB, 0);
	    }

	    public function protectedKeyContent(): ?ProtectedKeyContentContext
	    {
	    	return $this->getTypedRuleContext(ProtectedKeyContentContext::class, 0);
	    }

	    public function CB(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::CB, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterProtectedKey($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitProtectedKey($this);
		    }
		}
	} 

	class ProtectedKeyContentContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return LuaParser::RULE_protectedKeyContent;
	    }

	    public function NORMALSTRING(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::NORMALSTRING, 0);
	    }

	    public function CHARSTRING(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::CHARSTRING, 0);
	    }

	    public function INT(): ?TerminalNode
	    {
	        return $this->getToken(LuaParser::INT, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->enterProtectedKeyContent($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof LuaListener) {
			    $listener->exitProtectedKeyContent($this);
		    }
		}
	} 
}