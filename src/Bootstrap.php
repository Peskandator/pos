<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;
use Nette\Neon\Neon;
use Nette\Utils\FileSystem;

class Bootstrap
{
	public static function boot(): Configurator
	{
		$configurator = new Configurator;
		$appDir = dirname(__DIR__);

        $root = dirname(__DIR__);
        $configurator->addParameters($parameters = [
            'rootDir' => $root,
            'publicDir' => $root . '/www',
            'srcDir' => $root . '/src',
            'varDir' => $root . '/var',
            'logDir' => $root . '/var/log',
            'tempDir' => $root . '/var/temp',
        ]);

		//$configurator->setDebugMode('secret@23.75.345.200'); // enable for your remote IP

        $confNeon = Neon::decode(FileSystem::read($appDir . '/config/local.neon'));
        $debugMode = $confNeon['parameters']['debugMode'] ?? false;
        $configurator->setDebugMode($debugMode);

        $configurator->enableTracy($parameters['logDir']);
        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory($parameters['tempDir']);

		$configurator->createRobotLoader()
            ->addDirectory($parameters['srcDir'])
            ->addDirectory($parameters['tempDir'] . '/proxies')
            ->register()
        ;

		$configurator->addConfig($appDir . '/config/common.neon');
		$configurator->addConfig($appDir . '/config/services.neon');
		$configurator->addConfig($appDir . '/config/local.neon');

		return $configurator;
	}
}
