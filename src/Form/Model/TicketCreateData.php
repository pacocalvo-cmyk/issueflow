<?php

declare(strict_types=1);


namespace App\Form\Model;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Ticket;
use App\Model\TicketPriority;
use App\Model\TicketStatus;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/*validador de doctrine que impide repetir
 */
#[UniqueEntity(
    fields: ['code'],
    entityClass: Ticket::class,
    message: 'Ya existe una incidencia con ese código',
    errorPath: 'code'
)]
final class TicketCreateData
{
    #[Assert\NotBlank(
        message: 'El código es obligatorio',
        normalizer: 'trim'/*quita espacios vacios*/
    )]
    #[Assert\Regex(
        pattern: '/INC-\d{4}$/',
        message: 'el código tiene que tener el formato INC-0000',
        normalizer: 'trim'
    )]
    public string $code = '';



    #[Assert\NotBlank(
        message: 'El título es obligatorio',
        normalizer: 'trim'/*quita espacios vacios*/
    )]
    #[Assert\Length(
        max: 180,
        maxMessage: 'El título no puede pasar los 180 carcteres',
        normalizer: 'trim'
    )]
    public string $title = '';





    public TicketPriority $priority = TicketPriority::Normal;


    public TicketStatus $status = TicketStatus::Open;

    public ?Category $category = null;

    public ?User $assignee = null;




    /*metodo para mapear TicketCreateData a Ticket */


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
