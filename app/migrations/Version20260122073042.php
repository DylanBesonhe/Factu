<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122073042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(20) NOT NULL, raison_sociale VARCHAR(255) NOT NULL, siren VARCHAR(9) DEFAULT NULL, iban VARCHAR(34) DEFAULT NULL, bic VARCHAR(11) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, actif TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C744045577153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE client_lien (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, client_source_id INT NOT NULL, client_cible_id INT NOT NULL, INDEX IDX_74AE77D77E1CCC6E (client_source_id), INDEX IDX_74AE77D787886ECC (client_cible_id), UNIQUE INDEX unique_client_lien (client_source_id, client_cible_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE client_note (id INT AUTO_INCREMENT NOT NULL, contenu LONGTEXT NOT NULL, created_at DATETIME NOT NULL, auteur VARCHAR(100) DEFAULT NULL, client_id INT NOT NULL, INDEX IDX_1E21397619EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) DEFAULT NULL, fonction VARCHAR(100) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, principal TINYINT NOT NULL, created_at DATETIME NOT NULL, client_id INT NOT NULL, INDEX IDX_4C62E63819EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contrat (id INT AUTO_INCREMENT NOT NULL, numero VARCHAR(50) NOT NULL, date_signature DATE NOT NULL, date_anniversaire DATE NOT NULL, date_fin DATE DEFAULT NULL, periodicite VARCHAR(20) NOT NULL, facture_particuliere TINYINT NOT NULL, commentaire_facture LONGTEXT DEFAULT NULL, remise_globale NUMERIC(5, 2) DEFAULT NULL, statut VARCHAR(20) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, client_id INT NOT NULL, instance_id INT NOT NULL, emetteur_id INT NOT NULL, UNIQUE INDEX UNIQ_60349993F55AE19E (numero), INDEX IDX_6034999319EB6921 (client_id), INDEX IDX_603499933A51721D (instance_id), INDEX IDX_6034999379E92E8C (emetteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contrat_cgv (id INT AUTO_INCREMENT NOT NULL, date_debut DATE NOT NULL, date_fin DATE DEFAULT NULL, created_at DATETIME NOT NULL, contrat_id INT NOT NULL, cgv_id INT NOT NULL, INDEX IDX_C0CEC3621823061F (contrat_id), INDEX IDX_C0CEC362C3E49468 (cgv_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contrat_evenement (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, date_effet DATE DEFAULT NULL, auteur VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, contrat_id INT NOT NULL, INDEX IDX_80211FEE1823061F (contrat_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contrat_fichier (id INT AUTO_INCREMENT NOT NULL, nom_original VARCHAR(255) NOT NULL, chemin VARCHAR(255) NOT NULL, type_mime VARCHAR(100) DEFAULT NULL, taille INT DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, contrat_id INT NOT NULL, INDEX IDX_32B806691823061F (contrat_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE historique_licence (id INT AUTO_INCREMENT NOT NULL, nb_licences INT NOT NULL, date_effet DATE NOT NULL, source VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, contrat_id INT NOT NULL, INDEX IDX_7FBEB9371823061F (contrat_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE instance (id INT AUTO_INCREMENT NOT NULL, nom_actuel VARCHAR(255) NOT NULL, url VARCHAR(500) DEFAULT NULL, actif TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_4230B1DEC6DEC077 (nom_actuel), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE instance_nom (id INT AUTO_INCREMENT NOT NULL, ancien_nom VARCHAR(255) NOT NULL, date_changement DATETIME NOT NULL, created_at DATETIME NOT NULL, instance_id INT NOT NULL, INDEX IDX_6486008D3A51721D (instance_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ligne_contrat (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, remise NUMERIC(5, 2) DEFAULT NULL, taux_tva NUMERIC(5, 2) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, contrat_id INT NOT NULL, module_id INT NOT NULL, INDEX IDX_FFADA7AA1823061F (contrat_id), INDEX IDX_FFADA7AAAFC2B591 (module_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE client_lien ADD CONSTRAINT FK_74AE77D77E1CCC6E FOREIGN KEY (client_source_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE client_lien ADD CONSTRAINT FK_74AE77D787886ECC FOREIGN KEY (client_cible_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE client_note ADD CONSTRAINT FK_1E21397619EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E63819EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_6034999319EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_603499933A51721D FOREIGN KEY (instance_id) REFERENCES instance (id)');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_6034999379E92E8C FOREIGN KEY (emetteur_id) REFERENCES emetteur (id)');
        $this->addSql('ALTER TABLE contrat_cgv ADD CONSTRAINT FK_C0CEC3621823061F FOREIGN KEY (contrat_id) REFERENCES contrat (id)');
        $this->addSql('ALTER TABLE contrat_cgv ADD CONSTRAINT FK_C0CEC362C3E49468 FOREIGN KEY (cgv_id) REFERENCES cgv (id)');
        $this->addSql('ALTER TABLE contrat_evenement ADD CONSTRAINT FK_80211FEE1823061F FOREIGN KEY (contrat_id) REFERENCES contrat (id)');
        $this->addSql('ALTER TABLE contrat_fichier ADD CONSTRAINT FK_32B806691823061F FOREIGN KEY (contrat_id) REFERENCES contrat (id)');
        $this->addSql('ALTER TABLE historique_licence ADD CONSTRAINT FK_7FBEB9371823061F FOREIGN KEY (contrat_id) REFERENCES contrat (id)');
        $this->addSql('ALTER TABLE instance_nom ADD CONSTRAINT FK_6486008D3A51721D FOREIGN KEY (instance_id) REFERENCES instance (id)');
        $this->addSql('ALTER TABLE ligne_contrat ADD CONSTRAINT FK_FFADA7AA1823061F FOREIGN KEY (contrat_id) REFERENCES contrat (id)');
        $this->addSql('ALTER TABLE ligne_contrat ADD CONSTRAINT FK_FFADA7AAAFC2B591 FOREIGN KEY (module_id) REFERENCES module (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_lien DROP FOREIGN KEY FK_74AE77D77E1CCC6E');
        $this->addSql('ALTER TABLE client_lien DROP FOREIGN KEY FK_74AE77D787886ECC');
        $this->addSql('ALTER TABLE client_note DROP FOREIGN KEY FK_1E21397619EB6921');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E63819EB6921');
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_6034999319EB6921');
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_603499933A51721D');
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_6034999379E92E8C');
        $this->addSql('ALTER TABLE contrat_cgv DROP FOREIGN KEY FK_C0CEC3621823061F');
        $this->addSql('ALTER TABLE contrat_cgv DROP FOREIGN KEY FK_C0CEC362C3E49468');
        $this->addSql('ALTER TABLE contrat_evenement DROP FOREIGN KEY FK_80211FEE1823061F');
        $this->addSql('ALTER TABLE contrat_fichier DROP FOREIGN KEY FK_32B806691823061F');
        $this->addSql('ALTER TABLE historique_licence DROP FOREIGN KEY FK_7FBEB9371823061F');
        $this->addSql('ALTER TABLE instance_nom DROP FOREIGN KEY FK_6486008D3A51721D');
        $this->addSql('ALTER TABLE ligne_contrat DROP FOREIGN KEY FK_FFADA7AA1823061F');
        $this->addSql('ALTER TABLE ligne_contrat DROP FOREIGN KEY FK_FFADA7AAAFC2B591');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE client_lien');
        $this->addSql('DROP TABLE client_note');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE contrat');
        $this->addSql('DROP TABLE contrat_cgv');
        $this->addSql('DROP TABLE contrat_evenement');
        $this->addSql('DROP TABLE contrat_fichier');
        $this->addSql('DROP TABLE historique_licence');
        $this->addSql('DROP TABLE instance');
        $this->addSql('DROP TABLE instance_nom');
        $this->addSql('DROP TABLE ligne_contrat');
    }
}
