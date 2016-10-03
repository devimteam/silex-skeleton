<?php

namespace App;

/* @var Application $app */

use App\Command\ClearAllCachesCommand;
use App\Command\GenerateEntityCommand;
use App\Command\ImportMappingDoctrineCommand;
use App\Command\RebuildDatabaseCommand;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Isolate\ConsoleServiceProvider\Console\ConsoleEvent;
use Isolate\ConsoleServiceProvider\Console\ConsoleEvents;
use Silex\Application;

$app->on(ConsoleEvents::INIT, function (ConsoleEvent $event) use ($app) {
    $console = $event->getApp();
    $console->addCommands([
        new GenerateEntityCommand(),
        new RebuildDatabaseCommand(),
        new ClearAllCachesCommand(),
        new ImportMappingDoctrineCommand(),
    ]);

    if (isset($app['orm.em'])) {
        $console->setHelperSet(ConsoleRunner::createHelperSet($app['orm.em']));
        ConsoleRunner::addCommands($console);
    }
});
