<?php

namespace App\Controller;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(Request $request, MediaRepository $mediaRepository): Response
    {
        $recherche = $request->query->getString('q');
        $type = $request->query->getString('type');
        $genre = $request->query->getString('genre');

        return $this->render('home/index.html.twig', [
            'medias' => $mediaRepository->rechercher($recherche, $type, $genre),
            'recherche' => $recherche,
            'typeChoisi' => $type,
            'genreChoisi' => $genre,
            'types' => MediaType::TYPES,
            'genres' => MediaType::GENRES,
        ]);
    }

    #[Route('/catalogue/{id}', name: 'app_catalogue_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Media $media): Response
    {
        return $this->render('home/show.html.twig', [
            'media' => $media,
        ]);
    }
}