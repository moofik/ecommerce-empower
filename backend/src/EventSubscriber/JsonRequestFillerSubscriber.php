<?php

namespace App\EventSubscriber;

use App\Service\Api\Problem\ApiProblem;
use App\Service\Api\Problem\ApiProblemException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonRequestFillerSubscriber implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * RequestFillerSubscriber constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['fillRequestFromJsonContent', 1000],
            ],
        ];
    }

    public function fillRequestFromJsonContent()
    {
        $request = $this->requestStack->getCurrentRequest();

        if ('json' !== $request->getContentType() || !$request->getContent()) {
            return;
        }

        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $apiProblem = new ApiProblem(400, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT);
            throw new ApiProblemException($apiProblem);
        }

        $request->request->replace(is_array($data) ? $data : []);
    }
}