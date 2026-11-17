<?php

declare(strict_types=1);

namespace App\Tests\Api\Model;

use App\Api\Model\TicketIndexQuery;
use App\Model\TicketPriority;
use App\Model\TicketStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class TicketIndexQueryTest extends TestCase
{
    public function test_optional_parameters_default_to_empty_filters_and_first_page(): void
    {
        $query = new TicketIndexQuery();
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        self::assertSame('', $query->query);
        self::assertNull($query->priority);
        self::assertNull($query->status);
        self::assertNull($query->page);
        self::assertCount(0, $validator->validate($query));
    }

    public function test_query_and_optional_enum_filters_are_normalized(): void
    {
        $query = new TicketIndexQuery('  error INC-1234  ', ' urgent ', ' closed ', '2');
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        self::assertSame('error INC-1234', $query->query);
        self::assertSame(TicketPriority::Urgent->value, $query->priority);
        self::assertSame(TicketStatus::Closed->value, $query->status);
        self::assertSame('2', $query->page);
        self::assertCount(0, $validator->validate($query));
    }

    public function test_empty_enum_filters_remain_ignored_and_invalid_values_are_rejected(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        self::assertCount(0, $validator->validate(new TicketIndexQuery('', ' ', '')));
        $violations = $validator->validate(new TicketIndexQuery('', 'unknown', 'waiting'));
        self::assertCount(2, $violations);
        self::assertSame(['priority', 'status'], array_map(
            static fn ($violation): string => $violation->getPropertyPath(),
            iterator_to_array($violations),
        ));
    }
}
