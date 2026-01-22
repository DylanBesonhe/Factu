<?php

namespace App\Repository;

use App\Entity\Contrat;
use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Facture>
 */
class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

    /**
     * Recherche les factures par statut avec pagination
     */
    public function findByStatut(string $statut, int $page = 1, int $limit = 20): Paginator
    {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin('f.contrat', 'c')
            ->addSelect('c')
            ->where('f.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('f.dateFacture', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb);
    }

    /**
     * Retourne les factures en brouillon
     * @param \DateTimeInterface|null $moisReference Si fourni, filtre les factures dont la periode contient ce mois
     * @return Facture[]
     */
    public function findBrouillons(?\DateTimeInterface $moisReference = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin('f.contrat', 'c')
            ->addSelect('c')
            ->where('f.statut = :statut')
            ->setParameter('statut', Facture::STATUT_BROUILLON)
            ->orderBy('f.createdAt', 'DESC');

        if ($moisReference !== null) {
            // Filtrer: le mois de periodeDebut doit correspondre au mois selectionne
            $debutMois = new \DateTime($moisReference->format('Y-m-01'));
            $finMois = (clone $debutMois)->modify('last day of this month');
            $qb->andWhere('f.periodeDebut >= :debutMois AND f.periodeDebut <= :finMois')
                ->setParameter('debutMois', $debutMois)
                ->setParameter('finMois', $finMois);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les factures validees
     * @param \DateTimeInterface|null $moisReference Si fourni, filtre les factures dont la periode contient ce mois
     * @return Facture[]
     */
    public function findValidees(?\DateTimeInterface $moisReference = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin('f.contrat', 'c')
            ->addSelect('c')
            ->where('f.statut = :statut')
            ->setParameter('statut', Facture::STATUT_VALIDEE)
            ->orderBy('f.dateValidation', 'DESC');

        if ($moisReference !== null) {
            // Filtrer: le mois de periodeDebut doit correspondre au mois selectionne
            $debutMois = new \DateTime($moisReference->format('Y-m-01'));
            $finMois = (clone $debutMois)->modify('last day of this month');
            $qb->andWhere('f.periodeDebut >= :debutMois AND f.periodeDebut <= :finMois')
                ->setParameter('debutMois', $debutMois)
                ->setParameter('finMois', $finMois);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Recherche les factures avec filtres et pagination
     */
    public function searchWithFilters(
        ?string $search = null,
        ?string $statut = null,
        ?int $contratId = null,
        string $sort = 'dateFacture',
        string $direction = 'DESC',
        int $page = 1,
        int $limit = 20
    ): Paginator {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin('f.contrat', 'c')
            ->leftJoin('f.lignes', 'l')
            ->addSelect('c', 'l');

        if ($search) {
            $qb->andWhere('f.numero LIKE :search OR f.clientRaisonSociale LIKE :search OR f.clientCode LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($statut !== null) {
            $qb->andWhere('f.statut = :statut')
                ->setParameter('statut', $statut);
        }

        if ($contratId !== null) {
            $qb->andWhere('f.contrat = :contratId')
                ->setParameter('contratId', $contratId);
        }

        $allowedSorts = ['numero', 'dateFacture', 'dateEcheance', 'totalTtc', 'statut', 'createdAt'];
        if (in_array($sort, $allowedSorts)) {
            $qb->orderBy('f.' . $sort, $direction === 'DESC' ? 'DESC' : 'ASC');
        } elseif ($sort === 'client') {
            $qb->orderBy('f.clientRaisonSociale', $direction === 'DESC' ? 'DESC' : 'ASC');
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb);
    }

    /**
     * Trouve une facture existante pour un contrat et une periode
     */
    public function findByContratAndPeriod(Contrat $contrat, \DateTimeInterface $debut, \DateTimeInterface $fin): ?Facture
    {
        return $this->createQueryBuilder('f')
            ->where('f.contrat = :contrat')
            ->andWhere('f.periodeDebut = :debut')
            ->andWhere('f.periodeFin = :fin')
            ->setParameter('contrat', $contrat)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Verifie si une facture existe pour un contrat sur une periode donnee
     */
    public function existsForContratAndPeriod(Contrat $contrat, \DateTimeInterface $debut, \DateTimeInterface $fin): bool
    {
        $result = $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.contrat = :contrat')
            ->andWhere('f.periodeDebut <= :fin')
            ->andWhere('f.periodeFin >= :debut')
            ->setParameter('contrat', $contrat)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result > 0;
    }

    /**
     * Compte les factures par statut
     * @return array<string, int>
     */
    public function countByStatut(): array
    {
        $result = $this->createQueryBuilder('f')
            ->select('f.statut, COUNT(f.id) as count')
            ->groupBy('f.statut')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($result as $row) {
            $counts[$row['statut']] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * Retourne les factures d'un contrat
     * @return Facture[]
     */
    public function findByContrat(Contrat $contrat): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.contrat = :contrat')
            ->setParameter('contrat', $contrat)
            ->orderBy('f.dateFacture', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les statistiques de facturation
     * @return array{totalHt: string, totalTtc: string, nbFactures: int, nbPayees: int}
     */
    public function getStatistiques(\DateTimeInterface $debut = null, \DateTimeInterface $fin = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->select('SUM(f.totalHt) as totalHt, SUM(f.totalTtc) as totalTtc, COUNT(f.id) as nbFactures')
            ->where('f.statut != :brouillon')
            ->setParameter('brouillon', Facture::STATUT_BROUILLON);

        if ($debut !== null) {
            $qb->andWhere('f.dateFacture >= :debut')
                ->setParameter('debut', $debut);
        }

        if ($fin !== null) {
            $qb->andWhere('f.dateFacture <= :fin')
                ->setParameter('fin', $fin);
        }

        $result = $qb->getQuery()->getSingleResult();

        // Compter les factures payees
        $qbPayees = $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.statut = :payee')
            ->setParameter('payee', Facture::STATUT_PAYEE);

        if ($debut !== null) {
            $qbPayees->andWhere('f.dateFacture >= :debut')
                ->setParameter('debut', $debut);
        }

        if ($fin !== null) {
            $qbPayees->andWhere('f.dateFacture <= :fin')
                ->setParameter('fin', $fin);
        }

        $nbPayees = (int) $qbPayees->getQuery()->getSingleScalarResult();

        return [
            'totalHt' => $result['totalHt'] ?? '0.00',
            'totalTtc' => $result['totalTtc'] ?? '0.00',
            'nbFactures' => (int) ($result['nbFactures'] ?? 0),
            'nbPayees' => $nbPayees,
        ];
    }
}
