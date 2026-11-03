<?php

namespace App\Form;

use App\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MediaType extends AbstractType
{
    public const TYPES = ['Roman', 'BD', 'Manga', 'DVD'];

    public const GENRES = [
        'Action', 'Aventure', 'Comédie', 'Drame', 'Fantastique', 'Historique',
        'Horreur', 'Jeunesse', 'Policier', 'Romance', 'Science-fiction', 'Autre',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('auteur', null, ['label' => 'Auteur / Réalisateur'])
            ->add('type', ChoiceType::class, [
                'choices' => array_combine(self::TYPES, self::TYPES),
                'placeholder' => 'Choisir un type',
            ])
            ->add('genre', ChoiceType::class, [
                'choices' => array_combine(self::GENRES, self::GENRES),
                'placeholder' => 'Choisir un genre',
            ])
            ->add('annee', IntegerType::class, ['label' => 'Année'])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('exemplaires', IntegerType::class, [
                'label' => "Nombre d'exemplaires",
                'attr' => ['min' => 1],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image (jpg, png ou webp, 2 Mo max)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Image jpg, png ou webp uniquement',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}