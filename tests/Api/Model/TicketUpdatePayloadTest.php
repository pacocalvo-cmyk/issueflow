<?php

declare(strict_types=1);

namespace App\Tests\Api\Model;

use App\Api\Model\TicketUpdatePayload;
use Symfony\Component\Validator\Validation;
use PHPUnit\Framework\TestCase;

final class TicketUpdatePayloadTest extends TestCase
{
    public function test_it_distinguishes_missing_fields_from_explicit_null(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $missing = TicketUpdatePayload::fromArray([]);
        $explicitNull = TicketUpdatePayload::fromArray(['title' => null]);

        self::assertCount(1, $validator->validate($missing));
        self::assertCount(1, $validator->validate($explicitNull));
    }

    public function test_it_validates_title_and_enum_values(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        self::assertNotEmpty($validator->validate(TicketUpdatePayload::fromArray(['title' => '   '])));
        self::assertNotEmpty($validator->validate(TicketUpdatePayload::fromArray(['title' => str_repeat('x', 181)])));
        self::assertNotEmpty($validator->validate(TicketUpdatePayload::fromArray(['priority' => 'critical'])));
        self::assertNotEmpty($validator->validate(TicketUpdatePayload::fromArray(['status' => 'pending'])));
        self::assertNotEmpty($validator->validate(TicketUpdatePayload::fromArray(['title' => []])));
        self::assertCount(0, $validator->validate(TicketUpdatePayload::fromArray(['priority' => 'urgent', 'status' => 'closed'])));
    }
}
