<?php

namespace App\Form;

use App\Entity\Module;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du module',
                'attr' => [
                    'placeholder' => 'Ex: Module Premium',
                    'class' => 'form-input',
                ],
            ])
            ->add('prixDefaut', MoneyType::class, [
                'label' => 'Prix par defaut (HT)',
                'currency' => 'EUR',
                'attr' => [
                    'placeholder' => 'Ex: 100.00',
                    'class' => 'form-input',
                ],
            ])
            ->add('tauxTva', NumberType::class, [
                'label' => 'Taux de TVA (%)',
                'scale' => 2,
                'attr' => [
                    'placeholder' => 'Ex: 20.00',
                    'class' => 'form-input',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description optionnelle du module',
                    'class' => 'form-input',
                    'rows' => 3,
                ],
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Module actif',
                'required' => false,
                'attr' => [
                    'class' => 'form-checkbox',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Module::class,
        ]);
    }
}
