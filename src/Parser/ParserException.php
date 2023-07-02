<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Parser;

class ParserException extends \Exception
{

	/**
	 * Raise by parsers
	 *
	 * @param string $message
	 *        	Exception message.
	 * @param integer $line
	 *        	INI content line number.
	 * @param unknown $column
	 */
	public function __construct($message, $line = null, $column = null)
	{
		if (\is_integer($line))
		{
			$message .= ' at line ' . $line;
			if (\is_integer($column))
				$message .= ':' . $column;
		}
		elseif (\is_integer($column))
			$message .= ' at offset ' . $column;
		parent::__construct($message);
	}
}