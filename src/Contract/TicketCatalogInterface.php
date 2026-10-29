<?php

declare(strict_types=1);

namespace App\Contract;

use App\Entity\Ticket;
/*esto es uun contrato que define que necesia mi aplicación de cualquier catalogo de tickets
la interfaz describe las operaciones deben existir, pero no como se llevan a acabo*/

interface TicketCatalogInterface
{ /*necesito el metodo get() que me da los tikectes y un find() que busque uno determinado*/

    public function getTickets(): array;

    public function findByCode(string $code): ?Ticket;
}