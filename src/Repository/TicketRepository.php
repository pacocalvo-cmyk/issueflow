<?php

declare(strict_types=1);


namespace App\Repository;

use App\Contract\RepositoryInterface;
use App\Contract\TicketCatalogInterface;
use App\Entity\Ticket;

final class TicketRepository implements TicketCatalogInterface
{
    public function getTickets(): array
    {

        return [];
    }


    public function findByCode(string $code): ?Ticket
    {

        return null;
    }


}