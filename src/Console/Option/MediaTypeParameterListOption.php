<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Console\Option;

use NoreSources\Http\ParameterMapSerializer;
use NoreSources\MediaType\MediaTypeInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

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
		$p = $mediaType->getParameters();
		foreach ($values as $value)
		{
			$parts = \explode('=', $value, 2);
			if (\count($parts) == 1)
				$value .= '=""';
			elseif (empty($value))
				$value .= '""';
			ParameterMapSerializer::unserializeParameters($p, $value);
		}
	}
}
