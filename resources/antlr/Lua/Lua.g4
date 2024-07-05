// $antlr-format alignTrailingComments true, columnLimit 150, maxEmptyLinesToKeep 1, reflowComments false, useTab false
// $antlr-format allowShortRulesOnASingleLine true, allowShortBlocksOnASingleLine true, minEmptyLines 0, alignSemicolons ownLine
// $antlr-format alignColons trailing, singleLineOverrulesHangingColon true, alignLexerCommands true, alignLabels true, alignTrailers true

// Inspired by
// antlr/grammar-v4 project on GitHub

grammar Lua;

//---------------------

data
	: (RETURN WS)? value SEMI?
	;

value
	: keywordConstantValue
	| numberValue
	| stringValue
	| table
	;

keywordConstantValue
	: TRUE
	| FALSE
	| NIL
	;

numberValue
	: floatValue
	| integerValue
	;
	
floatValue : FLOAT | HEX_FLOAT ;
integerValue :  INT | HEX ;

stringValue
	: NORMALSTRING
	| CHARSTRING
	;

table
	: OCU (tableEntry (COMMA tableEntry)*)? CCU
	;
	
tableEntry
	: value
	| keyValuePair
	;
	
keyValuePair
	: key EQ value
	;
	
key
	: stringKey
	| protectedKey
	;

stringKey : NAME ;
protectedKey : OB protectedKeyContent CB ;

protectedKeyContent
	: NORMALSTRING
	| CHARSTRING
	| INT
	;

//---------------------

SEMI : ';';
EQ   : '=';
COMMA    : ',';
RETURN   : 'return';
NIL      : 'nil';
FALSE    : 'false';
TRUE     : 'true';
DOT      : '.';
OP       : '(';
CP       : ')';
OCU      : '{';
CCU      : '}';
OB       : '[';
CB       : ']';
DD       : '..';

NAME: [a-zA-Z_][a-zA-Z_0-9]*;

NORMALSTRING: '"' ( EscapeSequence | ~('\\' | '"'))* '"';

CHARSTRING: '\'' ( EscapeSequence | ~('\'' | '\\'))* '\'';

LONGSTRING: '[' NESTED_STR ']';

fragment NESTED_STR: '=' NESTED_STR '=' | '[' .*? ']';

INT: Digit+;

HEX: '0' [xX] HexDigit+;

FLOAT: Digit+ '.' Digit* ExponentPart? | '.' Digit+ ExponentPart? | Digit+ ExponentPart;

HEX_FLOAT:
    '0' [xX] HexDigit+ '.' HexDigit* HexExponentPart?
    | '0' [xX] '.' HexDigit+ HexExponentPart?
    | '0' [xX] HexDigit+ HexExponentPart
;

fragment ExponentPart: [eE] [+-]? Digit+;

fragment HexExponentPart: [pP] [+-]? Digit+;

fragment EscapeSequence:
    '\\' [abfnrtvz"'|$#\\] // World of Warcraft Lua additionally escapes |$# 
    | '\\' '\r'? '\n'
    | DecimalEscape
    | HexEscape
    | UtfEscape
;

fragment DecimalEscape: '\\' Digit | '\\' Digit Digit | '\\' [0-2] Digit Digit;

fragment HexEscape: '\\' 'x' HexDigit HexDigit;

fragment UtfEscape: '\\' 'u{' HexDigit+ '}';

fragment Digit: [0-9];

fragment HexDigit: [0-9a-fA-F];

fragment SingleLineInputCharacter: ~[\r\n\u0085\u2028\u2029];

LINE_COMMENT: '--' ~[\r\n]* -> channel(HIDDEN);

BLOCK_COMMENT: '--[[' .*? '--]]' -> channel(HIDDEN);

COMMENT: ( LINE_COMMENT | BLOCK_COMMENT ) -> channel(HIDDEN);

WS: [ \t\u000C\r]+ -> channel(HIDDEN);

NL: [\n] -> channel(2);

