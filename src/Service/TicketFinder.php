<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\TicketCatalogInterface;
use App\Entity\Ticket;
use App\Exception\InvalidTicketCodeException;
use App\Exception\TicketNotFoundException;

final class TicketFinder
{
    public function __construct(private readonly TicketCatalogInterface $ticketCatalog)
    {
    }

    public function findOrFail(string $code): Ticket
    {
        if (preg_match('/^INC-\d{4}$/', $code) !== 1) {
            throw new InvalidTicketCodeException();
        }

        return $this->ticketCatalog->findByCode($code) ?? throw new TicketNotFoundException();
    }
}
