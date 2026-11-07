<?php

namespace App\Repository;

use App\Entity\Emprunt;
use App\Entity\Media;
use App\Entity\User;
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

    public function compterEnCours(User $user): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.user = :user')
            ->andWhere('e.dateRetour IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findEnCoursPour(User $user, Media $media): ?Emprunt
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->andWhere('e.media = :media')
            ->andWhere('e.dateRetour IS NULL')
            ->setParameter('user', $user)
            ->setParameter('media', $media)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Emprunt[]
     */
    public function findPourUser(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->addSelect('m')
            ->join('e.media', 'm')
            ->andWhere('e.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.dateRetour', 'ASC')
            ->addOrderBy('e.dateEmprunt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Emprunt[]
     */
    public function findPourAdmin(string $filtre): array
    {
        $qb = $this->createQueryBuilder('e')
            ->addSelect('m', 'u')
            ->join('e.media', 'm')
            ->join('e.user', 'u')
            ->orderBy('e.dateEmprunt', 'DESC');

        if ('en_cours' === $filtre) {
            $qb->andWhere('e.dateRetour IS NULL');
        } elseif ('en_retard' === $filtre) {
            $qb->andWhere('e.dateRetour IS NULL')
                ->andWhere('e.dateRetourPrevue < :aujourdhui')
                ->setParameter('aujourdhui', new \DateTimeImmutable('today'));
        } elseif ('rendus' === $filtre) {
            $qb->andWhere('e.dateRetour IS NOT NULL');
        }

        return $qb->getQuery()->getResult();
    }

    public function compterTousEnCours(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.dateRetour IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
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
