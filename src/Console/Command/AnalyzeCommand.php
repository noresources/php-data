<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Console\Command;

use NoreSources\Container\Container;
use NoreSources\Data\Analyzer;
use NoreSources\Data\CollectionClass;
use NoreSources\Data\Console\Utility;
use NoreSources\Data\Console\Option\MediaTypeOption;
use NoreSources\Data\Console\Option\MediaTypeParameterListOption;
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\Text\Text;
use NoreSources\Type\TypeDescription;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyzeCommand extends Command
{

	public static $defaultName = 'analyze';

	public function __construct()
	{
		parent::__construct();

		$this->addArgument('input', InputArgument::REQUIRED,
			'File to analyze');
		$definition = $this->getDefinition();
		$definition->addOption(new MediaTypeOption());
		$definition->addOption(
			new MediaTypeParameterListOption('params'));

		$to = new MediaTypeOption('to', null, null, 'Output media type');
		$to->setDefault('text/plain');
		$toParams = new MediaTypeParameterListOption('to-params', null,
			null, 'Output media type parameters');
		$definition->addOption($to);
		$definition->addOption($toParams);
	}

	public function execute(InputInterface $input,
		OutputInterface $output)
	{
		$filename = $input->getArgument('input');
		if (!\is_file($filename))
			throw new \InvalidArgumentException(
				'Input must be an existing file.');

		$mediaType = $input->getOption('media-type');
		$factory = MediaTypeFactory::getInstance();
		if ($mediaType !== null)
			$mediaType = $factory->createFromString($mediaType);
		else
			$mediaType = $factory->createFromMedia($filename);

		MediaTypeParameterListOption::populateMediaType($mediaType,
			$input, 'params');

		$filename = \realpath($filename);

		$serializer = Utility::createSerializationManager($input,
			$output);

		$data = $serializer->unserializeFromFile($filename, $mediaType);
		$dataType = TypeDescription::getName($data);
		$analyzer = Analyzer::getInstance();
		$properties = $analyzer($data);
		$properties = \array_merge(
			[
				'media-type' => \strval($mediaType),
				'type' => $dataType
			], $properties);
		$keysMaxLength = 0;
		$keyMap = [];
		foreach ($properties as $key => $value)
		{
			$c = \strlen($key);
			if ($c > $keysMaxLength)
				$keysMaxLength = $c;

			switch ($key)
			{
				case Analyzer::CONTAINER_PROPERTIES:
					if ($output->getVerbosity() >
						OutputInterface::VERBOSITY_NORMAL)
						$value = $this->getContainerPropertyNames(
							$value);
				break;
				case Analyzer::COLLECTION_CLASS:
					if ($output->getVerbosity() >
						OutputInterface::VERBOSITY_NORMAL)
						$value = CollectionClass::getCollectionClassNames(
							$value);
				break;
			}

			if ($output->getVerbosity() >=
				OutputInterface::VERBOSITY_VERY_VERBOSE)
			{
				$keyMap[$key] = $this->getHumanReadable($key);
				if (Container::isTraversable($value))
					$value = Container::mapValues($value,
						[
							$this,
							'getHumanReadable'
						]);
				else
					$value = $this->getHumanReadable($value);
			}
			else
			{
				$keyMap[$key] = $key;
			}
			$properties[$key] = $value;
		}

		$tmp = [];
		foreach ($properties as $key => $value)
			$tmp[$keyMap[$key]] = $value;
		$properties = $tmp;

		$toMediaType = $input->getOption('to');
		if (\strcasecmp($toMediaType, 'text/plain') === 0)
		{

			$format = '%-' . $keysMaxLength . '.' . $keysMaxLength .
				's: %s';
			foreach ($properties as $key => $value)
			{
				if (Container::isTraversable($value))
					$value = Container::implodeValues($value, ', ');
				$output->writeln(\sprintf($format, $key, $value));
			}
		}
		else
		{
			$toMediaType = $factory->createFromString($toMediaType);
			MediaTypeParameterListOption::populateMediaType(
				$toMediaType, $input, 'to-params');
			$output->writeln(
				$serializer->serializeData($properties, $toMediaType));
		}

		return 0;
	}

	public function getHumanReadable($text)
	{
		return Text::toCodeCase($text,
			[
				Text::CODE_CASE_SEPARATOR => ' ',
				Text::CODE_CASE_CAPITALIZE => Text::CODE_CASE_CAPITALIZE_FIRST
			]);
	}

	private function getContainerPropertyNames($properties)
	{
		static $map = [
			Container::COUNTABLE => 'countable',
			Container::MODIFIABLE => 'modifiable',
			Container::SHRINKABLE => 'shrinkable',
			COntainer::EXTENDABLE => 'extendable',
			COntainer::TRAVERSABLE => 'traversable',
			Container::RANDOM_ACCESS => 'random-access',
			Container::PROPERTY_ACCESS => 'property-access',
			Container::OFFSET_ACCESS => 'offset-access'
		];

		$names = [];
		foreach ($map as $value => $name)
		{
			if (($properties & $value) == $value)
				$names[] = $name;
		}
		return $names;
	}
}