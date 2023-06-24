<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\Traits;

use NoreSources\Container\Container;
use NoreSources\Data\Serialization\SerializableMediaTypeInterface;
use NoreSources\Data\Utility\MediaTypeComparisonHelper;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\MediaRange;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInspector;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\MediaType\MediaTypeMatcher;

/**
 * Generic implementation of SerializableMediaTypeInterface
 */
trait SerializableMediaTypeTrait
{

	public function isMediaTypeSerializable(
		MediaTypeInterface $mediaType)
	{
		$list = $this->getSerializableMediaRanges();
		$matcher = new MediaTypeMatcher($mediaType);
		return $matcher->match($list);
	}

	public function getSerializableMediaRanges()
	{
		if ($this instanceof MediaTypeListInterface)
			return $this->getMediaTypes();
		return [];
	}

	public function isMediaTypeSerializableWithParameter(
		MediaTypeInterface $mediaType, $parameter, $value = null)
	{
		if ($this instanceof MediaTypeListInterface)
			return $this->supportsMediaTypeParameter($mediaType,
				$parameter, $value);
		return false;
	}

	/**
	 *
	 * @param MediaTypeInterface[] $expectedMediaRanges
	 * @param number $flags
	 * @return array
	 */
	public function buildSerialiableMediaTypeListMatchingMediaRanges(
		$expectedMediaRanges, $flags = 0)
	{
		$list = [];
		$inspector = MediaTypeInspector::getInstance();
		$availableMediaRanges = $this->getSerializableMediaRanges();
		foreach ($availableMediaRanges as $availableMediaRange)
		{
			foreach ($expectedMediaRanges as $expectedMediaRange)
			{
				$match = $availableMediaRange->match(
					$expectedMediaRange);

				if (!$match)
					continue;

				$mediaType = null;
				if ($inspector->isFullySpecifiedMediaType(
					$expectedMediaRange))
				{

					$parameters = $expectedMediaRange->getParameters();
					$parameters = Container::filter($parameters,
						function ($parameter, $value) use (
						$expectedMediaRange) {
							return $this->isMediaTypeSerializableWithParameter(
								$expectedMediaRange, $parameter);
						});

					$mediaType = MediaTypeFactory::getInstance()->createFromProperties(
						$expectedMediaRange->getType(),
						clone $expectedMediaRange->getSubType(),
						$parameters);
					foreach ($availableMediaRange->getParameters() as $k => $v)
						$mediaType->getParameters()->offsetSet($k, $v);
				}
				elseif ($inspector->isFullySpecifiedMediaType(
					$availableMediaRange))
				{
					$mediaType = MediaTypeFactory::getInstance()->createFromProperties(
						$availableMediaRange->getType(),
						clone $availableMediaRange->getSubType(),
						$availableMediaRange->getParameters());
					foreach ($expectedMediaRange as $parameter => $value)
					{
						if ($mediaType->getParameters()->has($parameter))
							continue;

						if (!$this->isMediaTypeSerializableWithParameter(
							$mediaType, $parameter, $value))
							continue;

						$mediaType->getParameters()->offsetSet(
							$parameter, $value);
					}
				}
				elseif ($flags &
					SerializableMediaTypeInterface::LIST_MEDIA_RANGE)
				{
					$c = MediaRange::compareMediaRanges(
						$expectedMediaRange, $availableMediaRange);

					if ($c < 0)
					{
						$mediaType = $availableMediaRange;
						if ($expectedMediaRange->getParameters()->count())
						{
							$mediaType = clone $expectedMediaRange;
							foreach ($expectedMediaRange as $parameter => $value)
							{
								if ($mediaType->getParameters()->has(
									$parameter))
									continue;

								if (!$this->isMediaTypeSerializableWithParameter(
									$mediaType, $parameter, $value))
									continue;

								$mediaType->getParameters()->offsetSet(
									$parameter, $value);
							}
						}
					}
					else
					{
						$parameters = $expectedMediaRange->getParameters();
						$parameters = Container::filter($parameters,
							function ($parameter, $value) use (
							$expectedMediaRange) {
								return $this->isMediaTypeSerializableWithParameter(
									$expectedMediaRange, $parameter);
							});
						$mediaType = MediaTypeFactory::getInstance()->createFromProperties(
							$expectedMediaRange->getType(),
							$expectedMediaRange->getSubType(),
							$parameters);
						foreach ($availableMediaRange->getParameters() as $k => $v)
							$mediaType->getParameters()->offsetSet($k,
								$v);
					}
				}

				if ($mediaType instanceof MediaTypeInterface)
					$list[] = $mediaType;
			} // each expected
		} // each available
		return Container::uniqueValues($list,
			[
				MediaTypeComparisonHelper::class,
				'lexicalCOmpare'
			]);
	}
}


