<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Container\Container;
use NoreSources\Data\Analyzer;
use NoreSources\Data\Serialization\Text\AsciiTableRenderer;
use NoreSources\Data\Serialization\Text\TableRenderer;
use NoreSources\Data\Serialization\Text\Utf8TableRenderer;
use NoreSources\Data\Serialization\Traits\SerializableMediaTypeTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerBaseTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerFileSerializerTrait;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\Data\Utility\Traits\FileExtensionListTrait;
use NoreSources\Data\Utility\Traits\MediaTypeListTrait;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

class TextArtTableSerializer implements SerializableMediaTypeInterface,
	SerializableContentInterface, DataSerializerInterface,
	StreamSerializerInterface, FileSerializerInterface,
	MediaTypeListInterface, FileExtensionListInterface
{

	use FileExtensionListTrait;
	use MediaTypeListTrait;
	use SerializableMediaTypeTrait;

	use StreamSerializerBaseTrait;
	use StreamSerializerFileSerializerTrait;

	const MEDIA_TYPE = 'text/vnd.ascii-art';

	public function isContentSerializable($data)
	{
		if (!Container::isTraversable($data))
			return false;
		$analyzer = Analyzer::getInstance();
		$min = $analyzer->getMinDepth($data);
		$max = $analyzer->getMaxDepth($data);

		return ($min == $max) && ($min == 2);
	}

	public function isSerializableTo($data,
		MediaTypeInterface $mediaType = null)
	{
		return $this->isContentSerializable($data) &&
			$this->isMediaTypeSerializable($mediaType);
		;
	}

	public function isSerializableToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		return $this->isSerializableTo($data, $mediaType);
	}

	public function serializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$renderer = $this->createTableRenderer($mediaType);
		$renderer->heading = $this->getHeadingMode($data, $mediaType);
		return $renderer->render($data);
	}

	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		$renderer = $this->createTableRenderer($mediaType);
		$renderer->heading = $this->getHeadingMode($data, $mediaType);
		$renderer->write($stream, $data);
	}

	public function createTableRenderer(
		MediaTypeInterface $mediaType = null)
	{
		if ($mediaType &&
			$mediaType->getParameters()->has(
				SerializationParameter::CHARSET))
		{
			$charset = $mediaType->getParameters()->get(
				SerializationParameter::CHARSET);

			if (\strcasecmp($charset, 'ascii') == 0)
				return new AsciiTableRenderer();
		}
		return new Utf8TableRenderer();
	}

	public function getHeadingMode($data,
		MediaTypeInterface $mediaType = null)
	{
		$mode = SerializationParameter::TABLE_HEADING_AUTO;
		if ($mediaType &&
			$mediaType->getParameters()->has(
				SerializationParameter::TABLE_HEADING))
		{
			$mode = $mediaType->getParameters()->get(
				SerializationParameter::TABLE_HEADING);
			$mode = \strtolower($mode);
		}

		switch ($mode)
		{
			case SerializationParameter::TABLE_HEADING_NONE:
				return 0;
			case SerializationParameter::TABLE_HEADING_ROW:
				return TableRenderer::HEADING_ROW;
			case SerializationParameter::TABLE_HEADING_COLUMN:
				return TableRenderer::HEADING_COLUMN;
			case SerializationParameter::TABLE_HEADING_BOTH:
				return TableRenderer::HEADING_BOTH;
		}
		return TableRenderer::guessHeadingMode($data);
	}

	protected function buildFileExtensionList()
	{
		return [
			'txt',
			'ascii'
		];
	}

	protected function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				self::MEDIA_TYPE)
		];
	}

	protected function getSupportedMediaTypeParameterValues()
	{
		if (!isset(self::$supportedMediaTypeParameters))
		{
			self::$supportedMediaTypeParameters = [
				self::MEDIA_TYPE => [
					SerializationParameter::CHARSET => true,
					SerializationParameter::TABLE_HEADING => [
						SerializationParameter::TABLE_HEADING_AUTO,
						SerializationParameter::TABLE_HEADING_NONE,
						SerializationParameter::TABLE_HEADING_COLUMN,
						SerializationParameter::TABLE_HEADING_ROW,
						SerializationParameter::TABLE_HEADING_BOTH
					]
				]
			];
		}
		return self::$supportedMediaTypeParameters;
	}

	private static $supportedMediaTypeParameters;
}
