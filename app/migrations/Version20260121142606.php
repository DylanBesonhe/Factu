<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260121142606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cgv (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, fichier_chemin VARCHAR(255) NOT NULL, fichier_original VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE DEFAULT NULL, par_defaut TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE emetteur (id INT AUTO_INCREMENT NOT NULL, raison_sociale VARCHAR(255) NOT NULL, forme_juridique VARCHAR(50) DEFAULT NULL, capital NUMERIC(15, 2) DEFAULT NULL, adresse LONGTEXT NOT NULL, siren VARCHAR(9) NOT NULL, tva VARCHAR(15) DEFAULT NULL, email VARCHAR(180) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, iban VARCHAR(34) DEFAULT NULL, bic VARCHAR(11) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prix_defaut NUMERIC(10, 2) NOT NULL, taux_tva NUMERIC(5, 2) NOT NULL, actif TINYINT NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C2426286C6E55B5 (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE parametre_facturation (id INT AUTO_INCREMENT NOT NULL, format_numero VARCHAR(50) NOT NULL, prochain_numero INT NOT NULL, delai_echeance INT NOT NULL, mentions_legales VARCHAR(255) DEFAULT NULL, email_objet VARCHAR(255) DEFAULT NULL, email_corps LONGTEXT DEFAULT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cgv');
        $this->addSql('DROP TABLE emetteur');
        $this->addSql('DROP TABLE module');
        $this->addSql('DROP TABLE parametre_facturation');
    }
}
