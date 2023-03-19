<?php
/**
 * Copyright © 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Serialization;

use NoreSources\Container\Container;
use NoreSources\Container\Stack;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Data(De)serializer aggregate
 */
class DataSerializationManager implements DataUnserializerInterface,
	DataSerializerInterface, FileUnserializerInterface,
	FileSerializerInterface, StreamSerializerInterface,
	StreamUnserializerInterface, FileExtensionListInterface
{

	/**
	 *
	 * @param boolean $registerBuiltins
	 *        	if TRUE, register all buil-tin serializers.
	 */
	public function __construct($registerBuiltins = true)
	{
		$this->stacks = [
			DataUnserializerInterface::class => new Stack(),
			DataSerializerInterface::class => new Stack(),
			FileUnserializerInterface::class => new Stack(),
			FileSerializerInterface::class => new Stack(),
			StreamUnserializerInterface::class => new Stack(),
			StreamSerializerInterface::class => new Stack()
		];

		if ($registerBuiltins)
		{
			$this->registerSerializer(new PlainTextSerializer());
			$this->registerSerializer(new UrlEncodedSerializer());
			$this->registerSerializer(new IniSerializer());
			$this->registerSerializer(new CsvSerializer());
			$this->registerSerializer(new LuaSerializer());
			if (YamlSerializer::prerequisites())
				$this->registerSerializer(new YamlSerializer());
			if (JsonSerializer::prerequisites())
				$this->registerSerializer(new JsonSerializer());
		}
	}

	/**
	 * Add a (file|data) (de)serializer method.
	 *
	 * @param DataUnserializerInterface|DataSerializerInterface|FileUnserializerInterface|FileSerializerInterface $serializer
	 *        	(De)serializer to add
	 */
	public function registerSerializer($serializer)
	{
		foreach ($this->stacks as $classname => $stack)
		{
			/** @var Stack $stack */
			if (\is_a($serializer, $classname, true))
				$stack->push($serializer);
		}
	}

	///////////////////////////////////////////////////
	// StreamSerializer
	public function getSerializableMediaTypes()
	{
		$stack = $this->stacks[StreamSerializerInterface::class];
		$list = [];
		foreach ($stack as $serializer)
		{
			$list = \array_merge($list,
				$serializer->getSerializableMediaTypes());
		}
		return \array_unique($list);
	}

	public function isSerializable($data,
		MediaTypeInterface $mediaType = null)
	{
		$stack = $this->stacks[StreamSerializerInterface::class];

		foreach ($stack as $serializer)
		{
			$b = $serializer->isSerializable($data, $mediaType);
			if ($b)
				return true;
		}

		return false;
	}

	public function serializeToStream($stream, $data,
		MediaTypeInterface $mediaType = null)
	{
		$list = $this->getSerializersFor($data, $mediaType);
		$messages = [];
		/**
		 *
		 * @var StreamSerializerInterface $serializer
		 */
		foreach ($list as $serializer)
		{
			try
			{
				return $serializer->serializeToStream($stream, $data,
					$mediaType);
			}
			catch (\Exception $e)
			{
				$messages[] = $e->getMessage();
			}
		}

		throw new DataSerializationException(
			\implode(PHP_EOL, $messages));
	}

	/**
	 * Get a list of serializers that MAY be used to serialize the given data to the given media
	 * type
	 *
	 * @param mixed $data
	 *        	Data to serialize
	 * @param MediaTypeInterface $mediaType
	 *        	Target media type
	 * @return StreamSerializerInterface[]
	 */
	public function getSerializersFor($data,
		MediaTypeInterface $mediaType = null)
	{
		$stack = $this->stacks[StreamSerializerInterface::class];
		return Container::filterValues($stack,
			function ($s) use ($data, $mediaType) {
				return $s->isSerializable($data, $mediaType);
			});
	}

	//////////////////////////////////////////////////////////////
// DataUnserializerInterface
	public function getUnserializableDataMediaTypes()
	{
		$stack = $this->stacks[DataUnserializerInterface::class];
		$list = [];
		foreach ($stack as $serializer)
		{
			$l = $serializer->getUnserializableDataMediaTypes();
			foreach ($l as $s => $t)
			{
				if (!\is_string($s))
					$s = \strval($t);
				$list[$s] = $t;
			}
		}
		return $list;
	}

	public function canUnserializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$stack = $this->stacks[DataUnserializerInterface::class];
		foreach ($stack as $serializer)
		{
			if ($serializer->canUnserializeData($data, $mediaType))
				return true;
		}

		return false;
	}

	/**
	 * Get a list of DataUnserializerInterface capable of
	 * unserialize the given data/media type
	 *
	 * @param unknown $data
	 * @param MediaTypeInterface $mediaType
	 * @return DataUnserializerInterface[]
	 */
	public function getDataUnserializerFor($data,
		MediaTypeInterface $mediaType = null)
	{
		$stack = $this->stacks[DataUnserializerInterface::class];
		return Container::filterValues($stack,
			function ($s) use ($data, $mediaType) {
				return $s->canUnserializeData($data, $mediaType);
			});
	}

	public function getUnserializableMediaTypes()
	{
		$stack = $this->stacks[StreamUnserializerInterface::class];
		$list = [];
		foreach ($stack as $serializer)
		{
			$list = \array_merge($list,
				$serializer->getUnserializableMediaTypes());
		}
		return \array_unique($list);
	}

	public function isUnserializable($data,
		MediaTypeInterface $mediaType = null)
	{
		$stack = $this->stacks[StreamUnserializerInterface::class];

		foreach ($stack as $serializer)
		{
			$b = $serializer->isUnserializable($data, $mediaType);
			if ($b)
				return true;
		}

		return false;
	}

	public function unserializeFromStream($stream,
		MediaTypeInterface $mediaType = null)
	{
		$list = $this->getUnserializersFor($stream, $mediaType);
		$messages = [];
		/**
		 *
		 * @var StreamUnserializerInterface $unserializer
		 */
		foreach ($list as $unserializer)
		{
			try
			{
				return $unserializer->unserializeFromStream($stream,
					$mediaType);
			}
			catch (\Exception $e)
			{
				$messages[] = $e->getMessage();
			}
		}

		throw new DataSerializationException(
			\implode(PHP_EOL, $messages));
	}

	public function getUnserializersFor($stream,
		MediaTypeInterface $mediaType = null)
	{
		$stack = $this->stacks[StreamUnserializerInterface::class];
		return Container::filterValues($stack,
			function ($s) use ($stream, $mediaType) {
				return $s->isUnserializable($stream, $mediaType);
			});
	}

	public function unserializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		$list = $this->getDataUnserializerFor($data, $mediaType);
		$messages = [];
		foreach ($list as $serializer)
		{
			try
			{
				return $serializer->unserializeData($data, $mediaType);
			}
			catch (\Exception $e)
			{
				$messages[] = $e->getMessage();
			}
		}

		throw new DataSerializationException(
			\implode(PHP_EOL, $messages));
	}

	public function getSerializableDataMediaTypes()
	{
		$stack = $this->stacks[DataSerializerInterface::class];
		$list = [];
		foreach ($stack as $serializer)
		{
			$l = $serializer->getSerializableDataMediaTypes();
			foreach ($l as $s => $t)
			{
				if (!\is_string($s))
					$s = \strval($t);
				$list[$s] = $t;
			}
		}
		return $list;
	}

	public function canSerializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		if (!$mediaType && $data instanceof \Serializable)
			return false;
		$stack = $this->stacks[DataSerializerInterface::class];
		foreach ($stack as $serializer)
		{
			if ($serializer->canSerializeData($data, $mediaType))
				return true;
		}

		return false;
	}

	/**
	 * Get a list of DataSerializerInterface capable of serialize the given data / media type
	 *
	 * @param unknown $data
	 *        	Data to serialize
	 * @param MediaTypeInterface $mediaType
	 *        	Target media type
	 * @return DataSerializerInterface[]
	 */
	public function getDataSerializersFor($data,
		MediaTypeInterface $mediaType = null)
	{
		$stack = $this->stacks[DataSerializerInterface::class];
		return Container::filterValues($stack,
			function ($serializer) use ($data, $mediaType) {
				return $serializer->canSerializeData($data, $mediaType);
			});
	}

	public function serializeData($data,
		MediaTypeInterface $mediaType = null)
	{
		if (!$mediaType && $data instanceof \Serializable)
			return $data->serialize();
		$list = $this->getDataSerializersFor($data, $mediaType);
		$messages = [];
		foreach ($list as $serializer)
		{
			try
			{
				return $serializer->serializeData($data, $mediaType);
			}
			catch (\Exception $e)
			{
				$messages[] = $e->getMessage();
			}
		}

		throw new DataSerializationException(
			\implode(PHP_EOL, $messages));
	}

	public function getUnserializableFileMediaTypes()
	{
		$stack = $this->stacks[FileUnserializerInterface::class];
		$list = [];
		foreach ($stack as $serializer)
		{
			$l = $serializer->getUnserializableFileMediaTypes();
			foreach ($l as $s => $t)
			{
				if (!\is_string($s))
					$s = \strval($t);
				$list[$s] = $t;
			}
		}
		return $list;
	}

	public function canUnserializeFromFile($filename,
		MediaTypeInterface $mediaType = null)
	{
		$mediaType = $this->normalizeMediaTypeFromMedia($filename,
			$mediaType);
		$stack = $this->stacks[FileUnserializerInterface::class];
		foreach ($stack as $serializer)
		{
			if ($serializer->canUnserializeFromFile($filename,
				$mediaType))
				return true;
		}

		return false;
	}

	/**
	 * Get a list of FileUnserializerTrait capable of unserialize the given file of the given
	 * media type
	 *
	 * @param unknown $filename
	 *        	File to unserialize
	 * @param MediaTypeInterface $mediaType
	 *        	File media type
	 * @param boolean $normalizeMediaTypeFromMedia
	 *        	Indicates if media type must be normalized
	 * @return FileUnserializerTrait[]
	 */
	public function getFileUnserializersFor($filename,
		MediaTypeInterface $mediaType = null,
		$normalizeMediaTypeFromMedia = true)
	{
		if ($normalizeMediaTypeFromMedia)
			$mediaType = $this->normalizeMediaTypeFromMedia($filename,
				$mediaType);
		$stack = $this->stacks[FileUnserializerInterface::class];
		return Container::filterValues($stack,
			function ($serializer) use ($filename, $mediaType) {
				return $serializer->canUnserializeFromFile($filename,
					$mediaType);
			});
	}

	public function unserializeFromFile($filename,
		MediaTypeInterface $mediaType = null)
	{
		$mediaType = $this->normalizeMediaTypeFromMedia($filename,
			$mediaType);
		$list = $this->getFileUnserializersFor($filename, $mediaType,
			false);
		$messages = [];
		foreach ($list as $serializer)
		{
			try
			{
				$result = $serializer->unserializeFromFile($filename,
					$mediaType);

				return $result;
			}
			catch (\Exception $e)
			{
				$messages[] = $e->getMessage();
			}
		}

		if (\count($messages))
			throw new DataSerializationException(
				\implode(PHP_EOL, $messages));

		$name = ($mediaType) ? \strval($mediaType) : \pathinfo(
			$filename, PATHINFO_EXTENSION);
		throw new DataSerializationException(
			'No deserializer found for ' . $name . ' file');
	}

	public function getSerializableFileMediaTypes()
	{
		$stack = $this->stacks[FileSerializerInterface::class];
		$list = [];
		foreach ($stack as $serializer)
		{
			$l = $serializer->getSerializableFileMediaTypes();
			foreach ($l as $s => $t)
			{
				if (!\is_string($s))
					$s = \strval($t);
				$list[$s] = $t;
			}
		}
		return $list;
	}

	public function canSerializeToFile($filename, $data,
		MediaTypeInterface $mediaType = null)
	{
		$mediaType = $this->normalizeMediaTypeFromMedia($filename,
			$mediaType, MediaTypeFactory::FROM_EXTENSION);

		$stack = $this->stacks[FileSerializerInterface::class];
		foreach ($stack as $serializer)
		{
			if ($serializer->canSerializeToFile($filename, $data,
				$mediaType))
				return true;
		}
		return false;
	}

	public function matchFileExtension($extension)
	{
		$extensions = $this->getFileExtensions();
		foreach ($extensions as $x)
		{
			if (\strcasecmp($extension, $x) == 0)
				return true;
		}
		return false;
	}

	public function getFileExtensions()
	{
		$extensions = [];
		foreach ([
			FileSerializerInterface::class,
			FileUnserializerInterface::class
		] as $s)
		{
			$stack = $this->stacks[$s];
			foreach ($stack as $serializer)
			{
				if ($serializer instanceof FileExtensionListInterface)
					$extensions = \array_merge($extensions,
						$serializer->getFileExtensions());
			}
		}
		return \array_unique($extensions);
	}

	/**
	 * Get a list of FileSerializerInterface capable of serialize data to the given file to the
	 * given file media type
	 *
	 * @param unknown $filename
	 *        	Target file name
	 * @param MediaTypeInterface $mediaType
	 *        	Target media type
	 * @param boolean $normalizeMediaTypeFromMedia
	 *        	Indicates if the media type must be normalized
	 * @return FileSerializerInterface[]
	 */
	public function getFileSerializersFor($filename, $data = null,
		MediaTypeInterface $mediaType = null,
		$normalizeMediaTypeFromMedia = true)
	{
		if ($normalizeMediaTypeFromMedia)
			$mediaType = $this->normalizeMediaTypeFromMedia($filename,
				$mediaType);
		$stack = $this->stacks[FileSerializerInterface::class];
		return Container::filterValues($stack,
			function ($serializer) use ($filename, $data, $mediaType) {
				return $serializer->canSerializeToFile($filename, $data,
					$mediaType);
			});
	}

	public function serializeToFile($filename, $data,
		MediaTypeInterface $mediaType = null)
	{
		$mediaType = $this->normalizeMediaTypeFromMedia($filename,
			$mediaType, MediaTypeFactory::FROM_EXTENSION);
		$list = $this->getFileSerializersFor($filename, $data,
			$mediaType, false);
		$messages = [];
		foreach ($list as $serializer)
		{
			try
			{
				return $serializer->serializeToFile($filename, $data,
					$mediaType);
			}
			catch (\Exception $e)
			{
				$messages[] = $e->getMessage();
			}
		}

		if (\count($messages))
			throw new DataSerializationException(
				\implode(PHP_EOL, $messages));

		$name = ($mediaType) ? \strval($mediaType) : \pathinfo(
			$filename, PATHINFO_EXTENSION);
		throw new DataSerializationException(
			'No deserializer found for ' . $name . ' file');
		throw new DataSerializationException(
			'No serializer found for ' . $name . ' file');
	}

	private function normalizeMediaTypeFromMedia($media,
		MediaTypeInterface $mediaType = null)
	{
		if ($mediaType instanceof MediaTypeInterface)
			return $mediaType;
		try
		{
			return MediaTypeFactory::getInstance()->createFromMedia($media);
		}
		catch (MediaTypeException $e)
		{
			return null;
		}

		return ($mediaType && \strval($mediaType) == 'text/plain') ? null : $mediaType;
	}

	/** @var Stack[] */
	private $stacks;
}
