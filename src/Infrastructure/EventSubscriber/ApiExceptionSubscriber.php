<?php

namespace App\Infrastructure\EventSubscriber;

use App\Application\Exception\Orders\ProductNotFoundException;
use Sce\CasasJewelsVendor\DTO\ErrorResponseDTO;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer
    )
    {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        $statusCode = match (true) {
            $exception instanceof ProductNotFoundException => 404,
            $exception instanceof \InvalidArgumentException => 400,
            $exception instanceof \DomainException => 422,
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            default => 500,
        };

        $message = $statusCode === 500
            ? 'Internal server Error.'
            : $exception->getMessage();

        $response = new ErrorResponseDTO($statusCode, $message, $exception->getTrace());
        $jsonResponse = $this->serializer->serialize($response, 'json');
        $response = new JsonResponse($jsonResponse, $statusCode, [], true);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
