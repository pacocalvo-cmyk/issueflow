<?php

declare(strict_types=1);

namespace App\Model;

/*prioridades  permitidas por el sistema issuefloqw
el sistema permita solo 4 prioridades: urgent, high, normal, low.

*/

enum TicketPriority: string
{
    case Urgent = "urgent";
    case High = "high";
    case Normal = "normal";
    case Low = "low";
    /*un enum puede tener metodos.
Implementamos el metodo getlabel() para asignar el texto que verá el usuario*/

    public function getLabel(): string
    {
        return match ($this) {
            self::Urgent => "Urgente",
            self::Normal => "Normal",
            self::Low => "Baja",
            self::High => "Alta",
        };
    }
}
