<?php

namespace App\Rpc\Middleware\AssertMiddleware\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Param
{
    /**
     * @Required
     *
     * @var string
     */
    public $property;

    /**
     * @Required
     *
     * @var array
     */
    public $constraints;
}
