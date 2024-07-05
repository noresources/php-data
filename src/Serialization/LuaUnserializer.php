<?php

/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\InputStream;
use Antlr\Antlr4\Runtime\RuntimeMetaData;
use Antlr\Antlr4\Runtime\Tree\ParseTreeWalker;
use NoreSources\SemanticVersion;
use NoreSources\Data\Serialization\Lua\GenericLuaUnserializerVisitor;
use NoreSources\Data\Serialization\Traits\StreamUnserializerBaseTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerDataUnserializerTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerFileUnserializerTrait;
use NoreSources\Data\Serialization\Traits\UnserializableMediaTypeTrait;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\Data\Utility\Traits\FileExtensionListTrait;
use NoreSources\Data\Utility\Traits\MediaTypeListTrait;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

class LuaUnserializer implements DataUnserializerInterface,
	StreamUnserializerInterface, FileUnserializerInterface,
	UnserializableMediaTypeInterface, MediaTypeListInterface,
	FileExtensionListInterface
{
	use MediaTypeListTrait;
	use FileExtensionListTrait;

	Use UnserializableMediaTypeTrait;

	use StreamUnserializerBaseTrait;
	use StreamUnserializerDataUnserializerTrait;
	use StreamUnserializerFileUnserializerTrait;

	/**
	 * Unregisted media type
	 *
	 * @var string
	 */
	const MEDIA_TYPE = 'text/x-lua';

	public static function prerequisites()
	{
		if (!\class_exists(RuntimeMetaData::class))
			return false;
		return true;
	}

	public function isUnserializableFrom($data,
		MediaTypeInterface $mediaType = null)
	{
		if (!\is_string($data))
			return false;
		if ($mediaType)
			return $this->isMediaTypeUnserializable($mediaType);
		return true;
	}

	public function unserializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$input = InputStream::fromString($data);
		return $this->unserializeInput($input);
	}

	public function unserializeFromStream($stream,
		MediaTypeInterface $mediaType = null)
	{
		return $this->unserializeData(\stream_get_contents($stream),
			$mediaType);
	}

	public function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				self::MEDIA_TYPE)
		];
	}

	public function getFileExtensions()
	{
		return [
			'lua'
		];
	}

	protected function unserializeInput($input)
	{
		static $availableVersions = [
			'4.11.0' => 'v41100',
			'4.9.0' => 'v40900'
		];
		$runtimeVersion = new SemanticVersion(
			RuntimeMetaData::getRuntimeVersion());
		$lexerClassName = null;
		$parserClassName = null;
		$visitorClassName = null;
		$parserBaseNamespace = '\NoreSources\Data\Parser\ANTLR';
		$visitorBaseNamespace = __NAMESPACE__ . '\ANTLR';
		$parserVersion = null;

		foreach ($availableVersions as $availableVersion => $namespace)
		{
			if (SemanticVersion::compareVersions($availableVersion,
				$runtimeVersion) < 0)
			{
				$parserNamespace = $parserBaseNamespace . '\\' .
					$namespace . '\Lua';
				$lexerClassName = $parserNamespace . '\LuaLexer';
				$parserClassName = $parserNamespace . '\LuaParser';

				$visitorClassName = $visitorBaseNamespace . '\\' .
					$namespace . '\Lua\LuaUnserializerVisitor';
				$parserVersion = $availableVersion;
				break;
			}
		}

		if (!$parserClassName)
			throw new SerializationException(
				'No lua parser available for ANTLR runtime version ' .
				$runtimeVersion);

		try
		{
			$lexer = new $lexerClassName($input);
			$tokens = new CommonTokenStream($lexer);
			$parser = new $parserClassName($tokens);
			$parser->setBuildParseTree(true);
			$tree = $parser->value();
			$visitor = new $visitorClassName(
				new GenericLuaUnserializerVisitor());
			ParseTreeWalker::default()->walk($visitor, $tree);
			return $visitor->finalize();
		}
		catch (\Exception $e)
		{
			$message = $e->getMessage() . PHP_EOL . 'Parser version: ' .
				\strval($parserVersion) . PHP_EOL . 'Runtime version: ' .
				\strval($runtimeVersion);
			throw new SerializationException($message);
		}
	}
}

