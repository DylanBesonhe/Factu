<?php

namespace App\Form;

use App\Entity\HistoriqueLicence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistoriqueLicenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nbLicences', IntegerType::class, [
                'label' => 'Nombre de licences',
                'attr' => [
                    'min' => 0,
                    'placeholder' => 'Ex: 25',
                ],
            ])
            ->add('dateEffet', DateType::class, [
                'label' => 'Date d\'effet',
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HistoriqueLicence::class,
        ]);
    }
}
