<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Api\Model\TicketIndexQuery;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final class ApiTicketIndexQueryExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $validationFailure = $exception->getPrevious();

        if (!$exception instanceof HttpExceptionInterface
            || !$validationFailure instanceof ValidationFailedException
            || !$validationFailure->getValue() instanceof TicketIndexQuery
        ) {
            return;
        }

        foreach ($validationFailure->getViolations() as $violation) {
            $error = match ($violation->getPropertyPath()) {
                'priority' => [
                    'code' => 'invalid_priority',
                    'message' => 'La prioridad indicada no es válida.',
                ],
                'status' => [
                    'code' => 'invalid_status',
                    'message' => 'El estado indicado no es válido.',
                ],
                default => null,
            };

            if ($error !== null) {
                $event->setResponse(new JsonResponse(['error' => $error], Response::HTTP_BAD_REQUEST));

                return;
            }
        }
    }
}
