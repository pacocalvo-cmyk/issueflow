<?php

declare(strict_types=1);

namespace App\Model;

final class Ticket
{

    /* constructor property production (php moderno)*/
    public function __construct(
        private string $id,
        private string $title,
        private TicketPriority $priority,
        private string $status
    ) {
    }
    /*creamos los getters para cada uno de los atributos */
    public function getId(): string
    {
        return $this->id;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getPriority(): TicketPriority
    {
        return $this->priority;
    }
    public function getStatus(): string
    {
        return $this->status;
    }



    public static function getAllTickets(): array
    {
        return [
            new Ticket(
                id: 'INC-1001',
                title: 'No puedo acceder',
                priority: TicketPriority::Urgent,
                status: 'open'
            ),
            new Ticket(
                id: 'INC-1002',
                title: 'Error en factura',
                priority: TicketPriority::High,
                status: 'in_progress'
            ),
            new Ticket(
                id: 'INC-1003',
                title: 'Actualizar datos',
                priority: TicketPriority::Normal,
                status: 'closed'
            ),
            new Ticket(
                id: 'INC-1004',
                title: 'El ordenador está en llamas',
                priority: TicketPriority::Urgent,
                status: 'in_progress'
            ),
            new Ticket(
                id: 'INC-1005',
                title: 'No me deja cambiar prioridad',
                priority: TicketPriority::Low,
                status: 'closed'
            )
        ];
    }
    /* Metodo find que encuentra un ticket en el listado, dado su id 
        ?Ticket quiere decir que el método puede devolver o un Ticket o null
        */
    public static function find(string $id): ?Ticket
    {
        /* array_find(), disponible en PHP 8.4+ devuelve el primer elemento
        del array que cumple con la condición indicada por la función callback.
        Si no encuentra ningún elemento que cumpla la condición devuelve null */
        return array_find(
            Ticket::getAllTickets(),
            fn(Ticket $ticket): bool => $ticket->getId() === $id
        );
    }
}
