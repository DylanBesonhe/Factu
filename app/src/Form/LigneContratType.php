<?php

namespace App\Form;

use App\Entity\LigneContrat;
use App\Entity\Module;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LigneContratType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('module', EntityType::class, [
                'class' => Module::class,
                'choice_label' => 'nom',
                'label' => 'Module',
                'placeholder' => 'Selectionner un module',
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
                'query_builder' => function (\App\Repository\ModuleRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->where('m.actif = :actif')
                        ->setParameter('actif', true)
                        ->orderBy('m.nom', 'ASC');
                },
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
            'data_class' => LigneContrat::class,
        ]);
    }
}
