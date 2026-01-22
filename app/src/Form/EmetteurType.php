<?php

namespace App\Form;

use App\Entity\Emetteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmetteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'attr' => [
                    'placeholder' => 'Ex: ZK, KM...',
                    'class' => 'form-input uppercase',
                    'maxlength' => 20,
                ],
                'help' => 'Code court unique pour identifier l\'emetteur (ex: ZK pour Zephir Kemeo)',
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Ex: Zephir Kemeo',
                    'class' => 'form-input',
                ],
                'help' => 'Nom d\'affichage de l\'emetteur',
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'attr' => [
                    'class' => 'form-checkbox',
                ],
                'help' => 'Un emetteur inactif n\'apparait plus dans les selections',
            ])
            ->add('parDefaut', CheckboxType::class, [
                'label' => 'Emetteur par defaut',
                'required' => false,
                'attr' => [
                    'class' => 'form-checkbox',
                ],
                'help' => 'L\'emetteur par defaut est pre-selectionne pour les nouveaux contrats',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Emetteur::class,
        ]);
    }
}
