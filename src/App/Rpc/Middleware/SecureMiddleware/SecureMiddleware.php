<?php

namespace App\Rpc\Middleware\SecureMiddleware;

use App\Exception\RpcException\RpcUnauthorizedException;
use App\Rpc\Middleware\SecureMiddleware\Annotation\Secured;
use Devim\Component\RpcServer\MiddlewareInterface;
use Doctrine\Common\Annotations\Reader as AnnotationReaderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class SecureMiddleware
 */
class SecureMiddleware implements MiddlewareInterface
{
    /**
     * @var AnnotationReaderInterface
     */
    private $annotationReader;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * SecureMiddleware constructor
     *
     * @param AnnotationReaderInterface $annotationReader
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        AnnotationReaderInterface $annotationReader,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->annotationReader = $annotationReader;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param mixed $service
     * @param string $methodName
     * @param array $params
     *
     * @throws \App\Exception\RpcException\RpcUnauthorizedException
     * @throws \RuntimeException
     */
    public function execute($service, string $methodName, array $params)
    {
        if ($service instanceof SecuredInterface) {
            $service->setToken($this->tokenStorage->getToken());
        }

        /** @var Secured $securedAnnotation */
        $securedAnnotation = $this->annotationReader->getMethodAnnotation(
            new \ReflectionMethod($service, $methodName),
            new Secured()
        );

        if (null !== $securedAnnotation) {
            $token = $this->tokenStorage->getToken();

            if (null === $token || !$token->isAuthenticated()) {
                throw new RpcUnauthorizedException();
            }

            if (!$this->authorizationChecker->isGranted($securedAnnotation->roles, $token->getUser())) {
                throw new RpcUnauthorizedException();
            }
        }
    }
}
