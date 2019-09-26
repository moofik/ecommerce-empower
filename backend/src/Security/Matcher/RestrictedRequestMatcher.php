<?php

namespace App\Security\Matcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class RestrictedRequestMatcher implements RequestMatcherInterface
{
    private const DEFAULT_IGNORE_LIST = [
        ['GET' => '/api/shops'],
        ['GET'  => '/api/register'],
        ['POST' => '/api/register'],
    ];

    /**
     * @var array
     */
    private $ignoreList;

    /**
     * RestrictedRequestMatcher constructor.
     *
     * @param array $ignoreList
     */
    public function __construct(array $ignoreList = [])
    {
        if (empty($ignoreList)) {
            $ignoreList = self::DEFAULT_IGNORE_LIST;
        }

        $this->ignoreList = $ignoreList;
    }

    /**
     * Decides whether the rule(s) implemented by the strategy matches the supplied request.
     *
     * @param Request $request
     *
     * @return bool true if the request matches, false otherwise
     */
    public function matches(Request $request)
    {
        $requestUri = $request->getUri();
        $requestMethod = $request->getMethod();

        foreach ($this->ignoreList as $ignoreEntity) {
            if (
                isset($ignoreEntity[$requestMethod])
                && strpos($requestUri, $ignoreEntity[$requestMethod]) !== false
            ) {
                return false;
            }
        }

        if (strpos($requestUri, '/api') !== false) {
            return true;
        }

        return false;
    }
}
