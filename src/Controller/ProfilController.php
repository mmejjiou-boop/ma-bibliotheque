<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\MotDePasseType;
use App\Form\PreferencesType;
use App\Form\ProfilType;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profil')]
#[IsGranted('ROLE_USER')]
final class ProfilController extends AbstractController
{
    #[Route(name: 'app_profil', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        EmpruntRepository $empruntRepository,
        SluggerInterface $slugger,
    ): Response {
        $profilForm = $this->createForm(ProfilType::class, $user);
        $profilForm->handleRequest($request);

        if ($profilForm->isSubmitted() && $profilForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour');

            return $this->redirectToRoute('app_profil', [], Response::HTTP_SEE_OTHER);
        }

        $preferencesForm = $this->createForm(PreferencesType::class, $user);
        $preferencesForm->handleRequest($request);

        if ($preferencesForm->isSubmitted() && $preferencesForm->isValid()) {
            $photoFile = $preferencesForm->get('photoFile')->getData();
            if ($photoFile) {
                $this->supprimerPhoto($user->getPhoto());
                $user->setPhoto($this->uploadPhoto($photoFile, $slugger));
            }

            $entityManager->flush();
            $this->addFlash('success', 'Préférences enregistrées');

            return $this->redirectToRoute('app_profil', [], Response::HTTP_SEE_OTHER);
        }

        if ($profilForm->isSubmitted() || $preferencesForm->isSubmitted()) {
            // le formulaire invalide a modifié l'utilisateur connecté en mémoire : on annule
            $entityManager->refresh($user);
        }

        $motDePasseForm = $this->createForm(MotDePasseType::class);
        $motDePasseForm->handleRequest($request);

        if ($motDePasseForm->isSubmitted() && $motDePasseForm->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $motDePasseForm->get('nouveau')->getData()));
            $entityManager->flush();
            $this->addFlash('success', 'Mot de passe modifié');

            return $this->redirectToRoute('app_profil', [], Response::HTTP_SEE_OTHER);
        }

        $emprunts = $user->getEmprunts();

        return $this->render('profil/index.html.twig', [
            'profilForm' => $profilForm,
            'preferencesForm' => $preferencesForm,
            'motDePasseForm' => $motDePasseForm,
            'nbEnCours' => $empruntRepository->compterEnCours($user),
            'nbTotal' => $emprunts->count(),
            'nbEnRetard' => $emprunts->filter(fn ($e) => $e->isEnRetard())->count(),
        ]);
    }

    #[Route('/photo/supprimer', name: 'app_profil_photo_supprimer', methods: ['POST'])]
    public function supprimerPhotoAction(Request $request, #[CurrentUser] User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('supprimer_photo', $request->getPayload()->getString('_token'))) {
            $this->supprimerPhoto($user->getPhoto());
            $user->setPhoto(null);
            $entityManager->flush();
            $this->addFlash('success', 'Photo supprimée');
        }

        return $this->redirectToRoute('app_profil', [], Response::HTTP_SEE_OTHER);
    }

    private function uploadPhoto(UploadedFile $file, SluggerInterface $slugger): string
    {
        $nom = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->lower();
        $nouveauNom = $nom.'-'.uniqid().'.'.$file->guessExtension();

        $file->move($this->getParameter('kernel.project_dir').'/public/uploads/avatars', $nouveauNom);

        return $nouveauNom;
    }

    private function supprimerPhoto(?string $photo): void
    {
        if (!$photo) {
            return;
        }

        $chemin = $this->getParameter('kernel.project_dir').'/public/uploads/avatars/'.$photo;
        if (file_exists($chemin)) {
            unlink($chemin);
        }
    }
}
