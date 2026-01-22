<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function save(Client $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Client $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Paginator<Client>
     */
    public function searchWithFilters(
        ?string $search = null,
        ?bool $actif = null,
        string $sort = 'raisonSociale',
        string $direction = 'ASC',
        int $page = 1,
        int $limit = 20
    ): Paginator {
        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.code LIKE :search OR c.raisonSociale LIKE :search OR c.siren LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($actif !== null) {
            $qb->andWhere('c.actif = :actif')
               ->setParameter('actif', $actif);
        }

        $allowedSorts = ['code', 'raisonSociale', 'createdAt', 'siren'];
        if (in_array($sort, $allowedSorts)) {
            $qb->orderBy('c.' . $sort, $direction === 'DESC' ? 'DESC' : 'ASC');
        } else {
            $qb->orderBy('c.raisonSociale', 'ASC');
        }

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return new Paginator($qb);
    }

    /**
     * @return Client[]
     */
    public function findAllForExport(?string $search = null, ?bool $actif = null): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.code LIKE :search OR c.raisonSociale LIKE :search OR c.siren LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($actif !== null) {
            $qb->andWhere('c.actif = :actif')
               ->setParameter('actif', $actif);
        }

        $qb->orderBy('c.raisonSociale', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Client[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('c.raisonSociale', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Client[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.raisonSociale', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Client[]
     */
    public function findAllExcept(Client $client): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id != :clientId')
            ->andWhere('c.actif = :actif')
            ->setParameter('clientId', $client->getId())
            ->setParameter('actif', true)
            ->orderBy('c.raisonSociale', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
