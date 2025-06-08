noresources/data
=======================================

Data serialization library

## Features

Serialize/Unserialize content to/from
* Ascii art
* CSV
* JSON
* INI
* Lua
* Plain text
* URL-encoded [application/x-www-form-urlencoded](https://datatracker.ietf.org/doc/html/rfc3986)
* XML (Apple property list)
* YAML

## Installation

```bash
composer require noresources/data
```

## Basic usage

```php
use NoreSources\Data\Serialization\SerializationManager;

$serializer = SerializationManager::getInstance();
$data = $serializer->unserializeFromFile ('foo.json');
$serializer->serializeToFile ('bar.yaml', $data);
```
## Specifying output format

```php
<?php
use NoreSources\Data\Serialization\SerializationManager;
use NoreSources\MediaType\MediaTypeFactory;

$data = [
	'foo' => 'bar',
	'int' => 42,
	'list' => [
		1,
		2,
		3
	],
	'object' => [
		'firstName' => 'John',
		'lastName' => 'Doe'
	],
	'bool' => true,
	'null' => null
];

$luaMediaType = MediaTypeFactory::getInstance()->createFromString(
	'text/x-lua; mode=module');

$serializer = new SerializationManager();
$lua = $serializer->serializeData($data, $luaMediaType);
die($lua . PHP_EOL);
```
