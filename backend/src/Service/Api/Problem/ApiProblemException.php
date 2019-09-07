<?php


namespace App\Service\Api\Problem;


use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ApiProblemException extends HttpException
{
    /**
     * @var ApiProblem
     */
    private $apiProblem;

    /**
     * ApiProblemException constructor.
     * @param ApiProblem $apiProblem
     * @param Throwable|null $t
     * @param array $headers
     * @param int $code
     */
    public function __construct(ApiProblem $apiProblem, Throwable $t = null, array $headers = [], ?int $code = null)
    {
        parent::__construct($apiProblem->getStatusCode(), $apiProblem->getTitle(), $t, $headers, $code);

        $this->apiProblem = $apiProblem;
    }

    /**
     * @return ApiProblem
     */
    public function getApiProblem(): ApiProblem
    {
        return $this->apiProblem;
    }
}