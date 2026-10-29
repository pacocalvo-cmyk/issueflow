<?php

declare(strict_types=1);

namespace App\Entity;
use InvalidArgumentException;
use App\Model\TicketPriority;
use App\Model\TicketStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Console\Attribute\Option;

/*
Entity Doctrine


*/

#[ORM\Entity]//decimos que la clase es una entidad doctrine
#[ORM\Table(name: "tickets")]//asignamos la tabla correspondiente en la base de datos
final class Ticket
{
    /*
    identificador tecnico(id) de la base de datos comienza con valor null
    porque la entidad recien creada no ha sido insertada.
    el valor de id será asignado al momento de persistirla
    */
    #[ORM\Id] //indica que corresponde a la PK o clave primaria
    #[ORM\GeneratedValue]//indica que es un valor autogenerado
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])] //si no especifico de la columna esta toma el nombre del atributo
    private ?int $id = null;

    /* constructor property production (php moderno)*/
    public function __construct(
        #[ORM\Column(length: 8, unique: true)]
        private readonly string $code,//solo lectura readonly
        #[ORM\Column(length: 180)]
        private string $title,
        #[ORM\Column(length: 20, enumType: TicketPriority::class)]

        private TicketPriority $priority,
        #[ORM\Column(length: 20, enumType: TicketStatus::class)]
        private TicketStatus $status
    ) {
        /*validar el formato de id: INC- seguido de 4 digitos
        para ello usamos una regex: una expresión regular: : /^INC_\d{4}$/  
        pregmacht devuelve 1 cuando es valida,  compara el texto con el regex para ver si es validp  */

        if (preg_match('/^INC-\d{4}$/', $this->code) !== 1) {
            throw new InvalidArgumentException("el código no tiene formato valido");
        }




        /* normalizacion de title: eliminar espacios en blanco antes y después
    $normalizatedTitle =trim($title) trim quita los espacios antes y despues de un texto*/

        $normalizatedTitle = trim($title);
        if ($normalizatedTitle === "") {
            throw new InvalidArgumentException("El titulo no puede estar vacio");

        }
        $this->title = $normalizatedTitle;    /*creamos los getters para cada uno de los atributos */


    }
    public function getId(): ?int
    {
        return $this->id;

    }

    public function getcode(): string
    {
        return $this->code;
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
