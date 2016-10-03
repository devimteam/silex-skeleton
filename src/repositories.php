<?php
namespace App;

/* @var Application $app */

use Doctrine\ORM\EntityManager;
use Silex\Application;

$app['repository'] = function () use ($app) {
    return function (...$parameters) use ($app) {
        $entityClassName = $parameters[0];

        unset($parameters[0]);

        /** @var EntityManager $em */
        $em = $app['orm.em'];

        $classMetadata = $em->getClassMetadata($entityClassName);

        if (($repositoryClassName = $classMetadata->customRepositoryClassName) === '') {
            throw new \RuntimeException(sprintf('Repository class name does not define in entity "%s"',
                $entityClassName));
        }

        $parameters = array_merge([$em, $classMetadata], $parameters);

        return (new \ReflectionClass($repositoryClassName))->newInstanceArgs($parameters);
    };
};
