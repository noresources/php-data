<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Container\Container;
use NoreSources\Data\Serialization\Traits\SerializableMediaTypeTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerBaseTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerDataSerializerTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerFileSerializerTrait;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\Data\Utility\Traits\MediaTypeListTrait;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\Type\TypeConversion;

/**
 * Lua primitive serialization
 *
 * @see https://datatracker.ietf.org/doc/html/rfc3986
 */
class LuaSerializer implements SerializableMediaTypeInterface,
	DataSerializerInterface, FileSerializerInterface,
	StreamSerializerInterface, MediaTypeListInterface
{
	use MediaTypeListTrait;

	use SerializableMediaTypeTrait;

	use StreamSerializerBaseTrait;
	use StreamSerializerDataSerializerTrait;
	use StreamSerializerFileSerializerTrait;

	/**
	 * Export value "as is"
	 *
	 * This is the default behavior of the serializeData() method
	 *
	 * @var string
	 */
	const MODE_RAW = 'raw';

	/**
	 * Export value prefixed by a "return" keyword
	 *
	 * This is the default behavior of the serializeDataToFile() method
	 *
	 * @var string
	 */
	const MODE_MODULE = 'module';

	public $indentation = ' ';

	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		$prefix = '';
		$meta = \stream_get_meta_data($stream);
		switch (Container::keyValue($meta, 'wrapper_type', 'undefined'))
		{
			case 'file':
				$prefix = 'return ';
			default:
			break;
		}
		if ($mediaType)
		{
			if (\is_string($mediaType))
				$mediaType = MediaTypeFactory::getInstance()->createFromMedia(
					$mediaType);

			if (($mediaType instanceof MediaTypeInterface) &&
				($mode = Container::keyValue(
					$mediaType->getParameters(), 'mode')) &&
				(\strcasecmp($mode, self::MODE_MODULE) == 0))
			{
				$prefix = 'return ';
			}
		}

		fwrite($stream, $prefix);

		if (Container::isTraversable($data))
			return $this->serializeTable($stream, $data);

		$this->serializeLiteral($stream, $data);
	}

	protected function serializeTableKey($stream, $key)
	{
		if (\preg_match(chr(1) . self::LUA_IDENTIFIER_PATTERN . chr(1),
			$key))
			return \fwrite($stream, $key);
		elseif (\is_integer($key))
			return \fwrite($stream, '[' . $key . ']');
		\fwrite($stream,
			'["' . \addslashes(TypeConversion::toString($key)) . '"]');
	}

	protected function serializeTable($stream, $table, $level = 0)
	{
		$first = true;
		\fwrite($stream, "{\n");
		$pad = \str_repeat($this->indentation, $level);
		if (Container::isIndexed($table))
		{
			foreach ($table as $value)
			{
				if (!$first)
					\fwrite($stream, ",\n");
				\fwrite($stream, $this->indentation . $pad);
				if (Container::isTraversable($value))
					$this->serializeTable($stream, $value, $level + 1);
				else
					$this->serializeLiteral($stream, $value);
				$first = false;
			}
		}
		else
		{
			foreach ($table as $key => $value)
			{
				if (!$first)
					\fwrite($stream, ",\n");

				\fwrite($stream, $this->indentation . $pad);
				$this->serializeTableKey($stream, $key);
				\fwrite($stream, ' = ');
				if (Container::isTraversable($value))
					$this->serializeTable($stream, $value, $level + 1);
				else
					$this->serializeLiteral($stream, $value);
				$first = false;
			}
		}

		\fwrite($stream, "\n" . $pad . '}');
	}

	protected function serializeLiteral($stream, $value)
	{
		if (\is_null($value))
			return \fwrite($stream, 'nil');
		if (\is_bool($value))
			return \fwrite($stream, ($value) ? 'true' : 'false');
		if (\is_numeric($value))
			return \fwrite($stream, \strval($value));

		\fwrite($stream, '"' . \addslashes($value) . '"');
	}

	public function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				'text/x-lua')
		];
	}

	const INTEGER_PATTERN = '^[1-9][0-9]*$';

	const LUA_IDENTIFIER_PATTERN = '^[a-zA-Z_][a-zA-Z0-9_]*$';
}
