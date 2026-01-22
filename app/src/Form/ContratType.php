<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Contrat;
use App\Entity\Emetteur;
use App\Entity\Instance;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContratType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero', TextType::class, [
                'label' => 'Numero de contrat',
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => function (Client $client) {
                    return $client->getCode() . ' - ' . $client->getRaisonSociale();
                },
                'label' => 'Client',
                'placeholder' => 'Selectionner un client',
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
                'query_builder' => function (\App\Repository\ClientRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.actif = :actif')
                        ->setParameter('actif', true)
                        ->orderBy('c.raisonSociale', 'ASC');
                },
            ])
            ->add('instance', EntityType::class, [
                'class' => Instance::class,
                'choice_label' => 'nomActuel',
                'label' => 'Instance',
                'placeholder' => 'Selectionner une instance',
                'required' => true,
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
                'query_builder' => function (\App\Repository\InstanceRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->where('i.actif = :actif')
                        ->setParameter('actif', true)
                        ->orderBy('i.nomActuel', 'ASC');
                },
            ])
            ->add('emetteur', EntityType::class, [
                'class' => Emetteur::class,
                'choice_label' => 'nom',
                'label' => 'Emetteur',
                'placeholder' => 'Selectionner un emetteur',
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
                'query_builder' => function (\App\Repository\EmetteurRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.actif = :actif')
                        ->setParameter('actif', true)
                        ->orderBy('e.nom', 'ASC');
                },
            ])
            ->add('dateSignature', DateType::class, [
                'label' => 'Date de signature',
                'widget' => 'single_text',
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
            ])
            ->add('dateAnniversaire', DateType::class, [
                'label' => 'Date anniversaire',
                'widget' => 'single_text',
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
            ])
            ->add('periodicite', ChoiceType::class, [
                'label' => 'Periodicite de facturation',
                'choices' => [
                    'Mensuelle' => Contrat::PERIODICITE_MENSUELLE,
                    'Trimestrielle' => Contrat::PERIODICITE_TRIMESTRIELLE,
                    'Annuelle' => Contrat::PERIODICITE_ANNUELLE,
                ],
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Actif' => Contrat::STATUT_ACTIF,
                    'Suspendu' => Contrat::STATUT_SUSPENDU,
                    'Resilie' => Contrat::STATUT_RESILIE,
                ],
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
            ])
            ->add('factureParticuliere', CheckboxType::class, [
                'label' => 'Facture particuliere (necessite attention)',
                'required' => false,
                'attr' => ['class' => 'w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500'],
            ])
            ->add('commentaireFacture', TextareaType::class, [
                'label' => 'Commentaire facture',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500',
                    'rows' => 3,
                ],
            ])
            ->add('remiseGlobale', NumberType::class, [
                'label' => 'Remise globale (%)',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500',
                    'min' => 0,
                    'max' => 100,
                    'step' => 0.01,
                ],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes internes',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500',
                    'rows' => 4,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contrat::class,
        ]);
    }
}
