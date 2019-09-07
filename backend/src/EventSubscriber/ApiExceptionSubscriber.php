<?php


namespace App\EventSubscriber;


use App\Service\Api\Problem\ApiProblem;
use App\Service\Api\Problem\ApiProblemException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    /**
     * @param ExceptionEvent $exceptionEvent
     */
    public function onKernelException(ExceptionEvent $exceptionEvent): void
    {
        $exception = $exceptionEvent->getException();

        if ($exception instanceof ApiProblemException) {
            $apiProblem = $exception->getApiProblem();
        } else {
            $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            $apiProblem = new ApiProblem($statusCode);

            if ($exception instanceof HttpExceptionInterface) {
                $apiProblem->set('detail', $exception->getMessage());
            }
        }

        $response = new JsonResponse($apiProblem->toArray(), $apiProblem->getStatusCode());
        $response->headers->set('Content-Type', 'application/problem+json');
        $exceptionEvent->setResponse($response);
    }
}