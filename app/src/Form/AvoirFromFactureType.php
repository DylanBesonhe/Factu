<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AvoirFromFactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type d\'avoir',
                'choices' => [
                    'Avoir total (annule la facture)' => 'total',
                    'Avoir partiel' => 'partiel',
                ],
                'expanded' => true,
                'attr' => ['class' => 'space-y-2'],
            ])
            ->add('motif', TextareaType::class, [
                'label' => 'Motif de l\'avoir',
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500',
                    'rows' => 3,
                    'placeholder' => 'Ex: Erreur de facturation, Annulation de la prestation...',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le motif est obligatoire'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
