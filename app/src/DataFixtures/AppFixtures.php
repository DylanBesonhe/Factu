<?php

namespace App\DataFixtures;

use App\Entity\Cgv;
use App\Entity\Client;
use App\Entity\ClientNote;
use App\Entity\Contact;
use App\Entity\Contrat;
use App\Entity\ContratEvenement;
use App\Entity\Emetteur;
use App\Entity\EmetteurCgv;
use App\Entity\EmetteurVersion;
use App\Entity\Facture;
use App\Entity\HistoriqueLicence;
use App\Entity\Instance;
use App\Entity\LigneContrat;
use App\Entity\LigneFacture;
use App\Entity\Module;
use App\Entity\ParametreFacturation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private int $factureNumero = 1;

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // 1. Utilisateur admin
        $admin = new User();
        $admin->setNom('Administrateur');
        $admin->setEmail('admin@factu.local');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setActif(true);
        $manager->persist($admin);

        // 2. Modules
        $modules = $this->createModules($manager);

        // 3. CGV
        $cgv = new Cgv();
        $cgv->setNom('CGV v1.0');
        $cgv->setDateDebut(new \DateTime('2025-01-01'));
        $cgv->setFichierChemin('cgv/cgv_v1.pdf');
        $cgv->setFichierOriginal('cgv_v1.pdf');
        $manager->persist($cgv);

        // 4. Emetteur
        $emetteurData = $this->createEmetteur($manager, $cgv);
        $emetteur = $emetteurData['emetteur'];
        $emetteurVersion = $emetteurData['version'];

        // 5. Instances
        $instances = $this->createInstances($manager);

        // 6. Clients
        $clients = $this->createClients($manager);

        // 7. Contrats (debutant en janvier 2025)
        $contrats = $this->createContrats($manager, $clients, $instances, $emetteur, $modules);

        // 8. Historique des licences (evolution mensuelle)
        $this->createHistoriqueLicences($manager, $contrats);

        // 9. Factures (sur toute la periode janvier 2025 - janvier 2026)
        $this->createFactures($manager, $contrats, $emetteurVersion);

        $manager->flush();
    }

    private function createModules(ObjectManager $manager): array
    {
        $modules = [];
        $modulesData = [
            ['nom' => 'Module de base', 'prix' => '50.00', 'tva' => '20.00'],
            ['nom' => 'Module Comptabilite', 'prix' => '30.00', 'tva' => '20.00'],
            ['nom' => 'Module Gestion de stock', 'prix' => '25.00', 'tva' => '20.00'],
            ['nom' => 'Module CRM', 'prix' => '35.00', 'tva' => '20.00'],
            ['nom' => 'Module Ressources Humaines', 'prix' => '40.00', 'tva' => '20.00'],
            ['nom' => 'Module Facturation', 'prix' => '20.00', 'tva' => '20.00'],
        ];

        foreach ($modulesData as $data) {
            $module = new Module();
            $module->setNom($data['nom']);
            $module->setPrixDefaut($data['prix']);
            $module->setTauxTva($data['tva']);
            $module->setActif(true);
            $manager->persist($module);
            $modules[$data['nom']] = $module;
        }

        return $modules;
    }

    /**
     * @return array{emetteur: Emetteur, version: EmetteurVersion}
     */
    private function createEmetteur(ObjectManager $manager, Cgv $cgv): array
    {
        $emetteur = new Emetteur();
        $emetteur->setCode('FACTU');
        $emetteur->setNom('Factu SAS');
        $emetteur->setActif(true);
        $emetteur->setParDefaut(true);
        $manager->persist($emetteur);

        $emetteurVersion = new EmetteurVersion();
        $emetteurVersion->setEmetteur($emetteur);
        $emetteurVersion->setRaisonSociale('Factu SAS');
        $emetteurVersion->setFormeJuridique('SAS');
        $emetteurVersion->setCapital('10000.00');
        $emetteurVersion->setAdresse("123 Rue de la Facturation\n75001 Paris");
        $emetteurVersion->setSiren('123456789');
        $emetteurVersion->setTva('FR12345678901');
        $emetteurVersion->setEmail('contact@factu.local');
        $emetteurVersion->setTelephone('01 23 45 67 89');
        $emetteurVersion->setIban('FR7630001007941234567890185');
        $emetteurVersion->setBic('BDFEFRPP');
        $emetteurVersion->setDateEffet(new \DateTime('2025-01-01'));
        $manager->persist($emetteurVersion);

        $emetteurCgv = new EmetteurCgv();
        $emetteurCgv->setEmetteur($emetteur);
        $emetteurCgv->setCgv($cgv);
        $emetteurCgv->setParDefaut(true);
        $manager->persist($emetteurCgv);

        $params = new ParametreFacturation();
        $params->setEmetteur($emetteur);
        $params->setFormatNumero('FAC-{YYYY}-{SEQ:5}');
        $params->setProchainNumero(100); // Apres toutes les factures generees
        $params->setDelaiEcheance(30);
        $params->setMentionsLegales('TVA non applicable, art. 293 B du CGI');
        $params->setEmailObjet('Votre facture {NUMERO}');
        $params->setEmailCorps("Bonjour,\n\nVeuillez trouver ci-joint votre facture.\n\nCordialement");
        $manager->persist($params);

        return ['emetteur' => $emetteur, 'version' => $emetteurVersion];
    }

    private function createInstances(ObjectManager $manager): array
    {
        $instances = [];
        $instancesData = [
            ['nom' => 'instance-creationmetal', 'url' => 'https://creationmetal.factu.local'],
            ['nom' => 'instance-generix', 'url' => 'https://generix.factu.local'],
            ['nom' => 'instance-techsolutions', 'url' => 'https://techsolutions.factu.local'],
            ['nom' => 'instance-digitalservices', 'url' => 'https://digitalservices.factu.local'],
            ['nom' => 'instance-innovation', 'url' => 'https://innovation.factu.local'],
        ];

        foreach ($instancesData as $data) {
            $instance = new Instance();
            $instance->setNomActuel($data['nom']);
            $instance->setUrl($data['url']);
            $instance->setActif(true);
            $manager->persist($instance);
            $instances[$data['nom']] = $instance;
        }

        return $instances;
    }

    private function createClients(ObjectManager $manager): array
    {
        $clients = [];
        $clientsData = [
            [
                'code' => 'CLI001',
                'raisonSociale' => 'CREATION METAL',
                'siren' => '123456782',
                'siret' => '12345678200010',
                'tva' => 'FR32123456782',
                'email' => 'contact@creation-metal.fr',
            ],
            [
                'code' => 'CLI002',
                'raisonSociale' => 'GENERIX GROUP',
                'siren' => '987654321',
                'siret' => '98765432100015',
                'tva' => 'FR15987654321',
                'email' => 'contact@generix.fr',
            ],
            [
                'code' => 'CLI003',
                'raisonSociale' => 'Tech Solutions SARL',
                'siren' => '456789123',
                'siret' => '45678912300020',
                'tva' => 'FR89456789123',
                'email' => 'contact@techsolutions.fr',
            ],
            [
                'code' => 'CLI004',
                'raisonSociale' => 'Digital Services',
                'siren' => '789123456',
                'siret' => '78912345600025',
                'tva' => 'FR56789123456',
                'email' => 'contact@digitalservices.fr',
            ],
            [
                'code' => 'CLI005',
                'raisonSociale' => 'Innovation Corp',
                'siren' => '321654987',
                'siret' => '32165498700030',
                'tva' => 'FR23321654987',
                'email' => 'contact@innovation-corp.fr',
            ],
        ];

        foreach ($clientsData as $data) {
            $client = new Client();
            $client->setCode($data['code']);
            $client->setRaisonSociale($data['raisonSociale']);
            $client->setSiren($data['siren']);
            $client->setSiret($data['siret']);
            $client->setTva($data['tva']);
            $client->setCodePaysTva('FR');
            $client->setAdresse("Adresse de test\n75001 Paris");
            $client->setEmail($data['email']);
            $client->setTelephone('01 23 45 67 89');
            $client->setActif(true);
            $manager->persist($client);
            $clients[$data['code']] = $client;

            $contact = new Contact();
            $contact->setClient($client);
            $contact->setNom('Dupont');
            $contact->setPrenom('Jean');
            $contact->setFonction('Directeur');
            $contact->setEmail('j.dupont@example.fr');
            $contact->setPrincipal(true);
            $manager->persist($contact);

            $note = new ClientNote();
            $note->setClient($client);
            $note->setContenu('Client cree via fixtures.');
            $note->setAuteur('Systeme');
            $manager->persist($note);
        }

        return $clients;
    }

    private function createContrats(ObjectManager $manager, array $clients, array $instances, Emetteur $emetteur, array $modules): array
    {
        $contrats = [];

        // Contrat 1: CREATION METAL - Mensuel, croissance reguliere
        $contrat1 = $this->createContrat(
            $manager,
            'CTR-2025-001',
            $clients['CLI001'],
            $instances['instance-creationmetal'],
            $emetteur,
            Contrat::PERIODICITE_MENSUELLE,
            new \DateTime('2025-01-10'),
            [
                ['module' => $modules['Module de base'], 'qte' => 5, 'prix' => '50.00'],
                ['module' => $modules['Module Comptabilite'], 'qte' => 5, 'prix' => '30.00'],
                ['module' => $modules['Module Facturation'], 'qte' => 5, 'prix' => '20.00'],
            ]
        );
        $contrats['CTR-2025-001'] = $contrat1;

        // Contrat 2: GENERIX - Annuel, gros client avec remise et facturation particuliere
        $contrat2 = $this->createContrat(
            $manager,
            'CTR-2025-002',
            $clients['CLI002'],
            $instances['instance-generix'],
            $emetteur,
            Contrat::PERIODICITE_ANNUELLE,
            new \DateTime('2025-01-15'),
            [
                ['module' => $modules['Module de base'], 'qte' => 50, 'prix' => '45.00'],
                ['module' => $modules['Module Comptabilite'], 'qte' => 50, 'prix' => '27.00'],
                ['module' => $modules['Module Gestion de stock'], 'qte' => 50, 'prix' => '22.50'],
                ['module' => $modules['Module CRM'], 'qte' => 50, 'prix' => '31.50'],
            ],
            '15.00' // 15% remise
        );
        $contrat2->setFactureParticuliere(true);
        $contrat2->setFactureParticuliereDescription('Envoyer la facture en 3 exemplaires au service comptabilite');
        $contrats['CTR-2025-002'] = $contrat2;

        // Contrat 3: Tech Solutions - Trimestriel
        $contrat3 = $this->createContrat(
            $manager,
            'CTR-2025-003',
            $clients['CLI003'],
            $instances['instance-techsolutions'],
            $emetteur,
            Contrat::PERIODICITE_TRIMESTRIELLE,
            new \DateTime('2025-01-20'),
            [
                ['module' => $modules['Module de base'], 'qte' => 15, 'prix' => '50.00'],
                ['module' => $modules['Module CRM'], 'qte' => 15, 'prix' => '35.00'],
                ['module' => $modules['Module Ressources Humaines'], 'qte' => 10, 'prix' => '40.00'],
            ]
        );
        $contrats['CTR-2025-003'] = $contrat3;

        // Contrat 4: Digital Services - Mensuel, petit client
        $contrat4 = $this->createContrat(
            $manager,
            'CTR-2025-004',
            $clients['CLI004'],
            $instances['instance-digitalservices'],
            $emetteur,
            Contrat::PERIODICITE_MENSUELLE,
            new \DateTime('2025-02-01'),
            [
                ['module' => $modules['Module de base'], 'qte' => 3, 'prix' => '50.00'],
                ['module' => $modules['Module Gestion de stock'], 'qte' => 3, 'prix' => '25.00'],
            ]
        );
        $contrats['CTR-2025-004'] = $contrat4;

        // Contrat 5: Innovation Corp - Mensuel, demarrage mars 2025
        $contrat5 = $this->createContrat(
            $manager,
            'CTR-2025-005',
            $clients['CLI005'],
            $instances['instance-innovation'],
            $emetteur,
            Contrat::PERIODICITE_MENSUELLE,
            new \DateTime('2025-03-01'),
            [
                ['module' => $modules['Module de base'], 'qte' => 8, 'prix' => '50.00'],
                ['module' => $modules['Module Comptabilite'], 'qte' => 8, 'prix' => '30.00'],
                ['module' => $modules['Module CRM'], 'qte' => 8, 'prix' => '35.00'],
            ]
        );
        $contrats['CTR-2025-005'] = $contrat5;

        return $contrats;
    }

    private function createContrat(
        ObjectManager $manager,
        string $numero,
        Client $client,
        Instance $instance,
        Emetteur $emetteur,
        string $periodicite,
        \DateTime $dateSignature,
        array $lignes,
        ?string $remise = null
    ): Contrat {
        $contrat = new Contrat();
        $contrat->setNumero($numero);
        $contrat->setClient($client);
        $contrat->setInstance($instance);
        $contrat->setEmetteur($emetteur);
        $contrat->setDateSignature($dateSignature);
        $contrat->setDateAnniversaire($dateSignature);
        $contrat->setDateDebutFacturation($dateSignature);
        $contrat->setPeriodicite($periodicite);
        $contrat->setStatut(Contrat::STATUT_ACTIF);
        if ($remise) {
            $contrat->setRemiseGlobale($remise);
        }
        $manager->persist($contrat);

        foreach ($lignes as $ligneData) {
            $ligne = new LigneContrat();
            $ligne->setModule($ligneData['module']);
            $ligne->setQuantite($ligneData['qte']);
            $ligne->setPrixUnitaire($ligneData['prix']);
            $ligne->setTauxTva('20.00');
            $contrat->addLigne($ligne); // Important: use addLigne to keep in-memory collection synced
            $manager->persist($ligne);
        }

        $evenement = new ContratEvenement();
        $evenement->setContrat($contrat);
        $evenement->setType(ContratEvenement::TYPE_CREATION);
        $evenement->setDescription('Signature du contrat');
        $evenement->setDateEffet($dateSignature);
        $evenement->setAuteur('Commercial');
        $manager->persist($evenement);

        return $contrat;
    }

    private function createHistoriqueLicences(ObjectManager $manager, array $contrats): void
    {
        // Evolution des licences par contrat (simulation realiste)
        $evolutionData = [
            'CTR-2025-001' => [ // CREATION METAL - croissance reguliere
                '2025-01' => 5, '2025-02' => 5, '2025-03' => 6, '2025-04' => 6,
                '2025-05' => 7, '2025-06' => 8, '2025-07' => 8, '2025-08' => 9,
                '2025-09' => 10, '2025-10' => 10, '2025-11' => 12, '2025-12' => 12,
                '2026-01' => 14,
            ],
            'CTR-2025-002' => [ // GENERIX - stable puis grosse augmentation
                '2025-01' => 50, '2025-02' => 50, '2025-03' => 50, '2025-04' => 52,
                '2025-05' => 52, '2025-06' => 55, '2025-07' => 55, '2025-08' => 55,
                '2025-09' => 60, '2025-10' => 65, '2025-11' => 70, '2025-12' => 75,
                '2026-01' => 80,
            ],
            'CTR-2025-003' => [ // Tech Solutions - legere croissance
                '2025-01' => 15, '2025-02' => 15, '2025-03' => 15, '2025-04' => 16,
                '2025-05' => 16, '2025-06' => 17, '2025-07' => 18, '2025-08' => 18,
                '2025-09' => 19, '2025-10' => 20, '2025-11' => 20, '2025-12' => 22,
                '2026-01' => 22,
            ],
            'CTR-2025-004' => [ // Digital Services - stable
                '2025-02' => 3, '2025-03' => 3, '2025-04' => 3, '2025-05' => 4,
                '2025-06' => 4, '2025-07' => 4, '2025-08' => 5, '2025-09' => 5,
                '2025-10' => 5, '2025-11' => 5, '2025-12' => 6, '2026-01' => 6,
            ],
            'CTR-2025-005' => [ // Innovation Corp - croissance rapide (startup)
                '2025-03' => 8, '2025-04' => 10, '2025-05' => 12, '2025-06' => 15,
                '2025-07' => 18, '2025-08' => 20, '2025-09' => 25, '2025-10' => 28,
                '2025-11' => 32, '2025-12' => 35, '2026-01' => 40,
            ],
        ];

        foreach ($evolutionData as $numeroContrat => $evolution) {
            $contrat = $contrats[$numeroContrat];
            foreach ($evolution as $mois => $nbLicences) {
                $historique = new HistoriqueLicence();
                $historique->setContrat($contrat);
                $historique->setNbLicences($nbLicences);
                $historique->setDateEffet(new \DateTime($mois . '-01'));
                $historique->setSource('import');
                $manager->persist($historique);
            }
        }
    }

    private function createFactures(ObjectManager $manager, array $contrats, EmetteurVersion $emetteurVersion): void
    {
        // Generer les factures pour chaque contrat selon sa periodicite
        foreach ($contrats as $numero => $contrat) {
            $this->generateFacturesForContrat($manager, $contrat, $emetteurVersion);
        }
    }

    private function generateFacturesForContrat(ObjectManager $manager, Contrat $contrat, EmetteurVersion $emetteurVersion): void
    {
        $dateDebut = clone $contrat->getDateDebutFacturation();
        $dateFin = new \DateTime('2026-01-31');
        $periodicite = $contrat->getPeriodicite();

        $currentDate = clone $dateDebut;

        while ($currentDate <= $dateFin) {
            $periodeDebut = clone $currentDate;

            switch ($periodicite) {
                case Contrat::PERIODICITE_MENSUELLE:
                    $periodeFin = (clone $currentDate)->modify('last day of this month');
                    $nextDate = (clone $currentDate)->modify('+1 month');
                    break;
                case Contrat::PERIODICITE_TRIMESTRIELLE:
                    $periodeFin = (clone $currentDate)->modify('+2 months')->modify('last day of this month');
                    $nextDate = (clone $currentDate)->modify('+3 months');
                    break;
                case Contrat::PERIODICITE_ANNUELLE:
                    $periodeFin = (clone $currentDate)->modify('+11 months')->modify('last day of this month');
                    $nextDate = (clone $currentDate)->modify('+1 year');
                    break;
                default:
                    $periodeFin = clone $currentDate;
                    $nextDate = (clone $currentDate)->modify('+1 month');
            }

            // Ne pas creer de facture si la periode depasse aujourd'hui
            if ($periodeDebut > new \DateTime()) {
                break;
            }

            $this->createFacture($manager, $contrat, $emetteurVersion, $periodeDebut, $periodeFin);

            $currentDate = $nextDate;
        }
    }

    private function createFacture(
        ObjectManager $manager,
        Contrat $contrat,
        EmetteurVersion $emetteurVersion,
        \DateTime $periodeDebut,
        \DateTime $periodeFin
    ): void {
        $client = $contrat->getClient();
        $dateFacture = (clone $periodeDebut)->modify('+5 days');
        $dateEcheance = (clone $dateFacture)->modify('+30 days');

        // Determiner le statut selon la date
        $now = new \DateTime();
        $isPast = $dateFacture < (clone $now)->modify('-2 months');
        $isRecent = $dateFacture < (clone $now)->modify('-1 month');

        if ($isPast) {
            $statut = Facture::STATUT_PAYEE;
        } elseif ($isRecent) {
            $statut = Facture::STATUT_ENVOYEE;
        } elseif ($dateFacture < $now) {
            $statut = Facture::STATUT_VALIDEE;
        } else {
            $statut = Facture::STATUT_BROUILLON;
        }

        $facture = new Facture();

        // Numero seulement pour factures non-brouillon
        if ($statut !== Facture::STATUT_BROUILLON) {
            $annee = $dateFacture->format('Y');
            $facture->setNumero(sprintf('FAC-%s-%05d', $annee, $this->factureNumero++));
        }

        $facture->setContrat($contrat);
        $facture->setClientCode($client->getCode());
        $facture->setClientRaisonSociale($client->getRaisonSociale());
        $facture->setClientAdresse($client->getAdresse());
        $facture->setClientSiren($client->getSiren());
        $facture->setClientSiret($client->getSiret());
        $facture->setClientTva($client->getTva());
        $facture->setClientCodePays($client->getCodePaysTva());
        $facture->setEmetteurRaisonSociale($emetteurVersion->getRaisonSociale());
        $facture->setEmetteurAdresse($emetteurVersion->getAdresse());
        $facture->setEmetteurSiren($emetteurVersion->getSiren());
        $facture->setEmetteurTva($emetteurVersion->getTva());
        $facture->setEmetteurIban($emetteurVersion->getIban());
        $facture->setEmetteurBic($emetteurVersion->getBic());
        $facture->setDateFacture($dateFacture);
        $facture->setDateEcheance($dateEcheance);
        $facture->setPeriodeDebut($periodeDebut);
        $facture->setPeriodeFin($periodeFin);
        $facture->setStatut($statut);

        if ($contrat->getRemiseGlobale()) {
            $facture->setRemiseGlobale($contrat->getRemiseGlobale());
        }

        $facture->setMentionsLegales('TVA non applicable, art. 293 B du CGI');

        // Dates selon statut
        if ($statut !== Facture::STATUT_BROUILLON) {
            $facture->setDateValidation((clone $dateFacture)->modify('+1 day'));
        }
        if ($statut === Facture::STATUT_ENVOYEE || $statut === Facture::STATUT_PAYEE) {
            $facture->setDateEnvoi((clone $dateFacture)->modify('+2 days'));
        }
        if ($statut === Facture::STATUT_PAYEE) {
            $facture->setDatePaiement((clone $dateFacture)->modify('+15 days'));
        }

        $manager->persist($facture);

        // Ajouter les lignes depuis le contrat
        foreach ($contrat->getLignes() as $ligneContrat) {
            $ligne = new LigneFacture();
            $ligne->setDesignation($ligneContrat->getModule()->getNom());
            $ligne->setQuantite($ligneContrat->getQuantite());
            $ligne->setPrixUnitaire($ligneContrat->getPrixUnitaire());
            $ligne->setTauxTva($ligneContrat->getTauxTva());
            if ($ligneContrat->getRemise()) {
                $ligne->setRemise($ligneContrat->getRemise());
            }
            $ligne->calculerTotaux();
            $facture->addLigne($ligne);
        }

        $facture->recalculerTotaux();
    }
}
