<?php

namespace App\Service;

use App\Entity\Emprunt;
use App\Entity\Media;
use App\Entity\User;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;

class EmpruntManager
{
    public const MAX_EMPRUNTS = 5;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmpruntRepository $empruntRepository,
    ) {
    }

    public function emprunter(User $user, Media $media): Emprunt
    {
        if ($media->getDisponibles() <= 0) {
            throw new \DomainException('Plus aucun exemplaire disponible pour ce média');
        }

        if ($this->empruntRepository->findEnCoursPour($user, $media)) {
            throw new \DomainException('Vous avez déjà emprunté ce média');
        }

        if ($this->empruntRepository->compterEnCours($user) >= self::MAX_EMPRUNTS) {
            throw new \DomainException('Vous avez déjà '.self::MAX_EMPRUNTS.' emprunts en cours');
        }

        $emprunt = (new Emprunt())->setUser($user);
        $media->addEmprunt($emprunt);

        $this->entityManager->persist($emprunt);
        $this->entityManager->flush();

        return $emprunt;
    }

    public function rendre(Emprunt $emprunt): void
    {
        if (!$emprunt->isEnCours()) {
            throw new \DomainException('Cet emprunt a déjà été rendu');
        }

        $emprunt->setDateRetour(new \DateTimeImmutable());
        $this->entityManager->flush();
    }
}