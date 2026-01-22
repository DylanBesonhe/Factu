<?php

namespace App\Form;

use App\Entity\ContratEvenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContratEvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type d\'evenement',
                'choices' => [
                    'Creation' => ContratEvenement::TYPE_CREATION,
                    'Modification' => ContratEvenement::TYPE_MODIFICATION,
                    'Renouvellement' => ContratEvenement::TYPE_RENOUVELLEMENT,
                    'Suspension' => ContratEvenement::TYPE_SUSPENSION,
                    'Resiliation' => ContratEvenement::TYPE_RESILIATION,
                    'Ajout de module' => ContratEvenement::TYPE_AJOUT_MODULE,
                    'Retrait de module' => ContratEvenement::TYPE_RETRAIT_MODULE,
                    'Changement de tarif' => ContratEvenement::TYPE_CHANGEMENT_TARIF,
                    'Autre' => ContratEvenement::TYPE_AUTRE,
                ],
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500',
                    'rows' => 3,
                ],
            ])
            ->add('dateEffet', DateType::class, [
                'label' => 'Date d\'effet',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContratEvenement::class,
        ]);
    }
}
