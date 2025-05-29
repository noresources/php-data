<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Data\Serialization\Traits\FileUnserializerTrait;
use NoreSources\Data\Serialization\Traits\UnserializableMediaTypeTrait;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\Traits\FileExtensionListTrait;
use NoreSources\Data\Utility\Traits\MediaTypeListTrait;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Load data from a PHP "module" file that returns data.
 *
 * For security reason, this serializer will not be available by default
 * with the SerializationManager.
 *
 * ATTENTION Never use this with untrusted files.
 */
class PhpFileUnserializer implements UnserializableMediaTypeInterface,
	FileUnserializerInterface, FileExtensionListInterface
{
	use MediaTypeListTrait;
	use FileExtensionListTrait;

	use UnserializableMediaTypeTrait;

	use FileUnserializerTrait;

	public function unserializeFromFile($filename,
		?MediaTypeInterface $mediaType = null)
	{
		$sandbox = new PhpFileUnserializerSandbox();
		return $sandbox($filename);
	}

	public function __construct()
	{}

	protected function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				'text/x-php')
		];
	}

	protected function buildFileExtensionList()
	{
		return [
			'php'
		];
	}
}

class PhpFileUnserializerSandbox
{

	public function __invoke($filename)
	{
		$data = null;
		$error = null;

		if (!\file_exists($filename))
			throw new SerializationException($filename . ' not found');
		$previous = set_error_handler(
			function ($errno, $message, $file, $line) use (&$error) {
				if (!(error_reporting() & $errno))
					return false;
				$error = $message;
			});
		try
		{
			$data = require ($filename);
		}
		catch (\Exception $e)
		{
			$error = $e->getMessage();
		}

		\set_error_handler($previous);

		if ($error)
			throw new SerializationException($error);
		return $data;
	}
}
