<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Ticket;
use App\Model\TicketPriority;
final class TicketSlaCalculator
{

public function getResponseTargetHours(Ticket $ticket):int

{
return match ($ticket->getPriority()){
    TicketPriority::Urgent => 4,
    TicketPriority::High => 8,
    TicketPriority::Low => 72,
    TicketPriority::Normal => 24,
};
}
}