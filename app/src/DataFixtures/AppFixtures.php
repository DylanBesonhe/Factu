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
use App\Entity\Instance;
use App\Entity\LigneContrat;
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
        $emetteur = $this->createEmetteur($manager, $cgv);

        // 5. Instances
        $instances = $this->createInstances($manager);

        // 6. Clients
        $clients = $this->createClients($manager);

        // 7. Contrats
        $this->createContrats($manager, $clients, $instances, $emetteur, $modules);

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

    private function createEmetteur(ObjectManager $manager, Cgv $cgv): Emetteur
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
        $params->setDelaiEcheance(30);
        $params->setMentionsLegales('TVA non applicable, art. 293 B du CGI');
        $params->setEmailObjet('Votre facture {NUMERO}');
        $params->setEmailCorps("Bonjour,\n\nVeuillez trouver ci-joint votre facture.\n\nCordialement");
        $manager->persist($params);

        return $emetteur;
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

    private function createContrats(ObjectManager $manager, array $clients, array $instances, Emetteur $emetteur, array $modules): void
    {
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
                'statut' => Contrat::STATUT_SUSPENDU,
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
            $contrat->setPeriodicite($data['periodicite']);
            $contrat->setStatut($data['statut'] ?? Contrat::STATUT_ACTIF);
            if (isset($data['remise'])) {
                $contrat->setRemiseGlobale($data['remise']);
            }
            $manager->persist($contrat);

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
    }
}
