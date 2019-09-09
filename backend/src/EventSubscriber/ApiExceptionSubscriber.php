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
     * @var bool
     */
    private $debug;

    /**
     * @var string
     */
    private $errorsDocsUri;

    /**
     * ApiExceptionSubscriber constructor.
     * @param bool $debug
     * @param string $errorsDocsUri
     */
    public function __construct(bool $debug, string $errorsDocsUri)
    {
        $this->debug = $debug;
        $this->errorsDocsUri = $errorsDocsUri;
    }

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

        $data = $apiProblem->toArray();

        if ('about:blank' !== $data['type']) {
            $data['type'] = $this->errorsDocsUri . $data['type'];
        }

        $response = new JsonResponse($data, $apiProblem->getStatusCode());
        $response->headers->set('Content-Type', 'application/problem+json');
        $exceptionEvent->setResponse($response);
    }
}