<?php

declare(strict_types=1);

namespace App\Model;
use InvalidArgumentException;
final class Ticket
{

    /* constructor property production (php moderno)*/
    public function __construct(
        private string $id,
        private string $title,
        private TicketPriority $priority,
        private TicketStatus $status
    )
     {
        /*validar el formato de id: INC- seguido de 4 digitos
        para ello usamos una regex: una expresión regular: : /^INC_\d{4}$/  
        pregmacht devuelve 1 cuando es valida,  compara el texto con el regex para ver si es validp  */

if(preg_match('/^INC-\d{4}$/', $this->id) !==1){
    throw new InvalidArgumentException("el id no tiene formato valido");
}




        /* normalizacion de title: eliminar espacios en blanco antes y después
    $normalizatedTitle =trim($title) trim quita los espacios antes y despues de un texto*/
$normalizatedTitle = trim($title);
if ($normalizatedTitle ==="") {
throw new InvalidArgumentException("El titulo no puede estar vacio");

    }
$this->title = $normalizatedTitle;    /*creamos los getters para cada uno de los atributos */
  

     }
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
    public function getStatus(): TicketStatus
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
                status: TicketStatus::Open
            ),
            new Ticket(
                id: 'INC-1002',
                title: 'Error en factura',
                priority: TicketPriority::High,
                status: TicketStatus::Open
            ),
            new Ticket(
                id: 'INC-1003',
                title: 'Actualizar datos',
                priority: TicketPriority::Normal,
                status: TicketStatus::Closed
            ),
            new Ticket(
                id: 'INC-1004',
                title: 'El ordenador está en llamas',
                priority: TicketPriority::Urgent,
                status: TicketStatus::InProgress
            ),
            new Ticket(
                id: 'INC-1005',
                title: 'No me deja cambiar la contraseña',
                priority: TicketPriority::Low,
                status: TicketStatus::Open
            ),
            new Ticket(
                id: 'INC-1006',
                title: "el pc no va",
                priority: TicketPriority::Low,
                status: TicketStatus::Open
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
