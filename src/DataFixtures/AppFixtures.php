<?php

namespace App\DataFixtures;

use App\Entity\Emprunt;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $superAdmin = $this->creerUser('mohamedamine@gmail.com', 'superadmin123', 'Mejjiou', 'Mohamed-Amine', ['ROLE_SUPER_ADMIN']);
        $admin = $this->creerUser('admin@biblio.fr', 'admin123', 'Martin', 'Sophie', ['ROLE_ADMIN']);
        $user = $this->creerUser('user@biblio.fr', 'user123', 'Durand', 'Lucas', []);

        foreach ([$superAdmin, $admin, $user] as $compte) {
            $manager->persist($compte);
        }

        $donnees = [
            ['Harry Potter à l\'école des sorciers', 'J.K. Rowling', 'Roman', 'Fantastique', 1997, 5, 'Un jeune orphelin découvre qu\'il est sorcier et entre à l\'école de Poudlard.'],
            ['Le Petit Prince', 'Antoine de Saint-Exupéry', 'Roman', 'Jeunesse', 1943, 3, 'Un aviateur perdu dans le désert rencontre un petit garçon venu d\'une autre planète.'],
            ['1984', 'George Orwell', 'Roman', 'Science-fiction', 1949, 2, 'Dans un État totalitaire, un homme ose remettre en question le système.'],
            ['Le Comte de Monte-Cristo', 'Alexandre Dumas', 'Roman', 'Aventure', 1844, 2, 'Emprisonné à tort, Edmond Dantès prépare sa vengeance.'],
            ['Astérix le Gaulois', 'Goscinny et Uderzo', 'BD', 'Aventure', 1961, 3, 'Un petit village gaulois résiste encore et toujours à l\'envahisseur romain.'],
            ['Tintin au Tibet', 'Hergé', 'BD', 'Aventure', 1960, 2, 'Tintin part dans l\'Himalaya à la recherche de son ami Tchang.'],
            ['Blacksad', 'Canales et Guarnido', 'BD', 'Policier', 2000, 1, 'Un détective privé enquête dans une ville peuplée d\'animaux.'],
            ['One Piece - Tome 1', 'Eiichirō Oda', 'Manga', 'Aventure', 1997, 4, 'Luffy prend la mer pour devenir le roi des pirates.'],
            ['Naruto - Tome 1', 'Masashi Kishimoto', 'Manga', 'Action', 1999, 3, 'Un jeune ninja rêve de devenir le chef de son village.'],
            ['Death Note - Tome 1', 'Tsugumi Ohba et Takeshi Obata', 'Manga', 'Policier', 2003, 2, 'Un lycéen trouve un carnet qui tue toute personne dont on y écrit le nom.'],
            ['Le Seigneur des anneaux', 'Peter Jackson', 'DVD', 'Fantastique', 2001, 2, 'Un hobbit doit détruire un anneau maléfique.'],
            ['Intouchables', 'Olivier Nakache et Éric Toledano', 'DVD', 'Comédie', 2011, 2, 'L\'amitié entre un riche tétraplégique et son aide à domicile.'],
            ['Le Voyage de Chihiro', 'Hayao Miyazaki', 'DVD', 'Fantastique', 2001, 1, 'Une fillette se retrouve piégée dans un monde peuplé d\'esprits.'],
        ];

        $medias = [];
        foreach ($donnees as [$titre, $auteur, $type, $genre, $annee, $exemplaires, $description]) {
            $media = (new Media())
                ->setTitre($titre)
                ->setAuteur($auteur)
                ->setType($type)
                ->setGenre($genre)
                ->setAnnee($annee)
                ->setExemplaires($exemplaires)
                ->setDescription($description);

            $manager->persist($media);
            $medias[] = $media;
        }

        $enCours = (new Emprunt())
            ->setUser($user)
            ->setMedia($medias[7]);

        $enRetard = (new Emprunt())
            ->setUser($user)
            ->setMedia($medias[0])
            ->setDateEmprunt(new \DateTimeImmutable('-30 days'))
            ->setDateRetourPrevue(new \DateTimeImmutable('-9 days'));

        $rendu = (new Emprunt())
            ->setUser($admin)
            ->setMedia($medias[10])
            ->setDateEmprunt(new \DateTimeImmutable('-40 days'))
            ->setDateRetourPrevue(new \DateTimeImmutable('-19 days'))
            ->setDateRetour(new \DateTimeImmutable('-20 days'));

        foreach ([$enCours, $enRetard, $rendu] as $emprunt) {
            $manager->persist($emprunt);
        }

        $manager->flush();
    }

    private function creerUser(string $email, string $motDePasse, string $nom, string $prenom, array $roles): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setNom($nom)
            ->setPrenom($prenom)
            ->setRoles($roles)
            ->setDateNaissance(new \DateTimeImmutable('2000-01-01'))
            ->setAdresse('1 place du Martroi')
            ->setCodePostal('45000')
            ->setVille('Orléans');

        $user->setPassword($this->hasher->hashPassword($user, $motDePasse));

        return $user;
    }
}