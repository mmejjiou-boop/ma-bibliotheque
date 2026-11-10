<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function rechercher(?string $recherche, ?string $type, ?string $genre): array
    {
        $qb = $this->createQueryBuilder('m')
            ->orderBy('m.createdAt', 'DESC');

        if ($recherche) {
            $qb->andWhere('m.titre LIKE :recherche OR m.auteur LIKE :recherche')
                ->setParameter('recherche', '%'.$recherche.'%');
        }

        if ($type) {
            $qb->andWhere('m.type = :type')
                ->setParameter('type', $type);
        }

        if ($genre) {
            $qb->andWhere('m.genre = :genre')
                ->setParameter('genre', $genre);
        }

        return $qb->getQuery()->getResult();
    }

    public function findNouveautes(string $type, int $limite): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.type = :type')
            ->setParameter('type', $type)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }
}