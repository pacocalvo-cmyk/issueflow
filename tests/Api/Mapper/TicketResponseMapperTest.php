<?php

declare(strict_types=1);

namespace App\Tests\Api\Mapper;

use App\Api\Mapper\TicketResponseMapper;
use App\Api\Model\TicketResponse;
use App\Entity\Ticket;
use App\Model\TicketPriority;
use App\Model\TicketStatus;
use PHPUnit\Framework\TestCase;

final class TicketResponseMapperTest extends TestCase
{
    public function test_map_returns_public_ticket_fields_and_scalar_enum_values(): void
    {
        $ticket = new Ticket('INC-1001', 'Authentication issue', TicketPriority::High, TicketStatus::Open);

        $response = (new TicketResponseMapper())->map($ticket);

        self::assertInstanceOf(TicketResponse::class, $response);
        self::assertSame('INC-1001', $response->code);
        self::assertSame('Authentication issue', $response->title);
        self::assertSame('high', $response->priority);
        self::assertSame('open', $response->status);
    }

    public function test_map_collection_maps_all_tickets(): void
    {
        $mapper = new TicketResponseMapper();
        $tickets = [
            new Ticket('INC-1001', 'First', TicketPriority::Urgent, TicketStatus::Open),
            new Ticket('INC-1002', 'Second', TicketPriority::Low, TicketStatus::Closed),
        ];

        $responses = $mapper->mapCollection($tickets);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(TicketResponse::class, $responses);
        self::assertSame(['urgent', 'low'], array_map(static fn (TicketResponse $response): string => $response->priority, $responses));
        self::assertSame(['open', 'closed'], array_map(static fn (TicketResponse $response): string => $response->status, $responses));
    }

    public function test_map_collection_returns_an_empty_array_for_no_tickets(): void
    {
        self::assertSame([], (new TicketResponseMapper())->mapCollection([]));
    }
}
