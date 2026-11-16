<?php

declare(strict_types=1);

namespace App\Api\Model;

use App\Model\TicketPriority;
use App\Model\TicketStatus;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/** Payload de entrada para PATCH /api/tickets/{code}. */
final class TicketUpdatePayload
{
    public function __construct(
        public readonly bool $titleProvided,
        #[Assert\When(
            expression: 'this.titleProvided',
            constraints: [new Assert\Sequentially([
                new Assert\Type('string'),
                new Assert\NotBlank(message: 'El título no puede estar vacío', normalizer: 'trim'),
                new Assert\Length(max: 180, maxMessage: 'El título no puede superar los 180 caracteres', normalizer: 'trim'),
            ])],
        )]
        public readonly mixed $title,
        public readonly bool $priorityProvided,
        #[Assert\When(
            expression: 'this.priorityProvided',
            constraints: [new Assert\NotNull(message: 'La prioridad indicada no es válida')],
        )]
        public readonly mixed $priority,
        public readonly bool $statusProvided,
        #[Assert\When(
            expression: 'this.statusProvided',
            constraints: [new Assert\NotNull(message: 'El estado indicado no es válido')],
        )]
        public readonly mixed $status,
        #[Assert\IsTrue(message: 'Debe enviar al menos un campo modificable')]
        public readonly bool $hasChanges,
    ) {}

    public static function fromArray(array $data): self
    {
        $titleProvided = array_key_exists('title', $data);
        $priorityProvided = array_key_exists('priority', $data);
        $statusProvided = array_key_exists('status', $data);

        return new self(
            $titleProvided,
            $data['title'] ?? null,
            $priorityProvided,
            $data['priority'] ?? null,
            $statusProvided,
            $data['status'] ?? null,
            $titleProvided || $priorityProvided || $statusProvided,
        );
    }

    public function priorityValue(): ?TicketPriority
    {
        return $this->priorityProvided && is_string($this->priority)
            ? TicketPriority::from($this->priority)
            : null;
    }

    public function statusValue(): ?TicketStatus
    {
        return $this->statusProvided && is_string($this->status)
            ? TicketStatus::from($this->status)
            : null;
    }

    #[Assert\Callback]
    public function validateEnums(ExecutionContextInterface $context): void
    {
        if ($this->priorityProvided && (!is_string($this->priority) || TicketPriority::tryFrom($this->priority) === null)) {
            $context->buildViolation('La prioridad indicada no es válida')->atPath('priority')->addViolation();
        }

        if ($this->statusProvided && (!is_string($this->status) || TicketStatus::tryFrom($this->status) === null)) {
            $context->buildViolation('El estado indicado no es válido')->atPath('status')->addViolation();
        }
    }
}
