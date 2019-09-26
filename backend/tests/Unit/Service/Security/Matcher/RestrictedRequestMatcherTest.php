<?php

namespace App\Tests\Unit\Service\Security\Matcher;

use App\Security\Matcher\RestrictedRequestMatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RestrictedRequestMatcherTest extends TestCase
{
    public function testMatchesWithDefaultIgnoreList()
    {
        $requestMatcher = new RestrictedRequestMatcher();

        $shopsGetRequest = new Request();
        $shopsGetRequest->setMethod('GET');
        $shopsGetRequest->server->set('REQUEST_URI', '/api/shops');

        $registerGetRequest = new Request();
        $registerGetRequest->setMethod('GET');
        $registerGetRequest->server->set('REQUEST_URI', '/api/register');

        $registerPostRequest = new Request();
        $registerPostRequest->setMethod('POST');
        $registerPostRequest->server->set('REQUEST_URI', '/api/register');

        $otherApiRequest = new Request();
        $otherApiRequest->setMethod('GET');
        $otherApiRequest->server->set('REQUEST_URI', '/api/random');

        $this->assertFalse($requestMatcher->matches($shopsGetRequest));
        $this->assertFalse($requestMatcher->matches($registerGetRequest));
        $this->assertFalse($requestMatcher->matches($registerPostRequest));
        $this->assertTrue($requestMatcher->matches($otherApiRequest));
    }

    public function testMatchesWithCustomIgnoreList()
    {
        $requestMatcher = new RestrictedRequestMatcher([['POST' => '/api/test']]);

        $testApiRequest = new Request();
        $testApiRequest->setMethod('POST');
        $testApiRequest->server->set('REQUEST_URI', '/api/test');

        $otherApiRequest = new Request();
        $otherApiRequest->setMethod('GET');
        $otherApiRequest->server->set('REQUEST_URI', '/api/random');

        $this->assertFalse($requestMatcher->matches($testApiRequest));
        $this->assertTrue($requestMatcher->matches($otherApiRequest));
    }
}
