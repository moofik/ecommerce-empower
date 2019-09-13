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
            $problem = new ApiProblem(500, ApiProblem::TYPE_SERVER_SERIALIZATION_ERROR);
            $problem->set('details', 'Error occurred while creating API response. Given serializer is not instance of SerializerInterface.');

            throw new ApiProblemException($problem);
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
