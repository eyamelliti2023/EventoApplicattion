<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type de réclamation',
                'choices' => [
                    'Événement problématique' => 'BAD_EVENT',
                    'Demande de remboursement' => 'REFUND_REQUEST',
                    'Problème technique' => 'TECHNICAL_ISSUE',
                    'Autre' => 'OTHER',
                ],
                'placeholder' => 'Choisir...',
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Détail du message',
                'attr' => [
                    'placeholder' => 'Décrivez votre problème...',
                    'rows' => 5,
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image à l\'appui (facultative)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier image valide.',
                    ])
                ]
            ]);

        if ($options['is_admin']) {
            $builder
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut',
                    'choices' => [
                        'En attente' => 'PENDING',
                        'Répondu' => 'REPLIED',
                        'Clôturé' => 'DELIVERED',
                    ]
                ])
                ->add('response', TextareaType::class, [
                    'label' => 'Réponse',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Réponse (facultative)',
                        'rows' => 4,
                    ]
                ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'Enregistrer'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
            'is_admin' => false 
        ]);
    }
}
