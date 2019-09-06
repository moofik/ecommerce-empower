<?php

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestDTOResolver implements ArgumentValueResolverInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * RequestDTOResolver constructor.
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return bool
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        try {
            $reflection = new \ReflectionClass($argument->getType());
        } catch (\ReflectionException $reflectionException) {
            return false;
        }

        if ($reflection->implementsInterface(RequestDTOInterface::class)) {
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return \Generator
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        // creating new instance of custom request DTO
        $class = $argument->getType();
        $dto = new $class($request);

        // throw bad request exception in case of invalid request data
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            $message = '';
            /** @var ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $message .= $error->getMessage();
            }

            throw new BadRequestHttpException($message);
        }

        yield $dto;
    }
}
