<?php

namespace App\Form;

use App\Entity\EmetteurVersion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmetteurVersionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateEffet', DateType::class, [
                'label' => 'Date d\'effet',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-input',
                ],
                'help' => 'Date a partir de laquelle cette version sera active',
            ])
            ->add('raisonSociale', TextType::class, [
                'label' => 'Raison sociale',
                'attr' => [
                    'placeholder' => 'Ex: Ma Societe SAS',
                    'class' => 'form-input',
                ],
            ])
            ->add('formeJuridique', TextType::class, [
                'label' => 'Forme juridique',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: SAS, SARL, SA...',
                    'class' => 'form-input',
                ],
            ])
            ->add('capital', MoneyType::class, [
                'label' => 'Capital social',
                'required' => false,
                'currency' => 'EUR',
                'attr' => [
                    'placeholder' => 'Ex: 10000',
                    'class' => 'form-input',
                ],
            ])
            ->add('adresse', TextareaType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'Adresse complete de l\'entreprise',
                    'class' => 'form-input',
                    'rows' => 3,
                ],
            ])
            ->add('siren', TextType::class, [
                'label' => 'SIREN',
                'attr' => [
                    'placeholder' => '123456789',
                    'maxlength' => 9,
                    'class' => 'form-input',
                ],
            ])
            ->add('tva', TextType::class, [
                'label' => 'N° TVA intracommunautaire',
                'required' => false,
                'attr' => [
                    'placeholder' => 'FR12345678901',
                    'class' => 'form-input',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'contact@example.com',
                    'class' => 'form-input',
                ],
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Telephone',
                'required' => false,
                'attr' => [
                    'placeholder' => '01 23 45 67 89',
                    'class' => 'form-input',
                ],
            ])
            ->add('iban', TextType::class, [
                'label' => 'IBAN',
                'required' => false,
                'attr' => [
                    'placeholder' => 'FR76 1234 5678 9012 3456 7890 123',
                    'class' => 'form-input',
                ],
            ])
            ->add('bic', TextType::class, [
                'label' => 'BIC',
                'required' => false,
                'attr' => [
                    'placeholder' => 'BNPAFRPP',
                    'maxlength' => 11,
                    'class' => 'form-input',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmetteurVersion::class,
        ]);
    }
}
