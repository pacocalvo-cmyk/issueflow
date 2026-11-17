<?php

declare(strict_types=1);

namespace App\Api\Model;

use App\Model\TicketPriority;
use App\Model\TicketStatus;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/** Parámetros validados de GET /api/tickets. */
final readonly class TicketIndexQuery
{
    public ?string $query;
    public ?string $priority;
    public ?string $status;
    public ?string $page;

    public function __construct(
        ?string $query = '',
        ?string $priority = null,
        ?string $status = null,
        ?string $page = null,
    ) {
        $this->query = trim($query ?? '');
        $this->priority = $priority === null ? null : trim($priority);
        $this->status = $status === null ? null : trim($status);
        $this->page = $page;
    }

    #[Assert\Callback]
    public function validateFilters(ExecutionContextInterface $context): void
    {
        if ($this->priority !== null && $this->priority !== '' && TicketPriority::tryFrom($this->priority) === null) {
            $context->buildViolation('La prioridad indicada no es válida.')
                ->atPath('priority')
                ->addViolation();
        }

        if ($this->status !== null && $this->status !== '' && TicketStatus::tryFrom($this->status) === null) {
            $context->buildViolation('El estado indicado no es válido.')
                ->atPath('status')
                ->addViolation();
        }
    }
}
