<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Shellscript;

class ZshShellscriptWriter extends ShellscriptWriter
{

	public function getListStartIndex()
	{
		return 1;
	}

	public function getArrayVariableDefinition($variable, $values)
	{
		return 'declare -A ' . $variable . self::EOL .
			parent::getArrayVariableDefinition($variable, $values);
	}
}
