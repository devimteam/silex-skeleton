<?php
namespace App;

/** @var Application $app */

/** @var \Closure $getEnvByApp */

use App\DataFixture\Provider\DateTimeProvider;
use Devim\Provider\DoctrineExtendServiceProvider\Type\JsonbArrayType;
use Doctrine\DBAL\PostgresTypes\InetType;
use Doctrine\DBAL\PostgresTypes\IntArrayType;
use Doctrine\DBAL\PostgresTypes\TextArrayType;
use Doctrine\DBAL\PostgresTypes\XmlType;
use Faker\Generator;
use Oro\ORM\Query\AST\Functions\Cast;
use Oro\ORM\Query\AST\Functions\DateTime\ConvertTz;
use Oro\ORM\Query\AST\Functions\Numeric\Pow;
use Oro\ORM\Query\AST\Functions\Numeric\Sign;
use Oro\ORM\Query\AST\Functions\Numeric\TimestampDiff;
use Oro\ORM\Query\AST\Functions\SimpleFunction;
use Oro\ORM\Query\AST\Functions\String\ConcatWs;
use Oro\ORM\Query\AST\Functions\String\GroupConcat;
use Silex\Application;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\User\UserInterface;

require __DIR__ . '/paths.php';

//<editor-fold desc="Basic Config" defaultstate="collapsed">
$app['name'] = 'Application Name';
$app['version'] = '1.0.0';
$app['locale'] = 'ru';
$app['monolog.name'] = 'app';
$app['monolog.logfile'] = $app['paths.logs'] . '/app.log';
$app['jwt.life_time'] = 86400;
//</editor-fold>

//<editor-fold desc="Console Config" defaultstate="collapsed">
$app['console.base_path'] = __DIR__;
$app['console.name'] = 'Console App';
$app['console.version'] = '1.0.0 ';
//</editor-fold>

//<editor-fold desc="Database Config" defaultstate="collapsed">
$app['db.options'] = [
    'driver' => 'pdo_pgsql',
    'dbname' => $getEnvByApp('DB_NAME'),
    'host' => $getEnvByApp('DB_HOST'),
    'user' => $getEnvByApp('DB_USER'),
    'password' => $getEnvByApp('DB_PASSWORD'),
];

$app['orm.default_cache'] = [
    'driver' => 'redis',
    'host' => $getEnvByApp('REDIS_HOST'),
    'port' => $getEnvByApp('REDIS_PORT'),
    'auth' => $getEnvByApp('REDIS_AUTH'),
];

$app['orm.custom.functions.string'] = [
    'cast' => Cast::class,
    'group_concat' => GroupConcat::class,
    'concat_ws' => ConcatWs::class,
];

$app['orm.custom.functions.datetime'] = [
    'date' => SimpleFunction::class,
    'time' => SimpleFunction::class,
    'timestamp' => SimpleFunction::class,
    'convert_tz' => ConvertTz::class,
];

$app['orm.custom.functions.numeric'] = [
    'timestampdiff' => TimestampDiff::class,
    'dayofyear' => SimpleFunction::class,
    'dayofweek' => SimpleFunction::class,
    'week' => SimpleFunction::class,
    'day' => SimpleFunction::class,
    'hour' => SimpleFunction::class,
    'minute' => SimpleFunction::class,
    'month' => SimpleFunction::class,
    'quarter' => SimpleFunction::class,
    'second' => SimpleFunction::class,
    'year' => SimpleFunction::class,
    'sign' => Sign::class,
    'pow' => Pow::class,
];

$app['orm.proxies_dir'] = $app['paths.cache'] . '/doctrine/proxies';
$app['orm.auto_generate_proxies'] = false;
$app['orm.em.options'] = [
    'mappings' => [
        'AppEntity' => [
            'type' => 'annotation',
            'path' => [
                realpath(__DIR__ . '/../src/App/Entity'),
            ],
            'namespace' => 'App\Entity',
            'alias' => 'Repository',
            'use_simple_annotation_reader' => false,
        ],
    ],
    'types' => [
        'text_array' => TextArrayType::class,
        'int_array' => IntArrayType::class,
        'xml' => XmlType::class,
        'inet' => InetType::class,
        'jsonb' => JsonbArrayType::class
    ],
];
$app['orm.extend.subscribers'] = [];
$app['orm.extend.filters'] = [];

//</editor-fold>

//<editor-fold desc="Security Config" defaultstate="collapsed">
$app['security.encoder_factory'] = function (Application $app) {
    return new EncoderFactory(
        [
            UserInterface::class => $app['security.encoder.digest'],
        ]
    );
};

$app['security.access_rules'] = [];
$app['roles.need_confirm'] = [];
$app['security.jwt.secret_key'] = 'secret';
$app['security.jwt.algorithm'] = ['HS256'];
$app['security.jwt.options'] = [
    'header_name' => 'Authorization',
    'token_prefix' => 'Bearer',
];

$app['security.firewalls'] = [];

if (!$app['cli']) {
    // TODO implement security routes
    $app['security.firewalls'] = [];
}

//</editor-fold>

//<editor-fold desc="Migration Config" defaultstate="collapsed">
$app['migrations.directory'] = $app['paths.root'] . '/src/App/Migrations';
$app['migrations.name'] = 'App Migrations';
$app['migrations.namespace'] = 'App\Migrations';
$app['migrations.table_name'] = 'app_migrations';
//</editor-fold>

//<editor-fold desc="Alice Config" defaultstate="collapsed">
$app['alice.config_path'] = __DIR__ . '/../tests/_data/db/fixtures/fixturesLoader.yml';
$app['alice.locale'] = 'ru_RU';

$app->extend('alice.providers', function ($providers) {

    $providers = array_merge(
        $providers, [
            new DateTimeProvider(new Generator()),
        ]
    );

    return $providers;
});
//</editor-fold
