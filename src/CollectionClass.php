<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data;

/**
 * Container/Collection classes
 */
class CollectionClass
{

	/**
	 * List
	 *
	 * Describe an array where keys is a sorted set of numbers starting from 0.
	 *
	 * @var number
	 */
	const LIST = 0x01;

	/**
	 * Alias of LIST
	 *
	 * @var unknown
	 */
	const INDEXED = self::LIST;

	/**
	 * Associative array
	 *
	 * Describe a container with arbitrary key type and ordering.
	 *
	 * @var number
	 */
	const MAP = 0x02;

	/**
	 * ALias of MAP
	 *
	 * @var unknown
	 */
	const ASSOCIATIVE = self::MAP;

	/**
	 * Dictionary
	 *
	 * Describe an array where all keys are strings.
	 *
	 * @var integer
	 */
	const DICTIONARY = 0x04 | self::MAP;

	/**
	 * Table
	 *
	 * Describe a 2 dimension array where all rows have
	 * the smae number of columns.
	 *
	 * @var number
	 */
	const TABLE = 0x10;

	const NAME_LIST = 'list';

	const NAME_MAP = 'map';

	const NAME_DICTIONARY = 'dictionary';

	const NAME_TABLE = 'table';

	public static function getCollectionClassNames($collectionClass)
	{
		$names = [];

		if (($collectionClass & self::TABLE) & self::TABLE)
			$names[] = self::NAME_TABLE;

		if (($collectionClass & self::INDEXED) == self::INDEXED)
			$names[] = self::NAME_LIST;
		elseif (($collectionClass & self::DICTIONARY) == self::DICTIONARY)
			$names[] = self::NAME_DICTIONARY;
		elseif (($collectionClass & self::MAP) == self::MAP)
			$names[] = self::NAME_MAP;

		return $names;
	}
}