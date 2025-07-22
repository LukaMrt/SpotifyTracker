<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
class InvalidRequestExceptionListener
{
    public function __construct(
        private readonly string $environment
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->environment === 'dev') {
            return;
        }

        $exception = $event->getThrowable();

        $code = $exception->getCode();

        if ($code === 0) {
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        if (is_string($code)) {
            $code = (int) $code;
        }

        $response = new JsonResponse(
            [
                'error' => $exception->getMessage(),
                'code' => $code,
            ],
            $code,
        );
        $event->setResponse($response);
    }
}
