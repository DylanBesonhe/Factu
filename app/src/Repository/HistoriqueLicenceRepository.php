<?php

namespace App\Repository;

use App\Entity\HistoriqueLicence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoriqueLicence>
 */
class HistoriqueLicenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriqueLicence::class);
    }

    /**
     * Retourne l'historique des 12 derniers mois pour un contrat
     * @return HistoriqueLicence[]
     */
    public function findLast12Months(int $contratId): array
    {
        $dateLimit = (new \DateTime())->modify('-12 months');

        return $this->createQueryBuilder('h')
            ->where('h.contrat = :contratId')
            ->andWhere('h.dateEffet >= :dateLimit')
            ->setParameter('contratId', $contratId)
            ->setParameter('dateLimit', $dateLimit)
            ->orderBy('h.dateEffet', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
