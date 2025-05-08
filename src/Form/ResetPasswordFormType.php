<?php

// src/Form/ResetPasswordFormType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('password', PasswordType::class, [
            'label' => 'Mot de passe',
            'constraints' => [
                new NotBlank(['message' => 'Le mot de passe est obligatoire.']),
                new Length([
                    'min' => 8,
                    'max' => 255,
                    'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                    'maxMessage' => 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.',
                ]),
                new Regex([
                    'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
                    'message' => 'Le mot de passe doit contenir au moins une lettre et un chiffre.',
                ]),
            ],
        ])
            ->add('submit', SubmitType::class, [
                'label' => 'Réinitialiser le mot de passe'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
