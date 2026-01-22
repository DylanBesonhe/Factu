<?php

namespace App\Form;

use App\Entity\LigneFacture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LigneFactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation', TextType::class, [
                'label' => 'Designation',
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500',
                    'rows' => 2,
                ],
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantite',
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500',
                    'min' => 1,
                ],
            ])
            ->add('prixUnitaire', NumberType::class, [
                'label' => 'Prix unitaire HT',
                'scale' => 2,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500',
                    'min' => 0,
                    'step' => 0.01,
                ],
            ])
            ->add('remise', NumberType::class, [
                'label' => 'Remise (%)',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500',
                    'min' => 0,
                    'max' => 100,
                    'step' => 0.01,
                ],
            ])
            ->add('tauxTva', NumberType::class, [
                'label' => 'Taux TVA (%)',
                'scale' => 2,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500',
                    'min' => 0,
                    'max' => 100,
                    'step' => 0.01,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LigneFacture::class,
        ]);
    }
}
