<?php

/**
 * Copyright © 2024 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization\ApplePropertyList;

use NoreSources\Container\Container;
use NoreSources\Type\TypeConversion;
use DOMDocument;
use DateTime;

class XmlFormat
{

	const ROOT_NODE_NAME = 'plist';

	/**
	 *
	 * @param string $filename
	 *        	Property list file
	 * @return mixed Data
	 */
	public function readFile($filename)
	{
		$document = new \DOMDocument('1.0', 'utf-8');
		$document->load($filename);
		return $this->extractPropertiesFromDocument($document);
	}

	/**
	 *
	 * @param \DOMDocument $document
	 *        	XML property list document
	 * @throws \InvalidArgumentException
	 * @return mixed
	 */
	public function extractPropertiesFromDocument(
		\DOMDocument $document)
	{
		$root = $document->documentElement;
		if (\strcasecmp($root->nodeName, self::ROOT_NODE_NAME) != 0)
			throw new \InvalidArgumentException(
				'Unexpected root element ' . $document->doctype->nodeName);

		$xpath = new \DOMXPath($document);
		$expression = './*';
		$list = $xpath->query($expression, $root);

		if ($list->length == 0)
			return NULL;
		if ($list->length > 1)
			throw new \InvalidArgumentException('Invalid plist content');

		return $this->getNodePropertyValue($list->item(0));
	}

	/**
	 *
	 * @param string $filename
	 *        	Property list file
	 * @param mixed $data
	 *        	Property list data
	 */
	public function writeFile($filename, $data)
	{
		$document = $this->createDocumentWithContent($data);
		$document->formatOutput = true;
		$document->save($filename);
	}

	/**
	 *
	 * @param mixed $data
	 *        	Property list data
	 * @return DOMDocument
	 */
	public function createDocumentWithContent($data)
	{
		$document = self::createDocument();

		$this->appendNodeFromProperty($document,
			$document->documentElement, null, $data);
		return $document;
	}

	private function getNodePropertyValue(\DOMNode $node)
	{
		switch (\strtolower($node->nodeName))
		{
			case 'null':
				return NULL;
			case 'true':
				return TRUE;
			case 'false':
				return FALSE;
			case 'integer':
				return TypeConversion::toInteger($node->nodeValue);
			case 'real':
				return TypeConversion::toFloat($node->nodeValue);
			case 'date':
				return \DateTime::createFromFormat(DateTime::ISO8601,
					$node->nodeValue);
			case 'array':
				$xpath = new \DOMXPath($node->ownerDocument);
				$expression = './*';
				$list = $xpath->query($expression, $node);
				return Container::mapValues($list,
					function ($n) {
						return $this->getNodePropertyValue($n);
					});
			case 'dict':
				{
					$dict = [];
					$offset = 0;
					$xpath = new \DOMXPath($node->ownerDocument);
					$expression = './*';
					$list = $xpath->query($expression, $node);
					foreach ($list as $keyOrValue)
					{
						if ($offset % 2 == 0)
						{
							if (\strcasecmp($keyOrValue->nodeName, 'key'))
								throw new \InvalidArgumentException(
									'Unexpected entry node ' .
									$keyOrValue->nodeName .
									'. key expected.');
							$key = $keyOrValue->nodeValue;
						}
						else
							$dict[$key] = $this->getNodePropertyValue(
								$keyOrValue);
						$offset++;
					}
					return $dict;
				}
			default:
				return $node->nodeValue;
		}
	}

	/**
	 *
	 * @param \DOMDocument $document
	 *        	Main document
	 * @param \DOMElement $element
	 *        	Parent element
	 * @param string|integer $key
	 *        	Entry key
	 * @param mixed $value
	 *        	Entry value
	 */
	private static function appendNodeFromProperty(
		\DOMDocument $document, \DOMElement $element, $key, $value)
	{
		if (\is_null($value))
		{
			if (empty($key))
				throw new \InvalidArgumentException(
					'Could not create NULL property');
			return;
		}
		if ($element === null)
			$element = $document->documentElement;
		if ($key !== null)
		{
			$key = $document->createElement('key', $key);
			$element->appendChild($key);
		}

		if (Container::isTraversable($value))
		{
			if (Container::isAssociative($value))
			{
				$dict = $document->createElement('dict');
				foreach ($value as $k => $v)
					self::appendNodeFromProperty($document, $dict, $k,
						$v);
				$element->appendChild($dict);
			}
			else
			{
				$array = $document->createElement('array');
				foreach ($value as $v)
					self::appendNodeFromProperty($document, $array, null,
						$v);
				$element->appendChild($array);
			}

			return;
		}

		if (\is_bool($value))
			$value = $document->createElement($value ? 'true' : 'false');
		elseif (\is_integer($value))
			$value = $document->createElement('integer',
				TypeConversion::toString($value));
		elseif (\is_numeric($value))
			$value = $document->createElement('real',
				TypeConversion::toString($value));
		elseif ($value instanceof \DateTimeInterface)
			$value = $document->createElement('date',
				$value->format(DateTime::ISO8601));
		else
			$value = $document->createElement('string',
				TypeConversion::toString($value));

		$element->appendChild($value);
	}

	/**
	 *
	 * @return \DOMDocument
	 */
	private static function createDocument()
	{
		$document = new \DOMDocument('1.0', 'UTF-8');
		$doctype = 'plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd"';
		$implementation = new \DOMImplementation();
		$doctype = $implementation->createDocumentType($doctype);
		$document->appendChild($doctype);
		$root = $document->createElement(self::ROOT_NODE_NAME);
		$root->setAttribute('version', '1.0');
		$document->appendChild($root);
		return $document;
	}
}
