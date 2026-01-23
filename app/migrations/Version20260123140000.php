<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajout d'index pour optimiser les performances
 */
final class Version20260123140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout d\'index pour optimiser les requetes frequentes';
    }

    public function up(Schema $schema): void
    {
        // Index sur Contrat
        $this->addSql('CREATE INDEX idx_contrat_statut ON contrat (statut)');
        $this->addSql('CREATE INDEX idx_contrat_periodicite ON contrat (periodicite)');
        $this->addSql('CREATE INDEX idx_contrat_date_debut_fact ON contrat (date_debut_facturation)');
        $this->addSql('CREATE INDEX idx_contrat_date_anniversaire ON contrat (date_anniversaire)');
        $this->addSql('CREATE INDEX idx_contrat_statut_periodicite ON contrat (statut, periodicite)');

        // Index sur Facture
        $this->addSql('CREATE INDEX idx_facture_statut ON facture (statut)');
        $this->addSql('CREATE INDEX idx_facture_date_facture ON facture (date_facture)');
        $this->addSql('CREATE INDEX idx_facture_periode_debut ON facture (periode_debut)');
        $this->addSql('CREATE INDEX idx_facture_type ON facture (type)');
        $this->addSql('CREATE INDEX idx_facture_statut_type ON facture (statut, type)');

        // Index sur Client
        $this->addSql('CREATE INDEX idx_client_actif ON client (actif)');

        // Index sur LigneContrat
        $this->addSql('CREATE INDEX idx_ligne_contrat_contrat ON ligne_contrat (contrat_id)');

        // Index sur LigneFacture
        $this->addSql('CREATE INDEX idx_ligne_facture_facture ON ligne_facture (facture_id)');
    }

    public function down(Schema $schema): void
    {
        // Contrat
        $this->addSql('DROP INDEX idx_contrat_statut ON contrat');
        $this->addSql('DROP INDEX idx_contrat_periodicite ON contrat');
        $this->addSql('DROP INDEX idx_contrat_date_debut_fact ON contrat');
        $this->addSql('DROP INDEX idx_contrat_date_anniversaire ON contrat');
        $this->addSql('DROP INDEX idx_contrat_statut_periodicite ON contrat');

        // Facture
        $this->addSql('DROP INDEX idx_facture_statut ON facture');
        $this->addSql('DROP INDEX idx_facture_date_facture ON facture');
        $this->addSql('DROP INDEX idx_facture_periode_debut ON facture');
        $this->addSql('DROP INDEX idx_facture_type ON facture');
        $this->addSql('DROP INDEX idx_facture_statut_type ON facture');

        // Client
        $this->addSql('DROP INDEX idx_client_actif ON client');

        // LigneContrat
        $this->addSql('DROP INDEX idx_ligne_contrat_contrat ON ligne_contrat');

        // LigneFacture
        $this->addSql('DROP INDEX idx_ligne_facture_facture ON ligne_facture');
    }
}
