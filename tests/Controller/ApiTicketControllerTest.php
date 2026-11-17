<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Contract\TicketCatalogInterface;
use App\Entity\Ticket;
use App\Model\TicketPriority;
use App\Model\TicketStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApiTicketControllerTest extends WebTestCase
{
    public function test_show_resolves_existing_ticket_and_error_responses(): void
    {
        $ticket = new Ticket('INC-1001', 'Issue', TicketPriority::Normal, TicketStatus::Open);
        $catalog = $this->createMock(TicketCatalogInterface::class);
        $catalog->method('findByCode')->willReturnCallback(static fn (string $code): ?Ticket => $code === 'INC-1001' ? $ticket : null);
        $client = static::createClient();
        static::getContainer()->set(TicketCatalogInterface::class, $catalog);

        $client->request('GET', '/api/tickets/INC-1001');
        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('{"ticket":{"code":"INC-1001","title":"Issue","priority":"normal","status":"open"}}', (string) $client->getResponse()->getContent());

        $this->assertErrorRequest($client, 'GET', '/api/tickets/bad', 400, 'invalid_ticket_code', 'El código debe tener el formato INC-0000');
        $this->assertErrorRequest($client, 'GET', '/api/tickets/INC-9999', 404, 'ticket_not_found', 'La incidencia solicitada no existe');
    }

    public function test_patch_resolves_ticket_and_preserves_payload_validation(): void
    {
        $ticket = new Ticket('INC-1001', 'Issue', TicketPriority::Normal, TicketStatus::Open);
        $catalog = $this->createMock(TicketCatalogInterface::class);
        $catalog->method('findByCode')->willReturnCallback(static fn (string $code): ?Ticket => $code === 'INC-1001' ? $ticket : null);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $client = static::createClient();
        static::getContainer()->set(TicketCatalogInterface::class, $catalog);
        static::getContainer()->set(EntityManagerInterface::class, $entityManager);

        $client->request('PATCH', '/api/tickets/INC-1001', server: ['CONTENT_TYPE' => 'application/json'], content: '{"title":"Changed"}');
        self::assertResponseIsSuccessful();
        self::assertSame('Changed', $ticket->getTitle());

        $client->request('PATCH', '/api/tickets/INC-1001', server: ['CONTENT_TYPE' => 'application/json'], content: '{}');
        self::assertResponseStatusCodeSame(422);
        $this->assertErrorRequest($client, 'PATCH', '/api/tickets/bad', 400, 'invalid_ticket_code', 'El código debe tener el formato INC-0000');
        $this->assertErrorRequest($client, 'PATCH', '/api/tickets/INC-9999', 404, 'ticket_not_found', 'La incidencia solicitada no existe');
    }

    public function test_delete_resolves_ticket_and_returns_no_content(): void
    {
        $ticket = new Ticket('INC-1001', 'Issue', TicketPriority::Normal, TicketStatus::Open);
        $catalog = $this->createMock(TicketCatalogInterface::class);
        $catalog->method('findByCode')->willReturnCallback(static fn (string $code): ?Ticket => $code === 'INC-1001' ? $ticket : null);
        $client = static::createClient();
        static::getContainer()->set(TicketCatalogInterface::class, $catalog);
        static::getContainer()->set(EntityManagerInterface::class, $this->createMock(EntityManagerInterface::class));

        $client->request('DELETE', '/api/tickets/INC-1001');
        self::assertResponseStatusCodeSame(204);
        $this->assertErrorRequest($client, 'DELETE', '/api/tickets/bad', 400, 'invalid_ticket_code', 'El código debe tener el formato INC-0000');
        $this->assertErrorRequest($client, 'DELETE', '/api/tickets/INC-9999', 404, 'ticket_not_found', 'La incidencia solicitada no existe');
    }

    private function assertErrorRequest($client, string $method, string $uri, int $status, string $code, string $message): void
    {
        $client->request($method, $uri);
        self::assertResponseStatusCodeSame($status);
        self::assertJsonStringEqualsJsonString(json_encode(['error' => ['code' => $code, 'message' => $message]], JSON_THROW_ON_ERROR), (string) $client->getResponse()->getContent());
    }
}
