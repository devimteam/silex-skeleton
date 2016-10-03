<?php

namespace App;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application as BaseApplication;

/**
 * Class ApplicationServiceProvider.
 */
class ApplicationServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     */
    public function register(Container $container)
    {
        $container['name'] = 'Application Name';
        $container['version'] = '1.0.0';
        $container['locale'] = 'ru';
    }
}
