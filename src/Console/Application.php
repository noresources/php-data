<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Console;

use NoreSources\Data\Console\Command\AnalyzeCommand;
use NoreSources\Data\Console\Command\ConvertCommand;
use NoreSources\Data\Console\Command\ListMediaTypesCommand;
use Symfony\Component\Console\Input\InputOption;

class Application extends \Symfony\Component\Console\Application
{

	public function __construct()
	{
		parent::__construct();

		$this->add(new ConvertCommand());
		$this->add(new AnalyzeCommand());
		$this->add(new ListMediaTypesCommand());
		$this->getDefinition()->addOption(
			new InputOption('auto-register-serializers', 'a', null,
				'Auto register (de)serializers based on composer package descriptions'));
	}
}