<?php

namespace App\Repository;

use App\Entity\Contrat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contrat>
 */
class ContratRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contrat::class);
    }

    /**
     * Recherche les contrats avec filtres et pagination
     */
    public function searchWithFilters(
        ?string $search = null,
        ?string $statut = null,
        ?int $clientId = null,
        ?int $emetteurId = null,
        string $sort = 'numero',
        string $direction = 'ASC',
        int $page = 1,
        int $limit = 20
    ): Paginator {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'cl')
            ->leftJoin('c.instance', 'i')
            ->leftJoin('c.emetteur', 'e')
            ->addSelect('cl', 'i', 'e');

        if ($search) {
            $qb->andWhere('c.numero LIKE :search OR cl.raisonSociale LIKE :search OR cl.code LIKE :search OR i.nomActuel LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($statut !== null) {
            $qb->andWhere('c.statut = :statut')
                ->setParameter('statut', $statut);
        }

        if ($clientId !== null) {
            $qb->andWhere('c.client = :clientId')
                ->setParameter('clientId', $clientId);
        }

        if ($emetteurId !== null) {
            $qb->andWhere('c.emetteur = :emetteurId')
                ->setParameter('emetteurId', $emetteurId);
        }

        $allowedSorts = ['numero', 'dateSignature', 'dateAnniversaire', 'statut', 'createdAt'];
        if (in_array($sort, $allowedSorts)) {
            $qb->orderBy('c.' . $sort, $direction === 'DESC' ? 'DESC' : 'ASC');
        } elseif ($sort === 'client') {
            $qb->orderBy('cl.raisonSociale', $direction === 'DESC' ? 'DESC' : 'ASC');
        } elseif ($sort === 'instance') {
            $qb->orderBy('i.nomActuel', $direction === 'DESC' ? 'DESC' : 'ASC');
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb);
    }

    /**
     * Retourne tous les contrats pour l'export CSV
     * @return Contrat[]
     */
    public function findAllForExport(?string $search = null, ?string $statut = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'cl')
            ->leftJoin('c.instance', 'i')
            ->leftJoin('c.emetteur', 'e')
            ->leftJoin('c.lignes', 'l')
            ->addSelect('cl', 'i', 'e', 'l')
            ->orderBy('c.numero', 'ASC');

        if ($search) {
            $qb->andWhere('c.numero LIKE :search OR cl.raisonSociale LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($statut !== null) {
            $qb->andWhere('c.statut = :statut')
                ->setParameter('statut', $statut);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les contrats actifs d'un client
     * @return Contrat[]
     */
    public function findActifsByClient(int $clientId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.client = :clientId')
            ->andWhere('c.statut = :statut')
            ->setParameter('clientId', $clientId)
            ->setParameter('statut', Contrat::STATUT_ACTIF)
            ->orderBy('c.dateSignature', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les contrats a facturer pour une periode
     * @return Contrat[]
     */
    public function findAFacturer(\DateTimeInterface $date, string $periodicite): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'cl')
            ->leftJoin('c.lignes', 'l')
            ->addSelect('cl', 'l')
            ->where('c.statut = :statut')
            ->andWhere('c.periodicite = :periodicite')
            ->andWhere('c.dateDebutFacturation <= :date')
            ->andWhere('c.dateFin IS NULL OR c.dateFin >= :date')
            ->setParameter('statut', Contrat::STATUT_ACTIF)
            ->setParameter('periodicite', $periodicite)
            ->setParameter('date', $date)
            ->orderBy('c.numero', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les contrats avec facture particuliere
     * @return Contrat[]
     */
    public function findFacturesParticulieres(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'cl')
            ->addSelect('cl')
            ->where('c.statut = :statut')
            ->andWhere('c.factureParticuliere = :particuliere')
            ->setParameter('statut', Contrat::STATUT_ACTIF)
            ->setParameter('particuliere', true)
            ->orderBy('c.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les contrats par statut
     * @return array<string, int>
     */
    public function countByStatut(): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('c.statut, COUNT(c.id) as count')
            ->groupBy('c.statut')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($result as $row) {
            $counts[$row['statut']] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * Genere le prochain numero de contrat
     */
    public function getNextNumero(string $prefix = 'CTR'): string
    {
        $year = date('Y');
        $pattern = $prefix . '-' . $year . '-%';

        $result = $this->createQueryBuilder('c')
            ->select('c.numero')
            ->where('c.numero LIKE :pattern')
            ->setParameter('pattern', $pattern)
            ->orderBy('c.numero', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null) {
            return $prefix . '-' . $year . '-0001';
        }

        $lastNumber = $result['numero'];
        $parts = explode('-', $lastNumber);
        $seq = (int) end($parts);

        return $prefix . '-' . $year . '-' . str_pad($seq + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Retourne les statistiques pour le dashboard (optimise avec SQL)
     * @return array{caMensuel: string, caAnnuel: string, contratsARenouveler: int}
     */
    public function getStatistiques(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $now = new \DateTime();

        // Calcul du CA par periodicite en une seule requete SQL
        $sql = "
            SELECT
                c.periodicite,
                SUM(l.quantite * l.prix_unitaire * (1 - COALESCE(l.remise, 0) / 100)) as total_ht
            FROM contrat c
            LEFT JOIN ligne_contrat l ON l.contrat_id = c.id
            WHERE c.statut = :statut
            AND (c.date_fin IS NULL OR c.date_fin >= :now)
            GROUP BY c.periodicite
        ";

        $result = $conn->executeQuery($sql, [
            'statut' => Contrat::STATUT_ACTIF,
            'now' => $now->format('Y-m-d'),
        ])->fetchAllAssociative();

        $caAnnuel = '0.00';
        foreach ($result as $row) {
            $totalHt = $row['total_ht'] ?? '0';
            $multiplicateur = match ($row['periodicite']) {
                Contrat::PERIODICITE_MENSUELLE => 12,
                Contrat::PERIODICITE_TRIMESTRIELLE => 4,
                Contrat::PERIODICITE_ANNUELLE => 1,
                default => 1,
            };
            $caAnnuel = bcadd($caAnnuel, bcmul($totalHt, (string) $multiplicateur, 2), 2);
        }

        $caMensuel = bcdiv($caAnnuel, '12', 2);

        // Contrats a renouveler (anniversaire dans les 30 prochains jours)
        $in30Days = (clone $now)->modify('+30 days');

        $sqlRenouveler = "
            SELECT COUNT(*) as count
            FROM contrat c
            WHERE c.statut = :statut
            AND (c.date_fin IS NULL OR c.date_fin >= :now)
            AND (
                (MONTH(c.date_anniversaire) = :currentMonth AND DAY(c.date_anniversaire) >= :currentDay)
                OR (MONTH(c.date_anniversaire) = :nextMonth AND DAY(c.date_anniversaire) <= :nextDay)
                OR (MONTH(c.date_anniversaire) > :currentMonth AND MONTH(c.date_anniversaire) < :nextMonth)
            )
        ";

        // Simplifier: compter les anniversaires entre maintenant et +30 jours
        $contratsARenouveler = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.statut = :statut')
            ->andWhere('c.dateFin IS NULL OR c.dateFin >= :now')
            ->setParameter('statut', Contrat::STATUT_ACTIF)
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        // Pour les renouvellements, on garde une logique simple
        $contratsActifs = $this->createQueryBuilder('c')
            ->select('c.dateAnniversaire')
            ->where('c.statut = :statut')
            ->andWhere('c.dateFin IS NULL OR c.dateFin >= :now')
            ->andWhere('c.dateAnniversaire IS NOT NULL')
            ->setParameter('statut', Contrat::STATUT_ACTIF)
            ->setParameter('now', $now)
            ->getQuery()
            ->getArrayResult();

        $contratsARenouveler = 0;
        foreach ($contratsActifs as $row) {
            $anniversaire = $row['dateAnniversaire'];
            if ($anniversaire === null) {
                continue;
            }

            $anniversaireThisYear = \DateTime::createFromFormat(
                'Y-m-d',
                $now->format('Y') . '-' . $anniversaire->format('m-d')
            );

            if ($anniversaireThisYear >= $now && $anniversaireThisYear <= $in30Days) {
                $contratsARenouveler++;
            }
        }

        return [
            'caMensuel' => $caMensuel,
            'caAnnuel' => $caAnnuel,
            'contratsARenouveler' => $contratsARenouveler,
        ];
    }
}
