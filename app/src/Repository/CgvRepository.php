<?php

namespace App\Repository;

use App\Entity\Cgv;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cgv>
 */
class CgvRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cgv::class);
    }

    /**
     * @return Cgv[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Cgv[]
     */
    public function findActives(): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('c')
            ->where('c.dateDebut <= :now')
            ->andWhere('c.dateFin IS NULL OR c.dateFin >= :now')
            ->setParameter('now', $now)
            ->orderBy('c.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Cgv[] CGV non encore associees a l'emetteur donne
     */
    public function findNotAssociatedToEmetteur(int $emetteurId): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('App\Entity\EmetteurCgv', 'ec', 'WITH', 'ec.cgv = c AND ec.emetteur = :emetteurId')
            ->where('ec.id IS NULL')
            ->setParameter('emetteurId', $emetteurId)
            ->orderBy('c.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
