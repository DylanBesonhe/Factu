<?php

namespace App\Repository;

use App\Entity\Facture;
use App\Entity\LigneFacture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LigneFacture>
 */
class LigneFactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LigneFacture::class);
    }

    /**
     * Retourne les lignes d'une facture
     * @return LigneFacture[]
     */
    public function findByFacture(Facture $facture): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.facture = :facture')
            ->setParameter('facture', $facture)
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
