<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122164036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, numero VARCHAR(50) NOT NULL, client_code VARCHAR(20) NOT NULL, client_raison_sociale VARCHAR(255) NOT NULL, client_adresse LONGTEXT DEFAULT NULL, client_siren VARCHAR(9) DEFAULT NULL, emetteur_raison_sociale VARCHAR(255) NOT NULL, emetteur_adresse LONGTEXT NOT NULL, emetteur_siren VARCHAR(9) NOT NULL, emetteur_tva VARCHAR(15) DEFAULT NULL, emetteur_iban VARCHAR(34) DEFAULT NULL, emetteur_bic VARCHAR(11) DEFAULT NULL, date_facture DATE NOT NULL, date_echeance DATE NOT NULL, periode_debut DATE NOT NULL, periode_fin DATE NOT NULL, total_ht NUMERIC(12, 2) NOT NULL, total_tva NUMERIC(12, 2) NOT NULL, total_ttc NUMERIC(12, 2) NOT NULL, remise_globale NUMERIC(5, 2) DEFAULT NULL, commentaire LONGTEXT DEFAULT NULL, mentions_legales LONGTEXT DEFAULT NULL, statut VARCHAR(20) NOT NULL, date_validation DATETIME DEFAULT NULL, date_envoi DATETIME DEFAULT NULL, date_paiement DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, contrat_id INT NOT NULL, UNIQUE INDEX UNIQ_FE866410F55AE19E (numero), INDEX IDX_FE8664101823061F (contrat_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ligne_facture (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, quantite INT NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, remise NUMERIC(5, 2) DEFAULT NULL, taux_tva NUMERIC(5, 2) NOT NULL, total_ht NUMERIC(12, 2) NOT NULL, montant_tva NUMERIC(12, 2) NOT NULL, total_ttc NUMERIC(12, 2) NOT NULL, created_at DATETIME NOT NULL, facture_id INT NOT NULL, INDEX IDX_611F5A297F2DEE08 (facture_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE8664101823061F FOREIGN KEY (contrat_id) REFERENCES contrat (id)');
        $this->addSql('ALTER TABLE ligne_facture ADD CONSTRAINT FK_611F5A297F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE8664101823061F');
        $this->addSql('ALTER TABLE ligne_facture DROP FOREIGN KEY FK_611F5A297F2DEE08');
        $this->addSql('DROP TABLE facture');
        $this->addSql('DROP TABLE ligne_facture');
    }
}
