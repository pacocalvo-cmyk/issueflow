<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\String\u;

final class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /** @return list<Category> */
    public function searchByName(?string $name): array
    {
        $queryBuilder = $this->createQueryBuilder('category')->orderBy('category.name', 'ASC');
        $name = trim((string) $name);

        if ($name !== '') {
            $queryBuilder
                ->andWhere('LOWER(category.name) LIKE :name')
                ->setParameter('name', '%'.u($name)->lower()->toString().'%');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
