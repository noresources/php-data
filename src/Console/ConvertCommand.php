<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Console;

use NoreSources\Container\Container;
use NoreSources\MediaType\MediaTypeException;
use NoreSources\MediaType\MediaTypeFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertCommand extends Command
{

	/**
	 * Command default name
	 *
	 * @var string
	 */
	public static $defaultName = 'convert';

	protected function configure()
	{
		$this->addArgument('input', InputArgument::OPTIONAL,
			'Input file', 'php://stdin')
			->addArgument('output', InputArgument::OPTIONAL,
			'Output file', 'php://stdout')
			->addOption('from', null, InputOption::VALUE_REQUIRED,
			'Input media type')
			->addOption('to', null, InputOption::VALUE_REQUIRED,
			'Output media type')
			->addOption('auto-register-serializers', 'a', null,
			'Auto register (de)serializers based on composer package descriptions');
	}

	public function execute(InputInterface $input,
		OutputInterface $output)
	{
		$errorOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
		$inputURI = $input->getArgument('input');
		$outputURI = $input->getArgument('output');
		$inputMediaType = $input->getOption('from');
		$outputMediaType = $input->getOption('to');
		$mediaTypeFactory = MediaTypeFactory::getInstance();
		$manager = Utility::createSerializationManager($input, $output);
		$inputStream = \filter_var($inputURI, FILTER_VALIDATE_URL);
		$outputStream = \filter_Var($outputURI, FILTER_VALIDATE_URL);

		$end = function ($code, $message) use ($output, &$inputStream,
		&$outputStream) {
			if (\is_resource($inputStream))
				\fclose($inputStream);
			if (\is_resource($outputStream))
				\fclose($outputStream);
			if ($code && ($output instanceof ConsoleOutputInterface))
				$output = $output->getErrorOutput();
			if (!empty($message))
				$output->writeln($message);
			return $code;
		};

		if ($inputStream && ($inputStream == $inputURI))
		{
			$inputStream = @\fopen($inputURI, 'r');
			if ($inputStream === false)
			{
				$error = \error_get_last();
				return $end(1, $error['message']);
			}
		}

		if ($outputStream && ($outputStream == $outputURI))
		{
			$outputStream = @\fopen($outputURI, 'w');
			if ($outputStream === false)
			{
				$error = \error_get_last();
				return $end(1, $error['message']);
			}
		}

		if (\is_string($inputMediaType))
		{
			try
			{
				$inputMediaType = $mediaTypeFactory->createFromString(
					$inputMediaType);
			}
			catch (MediaTypeException $e)
			{
				return $end(1,
					'<error>Invalid input media type ' .
					$input->getOption('from') . '</error>');
			}
		}

		if (!$inputMediaType && \is_file($inputURI))
		{

			try
			{
				$inputMediaType = $mediaTypeFactory->createFromMedia(
					$inputURI);
				if (!$manager->isMediaTypeSerializable($inputMediaType))
				{
					$alt = $inputMediaType = $mediaTypeFactory->createFromMedia(
						$inputURI, MediaTYpeFactory::FROM_EXTENSION);
					if ($manager->isMediaTypeSerializable($alt))
					{
						if ($output->getVerbosity() >=
							OutputInterface::VERBOSITY_VERY_VERBOSE)
						{
							$errorOutput->writeln(
								'Input media type ' .
								\strval($inputMediaType) .
								' guessed from input content is not supported by serialization manager but ' .
								\strval($alt) .
								' guessed from extension is.');
						}
						$inputMediaType = $alt;
					}
				}
			}
			catch (MediaTypeException $e)
			{}
		}

		if (!$inputMediaType && $inputStream)
		{
			try
			{
				$inputMediaType = $mediaTypeFactory->createFromMedia(
					$inputStream);
			}
			catch (MediaTypeException $e)
			{}
		}

		if (!$inputMediaType)
		{
			return $end(1,
				'<error>Input media type cannot be guessed. Use --from to specify it.</error>');
		}

		if (\is_string($outputMediaType))
		{
			try
			{
				$outputMediaType = $mediaTypeFactory->createFromString(
					$outputMediaType);
			}
			catch (MediaTypeException $e)
			{
				return $end(1,
					'<error>Invalid output media type ' .
					$input->getOption('to') . '</error>');
			}
		}

		if (!$outputMediaType && $outputStream)
		{
			try
			{
				$outputMediaType = $mediaTypeFactory->createFromMedia(
					$outputMediaType);
			}
			catch (MediaTypeException $e)
			{}
		}

		if (!$outputMediaType && \is_string($outputURI))
		{
			try
			{
				$flags = MediaTypeFactory::FROM_EXTENSION;
				if (\is_file($outputURI))
					$flags = MediaTypeFactory::FROM_EXTENSION_FIRST |
						MediaTypeFactory::FROM_CONTENT;

				$outputMediaType = $mediaTypeFactory->createFromMedia(
					$outputURI, $flags);
			}
			catch (MediaTypeException $e)
			{}
		}

		if (!$outputMediaType)
		{
			return $end(1,
				'<error>Output media type cannot be guessed. Use --to to specify it.</error>');
		}

		if ($output->getVerbosity() >=
			OutputInterface::VERBOSITY_VERY_VERBOSE)
		{

			$inputUnserializers = [];
			if ($inputStream)
				$inputUnserializers = $manager->getStreamUnserializersFor(
					$inputStream, $inputMediaType);
			else
				$inputUnserializers = $manager->getFileUnserializersFor(
					$inputURI, $inputMediaType);

			$outputSerializers = [];
			if ($outputStream)
				$outputSerializers = $manager->getStreamSerializersFor(
					$outputStream, null, $outputMediaType);
			else
				$outputSerializers = $manager->getFileSerializersFor(
					$outputURI, $outputMediaType);

			$output->writeln(
				'Input ' . ($inputStream ? 'stream ' : 'file ') .
				$inputURI);
			$output->writeln(
				' * Media type: ' . \strval($inputMediaType));
			$output->writeln(
				' * ' . \count($inputUnserializers) . ' deserializers');
			$output->writeln(
				Container::implodeValues(
					\array_map('\get_class', $inputUnserializers),
					[
						Container::IMPLODE_BEFORE => '   * ',
						Container::IMPLODE_BETWEEN => PHP_EOL
					]));

			$output->writeln(
				'Output ' . ($outputStream ? 'stream ' : 'file ') .
				$outputURI);
			$output->writeln(
				' * Media type: ' . \strval($outputMediaType));
			$output->writeln(
				' * ' . \count($outputSerializers) . ' deserializers');
			$output->writeln(
				Container::implodeValues(
					\array_map('\get_class', $outputSerializers),
					[
						Container::IMPLODE_BEFORE => '   * ',
						Container::IMPLODE_BETWEEN => PHP_EOL
					]));
		}
		elseif ($output->isVerbose())
		{
			$output->writeln(
				'Input ' . $inputURI . ' (' . $inputMediaType . ')');
			$output->writeln(
				'Outout ' . $outputURI . ' (' . $outputMediaType . ')');
		}

		$data = null;
		$exitCode = 0;

		try
		{
			if ($inputStream &&
				$manager->isUnserializableFromStream($inputStream,
					$inputMediaType))
			{
				$data = $manager->unserializeFromStream($inputStream,
					$inputMediaType);
			}
			elseif (\is_file($inputURI) &&
				($manager->isUnserializableFromFile($inputURI,
					$inputMediaType)))
			{
				$data = $manager->unserializeFromFile($inputURI,
					$inputMediaType);
			}
			else
				return $end(1,
					'<error>Unable to deserialize input</error>');

			if ($outputStream &&
				$manager->isSerializableToStream($outputStream, $data,
					$outputMediaType))
			{
				$manager->serializeToStream($outputStream, $data,
					$outputMediaType);
			}
			else
			{
				$manager->serializeToFile($outputURI, $data,
					$outputMediaType);
			}
		}
		catch (\Exception $e)
		{
			return $end(1, $e->getMessage());
		}

		return $end(0, '');
	}
}
