<?php

namespace App\Repository;

use App\Entity\Instance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Instance>
 */
class InstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Instance::class);
    }

    /**
     * Recherche les instances par nom
     * @return Instance[]
     */
    public function searchByNom(string $search): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.nomActuel LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('i.nomActuel', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne toutes les instances actives
     * @return Instance[]
     */
    public function findActives(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('i.nomActuel', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une instance par son nom (actuel ou ancien)
     */
    public function findByNom(string $nom): ?Instance
    {
        $instance = $this->findOneBy(['nomActuel' => $nom]);

        if ($instance !== null) {
            return $instance;
        }

        $qb = $this->createQueryBuilder('i')
            ->join('i.historiqueNoms', 'h')
            ->where('h.ancienNom = :nom')
            ->setParameter('nom', $nom)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
