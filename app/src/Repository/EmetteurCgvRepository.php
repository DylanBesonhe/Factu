<?php

namespace App\Repository;

use App\Entity\Cgv;
use App\Entity\Emetteur;
use App\Entity\EmetteurCgv;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmetteurCgv>
 */
class EmetteurCgvRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmetteurCgv::class);
    }

    /**
     * @return EmetteurCgv[]
     */
    public function findByEmetteur(Emetteur $emetteur): array
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.cgv', 'c')
            ->where('ec.emetteur = :emetteur')
            ->setParameter('emetteur', $emetteur)
            ->orderBy('c.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findDefautForEmetteur(Emetteur $emetteur): ?EmetteurCgv
    {
        return $this->createQueryBuilder('ec')
            ->where('ec.emetteur = :emetteur')
            ->andWhere('ec.parDefaut = :parDefaut')
            ->setParameter('emetteur', $emetteur)
            ->setParameter('parDefaut', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByEmetteurAndCgv(Emetteur $emetteur, Cgv $cgv): ?EmetteurCgv
    {
        return $this->createQueryBuilder('ec')
            ->where('ec.emetteur = :emetteur')
            ->andWhere('ec.cgv = :cgv')
            ->setParameter('emetteur', $emetteur)
            ->setParameter('cgv', $cgv)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Reset le defaut pour un emetteur
     */
    public function resetParDefaut(Emetteur $emetteur, ?int $exceptId = null): void
    {
        $qb = $this->createQueryBuilder('ec')
            ->update()
            ->set('ec.parDefaut', ':false')
            ->where('ec.emetteur = :emetteur')
            ->setParameter('false', false)
            ->setParameter('emetteur', $emetteur);

        if ($exceptId !== null) {
            $qb->andWhere('ec.id != :id')
                ->setParameter('id', $exceptId);
        }

        $qb->getQuery()->execute();
    }

    /**
     * Compte le nombre d'emetteurs associes a une CGV
     */
    public function countEmetteursByCgv(Cgv $cgv): int
    {
        return (int) $this->createQueryBuilder('ec')
            ->select('COUNT(ec.id)')
            ->where('ec.cgv = :cgv')
            ->setParameter('cgv', $cgv)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return string[] Codes des emetteurs associes a une CGV
     */
    public function findEmetteurCodesByCgv(Cgv $cgv): array
    {
        $results = $this->createQueryBuilder('ec')
            ->select('e.code')
            ->join('ec.emetteur', 'e')
            ->where('ec.cgv = :cgv')
            ->setParameter('cgv', $cgv)
            ->orderBy('e.code', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($results, 'code');
    }
}
