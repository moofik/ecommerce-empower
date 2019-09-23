<?php

namespace App\Service\Api;

use App\Serializer\Groups\GroupsResolver;
use App\Service\Api\Problem\ApiProblem;
use App\Service\Api\Problem\ApiProblemException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    private $serializer;

    /**
     * @param $data
     * @param int   $statusCode
     * @param array $headers
     *
     * @return Response
     */
    public function createApiResponse($data, int $statusCode, array $headers = []): Response
    {
        $headers = array_merge($headers, ['Content-Type' => 'application/json']);

        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $this->getGroupsResolver()->resolveGroups($context);

        return new Response(
            $this->getSerializer()->serialize($data, 'json', $context),
            $statusCode,
            $headers
        );
    }

    /**
     * @param array $errors
     *
     * @throws ApiProblemException
     */
    public function throwValidationErrorException(array $errors): void
    {
        $problem = new ApiProblem(400, ApiProblem::TYPE_VALIDATION_ERROR);
        $problem->set('errors', $errors);

        throw new ApiProblemException($problem);
    }

    /**
     * @return SerializerInterface
     */
    abstract public function getSerializer(): SerializerInterface;

    /**
     * @return GroupsResolver
     */
    abstract public function getGroupsResolver(): GroupsResolver;
}
