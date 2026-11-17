<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\Model\TicketCreatePayload;
use App\Api\Model\TicketIndexQuery;
use App\Api\Model\TicketUpdatePayload;
use App\Api\Mapper\TicketResponseMapper;
use App\Form\Model\TicketSearchCriteria;
use App\Model\TicketPriority;
use App\Model\TicketStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use App\Entity\Ticket;
use App\Service\TicketPaginator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use JsonException;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

final class ApiTicketController extends AbstractController
{
    public function __construct(
        private readonly TicketPaginator $ticketPaginator,
        private readonly EntityManagerInterface $entityManager,
        private readonly TicketResponseMapper $ticketResponseMapper,
    ) {}
    /**
     * Esta ruta devuelve un listado de las incidencias
     * Está petición se hace con el protocolo HTTP, aunque venga de una app móvil
     * Ejemplo de url: miappdeincidencias.com/api/tickets?query=factura&category=1&priority=urgent&page=1
     */
    #[Route('/api/tickets', name: 'app_api_ticket_index', methods: ['GET'])]
    public function index(
        #[MapQueryString(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST,
            mapWhenEmpty: true,
        )]
        TicketIndexQuery $query,
    ): JsonResponse
    {
        $criteria = new TicketSearchCriteria();
        $criteria->query = $query->query;
        if ($query->priority !== null && $query->priority !== '') {
            $criteria->priority = TicketPriority::from($query->priority);
        }
        if ($query->status !== null && $query->status !== '') {
            $criteria->status = TicketStatus::from($query->status);
        }

        /**
         * Usar el servicio TicketPaginator para obtener la página
         * adecuada
         */
        $ticketPage = $this->ticketPaginator->getTicketPage($criteria, $query->page);

        /**
         * Antes de enviar la respuesta al usuario, necesito convertir el objeto WindowPage
         * al formato JSON.
         */
        $items = $this->ticketResponseMapper->mapCollection($ticketPage->getItems());

        return $this->json([
            'tickets' => $items
        ]);
    }

    /**
     * Ruta para crear una incidencia desde JSON
     */
    #[Route('/api/tickets', name: 'app_api_ticket_create', methods: ['POST'])]

    /**
     * MapRequestPayload mapea los datos recibidos en el formato aceptado (JSON)
     * y los convierte a un objeto de la clase indicada (TicketCreatePayload),
     * aplica previamente las validaciones de este objeto si existen, si la
     * validación falla devuelve el error establecido en validationFailedStatusCode
     */
    public function create(
        #[MapRequestPayload(
            acceptFormat: 'json',
            validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
        )]
        TicketCreatePayload $payload,
    ): JsonResponse
    {
        // Si los datos son válidos los procesamos

        // Creo un nuevo ticket con los datos del payload
        $ticket = new Ticket(
            code: $payload->code,
            title: $payload->title,
            priority: $payload->priority,
            status: $payload->status,
        );

        // persisto el ticket en la base de datos

        // agrega la tarea a la unidad de trabajo
        $this->entityManager->persist($ticket);
        // ejecuta las tareas que esten en la unidad de trabajo
        $this->entityManager->flush();

        /**
         * Respondo al cliente con la data creada, código 201, cabeceras con la información de la ruta de creación
         */
        return $this->json(
            [
                'ticket' => $this->ticketResponseMapper->map($ticket),
            ],
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('app_api_ticket_show', ['code' => $ticket->getCode()])
            ]
        );
    }


    /**
     * Ruta para solicitar al API un ticket por su código
     */
    #[Route('/api/tickets/{code}', name: 'app_api_ticket_show', methods: ['GET'])]
    public function show(Ticket $ticket): JsonResponse
    {
        return $this->json([
            'ticket' => $this->ticketResponseMapper->map($ticket),
        ]);
    }

    #[Route('/api/tickets/{code}', name: 'app_api_ticket_delete', methods: ['DELETE'])]
    public function delete(Ticket $ticket): Response
    {
        $this->entityManager->remove($ticket);
        $this->entityManager->flush();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/tickets/{code}', name: 'app_api_ticket_update', methods: ['PATCH'])]
    public function update(Ticket $ticket, Request $request, ValidatorInterface $validator): JsonResponse
    {
        try {
            $decoded = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->json(['error' => [
                'code' => 'invalid_json',
                'message' => 'El cuerpo de la petición debe contener JSON válido',
            ]], Response::HTTP_BAD_REQUEST);
        }

        if (!$decoded instanceof \stdClass) {
            return $this->json(['error' => [
                'code' => 'invalid_json',
                'message' => 'El cuerpo de la petición debe ser un objeto JSON',
            ]], Response::HTTP_BAD_REQUEST);
        }

        $data = get_object_vars($decoded);

        $payload = TicketUpdatePayload::fromArray($data);
        $violations = $validator->validate($payload);
        if (count($violations) > 0) {
            return $this->json(['errors' => array_map(
                static fn ($violation): array => [
                    'property' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ],
                iterator_to_array($violations),
            )], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($payload->titleProvided) {
            $ticket->changeTitle($payload->title);
        }
        if ($payload->priorityProvided) {
            $ticket->changePriority($payload->priorityValue());
        }
        if ($payload->statusProvided) {
            $ticket->changeStatus($payload->statusValue());
        }

        $this->entityManager->flush();

        return $this->json(['ticket' => $this->ticketResponseMapper->map($ticket)]);
    }
}
