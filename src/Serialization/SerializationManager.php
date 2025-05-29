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
use NoreSources\Data\Serialization\Traits\SerializableMediaTypeTrait;
use NoreSources\Data\Serialization\Traits\UnserializableMediaTypeTrait;
use NoreSources\Data\Utility\FileExtensionListInterface;
use NoreSources\Data\Utility\MediaTypeListInterface;
use NoreSources\MediaType\Comparison;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\MediaType\MediaTypeInterface;

/**
 * Data(De)serializer aggregate
 */
class SerializationManager implements UnserializableMediaTypeInterface,
	SerializableMediaTypeInterface, DataUnserializerInterface,
	DataSerializerInterface, FileUnserializerInterface,
	FileSerializerInterface, StreamSerializerInterface,
	StreamUnserializerInterface, FileExtensionListInterface
{

	use UnserializableMediaTypeTrait;
	use SerializableMediaTypeTrait;

	/**
	 *
	 * @param boolean $registerBuiltins
	 *        	if TRUE, register all buil-tin serializers.
	 */
	public function __construct($registerBuiltins = true)
	{
		$this->serializers = [];
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
			$this->registerSerializer(new TextArtTableSerializer());
			$this->registerSerializer(new UrlEncodedSerializer());
			$this->registerSerializer(new ShellscriptSerializer());
			$this->registerSerializer(new IniSerializer());
			$this->registerSerializer(new CsvSerializer());
			$this->registerSerializer(new LuaSerializer());
			if (YamlSerializer::prerequisites())
				$this->registerSerializer(new YamlSerializer());
			if (JsonSerializer::prerequisites())
				$this->registerSerializer(new JsonSerializer());
			if (ApplePropertyListSerializer::prerequisites())
				$this->registerSerializer(
					new ApplePropertyListSerializer());
		}
	}

	/**
	 * Add a (file|data) (de)serializer method.
	 *
	 * @param DataUnserializerInterface|DataSerializerInterface|FileUnserializerInterface|FileSerializerInterface $e
	 *        	(De)serializer to add
	 */
	public function registerSerializer($e)
	{
		\array_unshift($this->serializers, $e);
		foreach ($this->stacks as $classname => $stack)
		{
			/** @var Stack $stack */
			if (\is_a($e, $classname, true))
				$stack->push($e);
		}
	}

	/**
	 *
	 * @param string $eClass
	 *        	Serialization class name
	 * @param string $filterMethod
	 *        	Serialization class filter method
	 * @param array $arguments
	 *        	Arguments to pass to $filterMethod
	 * @return array|array[]|\Traversable[]
	 */
	public function select($eClass, $filterMethod, $arguments = array())
	{
		$stack = $this->stacks[$eClass];
		return Container::filterValues($stack,
			function ($s) use ($filterMethod, $arguments) {
				return \call_user_func_array([
					$s,
					$filterMethod
				], $arguments);
			});
	}

	///////////////////////////////////////////////////////

	/**
	 * Get all media ranges supported by at least one serializer.
	 *
	 * @see \NoreSources\Data\Serialization\SerializableMediaTypeInterface::getSerializableMediaRanges()
	 */
	public function getSerializableMediaRanges()
	{
		$list = \array_filter($this->serializers,
			function ($s) {
				return $s instanceof SerializableMediaTypeInterface;
			});
		$mediaRanges = [];
		foreach ($list as $s)
		{
			$mediaRanges = \array_merge($mediaRanges,
				$s->getSerializableMediaRanges());
		}
		return \array_unique($mediaRanges);
	}

	public function buildSerialiableMediaTypeListMatchingMediaRanges(
		$expectedMediaRanges, $flags = 0)
	{

		/**
		 *
		 * @var SerializableMediaTypeInterface[] $serializers
		 */
		$serializers = \array_filter($this->serializers,
			function ($s) {
				return $s instanceof SerializableMediaTypeInterface;
			});

		$list = [];
		foreach ($serializers as $serializer)
		{
			$list = \array_merge($list,
				$serializer->buildSerialiableMediaTypeListMatchingMediaRanges(
					$expectedMediaRanges, $flags));
		} // each serializer

		return Container::uniqueValues($list,
			[
				Comparison::class,
				'lexical'
			]);
	}

	/**
	 * Get all media ranges supported by at least one deserializer.
	 *
	 * @see \NoreSources\Data\Serialization\UnserializableMediaTypeInterface::getUnserializableMediaRanges()
	 */
	public function getUnserializableMediaRanges()
	{
		$list = \array_filter($this->serializers,
			function ($s) {
				return $s instanceof UnserializableMediaTypeInterface;
			});
		$mediaRanges = [];
		foreach ($list as $s)
		{
			$mediaRanges = \array_merge($mediaRanges,
				$s->getUnserializableMediaRanges());
		}
		return \array_unique($mediaRanges);
	}

	///////////////////////////////////////////////////
	// StreamSerializer

	/**
	 *
	 * @see \NoreSources\Data\Serialization\StreamSerializerInterface::isSerializableToStream()
	 */
	public function isSerializableToStream($stream, $data,
		?MediaTypeInterface $mediaType = null)
	{
		$stack = $this->stacks[StreamSerializerInterface::class];

		foreach ($stack as $e)
		{
			$b = $e->isSerializableToStream($stream, $data, $mediaType);
			if ($b)
				return true;
		}

		return false;
	}

	/**
	 *
	 * @see \NoreSources\Data\Serialization\StreamSerializerInterface::serializeToStream()
	 */
	public function serializeToStream($stream, $data,
		?MediaTypeInterface $mediaType = null)
	{
		if (!$mediaType)
			$mediaType = $this->normalizeMediaTypeFromMedia($stack,
				$mediaType);

		$list = $this->getStreamSerializersFor($stream, $data,
			$mediaType, false);

		$messages = [];
		/**
		 *
		 * @var DataSerializerInterface $e
		 */
		foreach ($list as $e)
		{
			try
			{
				return $e->serializeToStream($stream, $data, $mediaType);
			}
			catch (\Exception $e)
			{
				$messages[] = $e->getMessage();
			}
		}

		throw new SerializationException(\implode(PHP_EOL, $messages));
	}

	public function getStreamSerializersFor($stream, $data = null,
		?MediaTypeInterface $mediaType = null,
		$normalizeMediaTypeFromMedia = true)
	{
		if ($normalizeMediaTypeFromMedia)
			$mediaType = $this->normalizeMediaTypeFromMedia($stream,
				$mediaType);

		return $this->select(StreamSerializerInterface::class,
			'isSerializableToStream', [
				$stream,
				$data,
				$mediaType
			]);
	}

	//////////////////////////////////////////////////////////////
// // StreamUnserializerInterface

	/**
	 *
	 * @see \NoreSources\Data\Serialization\StreamUnserializerInterface::isUnserializableFromStream()
	 */
	public function isUnserializableFromStream($data,
		?MediaTypeInterface $mediaType = null)
	{
		$stack = $this->stacks[StreamUnserializerInterface::class];
		foreach ($stack as $e)
		{
			$b = $e->isUnserializableFromStream($data, $mediaType);
			if ($b)
				return true;
		}

		return false;
	}

	/**
	 *
	 * @see \NoreSources\Data\Serialization\StreamUnserializerInterface::unserializeFromStream()
	 */
	public function unserializeFromStream($stream,
		?MediaTypeInterface $mediaType = null)
	{
		$messages = [];
		if (!$mediaType)
			$mediaType = $this->normalizeMediaTypeFromMedia($stream,
				$mediaType);

		$list = $this->getStreamUnserializersFor($stream, $mediaType,
			false);

		/**
		 *
		 * @var StreamUnserializerInterface $e
		 */
		foreach ($list as $e)
		{
			try
			{
				return $e->unserializeFromStream($stream, $mediaType);
			}
			catch (\Exception $e)
			{
				$messages[] = $e->getMessage();
			}
		}

		throw new SerializationException(\implode(PHP_EOL, $messages));
	}

	public function getStreamUnserializersFor($stream,
		?MediaTypeInterface $mediaType = null,
		$normalizeMediaTypeFromMedia = true)
	{
		if ($normalizeMediaTypeFromMedia)
			$mediaType = $this->normalizeMediaTypeFromMedia($stream,
				$mediaType);

		return $this->select(StreamUnserializerInterface::class,
			'isUnserializableFromStream', [
				$stream,
				$mediaType
			]);
	}

	/////////////////////////////////////////////////////////
	// DataUnserializerInterface

	/**
	 *
	 * @see \NoreSources\Data\Serialization\DataUnserializerInterface::isUnserializableFrom()
	 */
	public function isUnserializableFrom($data,
		?MediaTypeInterface $mediaType = null)
	{
		$stack = $this->stacks[DataUnserializerInterface::class];
		foreach ($stack as $e)
		{
			if ($e->isUnserializableFrom($data, $mediaType))
				return true;
		}

		return false;
	}

	/**
	 *
	 * @see \NoreSources\Data\Serialization\DataUnserializerInterface::unserializeData()
	 */
	public function unserializeData($data,
		?MediaTypeInterface $mediaType = null)
	{
		$list = $this->getDataUnserializersFor($data, $mediaType);
		$messages = [];
		foreach ($list as $e)
		{
			try
			{
				return $e->unserializeData($data, $mediaType);
			}
			catch (\Exception $e)
			{
				$messages[] = $e->getMessage();
			}
		}

		throw new SerializationException(\implode(PHP_EOL, $messages));
	}

	/**
	 * Get a list of DataUnserializerInterface capable of
	 * unserialize the given data/media type
	 *
	 * @param unknown $data
	 * @param MediaTypeInterface $mediaType
	 * @return DataUnserializerInterface[]
	 */
	public function getDataUnserializersFor($data,
		?MediaTypeInterface $mediaType = null)
	{
		$stack = $this->stacks[DataUnserializerInterface::class];
		return Container::filterValues($stack,
			function ($s) use ($data, $mediaType) {
				return $s->isUnserializableFrom($data, $mediaType);
			});
	}

	//////////////////////////////////////////////////////////
	// DataSerializerInterface

	/**
	 *
	 * @see \NoreSources\Data\Serialization\DataSerializerInterface::isSerializableTo()
	 */
	public function isSerializableTo($data,
		?MediaTypeInterface $mediaType = null)
	{
		if (!$mediaType && $data instanceof \Serializable)
			return false;
		$stack = $this->stacks[DataSerializerInterface::class];
		foreach ($stack as $e)
		{
			if ($e->isSerializableTo($data, $mediaType))
				return true;
		}

		return false;
	}

	/**
	 *
	 * @see \NoreSources\Data\Serialization\DataSerializerInterface::serializeData()
	 */
	public function serializeData($data,
		?MediaTypeInterface $mediaType = null)
	{
		if (!$mediaType && $data instanceof \Serializable)
			return $data->serialize();
		$list = $this->getDataSerializersFor($data, $mediaType);
		$messages = [];
		foreach ($list as $e)
		{
			try
			{
				return $e->serializeData($data, $mediaType);
			}
			catch (\Exception $e)
			{
				$messages[] = $e->getMessage();
			}
		}

		throw new SerializationException(\implode(PHP_EOL, $messages));
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
		?MediaTypeInterface $mediaType = null)
	{
		return $this->select(DataSerializerInterface::class,
			'isSerializableTo', [
				$data,
				$mediaType
			]);
	}

	////////////////////////////////////////////////
// 	FileUnserializerInterface

	/**
	 *
	 * @see \NoreSources\Data\Serialization\FileUnserializerInterface::isUnserializableFromFile()
	 */
	public function isUnserializableFromFile($filename,
		?MediaTypeInterface $mediaType = null)
	{
		$mediaType = $this->normalizeMediaTypeFromMedia($filename,
			$mediaType);
		$stack = $this->stacks[FileUnserializerInterface::class];
		foreach ($stack as $e)
		{
			if ($e->isUnserializableFromFile($filename, $mediaType))
				return true;
		}

		return false;
	}

	/**
	 *
	 * @see \NoreSources\Data\Serialization\FileUnserializerInterface::unserializeFromFile()
	 */
	public function unserializeFromFile($filename,
		?MediaTypeInterface $mediaType = null)
	{
		$mediaType = $this->normalizeMediaTypeFromMedia($filename,
			$mediaType);
		$list = $this->getFileUnserializersFor($filename, $mediaType,
			false);
		$messages = [];
		foreach ($list as $e)
		{
			try
			{
				$result = $e->unserializeFromFile($filename, $mediaType);

				return $result;
			}
			catch (\Exception $e)
			{
				$messages[] = $e->getMessage();
			}
		}

		if (\count($messages))
			throw new SerializationException(
				\implode(PHP_EOL, $messages));

		$name = ($mediaType) ? \strval($mediaType) : \pathinfo(
			$filename, PATHINFO_EXTENSION);
		throw new SerializationException(
			'No deserializer found for ' . $name . ' file');
	}

	/**
	 * Get a list of FileUnserializerTrait capable of unserialize the given file of the given
	 * media type
	 *
	 * @param string $filename
	 *        	File name
	 *        	File to unserialize
	 * @param MediaTypeInterface $mediaType
	 *        	File media type
	 * @param boolean $normalizeMediaTypeFromMedia
	 *        	Indicates if media type must be normalized
	 * @return FileUnserializerTrait[]
	 */
	public function getFileUnserializersFor($filename,
		?MediaTypeInterface $mediaType = null,
		$normalizeMediaTypeFromMedia = true)
	{
		if ($normalizeMediaTypeFromMedia)
			$mediaType = $this->normalizeMediaTypeFromMedia($filename,
				$mediaType);

		return $this->select(FileUnserializerInterface::class,
			'isUnserializableFromFile', [
				$filename,
				$mediaType
			]);
	}

	//////////////////////////////////////////////
	// FileSerializerInterface

	/**
	 *
	 * @see \NoreSources\Data\Serialization\FileSerializerInterface::isSerializableToFile()
	 */
	public function isSerializableToFile($filename, $data,
		?MediaTypeInterface $mediaType = null)
	{
		$mediaType = $this->normalizeMediaTypeFromMedia($filename,
			$mediaType, MediaTypeFactory::FROM_EXTENSION);

		$stack = $this->stacks[FileSerializerInterface::class];
		foreach ($stack as $e)
		{
			if ($e->isSerializableToFile($filename, $data, $mediaType))
				return true;
		}
		return false;
	}

	///////////////////////////////////////////////////////////////////////
	// FileExtensionListInterface

	/**
	 *
	 * @see \NoreSources\Data\Utility\FileExtensionListInterface::matchFileExtension()
	 */
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

	/**
	 *
	 * @return string[] List of all file extensions supported by manager (de)serializers
	 */
	public function getFileExtensions()
	{
		$extensions = [];
		foreach ([
			FileSerializerInterface::class,
			FileUnserializerInterface::class
		] as $s)
		{
			$stack = $this->stacks[$s];
			foreach ($stack as $e)
			{
				if ($e instanceof FileExtensionListInterface)
					$extensions = \array_merge($extensions,
						$e->getFileExtensions());
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
		?MediaTypeInterface $mediaType = null,
		$normalizeMediaTypeFromMedia = true)
	{
		if ($normalizeMediaTypeFromMedia)
			$mediaType = $this->normalizeMediaTypeFromMedia($filename,
				$mediaType);
		return $this->select(FileSerializerInterface::class,
			'isSerializableToFile', [
				$filename,
				$data,
				$mediaType
			]);
	}

	public function serializeToFile($filename, $data,
		?MediaTypeInterface $mediaType = null)
	{
		$mediaType = $this->normalizeMediaTypeFromMedia($filename,
			$mediaType, MediaTypeFactory::FROM_EXTENSION);
		$list = $this->getFileSerializersFor($filename, $data,
			$mediaType, false);
		$messages = [];
		foreach ($list as $e)
		{
			try
			{
				return $e->serializeToFile($filename, $data, $mediaType);
			}
			catch (\Exception $e)
			{
				$messages[] = $e->getMessage();
			}
		}

		if (\count($messages))
			throw new SerializationException(
				\implode(PHP_EOL, $messages));

		$name = ($mediaType) ? \strval($mediaType) : \pathinfo(
			$filename, PATHINFO_EXTENSION);
		throw new SerializationException(
			'No deserializer found for ' . $name . ' file');
		throw new SerializationException(
			'No serializer found for ' . $name . ' file');
	}

	/**
	 * Get the list of media types supported by (de)serializers that corresponds to the given file
	 * extension.
	 *
	 * @param string $extension
	 *        	File extension
	 * @return MediaTypeListInterface[]
	 */
	public function getMediaTypesForExtension($extension)
	{
		$list = [];
		foreach ($this->serializers as $serializer)
		{
			if (!($serializer instanceof FileExtensionListInterface))
				continue;
			if (!($serializer instanceof MediaTypeListInterface))
				continue;
			if (!\in_array($extension, $serializer->getFileExtensions()))
				continue;
			$list = \array_merge($list, $serializer->getMediaTypes());
		}

		return \array_unique($list);
	}

	private function normalizeMediaTypeFromMedia($media,
		?MediaTypeInterface $mediaType = null)
	{
		if ($mediaType instanceof MediaTypeInterface)
			return $mediaType;
		try
		{
			return MediaTypeFactory::getInstance()->createFromMedia(
				$media);
		}
		catch (MediaTypeException $e)
		{
			return null;
		}

		return ($mediaType && \strval($mediaType) == 'text/plain') ? null : $mediaType;
	}

	/**
	 * Per-serializer interface serializer stack
	 *
	 * @var Stack[]
	 */
	private $stacks;

	/**
	 *
	 * @var array
	 */
	private $serializers;
}
