<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Contract\TicketCatalogInterface;
use App\Form\Model\TicketSearchCriteria;
use App\Model\TicketPriority;
use App\Model\TicketStatus;
use Doctrine\ORM\Tools\Pagination\Window;
use Doctrine\ORM\Tools\Pagination\WindowPage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApiTicketIndexQueryTest extends WebTestCase
{
    public function test_invalid_priority_keeps_the_existing_json_error_contract(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/tickets?priority=unknown');

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":{"code":"invalid_priority","message":"La prioridad indicada no es válida."}}',
            (string) $client->getResponse()->getContent(),
        );
    }

    public function test_invalid_status_keeps_the_existing_json_error_contract(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/tickets?status=waiting');

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":{"code":"invalid_status","message":"El estado indicado no es válido."}}',
            (string) $client->getResponse()->getContent(),
        );
    }

    public function test_listing_without_query_parameters_keeps_the_existing_response_shape(): void
    {
        $catalog = $this->createMock(TicketCatalogInterface::class);
        $catalog->expects(self::once())
            ->method('search')
            ->with(self::callback(static fn (TicketSearchCriteria $criteria): bool => $criteria->query === ''
                && $criteria->priority === null
                && $criteria->status === null), 1, 3)
            ->willReturn(new WindowPage([], 0, Window::fromPageNumberAndSize(1, 3)));

        $client = static::createClient();
        static::getContainer()->set(TicketCatalogInterface::class, $catalog);
        $client->request('GET', '/api/tickets');

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('{"tickets":[]}', (string) $client->getResponse()->getContent());
    }

    public function test_empty_query_and_combined_filters_are_mapped_to_search_criteria(): void
    {
        $catalog = $this->createMock(TicketCatalogInterface::class);
        $catalog->expects(self::once())
            ->method('search')
            ->with(self::callback(static function (TicketSearchCriteria $criteria): bool {
                return $criteria->query === 'invoice'
                    && $criteria->priority === TicketPriority::Urgent
                    && $criteria->status === TicketStatus::Closed;
            }), 2, 3)
            ->willReturn(new WindowPage([], 4, Window::fromPageNumberAndSize(2, 3)));

        $client = static::createClient();
        static::getContainer()->set(TicketCatalogInterface::class, $catalog);
        $client->request('GET', '/api/tickets?query=%20invoice%20&priority=%20urgent%20&status=closed&page=2');

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('{"tickets":[]}', (string) $client->getResponse()->getContent());
    }

    public function test_invalid_and_below_one_pages_still_use_the_first_page(): void
    {
        $catalog = $this->createMock(TicketCatalogInterface::class);
        $catalog->expects(self::once())
            ->method('search')
            ->with(self::isInstanceOf(TicketSearchCriteria::class), 1, 3)
            ->willReturn(new WindowPage([], 0, Window::fromPageNumberAndSize(1, 3)));

        $client = static::createClient();
        static::getContainer()->set(TicketCatalogInterface::class, $catalog);
        $client->request('GET', '/api/tickets?page=abc');
        self::assertResponseIsSuccessful();
    }

    public function test_page_below_one_still_uses_the_first_page(): void
    {
        $catalog = $this->createMock(TicketCatalogInterface::class);
        $catalog->expects(self::once())
            ->method('search')
            ->with(self::isInstanceOf(TicketSearchCriteria::class), 1, 3)
            ->willReturn(new WindowPage([], 0, Window::fromPageNumberAndSize(1, 3)));

        $client = static::createClient();
        static::getContainer()->set(TicketCatalogInterface::class, $catalog);
        $client->request('GET', '/api/tickets?page=0');
        self::assertResponseIsSuccessful();
    }

    public function test_page_above_the_last_page_uses_the_last_page(): void
    {
        $requestedPages = [];
        $catalog = $this->createMock(TicketCatalogInterface::class);
        $catalog->expects(self::exactly(2))
            ->method('search')
            ->willReturnCallback(static function (TicketSearchCriteria $criteria, int $page, int $pageSize) use (&$requestedPages): WindowPage {
                $requestedPages[] = $page;

                return new WindowPage([], 10, Window::fromPageNumberAndSize($page, $pageSize));
            });

        $client = static::createClient();
        static::getContainer()->set(TicketCatalogInterface::class, $catalog);
        $client->request('GET', '/api/tickets?page=99');

        self::assertResponseIsSuccessful();
        self::assertSame([99, 4], $requestedPages);
    }
}
