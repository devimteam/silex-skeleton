<?php

namespace App;

/* @var Application $app */

use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Devim\Provider\DoctrineExtendServiceProvider\DoctrineExtendServiceProvider;
use Devim\Provider\SecurityJwtServiceProvider\SecurityJwtServiceProvider;
use Devim\Provider\CorsServiceProvider\CorsServiceProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Isolate\ConsoleServiceProvider\ConsoleServiceProvider;
use App\Provider\AliceServiceProvider\AliceServiceProvider;

$app
    ->register(new MonologServiceProvider())
    ->register(new DoctrineServiceProvider())
    ->register(new DoctrineOrmServiceProvider())
    ->register(new DoctrineExtendServiceProvider())
    ->register(new SecurityJwtServiceProvider())
    ->register(new AliceServiceProvider())
    ->register(new ServiceControllerServiceProvider())
    ->register(new ValidatorServiceProvider())
    ->register(new ConsoleServiceProvider())
    ->register(new SecurityServiceProvider())
    ->register(new CorsServiceProvider());
