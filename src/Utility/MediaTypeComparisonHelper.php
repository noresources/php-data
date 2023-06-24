<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Utility;

use NoreSources\MediaType\MediaTypeInterface;

/**
 *
 * @deprecated This class will be removed as soon as a better replacement will be introduced in the
 *             noresources/ns-php-mediatype package.
 *
 */
class MediaTypeComparisonHelper
{

	public static function lexicalCOmpare(MediaTypeInterface $a,
		MediaTypeInterface $b, $withParameters = true)

	{
		$c = \strcasecmp(\strval($a), \strval($b));
		if ($c != 0)
			return $c;

		if (!$withParameters)
			return 0;

		$c = ($a->getParameters()->count() - $b->getParameters()->count());
		if ($c != 0)
			return $c;

		foreach ($a->getParameters() as $id => $value)
		{
			if (!$b->getParameters()->has($id))
				return 1;
			$c = \strcmp($value, $b->getParameters()->offsetGet($id));
			if ($c != 0)
				return $c;
		}
		return 0;
	}
}
