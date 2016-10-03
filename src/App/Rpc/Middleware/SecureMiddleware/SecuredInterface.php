<?php

namespace App\Rpc\Middleware\SecureMiddleware;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Interface SecuredInterface
 */
interface SecuredInterface
{
    /**
     * @param TokenInterface|null $token
     */
    public function setToken(?TokenInterface $token);
}
