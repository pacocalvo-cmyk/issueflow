<?php

declare(strict_types=1);

namespace App\Controller;
use App\Model\Ticket;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
final class TicketController extends AbstractController
{
    

    /* Crear la ruta /tickets que muestra el listado de todos los tickets
    Por convención se identifica con index a la ruta que muestra un listado
    del recurso (en este caso tickets)
    */
    #[Route('/tickets', name: 'app_ticket_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('ticket/index.html.twig', [
            'tickets' => Ticket::getAllTickets(),
            'title' => 'Incidencias'

        ]);
    }
    /*ruta dinamica que muestra la información de un ticket en particular
    {id} representa una parte variable de la URL*/


    #[Route('/tickets/{id}', name: 'app_ticket_show', methods: ['GET'])]
    public function show(string $id): Response
    {
        /*buscamo el ticket en el listado, pueden suceder que no exista */
        /*array_find, disponible en php 8.4 o mas devuelve el primer elemento del array que
         cumple con la condicion indicada por la funcion callback si no encuentra devuelve null*/
        $ticket = Ticket::find($id);
        if ($ticket === null) {
            throw $this->createNotFoundException('No existe la incidencia');
        }
        /*si $ticket  no es null envio ala plantilla la informacion de la incidencia*/
        return $this->render('ticket/show.html.twig', ['ticket' => $ticket]);

    }
}