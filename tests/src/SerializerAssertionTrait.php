<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Test;

use NoreSources\Test\DerivedFileTestTrait;

trait SerializerAssertionTrait
{
	use DerivedFileTestTrait;

	public function initializeSerializerAssertions($classname, $basePath)
	{
		$this->initializeDerivedFileTest($basePath);
		$this->serializerClass = new \ReflectionClass($classname);
	}

	/**
	 *
	 * @param mixed ...$arguments
	 *        	Constructor arguments
	 * @return object
	 */
	protected function createSerializer(...$arguments)
	{
		return $this->serializerClass->newInstanceArgs($arguments);
	}

	/**
	 *
	 * @return boolean TRUE if serializer is availabke
	 */
	protected function canTestSerializer()
	{
		if (!$this->serializerClass->hasMethod('prerequisites'))
			return true;

		$method = $this->serializerClass->getMethod('prerequisites');
		$ok = $method->invoke(null);
		if ($ok)
			return true;
		if (\method_exists($this, 'assertFalse'))
			$this->assertFalse($ok,
				$this->serializerClass->getShortName() . ' not available');
		return false;
	}

	/**
	 *
	 * @var \ReflectionClass
	 */
	private $serializerClass;
}
