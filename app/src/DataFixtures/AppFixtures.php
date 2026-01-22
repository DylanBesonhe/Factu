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
        $cgv->setDateDebut(new \DateTime('2026-01-01'));
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

        // 7. Contrats
        $contrats = $this->createContrats($manager, $clients, $instances, $emetteur, $modules);

        // 8. Factures
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
        $emetteurVersion->setDateEffet(new \DateTime('2026-01-01'));
        $manager->persist($emetteurVersion);

        $emetteurCgv = new EmetteurCgv();
        $emetteurCgv->setEmetteur($emetteur);
        $emetteurCgv->setCgv($cgv);
        $emetteurCgv->setParDefaut(true);
        $manager->persist($emetteurCgv);

        $params = new ParametreFacturation();
        $params->setEmetteur($emetteur);
        $params->setFormatNumero('FAC-{YYYY}-{SEQ:5}');
        $params->setProchainNumero(3); // 2 factures numerotees dans les fixtures (brouillon sans numero)
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
            ['nom' => 'instance-demo', 'url' => 'https://demo.factu.local'],
            ['nom' => 'instance-prod', 'url' => 'https://prod.factu.local'],
            ['nom' => 'instance-test', 'url' => 'https://test.factu.local'],
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
            ['code' => 'CLI001', 'raisonSociale' => 'CREATION METAL', 'siren' => '123456782', 'email' => 'contact@creation-metal.fr'],
            ['code' => 'CLI002', 'raisonSociale' => 'GENERIX GROUP', 'siren' => '987654321', 'email' => 'contact@generix.fr'],
            ['code' => 'CLI003', 'raisonSociale' => 'Tech Solutions SARL', 'siren' => '456789123', 'email' => 'contact@techsolutions.fr'],
            ['code' => 'CLI004', 'raisonSociale' => 'Digital Services', 'siren' => '789123456', 'email' => 'contact@digitalservices.fr'],
            ['code' => 'CLI005', 'raisonSociale' => 'Innovation Corp', 'siren' => '321654987', 'email' => 'contact@innovation-corp.fr'],
        ];

        foreach ($clientsData as $data) {
            $client = new Client();
            $client->setCode($data['code']);
            $client->setRaisonSociale($data['raisonSociale']);
            $client->setSiren($data['siren']);
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
        $contratsData = [
            [
                'numero' => 'CTR-2026-001',
                'client' => 'CLI001',
                'instance' => 'instance-demo',
                'periodicite' => Contrat::PERIODICITE_ANNUELLE,
                'nbLicences' => 5,
                'lignes' => [
                    ['module' => 'Module de base', 'qte' => 5],
                    ['module' => 'Module Comptabilite', 'qte' => 5],
                    ['module' => 'Module Facturation', 'qte' => 5],
                ],
            ],
            [
                'numero' => 'CTR-2026-002',
                'client' => 'CLI002',
                'instance' => 'instance-prod',
                'periodicite' => Contrat::PERIODICITE_ANNUELLE,
                'nbLicences' => 20,
                'remise' => '10.00',
                'lignes' => [
                    ['module' => 'Module de base', 'qte' => 20],
                    ['module' => 'Module Comptabilite', 'qte' => 20],
                    ['module' => 'Module Gestion de stock', 'qte' => 20],
                    ['module' => 'Module CRM', 'qte' => 20],
                ],
            ],
            [
                'numero' => 'CTR-2026-003',
                'client' => 'CLI003',
                'instance' => 'instance-test',
                'periodicite' => Contrat::PERIODICITE_MENSUELLE,
                'nbLicences' => 3,
                'lignes' => [
                    ['module' => 'Module de base', 'qte' => 3],
                    ['module' => 'Module CRM', 'qte' => 3],
                ],
            ],
            [
                'numero' => 'CTR-2026-004',
                'client' => 'CLI004',
                'instance' => 'instance-demo',
                'periodicite' => Contrat::PERIODICITE_TRIMESTRIELLE,
                'nbLicences' => 10,
                'statut' => Contrat::STATUT_ACTIF,
                'lignes' => [
                    ['module' => 'Module de base', 'qte' => 10],
                    ['module' => 'Module Gestion de stock', 'qte' => 10],
                ],
            ],
        ];

        foreach ($contratsData as $data) {
            $contrat = new Contrat();
            $contrat->setNumero($data['numero']);
            $contrat->setClient($clients[$data['client']]);
            $contrat->setInstance($instances[$data['instance']]);
            $contrat->setEmetteur($emetteur);
            $contrat->setDateSignature(new \DateTime('2026-01-15'));
            $contrat->setDateAnniversaire(new \DateTime('2026-01-15'));
            $contrat->setDateDebutFacturation(new \DateTime('2026-01-01'));
            $contrat->setPeriodicite($data['periodicite']);
            $contrat->setStatut($data['statut'] ?? Contrat::STATUT_ACTIF);
            if (isset($data['remise'])) {
                $contrat->setRemiseGlobale($data['remise']);
            }
            $manager->persist($contrat);
            $contrats[$data['numero']] = $contrat;

            foreach ($data['lignes'] as $ligneData) {
                $ligne = new LigneContrat();
                $ligne->setContrat($contrat);
                $ligne->setModule($modules[$ligneData['module']]);
                $ligne->setQuantite($ligneData['qte']);
                $ligne->setPrixUnitaire($modules[$ligneData['module']]->getPrixDefaut());
                $ligne->setTauxTva('20.00');
                $manager->persist($ligne);
            }

            $evenement = new ContratEvenement();
            $evenement->setContrat($contrat);
            $evenement->setType(ContratEvenement::TYPE_CREATION);
            $evenement->setDescription('Creation du contrat via fixtures');
            $evenement->setDateEffet(new \DateTime('2026-01-15'));
            $evenement->setAuteur('Systeme');
            $manager->persist($evenement);
        }

        return $contrats;
    }

    private function createFactures(ObjectManager $manager, array $contrats, EmetteurVersion $emetteurVersion): void
    {

        // Facture brouillon (sans numero - sera attribue a la validation)
        $facture1 = new Facture();
        // Pas de numero pour les brouillons
        $facture1->setContrat($contrats['CTR-2026-001']);
        $facture1->setClientCode($contrats['CTR-2026-001']->getClient()->getCode());
        $facture1->setClientRaisonSociale($contrats['CTR-2026-001']->getClient()->getRaisonSociale());
        $facture1->setClientAdresse($contrats['CTR-2026-001']->getClient()->getAdresse());
        $facture1->setClientSiren($contrats['CTR-2026-001']->getClient()->getSiren());
        $facture1->setEmetteurRaisonSociale($emetteurVersion->getRaisonSociale());
        $facture1->setEmetteurAdresse($emetteurVersion->getAdresse());
        $facture1->setEmetteurSiren($emetteurVersion->getSiren());
        $facture1->setEmetteurTva($emetteurVersion->getTva());
        $facture1->setEmetteurIban($emetteurVersion->getIban());
        $facture1->setEmetteurBic($emetteurVersion->getBic());
        $facture1->setDateFacture(new \DateTime('2026-01-15'));
        $facture1->setDateEcheance(new \DateTime('2026-02-14'));
        $facture1->setPeriodeDebut(new \DateTime('2026-01-01'));
        $facture1->setPeriodeFin(new \DateTime('2026-12-31'));
        $facture1->setStatut(Facture::STATUT_BROUILLON);
        $facture1->setMentionsLegales('TVA non applicable, art. 293 B du CGI');
        $manager->persist($facture1);

        $ligne1 = new LigneFacture();
        $ligne1->setDesignation('Module de base');
        $ligne1->setQuantite(5);
        $ligne1->setPrixUnitaire('50.00');
        $ligne1->setTauxTva('20.00');
        $ligne1->calculerTotaux();
        $facture1->addLigne($ligne1);

        $ligne2 = new LigneFacture();
        $ligne2->setDesignation('Module Comptabilite');
        $ligne2->setQuantite(5);
        $ligne2->setPrixUnitaire('30.00');
        $ligne2->setTauxTva('20.00');
        $ligne2->calculerTotaux();
        $facture1->addLigne($ligne2);

        $facture1->recalculerTotaux();

        // Facture validee (numero attribue a la validation)
        $facture2 = new Facture();
        $facture2->setNumero('FAC-2026-00001');
        $facture2->setContrat($contrats['CTR-2026-002']);
        $facture2->setClientCode($contrats['CTR-2026-002']->getClient()->getCode());
        $facture2->setClientRaisonSociale($contrats['CTR-2026-002']->getClient()->getRaisonSociale());
        $facture2->setClientAdresse($contrats['CTR-2026-002']->getClient()->getAdresse());
        $facture2->setClientSiren($contrats['CTR-2026-002']->getClient()->getSiren());
        $facture2->setEmetteurRaisonSociale($emetteurVersion->getRaisonSociale());
        $facture2->setEmetteurAdresse($emetteurVersion->getAdresse());
        $facture2->setEmetteurSiren($emetteurVersion->getSiren());
        $facture2->setEmetteurTva($emetteurVersion->getTva());
        $facture2->setEmetteurIban($emetteurVersion->getIban());
        $facture2->setEmetteurBic($emetteurVersion->getBic());
        $facture2->setDateFacture(new \DateTime('2026-01-10'));
        $facture2->setDateEcheance(new \DateTime('2026-02-09'));
        $facture2->setPeriodeDebut(new \DateTime('2026-01-01'));
        $facture2->setPeriodeFin(new \DateTime('2026-12-31'));
        $facture2->setStatut(Facture::STATUT_VALIDEE);
        $facture2->setDateValidation(new \DateTime('2026-01-11'));
        $facture2->setRemiseGlobale('10.00');
        $facture2->setMentionsLegales('TVA non applicable, art. 293 B du CGI');
        $manager->persist($facture2);

        $ligne3 = new LigneFacture();
        $ligne3->setDesignation('Module de base');
        $ligne3->setQuantite(20);
        $ligne3->setPrixUnitaire('50.00');
        $ligne3->setTauxTva('20.00');
        $ligne3->calculerTotaux();
        $facture2->addLigne($ligne3);

        $ligne4 = new LigneFacture();
        $ligne4->setDesignation('Module CRM');
        $ligne4->setQuantite(20);
        $ligne4->setPrixUnitaire('35.00');
        $ligne4->setTauxTva('20.00');
        $ligne4->calculerTotaux();
        $facture2->addLigne($ligne4);

        $facture2->recalculerTotaux();

        // Facture payee (numero attribue a la validation)
        $facture3 = new Facture();
        $facture3->setNumero('FAC-2026-00002');
        $facture3->setContrat($contrats['CTR-2026-003']);
        $facture3->setClientCode($contrats['CTR-2026-003']->getClient()->getCode());
        $facture3->setClientRaisonSociale($contrats['CTR-2026-003']->getClient()->getRaisonSociale());
        $facture3->setClientAdresse($contrats['CTR-2026-003']->getClient()->getAdresse());
        $facture3->setClientSiren($contrats['CTR-2026-003']->getClient()->getSiren());
        $facture3->setEmetteurRaisonSociale($emetteurVersion->getRaisonSociale());
        $facture3->setEmetteurAdresse($emetteurVersion->getAdresse());
        $facture3->setEmetteurSiren($emetteurVersion->getSiren());
        $facture3->setEmetteurTva($emetteurVersion->getTva());
        $facture3->setEmetteurIban($emetteurVersion->getIban());
        $facture3->setEmetteurBic($emetteurVersion->getBic());
        $facture3->setDateFacture(new \DateTime('2026-01-05'));
        $facture3->setDateEcheance(new \DateTime('2026-02-04'));
        $facture3->setPeriodeDebut(new \DateTime('2026-01-01'));
        $facture3->setPeriodeFin(new \DateTime('2026-01-31'));
        $facture3->setStatut(Facture::STATUT_PAYEE);
        $facture3->setDateValidation(new \DateTime('2026-01-06'));
        $facture3->setDateEnvoi(new \DateTime('2026-01-07'));
        $facture3->setDatePaiement(new \DateTime('2026-01-18'));
        $facture3->setMentionsLegales('TVA non applicable, art. 293 B du CGI');
        $manager->persist($facture3);

        $ligne5 = new LigneFacture();
        $ligne5->setDesignation('Module de base');
        $ligne5->setQuantite(3);
        $ligne5->setPrixUnitaire('50.00');
        $ligne5->setTauxTva('20.00');
        $ligne5->calculerTotaux();
        $facture3->addLigne($ligne5);

        $ligne6 = new LigneFacture();
        $ligne6->setDesignation('Module CRM');
        $ligne6->setQuantite(3);
        $ligne6->setPrixUnitaire('35.00');
        $ligne6->setTauxTva('20.00');
        $ligne6->calculerTotaux();
        $facture3->addLigne($ligne6);

        $facture3->recalculerTotaux();
    }
}
