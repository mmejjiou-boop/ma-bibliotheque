<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Media;
use App\Entity\User;
use App\Repository\EmpruntRepository;
use App\Service\EmpruntManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class EmpruntController extends AbstractController
{
    #[Route('/emprunter/{id}', name: 'app_emprunter', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function emprunter(Media $media, Request $request, EmpruntManager $empruntManager, #[CurrentUser] User $user): Response
    {
        if (!$this->isCsrfTokenValid('emprunter'.$media->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide');

            return $this->redirectToRoute('app_catalogue_show', ['id' => $media->getId()], Response::HTTP_SEE_OTHER);
        }

        try {
            $emprunt = $empruntManager->emprunter($user, $media);
            $this->addFlash('success', 'Vous avez emprunté « '.$media->getTitre().' ». Retour prévu le '.$emprunt->getDateRetourPrevue()->format('d/m/Y'));
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_catalogue_show', ['id' => $media->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/mes-emprunts', name: 'app_mes_emprunts', methods: ['GET'])]
    public function index(EmpruntRepository $empruntRepository, #[CurrentUser] User $user): Response
    {
        return $this->render('emprunt/index.html.twig', [
            'emprunts' => $empruntRepository->findBy(['user' => $user], ['dateEmprunt' => 'DESC']),
            'nbEnCours' => $empruntRepository->compterEnCours($user),
            'max' => EmpruntManager::MAX_EMPRUNTS,
        ]);
    }

    #[Route('/mes-emprunts/{id}/rendre', name: 'app_rendre', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function rendre(Emprunt $emprunt, Request $request, EmpruntManager $empruntManager, #[CurrentUser] User $user): Response
    {
        if ($emprunt->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('rendre'.$emprunt->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide');

            return $this->redirectToRoute('app_mes_emprunts', [], Response::HTTP_SEE_OTHER);
        }

        try {
            $empruntManager->rendre($emprunt);
            $this->addFlash('success', '« '.$emprunt->getMedia()->getTitre().' » a bien été rendu');
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_mes_emprunts', [], Response::HTTP_SEE_OTHER);
    }
}