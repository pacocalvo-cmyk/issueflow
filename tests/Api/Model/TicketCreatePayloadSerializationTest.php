<?php

declare(strict_types=1);

namespace App\Tests\Api\Model;

use App\Api\Model\TicketCreatePayload;
use App\Model\TicketPriority;
use App\Model\TicketStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class TicketCreatePayloadSerializationTest extends TestCase
{
    public function test_reference_json_deserializes_enum_values_into_enum_cases(): void
    {
        $payload = $this->serializer()->deserialize(
            '{"code":"INC-1099","title":"Error de autenticación","priority":"high","status":"open"}',
            TicketCreatePayload::class,
            'json',
        );

        self::assertInstanceOf(TicketCreatePayload::class, $payload);
        self::assertSame(TicketPriority::High, $payload->priority);
        self::assertSame(TicketStatus::Open, $payload->status);
    }

    public function test_invalid_enum_values_are_rejected(): void
    {
        $this->expectException(NotNormalizableValueException::class);

        $this->serializer()->deserialize(
            '{"code":"INC-1099","title":"Issue","priority":"critical","status":"open"}',
            TicketCreatePayload::class,
            'json',
        );
    }

    private function serializer(): Serializer
    {
        return new Serializer([new BackedEnumNormalizer(), new ObjectNormalizer()], [new JsonEncoder()]);
    }
}
