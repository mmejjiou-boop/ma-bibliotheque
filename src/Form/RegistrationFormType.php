<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom', null, ['label' => 'Prénom'])
            ->add('email')
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('telephone', null, [
                'label' => 'Téléphone (facultatif)',
                'required' => false,
            ])
            ->add('adresse')
            ->add('codePostal', null, ['label' => 'Code postal'])
            ->add('ville')
            // mot de passe en clair : non mappé, il est haché dans le contrôleur
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(message: 'Entrez un mot de passe'),
                    new Length(
                        min: 6,
                        minMessage: 'Le mot de passe doit faire au moins {{ limit }} caractères',
                        max: 4096,
                    ),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => "J'accepte les conditions d'utilisation",
                'mapped' => false,
                'constraints' => [
                    new IsTrue(message: 'Vous devez accepter les conditions.'),
                ],
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