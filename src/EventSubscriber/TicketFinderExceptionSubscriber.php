<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\InvalidTicketCodeException;
use App\Exception\TicketNotFoundException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final class TicketFinderExceptionSubscriber
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        [$status, $code, $message] = match (true) {
            $exception instanceof InvalidTicketCodeException => [Response::HTTP_BAD_REQUEST, 'invalid_ticket_code', 'El código debe tener el formato INC-0000'],
            $exception instanceof TicketNotFoundException => [Response::HTTP_NOT_FOUND, 'ticket_not_found', 'La incidencia solicitada no existe'],
            default => [null, null, null],
        };

        if ($status === null) {
            return;
        }

        $event->setResponse(new JsonResponse(['error' => ['code' => $code, 'message' => $message]], $status));
    }
}
