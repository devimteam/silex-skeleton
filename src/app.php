<?php

namespace IsolateApp;

use App\ApplicationServiceProvider;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Silex\Application;
use Symfony\Component\Debug\ErrorHandler;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$loader = require __DIR__ . '/../runtime/vendor/autoload.php';

ErrorHandler::register();

AnnotationRegistry::registerLoader(function ($class) use ($loader) {
    $loader->loadClass($class);

    return class_exists($class, false);
});

$app = new Application();

$app->register(new ApplicationServiceProvider());

require __DIR__ . '/providers.php';

require __DIR__ . '/constants.php';

require __DIR__ . '/../config/config.php';

if (file_exists(__DIR__ . '/../config/config.' . $app['env'] . '.php')) {
    require __DIR__ . '/../config/config.' . $app['env'] . '.php';
}

require __DIR__ . '/repositories.php';
require __DIR__ . '/services.php';

if (file_exists(__DIR__ . '/services.' . $app['env'] . '.php')) {
    require __DIR__ . '/services.' . $app['env'] . '.php';
}

require __DIR__ . '/events.php';
