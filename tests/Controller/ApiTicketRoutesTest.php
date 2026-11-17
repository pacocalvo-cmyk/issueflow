<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

final class ApiTicketRoutesTest extends KernelTestCase
{
    public function test_api_ticket_routes_keep_the_rest_contract(): void
    {
        $router = static::getContainer()->get('router');

        self::assertSame('app_api_ticket_create', $router->matchRequest(Request::create('/api/tickets', 'POST'))['_route']);
        self::assertSame('app_api_ticket_index', $router->matchRequest(Request::create('/api/tickets', 'GET'))['_route']);
        self::assertSame('app_api_ticket_show', $router->matchRequest(Request::create('/api/tickets/INC-1025', 'GET'))['_route']);
        self::assertSame('app_api_ticket_update', $router->matchRequest(Request::create('/api/tickets/INC-1025', 'PATCH'))['_route']);
        self::assertSame('app_api_ticket_delete', $router->matchRequest(Request::create('/api/tickets/INC-1025', 'DELETE'))['_route']);
    }

    public function test_post_with_a_ticket_code_in_the_url_is_not_a_creation_route(): void
    {
        $router = static::getContainer()->get('router');

        $this->expectException(MethodNotAllowedException::class);
        $router->matchRequest(Request::create('/api/tickets/INC-1025', 'POST'));
    }
}
