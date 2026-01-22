<?php

namespace App\Repository;

use App\Entity\Emetteur;
use App\Entity\ParametreFacturation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParametreFacturation>
 */
class ParametreFacturationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParametreFacturation::class);
    }

    public function findByEmetteur(Emetteur $emetteur): ?ParametreFacturation
    {
        return $this->createQueryBuilder('p')
            ->where('p.emetteur = :emetteur')
            ->setParameter('emetteur', $emetteur)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Recupere ou cree les parametres pour un emetteur
     */
    public function getOrCreateForEmetteur(Emetteur $emetteur): ParametreFacturation
    {
        $parametres = $this->findByEmetteur($emetteur);

        if (!$parametres) {
            $parametres = new ParametreFacturation();
            $parametres->setEmetteur($emetteur);
            // Format par defaut avec le code de l'emetteur
            $parametres->setFormatNumero('{CODE}-{YYYY}-{SEQ:5}');
        }

        return $parametres;
    }
}
