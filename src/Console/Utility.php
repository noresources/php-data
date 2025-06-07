<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package Data
 */
namespace NoreSources\Data\Console;

use Composer\InstalledVersions;
use NoreSources\Container\Container;
use NoreSources\Data\Serialization\SerializationManager;
use NoreSources\MediaType\MediaTypeFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Utility
{

	const IO_OUTPUT = 0x10;

	const IO_INPUT = 0x11;

	public static $IO_TYPE_NAMES = [
		'input' => self::IO_INPUT,
		'unserialize' => self::IO_INPUT,
		'deserialize' => self::IO_INPUT,
		'output' => self::IO_OUTPUT,
		'serialize' => self::IO_OUTPUT,
		'io' => self::IO_INPUT | self::IO_OUTPUT,
		'all' => self::IO_INPUT | self::IO_OUTPUT
	];

	public static function getIOFlags($text)
	{
		if (empty($text))
			return (self::IO_INPUT | self::IO_OUTPUT);

		foreach (self::$IO_TYPE_NAMES as $name => $value)
		{
			if (\strcasecmp($name, $text) === 0)
				return $value;
		}
		$list = Container::keys(self::$IO_TYPE_NAMES);
		$list = Container::implodeValues($list,
			[
				Container::IMPLODE_BETWEEN => ', ',
				Container::IMPLODE_BETWEEN_LAST => ' or '
			]);
		throw new \InvalidArgumentException(
			'IO argument must be one of ' . $list);
	}

	/**
	 *
	 * @param InputInterface $input
	 * @return \NoreSources\Data\Serialization\SerializationManager
	 */
	public static function createSerializationManager(
		InputInterface $input, OutputInterface $output)
	{
		$manager = new SerializationManager();
		if (!$input->getOption('auto-register-serializers'))
			return $manager;

		if ($output->isVerbose())
			$output->writeln(
				'Registering additional serializers from composer package definitions.');

		$json = MediaTypeFactory::getInstance()->createFromString(
			'application/json');
		$depedencies = InstalledVersions::getInstalledPackages();
		foreach ($depedencies as $packageName)
		{
			$packagePath = InstalledVersions::getInstallPath(
				$packageName);
			if (!($packagePath && \is_dir($packagePath)))
				continue;

			$packageDescription = $packagePath . '/composer.json';
			$package = $manager->unserializeFromFile(
				$packageDescription, $json);

			self::registerSerializerFromComposerPackage($manager,
				$package, $output);
		}

		$package = InstalledVersions::getRootPackage();
		self::registerSerializerFromComposerPackage($manager, $package,
			$output);

		return $manager;
	}

	public static function registerSerializerFromComposerPackage(
		SerializationManager $manager, $package, OutputInterface $output)
	{
		if (($extra = Container::keyValue($package, 'extra')) &&
			($section = Container::keyValue($extra, 'ns-php-data')) &&
			($serializers = Container::keyValue($section, 'serializers')))
		{
			foreach ($serializers as $className)
			{
				if ($output->isVerbose())
				{
					$output->writeln(
						' * ' . $className . ' from ' .
						Container::keyValue($package, 'name',
							'a package'));
				}
				$cls = new \ReflectionClass($className);
				$manager->registerSerializer($cls->newInstance());
			}
		}
	}
}
