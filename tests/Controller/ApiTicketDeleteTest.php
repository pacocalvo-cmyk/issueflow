<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Contract\TicketCatalogInterface;
use App\Api\Mapper\TicketResponseMapper;
use App\Controller\ApiTicketController;
use App\Entity\Ticket;
use App\Model\TicketPriority;
use App\Model\TicketStatus;
use App\Service\TicketPaginator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Response;

final class ApiTicketDeleteTest extends TestCase
{
    public function test_it_removes_an_existing_ticket_and_returns_no_content(): void
    {
        $ticket = new Ticket('INC-1001', 'Ticket', TicketPriority::Normal, TicketStatus::Open);
        $catalog = $this->createMock(TicketCatalogInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with($ticket);
        $entityManager->expects(self::once())->method('flush');

        $response = $this->controller($catalog, $entityManager)->delete($ticket);

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertSame('', $response->getContent());
    }

    public function test_a_persistence_failure_is_propagated_without_a_success_response(): void
    {
        $ticket = new Ticket('INC-1001', 'Ticket', TicketPriority::Normal, TicketStatus::Open);
        $catalog = $this->createMock(TicketCatalogInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with($ticket);
        $entityManager->expects(self::once())->method('flush')->willThrowException(new \RuntimeException('database failure'));

        try {
            $this->controller($catalog, $entityManager)->delete($ticket);
            self::fail('The persistence exception should be propagated.');
        } catch (\RuntimeException $exception) {
            self::assertSame('database failure', $exception->getMessage());
        }
    }

    public function test_ticket_relations_do_not_cascade_deletions_to_category_or_user(): void
    {
        foreach (['category', 'assignee'] as $relation) {
            $property = new \ReflectionProperty(Ticket::class, $relation);
            $mapping = $property->getAttributes(\Doctrine\ORM\Mapping\ManyToOne::class)[0]->newInstance();
            self::assertSame(null, $mapping->cascade);
        }

        $categoryJoin = (new \ReflectionProperty(Ticket::class, 'category'))
            ->getAttributes(\Doctrine\ORM\Mapping\JoinColumn::class)[0]->newInstance();
        $assigneeJoin = (new \ReflectionProperty(Ticket::class, 'assignee'))
            ->getAttributes(\Doctrine\ORM\Mapping\JoinColumn::class)[0]->newInstance();

        self::assertSame('SET NULL', $categoryJoin->onDelete);
        self::assertSame('SET NULL', $assigneeJoin->onDelete);
    }

    private function controller(TicketCatalogInterface $catalog, EntityManagerInterface $entityManager): ApiTicketController
    {
        $controller = new ApiTicketController(new TicketPaginator($catalog), $entityManager, new TicketResponseMapper());
        $controller->setContainer(new ContainerBuilder());

        return $controller;
    }
}
