<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Console;

use Symfony\Component\Console\Application;

class DataApplication extends Application
{

	public function __construct()
	{
		parent::__construct();

		$convert = new ConvertCommand();
		$this->add($convert);
		$this->setDefaultCommand($convert->getName());
	}
}