<?php

namespace App\Repository;

use App\Entity\Emprunt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Emprunt>
 */
class EmpruntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emprunt::class);
    }

    public function compterEnRetard(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.dateRetour IS NULL')
            ->andWhere('e.dateRetourPrevue < :aujourdhui')
            ->setParameter('aujourdhui', new \DateTimeImmutable('today'))
            ->getQuery()
            ->getSingleScalarResult();
    }
}