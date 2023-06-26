<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Console;

use NoreSources\Data\Console\Command\ConvertCommand;
use Symfony\Component\Console\Input\InputOption;

class Application extends \Symfony\Component\Console\Application
{

	public function __construct()
	{
		parent::__construct();

		$convert = new ConvertCommand();
		$this->add($convert);
		$this->getDefinition()->addOption(
			new InputOption('auto-register-serializers', 'a', null,
				'Auto register (de)serializers based on composer package descriptions'));
	}
}