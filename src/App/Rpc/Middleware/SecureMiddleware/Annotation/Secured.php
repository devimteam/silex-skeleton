<?php

namespace App\Rpc\Middleware\SecureMiddleware\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Secured
{
    /**
     * @var array
     */
    public $roles;
}
