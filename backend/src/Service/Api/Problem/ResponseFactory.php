<?php


namespace App\Service\Api\Problem;


use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseFactory
{
    /**
     * @var string
     */
    private $errorsDocsUri;

    /**
     * ResponseFactory constructor.
     * @param string $errorsDocsUri
     */
    public function __construct(string $errorsDocsUri)
    {
        $this->errorsDocsUri = $errorsDocsUri;
    }

    /**
     * @param ApiProblem $apiProblem
     * @return JsonResponse
     */
    public function create(ApiProblem $apiProblem): JsonResponse
    {
        $data = $apiProblem->toArray();

        if ('about:blank' !== $data['type']) {
            $data['type'] = $this->errorsDocsUri.$data['type'];
        }

        $response = new JsonResponse($data, $apiProblem->getStatusCode());
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }
}