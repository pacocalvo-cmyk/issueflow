<?php

declare(strict_types=1);

namespace App\Contract;

use App\Model\Ticket;
/*esto es uun contrato que define que necesia mi aplicación de cualquier catalogo de tickets
la interfaz describe las operaciones deben existir, pero no como se llevana acabo*/

interface TicketCatalogInterface
{
    public function getTickets(): array;

public function find(string $id): ?Ticket;
}