#!/usr/bin/env php
<?php
use NoreSources\MediaType\MediaTypeFactory;
use NoreSources\Data\Serialization\SerializationManager;
use NoreSources\Data\Serialization\Traits\FileMediaTypeNormalizerTrait;
use NoreSources\Type\TypeDescription;

require_once (__DIR__ . '/../vendor/autoload.php');

class App
{

	public function run($argv)
	{
		$verbose = false;
		$from = '';
		$input = '';
		$to = '';
		$output = '';

		$program = \array_shift($argv);
		while (\count($argv))
		{
			$a = \array_shift($argv);
			if ($a == '--verbose')
				$verbose = true;
			elseif ($a == '--from')
			{
				if (!\count($argv))
				{
					trigger_error('Missing --from argument value');
					exit(1);
				}

				$a = \array_shift($argv);
				$from = MediaTypeFactory::getInstance()->createFromString($a);
			}
			elseif ($a == '--to')
			{
				if (!\count($argv))
				{
					trigger_error('Missing --to argument value');
					exit(1);
				}

				$a = \array_shift($argv);
				$to = MediaTypeFactory::getInstance()->createFromString($a);
			}
			elseif (empty($input))
				$input = $a;
			elseif (empty($output))
				$output = $a;
		}

		if (empty($input))
			$input = 'php://stdin';
		if (empty($output))
			$output = 'php://stdout';
		if (empty($from))
		{
			try
			{
				$from = MediaTypeFactory::getInstance()->createFromMedia($input);
			}
			catch (\Exception $e)
			{
				$from = MediaTypeFactory::getInstance()->createFromString('text/plain');
			}
		}

		if (empty($to))
		{
			try
			{
				$to = MediaTypeFactory::getInstance()->createFromMedia($output);
			}
			catch (\Exception $e)
			{
				trigger_error($e->getMessage(), E_USER_WARNING);
			}
		}

		$manager = new SerializationManager();
		if (empty($to) ||
			!$manager->canSerializeToFile($output, null, $to))
			$to = MediaTypeFactory::getInstance()->createFromString('application/json');

		if ($verbose)
		{
			echo ('Input  ' . $input . ' (' . $from->serialize() . ')' .
				PHP_EOL);

			$unserializers = $manager->getFileUnserializersFor(
				$input, $from);
			$unserializers = \array_map(
				function ($c) {
					return TypeDescription::getLocalName($c);
				}, $unserializers);
			echo ("\tusing " . \implode(', ', $unserializers) . PHP_EOL);

			echo ('Output ' . $output . ' (' . $to->serialize() . ')' .
				PHP_EOL);

			$serializers = $manager->getFileSerializersFor($output,
				null, $to);
			$serializers = \array_map(
				function ($c) {
					return TypeDescription::getLocalName($c);
				}, $serializers);
			echo ("\tusing " . \implode(', ', $serializers) . PHP_EOL);
		}

		$data = $manager->unserializeFromFile($input, $from);

		$manager->serializeToFile($output, $data, $to);
	}
}

$app = new App();
$app->run($_SERVER['argv']);
echo (PHP_EOL);