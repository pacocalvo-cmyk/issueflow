<?php

declare(strict_types=1);


namespace App\Repository;

use App\Contract\RepositoryInterface;
use App\Contract\TicketCatalogInterface;
use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
#[AsAlias(TicketCatalogInterface::class)]

final class TicketRepository extends ServiceEntityRepository implements TicketCatalogInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);

    }

    public function getTickets(): array
    {

        return $this->findBy([], ['id' => 'ASC']);
    }


    public function findByCode(string $code): ?Ticket
    {

        return $this->findOneBy(['code'=> $code]);
    }


}