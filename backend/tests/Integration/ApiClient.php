<?php


namespace App\Tests\Integration;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

final class ApiClient extends HttpKernelBrowser
{
    /**
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $files
     * @param array $server
     * @param string|null $content
     * @param bool $changeHistory
     * @return Response
     */
    public function request(string $method, string $uri, array $parameters = [], array $files = [], array $server = [], string $content = null, bool $changeHistory = true)
    {
        parent::request($method, $uri, $parameters, $files, $server, $content, $changeHistory);

        return $this->getResponse();
    }
}