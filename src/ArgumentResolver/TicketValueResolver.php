<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Entity\Ticket;
use App\Service\TicketFinder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;

final class TicketValueResolver implements ValueResolverInterface
{
    public function __construct(private readonly TicketFinder $ticketFinder)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== Ticket::class) {
            return [];
        }

        yield $this->ticketFinder->findOrFail((string) $request->attributes->get('code'));
    }
}
