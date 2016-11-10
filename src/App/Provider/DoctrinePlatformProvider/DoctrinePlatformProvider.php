<?php

namespace App\Provider\DoctrinePlatformProvider;

use App\Provider\DoctrinePlatformProvider\Platforms\PostgreSQL94Platform;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class DoctrinePlatformProvider
 */
class DoctrinePlatformProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     */
    public function register(Container $container)
    {
        $container['pg94_platform'] = function () use ($container) {
            return new PostgreSQL94Platform();
        };
    }

}
