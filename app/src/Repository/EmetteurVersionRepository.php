<?php

namespace App\Repository;

use App\Entity\Emetteur;
use App\Entity\EmetteurVersion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmetteurVersion>
 */
class EmetteurVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmetteurVersion::class);
    }

    /**
     * @return EmetteurVersion[]
     */
    public function findByEmetteur(Emetteur $emetteur): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.emetteur = :emetteur')
            ->setParameter('emetteur', $emetteur)
            ->orderBy('v.dateEffet', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveForEmetteur(Emetteur $emetteur, ?\DateTimeInterface $date = null): ?EmetteurVersion
    {
        $date = $date ?? new \DateTime();

        return $this->createQueryBuilder('v')
            ->where('v.emetteur = :emetteur')
            ->andWhere('v.dateEffet <= :date')
            ->andWhere('v.dateFin IS NULL OR v.dateFin >= :date')
            ->setParameter('emetteur', $emetteur)
            ->setParameter('date', $date)
            ->orderBy('v.dateEffet', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestForEmetteur(Emetteur $emetteur): ?EmetteurVersion
    {
        return $this->createQueryBuilder('v')
            ->where('v.emetteur = :emetteur')
            ->setParameter('emetteur', $emetteur)
            ->orderBy('v.dateEffet', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Clot la version precedente en mettant une dateFin
     */
    public function closeVersionsBefore(Emetteur $emetteur, \DateTimeInterface $newDateEffet): void
    {
        $dateFin = (clone $newDateEffet)->modify('-1 day');

        $this->createQueryBuilder('v')
            ->update()
            ->set('v.dateFin', ':dateFin')
            ->where('v.emetteur = :emetteur')
            ->andWhere('v.dateFin IS NULL')
            ->andWhere('v.dateEffet < :newDateEffet')
            ->setParameter('dateFin', $dateFin)
            ->setParameter('emetteur', $emetteur)
            ->setParameter('newDateEffet', $newDateEffet)
            ->getQuery()
            ->execute();
    }
}
