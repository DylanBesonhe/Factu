<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Contrat;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class FactureManuelleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'label' => 'Client',
                'placeholder' => 'Selectionnez un client',
                'choice_label' => function (Client $client) {
                    return $client->getCode() . ' - ' . $client->getRaisonSociale();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.actif = :actif')
                        ->setParameter('actif', true)
                        ->orderBy('c.raisonSociale', 'ASC');
                },
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
                'constraints' => [
                    new NotNull(message: 'Le client est obligatoire'),
                ],
                'mapped' => false,
            ])
            ->add('contrat', EntityType::class, [
                'class' => Contrat::class,
                'label' => 'Contrat (optionnel)',
                'placeholder' => 'Aucun contrat',
                'required' => false,
                'choice_label' => function (Contrat $contrat) {
                    return $contrat->getNumero() . ' - ' . $contrat->getClient()?->getRaisonSociale();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.actif = :actif')
                        ->setParameter('actif', true)
                        ->orderBy('c.numero', 'ASC');
                },
                'attr' => ['class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500'],
                'mapped' => false,
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500',
                    'rows' => 3,
                ],
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
