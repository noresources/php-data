<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Console\Command;

use NoreSources\Container\Container;
use NoreSources\Data\Console\Utility;
use NoreSources\Data\Console\Option\MediaTypeOption;
use NoreSources\Data\Serialization\SerializableMediaTypeInterface;
use NoreSources\Data\Serialization\SerializationException;
use NoreSources\Data\Serialization\UnserializableMediaTypeInterface;
use NoreSources\MediaType\MediaTypeFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListMediaTypesCommand extends Command
{

	/**
	 * Command default name
	 *
	 * @var string
	 */
	public static $defaultName = 'list-media-types';

	protected function configure()
	{
		$outputMediaType = new MediaTypeOption('output-media-type', 'o',
			null, 'List output media type');
		$outputMediaType->setDefault('text/plain');
		$description = $this->getDefinition();
		$description->addOption($outputMediaType);

		$this->addOption('for-file', 'f', InputOption::VALUE_REQUIRED,
			'Get media types for the given file');
		{
			$list = Container::keys(Utility::$IO_TYPE_NAMES);
			$list = Container::implodeValues($list,
				[
					Container::IMPLODE_BETWEEN => ', ',
					Container::IMPLODE_BETWEEN_LAST => ' or '
				]);

			$this->addOption('io', null, InputOption::VALUE_REQUIRED,
				'I/O mode. Must be one of ' . $list);
		}
	}

	public function execute(InputInterface $input,
		OutputInterface $output)
	{
		$errorOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
		$manager = Utility::createSerializationManager($input, $output);

		$io = Utility::getIOFlags($input->getOption('io'));
		$filename = $input->getOption('for-file');
		if ($filename && !\is_file($filename))
			throw new \InvalidArgumentException(
				'Argument of --for-file must be an existing file;');

		$mediaTypes = [];

		if ($filename)
		{
			if (($io & Utility::IO_INPUT) == Utility::IO_INPUT)
			{
				$list = $manager->getFileUnserializersFor($filename);

				/**
				 *
				 * @var UnserializableMediaTypeInterface $serializer
				 */
				foreach ($list as $serializer)
				{
					if ($serializer instanceof UnserializableMediaTypeInterface)
					{
						$mediaTypes = \array_merge($mediaTypes,
							$serializer->getUnserializableMediaRanges());
					}
				}
			}

			if (($io & Utility::IO_OUTPUT) == Utility::IO_OUTPUT)
			{
				$list = $manager->getFileSerializersFor($filename);
				foreach ($list as $serializer)
				{
					/**
					 *
					 * @var SerializableMediaTypeInterface $serializer
					 */
					if ($serializer instanceof SerializableMediaTypeInterface)
						$mediaTypes = \array_merge($mediaTypes,
							$serializer->getSerializableMediaRanges());
				}
			}
		}
		else
		{
			if (($io & Utility::IO_INPUT) == Utility::IO_INPUT)
			{
				$mediaTypes = \array_merge($mediaTypes,
					$manager->getUnserializableMediaRanges());
			}

			if (($io & Utility::IO_OUTPUT) == Utility::IO_OUTPUT)
			{
				$mediaTypes = \array_merge($mediaTypes,
					$manager->getSerializableMediaRanges());
			}
		}

		$mediaTypes = Container::mapValues($mediaTypes, '\strval');
		$mediaTypes = \array_values(\array_unique($mediaTypes));

		$outputMediaType = MediaTypeFactory::createFromString(
			$input->getOption('output-media-type'));

		$text = '';
		$exitCode = Command::SUCCESS;
		try
		{
			$text = $manager->serializeData($mediaTypes,
				$outputMediaType);
			$output->writeln($text);
		}
		catch (SerializationException $e)
		{
			$errorOutput->writeln($e->getMessage());

			if ($output->isVeryVerbose())
				throw $e;

			$exitCode = Command::FAILURE;
		}

		return $exitCode;
	}
}