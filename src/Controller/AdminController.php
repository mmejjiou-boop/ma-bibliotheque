<?php

namespace App\Controller;

use App\Form\MediaType;
use App\Repository\EmpruntRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin', methods: ['GET'])]
    public function index(
        MediaRepository $mediaRepository,
        UserRepository $userRepository,
        EmpruntRepository $empruntRepository,
    ): Response {
        $parType = [];
        foreach (MediaType::TYPES as $type) {
            $parType[$type] = $mediaRepository->count(['type' => $type]);
        }

        return $this->render('admin/index.html.twig', [
            'nbMedias' => $mediaRepository->count([]),
            'nbUsers' => $userRepository->count([]),
            'nbEnCours' => $empruntRepository->count(['dateRetour' => null]),
            'nbEnRetard' => $empruntRepository->compterEnRetard(),
            'parType' => $parType,
        ]);
    }
}