<?php

namespace App\Service;

use App\Entity\Contrat;
use App\Entity\Facture;
use App\Entity\LigneFacture;
use App\Repository\ContratRepository;
use App\Repository\FactureRepository;
use App\Repository\ParametreFacturationRepository;
use Doctrine\ORM\EntityManagerInterface;

class FacturationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ContratRepository $contratRepository,
        private FactureRepository $factureRepository,
        private ParametreFacturationRepository $parametreFacturationRepository
    ) {
    }

    /**
     * Trouve les contrats a facturer a une date donnee
     * @return array{mensuelle: Contrat[], trimestrielle: Contrat[], annuelle: Contrat[]}
     */
    public function findContratsAFacturer(?\DateTimeInterface $date = null): array
    {
        $date = $date ?? new \DateTime();

        $result = [
            Contrat::PERIODICITE_MENSUELLE => [],
            Contrat::PERIODICITE_TRIMESTRIELLE => [],
            Contrat::PERIODICITE_ANNUELLE => [],
        ];

        foreach ([Contrat::PERIODICITE_MENSUELLE, Contrat::PERIODICITE_TRIMESTRIELLE, Contrat::PERIODICITE_ANNUELLE] as $periodicite) {
            $contrats = $this->contratRepository->findAFacturer($date, $periodicite);

            foreach ($contrats as $contrat) {
                if ($this->doitEtreFacture($contrat, $date)) {
                    $result[$periodicite][] = $contrat;
                }
            }
        }

        return $result;
    }

    /**
     * Verifie si un contrat doit etre facture a une date donnee
     */
    private function doitEtreFacture(Contrat $contrat, \DateTimeInterface $date): bool
    {
        $periodicite = $contrat->getPeriodicite();
        $anniversaire = $contrat->getDateAnniversaire();
        $debutFacturation = $contrat->getDateDebutFacturation();

        if ($anniversaire === null || $debutFacturation === null) {
            return false;
        }

        $jourAnniversaire = (int) $anniversaire->format('d');
        $moisAnniversaire = (int) $anniversaire->format('m');
        $jourCourant = (int) $date->format('d');
        $moisCourant = (int) $date->format('m');
        $anneeCourante = (int) $date->format('Y');

        // Calcul de la periode
        [$periodeDebut, $periodeFin] = $this->calculerPeriode($contrat, $date);

        // Verifier que la periode est apres le debut de facturation
        if ($periodeDebut < $debutFacturation) {
            return false;
        }

        // Verifier si une facture existe deja pour cette periode
        if ($this->factureRepository->existsForContratAndPeriod($contrat, $periodeDebut, $periodeFin)) {
            return false;
        }

        switch ($periodicite) {
            case Contrat::PERIODICITE_MENSUELLE:
                // Facturer si jour courant >= jour anniversaire
                return $jourCourant >= $jourAnniversaire;

            case Contrat::PERIODICITE_TRIMESTRIELLE:
                // Determiner le trimestre selon le mois anniversaire
                $trimestreAnniversaire = (int) ceil($moisAnniversaire / 3);
                $trimestreCourant = (int) ceil($moisCourant / 3);
                $premierMoisTrimestre = ($trimestreCourant - 1) * 3 + 1;

                // Facturer si dans le premier mois du trimestre ET jour >= anniversaire
                return $moisCourant === $premierMoisTrimestre && $jourCourant >= $jourAnniversaire;

            case Contrat::PERIODICITE_ANNUELLE:
                // Facturer si dans le mois anniversaire ET jour >= anniversaire
                return $moisCourant === $moisAnniversaire && $jourCourant >= $jourAnniversaire;

            default:
                return false;
        }
    }

    /**
     * Calcule la periode de facturation
     * @return array{\DateTimeInterface, \DateTimeInterface}
     */
    public function calculerPeriode(Contrat $contrat, ?\DateTimeInterface $date = null): array
    {
        $date = $date ?? new \DateTime();
        $periodicite = $contrat->getPeriodicite();
        $anniversaire = $contrat->getDateAnniversaire();

        $moisCourant = (int) $date->format('m');
        $anneeCourante = (int) $date->format('Y');
        $jourAnniversaire = $anniversaire ? (int) $anniversaire->format('d') : 1;
        $moisAnniversaire = $anniversaire ? (int) $anniversaire->format('m') : 1;

        switch ($periodicite) {
            case Contrat::PERIODICITE_MENSUELLE:
                // Premier au dernier jour du mois courant
                $periodeDebut = new \DateTime("$anneeCourante-$moisCourant-01");
                $periodeFin = (clone $periodeDebut)->modify('last day of this month');
                break;

            case Contrat::PERIODICITE_TRIMESTRIELLE:
                // Premier jour du trimestre au dernier jour
                $trimestreCourant = (int) ceil($moisCourant / 3);
                $premierMoisTrimestre = ($trimestreCourant - 1) * 3 + 1;
                $dernierMoisTrimestre = $trimestreCourant * 3;

                $periodeDebut = new \DateTime("$anneeCourante-$premierMoisTrimestre-01");
                $periodeFin = new \DateTime("$anneeCourante-$dernierMoisTrimestre-01");
                $periodeFin->modify('last day of this month');
                break;

            case Contrat::PERIODICITE_ANNUELLE:
                // Date anniversaire a date anniversaire + 1 an - 1 jour
                $periodeDebut = new \DateTime("$anneeCourante-$moisAnniversaire-$jourAnniversaire");

                // Si on est avant le mois anniversaire, prendre l'annee precedente
                if ($moisCourant < $moisAnniversaire ||
                    ($moisCourant === $moisAnniversaire && (int)$date->format('d') < $jourAnniversaire)) {
                    $periodeDebut->modify('-1 year');
                }

                $periodeFin = (clone $periodeDebut)->modify('+1 year -1 day');
                break;

            default:
                $periodeDebut = new \DateTime("$anneeCourante-01-01");
                $periodeFin = new \DateTime("$anneeCourante-12-31");
        }

        return [$periodeDebut, $periodeFin];
    }

    /**
     * Cree une facture depuis un contrat (en brouillon, sans numero)
     * Le numero sera attribue lors de la validation pour garantir une numerotation sans trou
     * @param Contrat $contrat Le contrat a facturer
     * @param \DateTimeInterface|null $dateReference Date de reference pour la periode (null = aujourd'hui)
     */
    public function creerFacture(Contrat $contrat, ?\DateTimeInterface $dateReference = null): Facture
    {
        $emetteur = $contrat->getEmetteur();
        $client = $contrat->getClient();
        $versionEmetteur = $emetteur->getVersionActive();

        // 1. Recuperer les parametres de facturation
        $parametres = $this->parametreFacturationRepository->getOrCreateForEmetteur($emetteur);

        // 2. Creer la facture (sans numero - sera attribue a la validation)
        $facture = new Facture();
        $facture->setContrat($contrat);

        // 4. Snapshot client
        $facture->setClientCode($client->getCode());
        $facture->setClientRaisonSociale($client->getRaisonSociale());
        $facture->setClientAdresse($client->getAdresse());
        $facture->setClientSiren($client->getSiren());
        $facture->setClientSiret($client->getSiret());
        $facture->setClientTva($client->getTva());
        $facture->setClientCodePays($client->getCodePaysTva());

        // 5. Snapshot emetteur
        if ($versionEmetteur) {
            $facture->setEmetteurRaisonSociale($versionEmetteur->getRaisonSociale());
            $facture->setEmetteurAdresse($versionEmetteur->getAdresse());
            $facture->setEmetteurSiren($versionEmetteur->getSiren());
            $facture->setEmetteurTva($versionEmetteur->getTva());
            $facture->setEmetteurIban($versionEmetteur->getIban());
            $facture->setEmetteurBic($versionEmetteur->getBic());
        }

        // 6. Dates
        $dateFacture = new \DateTime();
        $facture->setDateFacture($dateFacture);

        $dateEcheance = (clone $dateFacture)->modify('+' . $parametres->getDelaiEcheance() . ' days');
        $facture->setDateEcheance($dateEcheance);

        [$periodeDebut, $periodeFin] = $this->calculerPeriode($contrat, $dateReference);
        $facture->setPeriodeDebut($periodeDebut);
        $facture->setPeriodeFin($periodeFin);

        // 7. Copier la remise globale et le commentaire du contrat
        $facture->setRemiseGlobale($contrat->getRemiseGlobale());
        $facture->setCommentaire($contrat->getCommentaireFacture());
        $facture->setMentionsLegales($parametres->getMentionsLegales());

        // 8. Copier les lignes de contrat
        foreach ($contrat->getLignes() as $ligneContrat) {
            $ligneFacture = new LigneFacture();
            $ligneFacture->setDesignation($ligneContrat->getModule()?->getNom() ?? 'Module');
            $ligneFacture->setQuantite($ligneContrat->getQuantite());
            $ligneFacture->setPrixUnitaire($ligneContrat->getPrixUnitaire());
            $ligneFacture->setRemise($ligneContrat->getRemise());
            $ligneFacture->setTauxTva($ligneContrat->getTauxTva());
            $ligneFacture->calculerTotaux();

            $facture->addLigne($ligneFacture);
        }

        // 9. Recalculer les totaux
        $facture->recalculerTotaux();

        // 10. Persister
        $this->em->persist($facture);
        $this->em->flush();

        return $facture;
    }

    /**
     * Valide une facture ou un avoir (brouillon -> validee)
     * C'est a ce moment que le numero definitif est attribue pour garantir une numerotation sans trou
     */
    public function validerFacture(Facture $facture): void
    {
        if ($facture->getStatut() !== Facture::STATUT_BROUILLON) {
            throw new \LogicException('Seule une facture en brouillon peut etre validee');
        }

        // Determiner l'emetteur (depuis le contrat ou depuis les donnees snapshotees)
        $emetteur = $facture->getContrat()?->getEmetteur();
        if (!$emetteur) {
            // Pour les factures sans contrat, trouver l'emetteur par defaut
            $emetteur = $this->em->getRepository(\App\Entity\Emetteur::class)->findOneBy(['parDefaut' => true]);
        }

        if (!$emetteur) {
            throw new \LogicException('Aucun emetteur trouve pour generer le numero');
        }

        $parametres = $this->parametreFacturationRepository->getOrCreateForEmetteur($emetteur);
        $numero = $parametres->genererNumero($facture->getType(), $facture->getDateFacture());
        $parametres->incrementProchainNumero();

        $facture->setNumero($numero);
        $facture->setStatut(Facture::STATUT_VALIDEE);
        $facture->setDateValidation(new \DateTime());

        // Si c'est un avoir lie, verifier l'impact sur la facture parente
        if ($facture->isAvoir() && $facture->getFactureParente()) {
            $this->verifierImpactAvoirSurFacture($facture);
        }

        $this->em->flush();
    }

    /**
     * Verifie et applique l'impact d'un avoir sur sa facture parente
     */
    private function verifierImpactAvoirSurFacture(Facture $avoir): void
    {
        $factureParente = $avoir->getFactureParente();
        if (!$factureParente) {
            return;
        }

        // Calculer le total des avoirs (incluant celui-ci)
        $totalAvoirs = '0.00';
        foreach ($factureParente->getAvoirs() as $a) {
            if ($a->getStatut() !== Facture::STATUT_BROUILLON) {
                $totalAvoirs = bcadd($totalAvoirs, $a->getTotalTtc(), 2);
            }
        }
        // Ajouter l'avoir actuel s'il n'est pas encore compte
        if ($avoir->getStatut() === Facture::STATUT_BROUILLON) {
            $totalAvoirs = bcadd($totalAvoirs, $avoir->getTotalTtc(), 2);
        }

        // Si le total des avoirs >= total facture, annuler la facture
        if (bccomp($totalAvoirs, $factureParente->getTotalTtc(), 2) >= 0) {
            $factureParente->setStatut(Facture::STATUT_ANNULEE);
        }
    }

    /**
     * Marque une facture comme envoyee (validee -> envoyee)
     */
    public function marquerEnvoyee(Facture $facture): void
    {
        if ($facture->getStatut() !== Facture::STATUT_VALIDEE) {
            throw new \LogicException('Seule une facture validee peut etre marquee comme envoyee');
        }

        $facture->setStatut(Facture::STATUT_ENVOYEE);
        $facture->setDateEnvoi(new \DateTime());

        $this->em->flush();
    }

    /**
     * Marque une facture comme payee (validee|envoyee -> payee)
     */
    public function marquerPayee(Facture $facture): void
    {
        if (!in_array($facture->getStatut(), [Facture::STATUT_VALIDEE, Facture::STATUT_ENVOYEE])) {
            throw new \LogicException('Seule une facture validee ou envoyee peut etre marquee comme payee');
        }

        $facture->setStatut(Facture::STATUT_PAYEE);
        $facture->setDatePaiement(new \DateTime());

        $this->em->flush();
    }

    /**
     * Supprime une facture brouillon
     */
    public function supprimerFacture(Facture $facture): void
    {
        if ($facture->getStatut() !== Facture::STATUT_BROUILLON) {
            throw new \LogicException('Seule une facture en brouillon peut etre supprimee');
        }

        $this->em->remove($facture);
        $this->em->flush();
    }

    /**
     * Met a jour les totaux d'une facture apres modification des lignes
     */
    public function recalculerFacture(Facture $facture): void
    {
        if ($facture->getStatut() !== Facture::STATUT_BROUILLON) {
            throw new \LogicException('Seule une facture en brouillon peut etre modifiee');
        }

        foreach ($facture->getLignes() as $ligne) {
            $ligne->calculerTotaux();
        }

        $facture->recalculerTotaux();
        $this->em->flush();
    }

    /**
     * Cree une facture ponctuelle (sans contrat ou avec contrat optionnel)
     */
    public function creerFactureManuelle(
        \App\Entity\Client $client,
        ?\App\Entity\Emetteur $emetteur = null,
        ?\App\Entity\Contrat $contrat = null
    ): Facture {
        // Si contrat fourni, utiliser son emetteur
        if ($contrat) {
            $emetteur = $contrat->getEmetteur();
        }

        // Sinon, utiliser l'emetteur par defaut
        if (!$emetteur) {
            $emetteur = $this->em->getRepository(\App\Entity\Emetteur::class)->findOneBy(['parDefaut' => true]);
        }

        if (!$emetteur) {
            throw new \LogicException('Aucun emetteur disponible');
        }

        $versionEmetteur = $emetteur->getVersionActive();
        $parametres = $this->parametreFacturationRepository->getOrCreateForEmetteur($emetteur);

        $facture = new Facture();
        $facture->setType(Facture::TYPE_FACTURE);
        $facture->setContrat($contrat);

        // Snapshot client
        $facture->setClientCode($client->getCode());
        $facture->setClientRaisonSociale($client->getRaisonSociale());
        $facture->setClientAdresse($client->getAdresse());
        $facture->setClientSiren($client->getSiren());
        $facture->setClientSiret($client->getSiret());
        $facture->setClientTva($client->getTva());
        $facture->setClientCodePays($client->getCodePaysTva());

        // Snapshot emetteur
        if ($versionEmetteur) {
            $facture->setEmetteurRaisonSociale($versionEmetteur->getRaisonSociale());
            $facture->setEmetteurAdresse($versionEmetteur->getAdresse());
            $facture->setEmetteurSiren($versionEmetteur->getSiren());
            $facture->setEmetteurTva($versionEmetteur->getTva());
            $facture->setEmetteurIban($versionEmetteur->getIban());
            $facture->setEmetteurBic($versionEmetteur->getBic());
        }

        // Dates
        $dateFacture = new \DateTime();
        $facture->setDateFacture($dateFacture);
        $dateEcheance = (clone $dateFacture)->modify('+' . $parametres->getDelaiEcheance() . ' days');
        $facture->setDateEcheance($dateEcheance);

        // Periode = date facture pour les ponctuelles
        $facture->setPeriodeDebut($dateFacture);
        $facture->setPeriodeFin($dateFacture);

        // Mentions legales
        $facture->setMentionsLegales($parametres->getMentionsLegales());

        $this->em->persist($facture);
        $this->em->flush();

        return $facture;
    }

    /**
     * Cree un avoir lie a une facture existante
     * @param bool $total Si true, copie toutes les lignes. Sinon, l'avoir est vide.
     */
    public function creerAvoirFromFacture(Facture $factureParente, bool $total = true, ?string $motif = null): Facture
    {
        // Verifier que la facture parente peut avoir un avoir
        if ($factureParente->isAvoir()) {
            throw new \LogicException('Impossible de creer un avoir sur un avoir');
        }

        $statutsAutorises = [Facture::STATUT_VALIDEE, Facture::STATUT_ENVOYEE, Facture::STATUT_PAYEE];
        if (!in_array($factureParente->getStatut(), $statutsAutorises)) {
            throw new \LogicException('Un avoir ne peut etre cree que sur une facture validee, envoyee ou payee');
        }

        $avoir = new Facture();
        $avoir->setType(Facture::TYPE_AVOIR);
        $avoir->setFactureParente($factureParente);
        $avoir->setContrat($factureParente->getContrat());
        $avoir->setMotifAvoir($motif);

        // Copier le snapshot client
        $avoir->setClientCode($factureParente->getClientCode());
        $avoir->setClientRaisonSociale($factureParente->getClientRaisonSociale());
        $avoir->setClientAdresse($factureParente->getClientAdresse());
        $avoir->setClientSiren($factureParente->getClientSiren());
        $avoir->setClientSiret($factureParente->getClientSiret());
        $avoir->setClientTva($factureParente->getClientTva());
        $avoir->setClientCodePays($factureParente->getClientCodePays());

        // Copier le snapshot emetteur
        $avoir->setEmetteurRaisonSociale($factureParente->getEmetteurRaisonSociale());
        $avoir->setEmetteurAdresse($factureParente->getEmetteurAdresse());
        $avoir->setEmetteurSiren($factureParente->getEmetteurSiren());
        $avoir->setEmetteurTva($factureParente->getEmetteurTva());
        $avoir->setEmetteurIban($factureParente->getEmetteurIban());
        $avoir->setEmetteurBic($factureParente->getEmetteurBic());

        // Dates
        $dateAvoir = new \DateTime();
        $avoir->setDateFacture($dateAvoir);
        $avoir->setDateEcheance($dateAvoir); // Echeance immediate pour un avoir
        $avoir->setPeriodeDebut($dateAvoir);
        $avoir->setPeriodeFin($dateAvoir);

        // Mentions legales
        $avoir->setMentionsLegales($factureParente->getMentionsLegales());
        $avoir->setRemiseGlobale($factureParente->getRemiseGlobale());

        // Si avoir total, copier toutes les lignes
        if ($total) {
            foreach ($factureParente->getLignes() as $ligneOrigine) {
                $ligneAvoir = new LigneFacture();
                $ligneAvoir->setDesignation($ligneOrigine->getDesignation());
                $ligneAvoir->setDescription($ligneOrigine->getDescription());
                $ligneAvoir->setQuantite($ligneOrigine->getQuantite());
                $ligneAvoir->setPrixUnitaire($ligneOrigine->getPrixUnitaire());
                $ligneAvoir->setRemise($ligneOrigine->getRemise());
                $ligneAvoir->setTauxTva($ligneOrigine->getTauxTva());
                $ligneAvoir->calculerTotaux();
                $avoir->addLigne($ligneAvoir);
            }
            $avoir->recalculerTotaux();
        }

        $this->em->persist($avoir);
        $this->em->flush();

        return $avoir;
    }

    /**
     * Cree un avoir libre (sans facture parente)
     */
    public function creerAvoirLibre(
        \App\Entity\Client $client,
        ?\App\Entity\Emetteur $emetteur = null,
        ?string $motif = null
    ): Facture {
        // Utiliser l'emetteur par defaut si non fourni
        if (!$emetteur) {
            $emetteur = $this->em->getRepository(\App\Entity\Emetteur::class)->findOneBy(['parDefaut' => true]);
        }

        if (!$emetteur) {
            throw new \LogicException('Aucun emetteur disponible');
        }

        $versionEmetteur = $emetteur->getVersionActive();
        $parametres = $this->parametreFacturationRepository->getOrCreateForEmetteur($emetteur);

        $avoir = new Facture();
        $avoir->setType(Facture::TYPE_AVOIR);
        $avoir->setMotifAvoir($motif);

        // Snapshot client
        $avoir->setClientCode($client->getCode());
        $avoir->setClientRaisonSociale($client->getRaisonSociale());
        $avoir->setClientAdresse($client->getAdresse());
        $avoir->setClientSiren($client->getSiren());
        $avoir->setClientSiret($client->getSiret());
        $avoir->setClientTva($client->getTva());
        $avoir->setClientCodePays($client->getCodePaysTva());

        // Snapshot emetteur
        if ($versionEmetteur) {
            $avoir->setEmetteurRaisonSociale($versionEmetteur->getRaisonSociale());
            $avoir->setEmetteurAdresse($versionEmetteur->getAdresse());
            $avoir->setEmetteurSiren($versionEmetteur->getSiren());
            $avoir->setEmetteurTva($versionEmetteur->getTva());
            $avoir->setEmetteurIban($versionEmetteur->getIban());
            $avoir->setEmetteurBic($versionEmetteur->getBic());
        }

        // Dates
        $dateAvoir = new \DateTime();
        $avoir->setDateFacture($dateAvoir);
        $avoir->setDateEcheance($dateAvoir);
        $avoir->setPeriodeDebut($dateAvoir);
        $avoir->setPeriodeFin($dateAvoir);

        // Mentions legales
        $avoir->setMentionsLegales($parametres->getMentionsLegales());

        $this->em->persist($avoir);
        $this->em->flush();

        return $avoir;
    }

    /**
     * Marque un avoir comme rembourse (pour les avoirs lies)
     */
    public function marquerRembourse(Facture $avoir): void
    {
        if (!$avoir->isAvoir()) {
            throw new \LogicException('Seul un avoir peut etre marque comme rembourse');
        }

        if ($avoir->getStatut() !== Facture::STATUT_VALIDEE) {
            throw new \LogicException('Seul un avoir valide peut etre marque comme rembourse');
        }

        $avoir->setStatut(Facture::STATUT_REMBOURSEE);
        $avoir->setDatePaiement(new \DateTime());

        $this->em->flush();
    }

    /**
     * Verifie si un avoir peut etre cree sur une facture
     * Retourne le montant maximum de l'avoir possible
     */
    public function getMontantAvoirDisponible(Facture $facture): string
    {
        if ($facture->isAvoir()) {
            return '0.00';
        }

        return $facture->getMontantRestant();
    }
}
