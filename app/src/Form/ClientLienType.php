<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\ClientLien;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientLienType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentClient = $options['current_client'] ?? null;

        $builder
            ->add('clientCible', EntityType::class, [
                'class' => Client::class,
                'label' => 'Client lie',
                'choice_label' => function (Client $client) {
                    return $client->getCode() . ' - ' . $client->getRaisonSociale();
                },
                'placeholder' => 'Selectionner un client',
                'query_builder' => function ($repository) use ($currentClient) {
                    $qb = $repository->createQueryBuilder('c')
                        ->where('c.actif = :actif')
                        ->setParameter('actif', true)
                        ->orderBy('c.raisonSociale', 'ASC');

                    if ($currentClient) {
                        $qb->andWhere('c.id != :currentId')
                           ->setParameter('currentId', $currentClient->getId());
                    }

                    return $qb;
                },
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de lien',
                'choices' => ClientLien::TYPES,
                'placeholder' => 'Selectionner un type',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClientLien::class,
            'current_client' => null,
        ]);
    }
}
