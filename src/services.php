<?php

namespace App;
/* @var Application $app */

use Silex\Application;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;

$app['validator.mapping.class_metadata_factory'] = function () use ($app) {
    return new LazyLoadingMetadataFactory(
        new AnnotationLoader(new AnnotationReader())
    );
};

$app['annotation_reader'] = function () {
    return new AnnotationReader();
};
