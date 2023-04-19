<?php

/**
 * Copyright © 2023 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * #package Data
 */
namespace NoreSources\Data\Console;

use Composer\InstalledVersions;
use NoreSources\Container\Container;
use NoreSources\Data\Serialization\DataSerializationManager;
use NoreSources\MediaType\MediaTypeFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Utility
{

	/**
	 *
	 * @param InputInterface $input
	 * @return \NoreSources\Data\Serialization\DataSerializationManager
	 */
	public static function createSerializationManager(
		InputInterface $input, OutputInterface $output)
	{
		$manager = new DataSerializationManager();
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
		DataSerializationManager $manager, $package,
		OutputInterface $output)
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
