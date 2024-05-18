<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data;

use NoreSources\Container\Container;
use NoreSources\Reflection\ReflectionService;
use NoreSources\Reflection\ReflectionServiceInterface;
use ReflectionProperty;

/**
 * Transform any type to PHP primitive type (also called "plain old data")
 */
class Primitifier
{

	/**
	 * Expose object private properties.
	 *
	 * Used by getObjectProperties()
	 *
	 * @var unknown
	 */
	const EXPOSE_PRIVATE_PROPERTIES = 0x01;

	public function __construct()
	{
		$this->leafPostprocessor = [
			self::class,
			'defaultLeafPostprocess'
		];
	}

	/**
	 *
	 * @param mixed $subject
	 *        	Data to transform to array
	 * @return array|mixed Array transformation of $subject
	 */
	public function __invoke($subject)
	{
		return $this->transform($subject, $this->recursionLimit);
	}

	public function getObjectProperties($object)
	{
		$reflectionPropertyFlags = ReflectionServiceInterface::ALLOW_READ_METHOD;

		if ($this->flags & self::EXPOSE_PRIVATE_PROPERTIES)
			$reflectionPropertyFlags |= ReflectionServiceInterface::READABLE;

		if (!isset($this->reflectionService))
			$this->reflectionService = new ReflectionService();

		$class = $this->reflectionService->getReflectionClass(
			\get_class($object));

		$result = [];
		foreach ($class->getProperties() as $property)
		{
			/**
			 *
			 * @var ReflectionProperty $property
			 */

			if ($property->isStatic())
				continue;

			try
			{
				$property = $this->reflectionService->getReflectionProperty(
					$class->getName(), $property->getName(),
					$reflectionPropertyFlags);

				if (($this->flags & self::EXPOSE_PRIVATE_PROPERTIES) == 0)
				{
					/**
					 * PHP 8+ ReflectionProperty is always readable even if private
					 */
					if (($property instanceof ReflectionProperty) &&
						(!$property->isPublic()))
						continue;
				}

				$result[$property->getName()] = $property->getValue(
					$object);
			}
			catch (\ReflectionException $e)
			{}
		}
		return $result;
	}

	/**
	 * Set transformation flags
	 *
	 * @param integer $flags
	 *        	Flags
	 * @return $this
	 */
	public function setFlags($flags)
	{
		$this->flags = $flags;
		return $this;
	}

	/**
	 *
	 * @param integer $recursionLimit
	 *        	Tree walker recursion limit
	 * @throws \InvalidArgumentException
	 * @return $this
	 */
	public function setRecursionLimit($recursionLimit)
	{
		if (!\is_integer($recursionLimit))
			throw new \InvalidArgumentException('Integer expected');
		if ($recursionLimit == 0)
			throw new \InvalidArgumentException(
				'Recursion level cannot be zero.');
		$this->recursionLimit = $recursionLimit;
		return $this;
	}

	/**
	 * Set key to use to create an array from a transformation result which is not an array.
	 *
	 * @param string|integer $key
	 *        	Fallback key
	 * @throws \InvalidArgumentException
	 */
	public function setSingleValueKey($key)
	{
		if (!(\is_string($key) || \is_integer($key)))
			throw new \InvalidArgumentException(
				'Integer or string expected.');
		$this->singleValueKey;
	}

	/**
	 *
	 * @param callable $callable
	 *        	Entry post-processing callback
	 * @throws \InvalidArgumentException
	 * @return $this
	 */
	public function setEntryPreprocessor($callable)
	{
		if (!\is_callable($callable))
			throw new \InvalidArgumentException('Callable expected');
		$this->entryPreprocessor = $callable;
		return $this;
	}

	/**
	 *
	 * @param callable $callable
	 *        	Leaf post-process callaback
	 * @throws \InvalidArgumentException
	 * @return $this
	 */
	public function setLeafPostprocessor($callable)
	{
		if (!\is_callable($callable))
			throw new \InvalidArgumentException('Callable expected');
		$this->leafPostprocessor = $callable;
		return $this;
	}

	public function setReflectionService(
		ReflectionService $reflectionService)
	{
		$this->reflectionService = $reflectionService;
	}

	/**
	 * Default leaf postproessor
	 *
	 * @param mixed $leaf
	 *        	Leaf node value
	 * @return mixed
	 */
	public static function defaultLeafPostprocess($leaf)
	{
		if ($leaf instanceof \DateTimeInterface)
			return $leaf->format(\DateTime::ISO8601);
		return $leaf;
	}

	protected function transform($subject, $depth)
	{
		if (isset($this->entryPreprocessor))
			$subject = \call_user_func($this->entryPreprocessor,
				$subject);

		if ($depth != 0)
		{
			if (Container::isTraversable($subject))
			{
				$z = [];
				foreach ($subject as $key => $value)
				{
					$a[$key] = $this->transform($value,
						\max(-1, $depth - 1));
				}
				$subject = $a;
			}
		}

		if (isset($this->leafPostprocessor))
			$subject = \call_user_func($this->leafPostprocessor,
				$subject);

		return $subject;
	}

	/**
	 * Processing recursion limit
	 */
	private $recursionLimit = -1;

	/**
	 *
	 * @var callable
	 */
	private $entryPreprocessor;

	/**
	 *
	 * @var callable
	 */
	private $leafPostprocessor;

	/**
	 * Transformation flags
	 *
	 * @var integer
	 */
	private $flags = 0;

	/**
	 *
	 * @var ReflectionServiceInterface
	 */
	private $reflectionService;
}
