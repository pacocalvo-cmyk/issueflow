<?php

declare(strict_types=1);

namespace App\Form\Model;


use App\Entity\Category;
use App\Entity\User;
use App\Entity\Ticket;
use App\Model\TicketPriority;
use App\Model\TicketStatus;
use Symfony\Component\Validator\Constraints as Assert;
final class TicketEditData
{
   /* #[Assert\NotBlank(
        message: 'El código es obligatorio',
        normalizer: 'trim'
    )]*/

    public string $code = '';

    #[Assert\NotBlank(
        message: 'El título es obligatorio',
        normalizer: 'trim'
    )]
    #[Assert\Length(
        max: 180,
        maxMessage: 'El título no puede superar los 180 caracteres',
        normalizer: 'trim'
    )]
    public string $title = '';

    public TicketPriority $priority = TicketPriority::Normal;
    public TicketStatus $status = TicketStatus::Open;
    public ?Category $category = null;
    public ?User $assignee = null;
    public static function fromTicket(Ticket $ticket): self
    {
        $dto = new self();

        $dto->title = $ticket->getTitle();
        $dto->priority = $ticket->getPriority();
        $dto->status = $ticket->getStatus();
        $dto->category = $ticket->getCategory();
        $dto->assignee = $ticket->getAssignee();

        return $dto;
    }
    public function applyToTicket(Ticket $ticket): void
    {
        $ticket->setTitle($this->title);
        $ticket->setPriority($this->priority);
        $ticket->setStatus($this->status);
        $ticket->setCategory($this->category);
        $ticket->assignTo($this->assignee);
    }

    /**
     * Método para mapear TicketCreateData a Ticket
     */
    public function mapToTicket(): Ticket
    {
        $ticket = new Ticket(
            code: $this->code,
            title: $this->title,
            priority: $this->priority,
            status: $this->status,
        );
        $ticket->setCategory($this->category);
        $ticket->assignTo($this->assignee);

        return $ticket;
    }
}