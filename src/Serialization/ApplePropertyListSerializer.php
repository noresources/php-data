<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Data\Serialization\ApplePropertyList\XmlFormat;
use NoreSources\Data\Serialization\Traits\DataSerializerStreamSerializerTrait;
use NoreSources\Data\Serialization\Traits\DataUnserializerStreamUnserializerTrait;
use NoreSources\Data\Serialization\Traits\FileSerializerTrait;
use NoreSources\Data\Serialization\Traits\PrimitifyTrait;
use NoreSources\Data\Serialization\Traits\SerializableMediaTypeTrait;
use NoreSources\Data\Serialization\Traits\StreamSerializerBaseTrait;
use NoreSources\Data\Serialization\Traits\StreamUnserializerBaseTrait;
use NoreSources\Data\Serialization\Traits\UnserializableMediaTypeTrait;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\Data\Utility\Traits\FileExtensionListTrait;
use NoreSources\Data\Utility\Traits\MediaTypeListTrait;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;
use NoreSources\Type\TypeDescription;
use DOMDocument;

class ApplePropertyListSerializer implements
	SerializableMediaTypeInterface, UnserializableMediaTypeInterface,
	MediaTypeListInterface, FileExtensionListInterface,
	DataSerializerInterface, DataUnserializerInterface,
	FileSerializerInterface, FileUnserializerInterface,
	StreamSerializerInterface, StreamUnserializerInterface
{
	use MediaTypeListTrait;
	use FileExtensionListTrait;

	use UnserializableMediaTypeTrait;
	use SerializableMediaTypeTrait;

	use PrimitifyTrait;

	use StreamSerializerBaseTrait;
	use DataSerializerStreamSerializerTrait;
	use FileSerializerTrait;

	use StreamUnserializerBaseTrait;
	use DataUnserializerStreamUnserializerTrait;

	const MEDIA_TYPE = 'application/x-plist';

	public static function prerequisites()
	{
		return \extension_loaded('dom');
	}

	protected function buildMediaTypeList()
	{
		return [
			MediaTypeFactory::getInstance()->createFromString(
				self::MEDIA_TYPE)
		];
	}

	protected function buildFileExtensionList()
	{
		return [
			'plist'
		];
	}

	public function isUnserializableFrom($data,
		MediaTypeInterface $mediaType = null)
	{
		if ($mediaType)
			return $this->isMediaTypeUnserializable($mediaType);
		return true;
	}

	public function unserializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$document = null;
		if ($data instanceof \DOMElement)
			$document = $data->ownerDocument;
		if (\is_string($data))
			$document = new \DOMDocument('1.0', 'utf-8');
		if (!($document instanceof \DOMDocument))
			throw new SerializationException(
				'Could not de-serialize ' .
				TypeDescription::getName($data));
		try
		{
			$xmlFormat = new XmlFormat();
			$document->loadXML($data);
			return $xmlFormat->extractPropertiesFromDocument($document);
		}
		catch (\Exception $e)
		{
			throw new SerializationException($e->getMessage());
		}
	}

	public function serializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$document = $this->processData($data);
		$document->formatOutput = $this->shouldFormat($mediaType);
		return $document->saveXML();
	}

	public function isSerializableTo($data,
		MediaTypeInterface $mediaType = null)
	{
		if ($mediaType)
			return $this->isMediaTypeSerializable($mediaType);
		return true;
	}

	public function serializeToFile($filename, $data,
		MediaTypeInterface $mediaType = null)
	{
		$document = $this->processData($data);
		$document->formatOutput = $this->shouldFormat($mediaType);
		$document->save($filename);
	}

	public function unserializeFromFile($filename,
		MediaTypeInterface $mediaType = null)
	{
		$xml = new XmlFormat();
		return $xml->readFile($filename);
	}

	private function shouldFormat(MediaTypeInterface $mediaType = null)
	{
		if (!$mediaType)
			return false;
		if (!$mediaType->getParameters()->has(
			SerializationParameter::PRESENTATION_STYLE))
			return false;
		return \strcasecmp(
			$mediaType->getParameters()->get(
				SerializationParameter::PRESENTATION_STYLE),
			SerializationParameter::PRESENTATION_STYLE_PRETTY) == 0;
	}

	/**
	 *
	 * @param unknown $data
	 * @return \DOMDocument|DOMDocument|unknown
	 */
	private function processData($data)
	{
		$data = $this->primitifyData($data);
		$xml = new XmlFormat();
		return $xml->createDocumentWithContent($data);
	}

	protected function getSupportedMediaTypeParameterValues()
	{
		if (!isset(self::$supportedMediaTypeParameters))
		{
			self::$supportedMediaTypeParameters = [
				self::MEDIA_TYPE => [
					SerializationParameter::PRESENTATION_STYLE => SerializationParameter::PRESENTATION_STYLE_PRETTY
				]
			];
		}

		return self::$supportedMediaTypeParameters;
	}

	private static $supportedMediaTypeParameters;
}
