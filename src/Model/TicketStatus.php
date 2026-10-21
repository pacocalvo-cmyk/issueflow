<?php

declare(strict_types= 1);
namespace App\Model;
enum TicketStatus: string
{
case Open ="open";
case Closed = "closed";
case InProgress  = "in_progress";


}