<?php

namespace App\Rpc\Middleware\AssertMiddleware;

use App\Rpc\Middleware\AssertMiddleware\Annotation\Param;
use Devim\Component\RpcServer\Exception\RpcInvalidParamsException;
use Devim\Component\RpcServer\MiddlewareInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader as AnnotationReaderInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

AnnotationRegistry::registerAutoloadNamespace('App\Rpc\Middleware\AssertMiddleware', __DIR__ . '/Annotation');

class AssertMiddleware implements MiddlewareInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var AnnotationReaderInterface
     */
    private $annotationReader;

    /**
     * AssertMiddleware constructor
     *
     * @param AnnotationReaderInterface $annotationReader
     * @param ValidatorInterface $validator
     */
    public function __construct(AnnotationReaderInterface $annotationReader, ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->annotationReader = $annotationReader;
    }

    /**
     * @param mixed $service
     * @param string $methodName
     * @param array $params
     *
     * @throws RpcInvalidParamsException
     */
    public function execute($service, string $methodName, array $params)
    {
        $results = [];

        $method = new \ReflectionMethod($service, $methodName);

        $methodParameters = $method->getParameters();

        $annotations = $this->annotationReader->getMethodAnnotations($method);

        $propertiesConstraints = [];

        /** @var Param $annotation */
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Param) {
                $propertiesConstraints[$annotation->property] = $annotation->constraints;
            }
        }

        foreach ($methodParameters as $index => $methodParameter) {
            if (array_key_exists($methodParameter->getName(), $propertiesConstraints)) {
                $errors = $this->validator->validate(
                    $params[$index],
                    $propertiesConstraints[$methodParameter->getName()]
                );

                if ($errors->count() > 0) {
                    /** @var ConstraintViolation $constraint */
                    foreach ($errors as $constraint) {
                        $className = get_class($constraint->getConstraint());
                        $parts = explode('\\', $className);

                        $constraintId = strtolower($parts[count($parts) - 1]);

                        $results[$methodParameter->getName()][] = $constraintId;
                    }
                }
            }
        }

        if (count($results) > 0) {
            throw new RpcInvalidParamsException($results);
        }
    }
}
