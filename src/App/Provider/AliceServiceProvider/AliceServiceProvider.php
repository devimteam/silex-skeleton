<?php

namespace App\Provider\AliceServiceProvider;

use App\Provider\AliceServiceProvider\EventSubscriber\ConsoleEventSubscriber;
use Nelmio\Alice\Fixtures;
use Nelmio\Alice\Persister\Doctrine;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AliceServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function register(Container $container)
    {
        if (!isset($container['orm.em'])) {
            throw new \RuntimeException('Doctrine entity manager not define');
        }

        $container['alice.config_path'] = '';
        $container['alice.locale'] = 'en_US';
        $container['alice.seed'] = 1;
        $container['alice.logger'] = null;
        $container['alice.persist_once'] = false;
        $container['alice.locale'] = 'en_US';

        $container['alice.processors'] = $container->protect(function () {
            return [];
        });

        $container['alice.providers'] = $container->protect(function () {
            return [];
        });

        $container['alice.persister'] = function () use ($container) {
            return new Doctrine($container['orm.em']);
        };

        $container['alice.fixtures'] = function () use ($container) {
            $providers = is_array($container['alice.providers']) ? $container['alice.providers'] : [];

            $fixtures = new Fixtures($container['alice.persister'], [
                'locale' => $container['alice.locale'],
                'providers' => $providers,
                'seed' => $container['alice.seed'],
                'logger' => $container['alice.logger'],
                'persist_once' => $container['alice.persist_once'],
            ]);

            foreach ($container['alice.processors'] as $processor) {
                $fixtures->addProcessor($processor);
            }

            return $fixtures;
        };
    }

    /**
     * @param Container $app
     * @param EventDispatcherInterface $dispatcher
     */
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber(new ConsoleEventSubscriber());
    }
}
