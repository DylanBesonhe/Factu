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
     * Retourne les statistiques pour le dashboard
     * @return array{caMensuel: string, caAnnuel: string, contratsARenouveler: int}
     */
    public function getStatistiques(): array
    {
        $contratsActifs = $this->createQueryBuilder('c')
            ->leftJoin('c.lignes', 'l')
            ->addSelect('l')
            ->where('c.statut = :statut')
            ->andWhere('c.dateFin IS NULL OR c.dateFin >= :now')
            ->setParameter('statut', Contrat::STATUT_ACTIF)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();

        $caMensuel = '0.00';
        $caAnnuel = '0.00';

        foreach ($contratsActifs as $contrat) {
            $totalHt = $contrat->getTotalHt();

            // CA annuel = somme des totaux selon périodicité
            $multiplicateur = match ($contrat->getPeriodicite()) {
                Contrat::PERIODICITE_MENSUELLE => 12,
                Contrat::PERIODICITE_TRIMESTRIELLE => 4,
                Contrat::PERIODICITE_ANNUELLE => 1,
                default => 1,
            };
            $caAnnuel = bcadd($caAnnuel, bcmul($totalHt, (string) $multiplicateur, 2), 2);

            // CA mensuel = total annuel / 12
            $caMensuel = bcadd($caMensuel, bcdiv(bcmul($totalHt, (string) $multiplicateur, 2), '12', 2), 2);
        }

        // Contrats à renouveler (anniversaire dans les 30 prochains jours)
        $contratsARenouveler = 0;
        $now = new \DateTime();
        $in30Days = (new \DateTime())->modify('+30 days');

        foreach ($contratsActifs as $contrat) {
            $anniversaire = $contrat->getDateAnniversaire();
            if ($anniversaire === null) {
                continue;
            }

            // Normaliser l'anniversaire à l'année courante pour comparaison
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
