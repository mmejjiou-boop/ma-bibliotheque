<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PreferencesType extends AbstractType
{
    public const THEMES = [
        '☀️ Clair' => 'clair',
        '🌙 Sombre' => 'sombre',
        '💻 Automatique' => 'auto',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('photoFile', FileType::class, [
                'label' => 'Photo de profil (jpg, png ou webp, 2 Mo max)',
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
            ->add('theme', ChoiceType::class, [
                'label' => 'Thème',
                'choices' => self::THEMES,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
