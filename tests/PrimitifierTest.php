<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\TestCase;

use NoreSources\Data\Primitifier;

class BasicClass
{

	public $publicValue = 'public';

	public $uninitializedValue;

	protected $protectedValue = 'protected';

	protected $uninitializedProtectedValue;

	public static $staticPublicValue = 123;

	private $privateValue = 'private';

	public function getReadOnlyProperty()
	{
		return $this->readOnlyProperty . ' with a getter';
	}

	private $readOnlyProperty = 'read-only property';
}

class PrimitifierTest extends \PHPUnit\Framework\TestCase
{

	public function testGetObjectProperties()
	{
		$method = __METHOD__;
		$suffix = null;
		$extension = 'json';
		$primitifier = new Primitifier();

		$tests = [
			'basic class' => [
				'subject' => new BasicClass(),
				'variants' => [
					'exposing private properties' => [
						'flags' => Primitifier::EXPOSE_PRIVATE_PROPERTIES,
						'expected' => [
							'publicValue' => 'public',
							'uninitializedValue' => null,
							'protectedValue' => 'protected',
							'uninitializedProtectedValue' => null,
							'privateValue' => 'private',
							'readOnlyProperty' => 'read-only property with a getter'
						]
					],
					'public-only' => [
						'flags' => 0,
						'expected' => [
							'publicValue' => 'public',
							'uninitializedValue' => null,
							'readOnlyProperty' => 'read-only property with a getter'
						]
					]
				]
			]
		];

		foreach ($tests as $label => $test)
		{
			$subject = $test['subject'];
			$variants = $test['variants'];
			foreach ($variants as $variantLabel => $variant)
			{
				$expected = $variant['expected'];
				$primitifier->setFlags($variant['flags']);
				$actual = $primitifier->getObjectProperties($subject);
				$this->assertEquals($expected, $actual,
					$label . ' ' . $variantLabel);
			}
		}
	}
}
