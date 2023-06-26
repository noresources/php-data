<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Console\Option;

use Symfony\Component\Console\Input\InputOption;
use NoreSources\MediaType\MediaTypeInterface;
use Symfony\Component\Console\Input\InputInterface;

class MediaTypeParameterListOption extends InputOption
{

	public function __construct($name = null, $short = null,
		$options = null, $description = null)
	{
		if ($name === null)
			$name = 'parameters';
		if ($options === null)
			$options = 0;
		if ($description === null)
			$description = 'Media type parameter list';
		$options |= InputOption::VALUE_REQUIRED |
			InputOption::VALUE_IS_ARRAY;
		parent::__construct($name, $short, $options, $description);
	}

	public static function populateMediaType(
		MediaTypeInterface $mediaType, InputInterface $input,
		$optionName)
	{
		$values = $input->getOption($optionName);
		foreach ($values as $value)
		{
			if (!\preg_match('/(.+?)=(.*)/', $value, $m))
				throw new \InvalidArgumentException(
					'Invalid ' . $optionName .
					' media type parameter format "' . $value . '"');
			$mediaType->getParameters()->offsetSet($m[1], $m[2]);
		}
	}
}
