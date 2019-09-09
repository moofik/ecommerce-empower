<?php

namespace App\Tests\Integration;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

final class ApiClient extends HttpKernelBrowser
{
    /**
     * @param string      $method
     * @param string      $uri
     * @param array       $parameters
     * @param array       $files
     * @param array       $server
     * @param string|null $content
     * @param bool        $changeHistory
     *
     * @return Response
     */
    public function request(string $method, string $uri, array $parameters = [], array $files = [], array $server = [], string $content = null, bool $changeHistory = true)
    {
        parent::request($method, $uri, $parameters, $files, $server, $content, $changeHistory);

        return $this->getResponse();
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $content
     * @param array  $server
     *
     * @return Response
     */
    public function jsonRequest(string $method, string $uri, array $content, array $server = []): Response
    {
        parent::request($method, $uri, [], [], $server, json_encode($content));

        return $this->getResponse();
    }

    /**
     * @param string $contentType
     */
    public function setRequestContentType(string $contentType): void
    {
        $this->setServerParameter('CONTENT_TYPE', $contentType);
    }
}
