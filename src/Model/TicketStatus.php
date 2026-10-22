<?php

declare(strict_types= 1);
namespace App\Model;
enum TicketStatus: string
{
case Open ="open";
case Closed = "closed";
case InProgress  = "in_progress";

 public function getLabel(): string
    {
        return match ($this) {
            self::Open => "Abierto",
            self::Closed => "Cerrado",
            self::InProgress => "En Progreso",
          
        };
    }

}