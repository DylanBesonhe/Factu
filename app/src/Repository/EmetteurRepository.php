<?php

namespace App\Repository;

use App\Entity\Emetteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Emetteur>
 */
class EmetteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emetteur::class);
    }

    /**
     * @return Emetteur[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Emetteur[]
     */
    public function findActifs(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findParDefaut(): ?Emetteur
    {
        return $this->createQueryBuilder('e')
            ->where('e.parDefaut = :parDefaut')
            ->setParameter('parDefaut', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByCode(string $code): ?Emetteur
    {
        return $this->createQueryBuilder('e')
            ->where('e.code = :code')
            ->setParameter('code', strtoupper($code))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Reset tous les emetteurs par defaut sauf celui specifie
     */
    public function resetParDefaut(?int $exceptId = null): void
    {
        $qb = $this->createQueryBuilder('e')
            ->update()
            ->set('e.parDefaut', ':false')
            ->setParameter('false', false);

        if ($exceptId !== null) {
            $qb->where('e.id != :id')
                ->setParameter('id', $exceptId);
        }

        $qb->getQuery()->execute();
    }
}
