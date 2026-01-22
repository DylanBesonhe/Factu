<?php

namespace App\Form;

use App\Entity\ParametreFacturation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParametreFacturationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('formatNumero', TextType::class, [
                'label' => 'Format du numero de facture',
                'attr' => [
                    'placeholder' => '{CODE}-{YYYY}-{SEQ:5}',
                    'class' => 'form-input',
                ],
                'help' => 'Variables: {CODE}=code emetteur, {YYYY}=annee, {YY}=annee courte, {MM}=mois, {SEQ:N}=sequence sur N chiffres, {SIREN}=SIREN',
            ])
            ->add('prochainNumero', IntegerType::class, [
                'label' => 'Prochain numero sequentiel',
                'attr' => [
                    'min' => 1,
                    'class' => 'form-input',
                ],
            ])
            ->add('delaiEcheance', IntegerType::class, [
                'label' => 'Delai d\'echeance (jours)',
                'attr' => [
                    'min' => 1,
                    'class' => 'form-input',
                ],
            ])
            ->add('mentionsLegales', TextareaType::class, [
                'label' => 'Mentions legales supplementaires',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Mentions a afficher sur les factures...',
                    'class' => 'form-input',
                    'rows' => 3,
                ],
            ])
            ->add('emailObjet', TextType::class, [
                'label' => 'Objet de l\'email',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Facture {NUMERO} - {CLIENT}',
                    'class' => 'form-input',
                ],
                'help' => 'Variables: {NUMERO}, {CLIENT}, {MONTANT}',
            ])
            ->add('emailCorps', TextareaType::class, [
                'label' => 'Corps de l\'email',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Contenu de l\'email d\'envoi de facture...',
                    'class' => 'form-input',
                    'rows' => 6,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParametreFacturation::class,
        ]);
    }
}
