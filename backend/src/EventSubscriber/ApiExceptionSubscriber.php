<?php

namespace App\EventSubscriber;

use App\Service\Api\Problem\ApiProblem;
use App\Service\Api\Problem\ApiProblemException;
use App\Service\Api\Problem\ResponseFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * ApiExceptionSubscriber constructor.
     *
     * @param ResponseFactory $responseFactory
     * @param bool            $debug
     */
    public function __construct(ResponseFactory $responseFactory, bool $debug)
    {
        $this->debug = $debug;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION        => 'onKernelException',
            Events::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
        ];
    }

    /**
     * @param ExceptionEvent $exceptionEvent
     */
    public function onKernelException(ExceptionEvent $exceptionEvent): void
    {
        $exception = $exceptionEvent->getException();
        $request = $exceptionEvent->getRequest();

        if (0 !== strpos($request->getPathInfo(), '/api')) {
            return;
        }

        if ($exception instanceof ApiProblemException) {
            $apiProblem = $exception->getApiProblem();
        } else {
            $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

            if (500 === $statusCode && $this->debug) {
                return;
            }

            $apiProblem = new ApiProblem($statusCode);

            if ($exception instanceof HttpExceptionInterface) {
                $apiProblem->set('detail', $exception->getMessage());
            }
        }

        $response = $this->responseFactory->create($apiProblem);
        $exceptionEvent->setResponse($response);
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $apiProblem = new ApiProblem($event->getResponse()->getStatusCode());
        $message = $event->getException() ? $event->getException()->getMessage() : 'Missing credentials';
        $apiProblem->set('detail', $message);

        $response = $this->responseFactory->create($apiProblem);
        $event->setResponse($response);
    }
}
