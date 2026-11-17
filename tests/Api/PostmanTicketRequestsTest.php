<?php

declare(strict_types=1);

namespace App\Tests\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class PostmanTicketRequestsTest extends TestCase
{
    public function test_create_request_posts_to_collection_url_and_sends_ticket_in_json_body(): void
    {
        $request = Yaml::parseFile($this->requestPath('Creacion de Ticket/New Request.request.yaml'));

        self::assertSame('POST', $request['method']);
        self::assertSame('http://localhost:8000/api/tickets', $request['url']);
        self::assertSame('json', $request['body']['type']);
        self::assertContains(['key' => 'Content-Type', 'value' => 'application/json'], $request['headers']);
        self::assertSame([
            'code' => 'INC-1026',
            'title' => 'Error de autenticación',
            'priority' => 'high',
            'status' => 'open',
        ], json_decode($request['body']['content'], true, flags: JSON_THROW_ON_ERROR));
    }

    public function test_ticket_code_remains_in_the_update_and_delete_urls(): void
    {
        $update = Yaml::parseFile($this->requestPath('Edicion de tickets/New Request.request.yaml'));
        $delete = Yaml::parseFile($this->requestPath('Eliminar Ticket/New Request.request.yaml'));

        self::assertSame('PATCH', $update['method']);
        self::assertSame('http://localhost:8000/api/tickets/INC-1025', $update['url']);
        self::assertSame('DELETE', $delete['method']);
        self::assertSame('http://localhost:8000/api/tickets/INC-1025', $delete['url']);
    }

    private function requestPath(string $path): string
    {
        return dirname(__DIR__, 2).'/postman/collections/issueflowDev/'.$path;
    }
}
