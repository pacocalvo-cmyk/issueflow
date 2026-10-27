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
    ) {
        /*validar el formato de id: INC- seguido de 4 digitos
        para ello usamos una regex: una expresión regular: : /^INC_\d{4}$/  
        pregmacht devuelve 1 cuando es valida,  compara el texto con el regex para ver si es validp  */

        if (preg_match('/^INC-\d{4}$/', $this->id) !== 1) {
            throw new InvalidArgumentException("el id no tiene formato valido");
        }




        /* normalizacion de title: eliminar espacios en blanco antes y después
    $normalizatedTitle =trim($title) trim quita los espacios antes y despues de un texto*/
    
        $normalizatedTitle = trim($title);
        if ($normalizatedTitle === "") {
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

    public function isUrgent(): bool
    {
        return $this->priority->isUrgent();
    }
    public function isClosed(): bool
    {
        return $this->status->isClosed();
    }
    
    
   
    
    
}
