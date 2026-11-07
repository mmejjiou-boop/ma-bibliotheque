<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Repository\EmpruntRepository;
use App\Service\EmpruntManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/emprunts')]
#[IsGranted('ROLE_ADMIN')]
final class AdminEmpruntController extends AbstractController
{
    public const FILTRES = [
        'en_cours' => 'En cours',
        'en_retard' => 'En retard',
        'rendus' => 'Rendus',
        'tous' => 'Tous',
    ];

    #[Route(name: 'app_admin_emprunts', methods: ['GET'])]
    public function index(Request $request, EmpruntRepository $empruntRepository): Response
    {
        $filtre = $request->query->getString('filtre', 'en_cours');
        if (!array_key_exists($filtre, self::FILTRES)) {
            $filtre = 'en_cours';
        }

        return $this->render('admin_emprunt/index.html.twig', [
            'emprunts' => $empruntRepository->findPourAdmin($filtre),
            'filtre' => $filtre,
            'filtres' => self::FILTRES,
        ]);
    }

    #[Route('/{id}/retour', name: 'app_admin_emprunts_retour', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function retour(Emprunt $emprunt, Request $request, EmpruntManager $empruntManager): Response
    {
        $filtre = $request->getPayload()->getString('filtre', 'en_cours');

        if (!$this->isCsrfTokenValid('retour'.$emprunt->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide');

            return $this->redirectToRoute('app_admin_emprunts', ['filtre' => $filtre], Response::HTTP_SEE_OTHER);
        }

        try {
            $empruntManager->rendre($emprunt);
            $this->addFlash('success', 'Retour de « '.$emprunt->getMedia()->getTitre().' » enregistré');
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_emprunts', ['filtre' => $filtre], Response::HTTP_SEE_OTHER);
    }
}
