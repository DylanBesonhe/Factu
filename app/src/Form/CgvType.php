<?php

namespace App\Form;

use App\Entity\Cgv;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CgvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la version',
                'attr' => [
                    'placeholder' => 'Ex: CGV 2026 v1',
                    'class' => 'form-input',
                ],
            ])
            ->add('fichier', FileType::class, [
                'label' => 'Fichier PDF',
                'mapped' => false,
                'required' => $options['require_file'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez telecharger un fichier PDF valide',
                    ])
                ],
                'attr' => [
                    'accept' => '.pdf',
                    'class' => 'form-input',
                ],
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de debut',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-input',
                ],
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin (optionnel)',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-input',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cgv::class,
            'require_file' => true,
        ]);
    }
}
