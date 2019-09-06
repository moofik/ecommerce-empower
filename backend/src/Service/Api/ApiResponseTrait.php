<?php


namespace App\Service\Api;


use App\Service\Api\ErrorHandling\ApiProblemException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param $data
     * @param int $statusCode
     * @param array $headers
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
}