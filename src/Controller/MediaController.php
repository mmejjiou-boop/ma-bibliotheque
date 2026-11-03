<?php

namespace App\Controller;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/media')]
#[IsGranted('ROLE_ADMIN')]
final class MediaController extends AbstractController
{
    #[Route(name: 'app_media_index', methods: ['GET'])]
    public function index(MediaRepository $mediaRepository): Response
    {
        return $this->render('media/index.html.twig', [
            'media' => $mediaRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_media_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $medium = new Media();
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $medium->setImage($this->uploadImage($imageFile, $slugger));
            }

            $entityManager->persist($medium);
            $entityManager->flush();

            $this->addFlash('success', 'Média ajouté');

            return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('media/new.html.twig', [
            'medium' => $medium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_media_show', methods: ['GET'])]
    public function show(Media $medium): Response
    {
        return $this->render('media/show.html.twig', [
            'medium' => $medium,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_media_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Media $medium, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $this->supprimerImage($medium->getImage());
                $medium->setImage($this->uploadImage($imageFile, $slugger));
            }

            $entityManager->flush();

            $this->addFlash('success', 'Média modifié');

            return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('media/edit.html.twig', [
            'medium' => $medium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_media_delete', methods: ['POST'])]
    public function delete(Request $request, Media $medium, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$medium->getId(), $request->getPayload()->getString('_token'))) {
            if ($medium->getEmprunts()->count() > 0) {
                $this->addFlash('error', 'Impossible de supprimer un média qui a des emprunts');

                return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
            }

            $this->supprimerImage($medium->getImage());
            $entityManager->remove($medium);
            $entityManager->flush();

            $this->addFlash('success', 'Média supprimé');
        }

        return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
    }

    private function uploadImage(UploadedFile $file, SluggerInterface $slugger): string
    {
        $nom = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->lower();
        $nouveauNom = $nom.'-'.uniqid().'.'.$file->guessExtension();

        $file->move($this->getParameter('kernel.project_dir').'/public/uploads/images', $nouveauNom);

        return $nouveauNom;
    }

    private function supprimerImage(?string $image): void
    {
        if (!$image) {
            return;
        }

        $chemin = $this->getParameter('kernel.project_dir').'/public/uploads/images/'.$image;
        if (file_exists($chemin)) {
            unlink($chemin);
        }
    }
}