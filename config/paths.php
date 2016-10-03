<?php

use Silex\Application;

/** @var Application $app */

$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : dirname(__DIR__);
$protocol = $_SERVER['HTTP_X_REAL_SCHEME'] ?? (isset($_SERVER['HTTPS']) ? 'https' : 'http');
$hostName = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? '');
$hostName = sprintf('%s://%s', $protocol, $hostName);
$ip = $_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');

$app['paths.root'] = str_replace(DIRECTORY_SEPARATOR, '/', $documentRoot);
$app['paths.runtime'] = $app['paths.root'] . '/runtime';
$app['paths.cache'] = $app['paths.runtime'] . '/cache';
$app['paths.logs'] = $app['paths.runtime'] . '/logs';
$app['paths.config'] = $app['paths.root'] . '/config';
$app['host'] = $hostName;
$app['ip'] = $ip;

if (false === ($environment = getenv('APP_ENV'))) {
    throw new \RuntimeException('Environment APP_ENV does not set, please use export APP_ENV');
}

$app['env'] = $environment;
$app['cli'] = php_sapi_name() === 'cli';

$getEnvByApp = function ($env) use ($app) {
    return getenv(strtoupper($app['env']) . '_' . $env);
};
