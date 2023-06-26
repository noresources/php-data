<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Console\Option;

use Symfony\Component\Console\Input\InputOption;

class MediaTypeOption extends InputOption
{

	public function __construct($name = null, $short = null,
		$options = null, $description = null)
	{
		if ($name === null)
			$name = 'media-type';
		if ($options === null)
			$options = 0;
		if ($description === null)
			$description = 'Media type';
		$options |= InputOption::VALUE_REQUIRED;

		parent::__construct($name, $short, $options, $description);
	}
}
