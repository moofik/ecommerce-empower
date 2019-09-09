<?php

namespace App\Service\Api;

use App\Service\Api\Problem\ApiProblem;
use App\Service\Api\Problem\ApiProblemException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        if (!$this->serializer instanceof SerializerInterface) {
            throw new ApiProblemException(500);
        }

        $headers = array_merge($headers, ['Content-Type' => 'application/json']);

        $context = new SerializationContext();
        $context->setSerializeNull(true);

        return new Response(
            $this->serializer->serialize($data, 'json', $context),
            $statusCode,
            $headers
        );
    }

    /**
     * @param array $errors
     *
     * @return Response
     */
    public function createValidationErrorResponse(array $errors): Response
    {
        $problem = new ApiProblem(400, ApiProblem::TYPE_VALIDATION_ERROR);
        $problem->set('errors', $errors);

        $response = new JsonResponse($problem->toArray(), $problem->getStatusCode());
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }
}
