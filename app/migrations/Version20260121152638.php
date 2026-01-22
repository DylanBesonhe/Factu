<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Sprint 2 Corrections - Multi-emetteurs avec versioning
 */
final class Version20260121152638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sprint 2 Corrections - Multi-emetteurs avec versioning';
    }

    public function up(Schema $schema): void
    {
        // Vider les tables existantes (repartir de zero selon spec)
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('TRUNCATE TABLE parametre_facturation');
        $this->addSql('TRUNCATE TABLE cgv');
        $this->addSql('TRUNCATE TABLE emetteur');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');

        // Creer les nouvelles tables
        $this->addSql('CREATE TABLE emetteur_cgv (id INT AUTO_INCREMENT NOT NULL, emetteur_id INT NOT NULL, cgv_id INT NOT NULL, par_defaut TINYINT(1) NOT NULL DEFAULT 0, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A9A9CBB479E92E8C (emetteur_id), INDEX IDX_A9A9CBB4C3E49468 (cgv_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emetteur_version (id INT AUTO_INCREMENT NOT NULL, emetteur_id INT NOT NULL, raison_sociale VARCHAR(255) NOT NULL, forme_juridique VARCHAR(50) DEFAULT NULL, capital NUMERIC(15, 2) DEFAULT NULL, adresse LONGTEXT NOT NULL, siren VARCHAR(9) NOT NULL, tva VARCHAR(15) DEFAULT NULL, email VARCHAR(180) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, iban VARCHAR(34) DEFAULT NULL, bic VARCHAR(11) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, date_effet DATE NOT NULL, date_fin DATE DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_52C13D479E92E8C (emetteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE emetteur_cgv ADD CONSTRAINT FK_A9A9CBB479E92E8C FOREIGN KEY (emetteur_id) REFERENCES emetteur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE emetteur_cgv ADD CONSTRAINT FK_A9A9CBB4C3E49468 FOREIGN KEY (cgv_id) REFERENCES cgv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE emetteur_version ADD CONSTRAINT FK_52C13D479E92E8C FOREIGN KEY (emetteur_id) REFERENCES emetteur (id) ON DELETE CASCADE');

        // Modifier la table cgv (retirer par_defaut)
        $this->addSql('ALTER TABLE cgv DROP COLUMN par_defaut');

        // Modifier la table emetteur
        $this->addSql('ALTER TABLE emetteur DROP COLUMN raison_sociale');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN forme_juridique');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN capital');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN adresse');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN siren');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN tva');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN email');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN telephone');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN iban');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN bic');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN logo');
        $this->addSql('ALTER TABLE emetteur ADD code VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE emetteur ADD nom VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE emetteur ADD actif TINYINT(1) NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE emetteur ADD par_defaut TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_52127D677153098 ON emetteur (code)');

        // Modifier la table parametre_facturation
        $this->addSql('ALTER TABLE parametre_facturation ADD emetteur_id INT NOT NULL');
        $this->addSql('ALTER TABLE parametre_facturation ADD CONSTRAINT FK_A3748D2F79E92E8C FOREIGN KEY (emetteur_id) REFERENCES emetteur (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3748D2F79E92E8C ON parametre_facturation (emetteur_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE emetteur_cgv DROP FOREIGN KEY FK_A9A9CBB479E92E8C');
        $this->addSql('ALTER TABLE emetteur_cgv DROP FOREIGN KEY FK_A9A9CBB4C3E49468');
        $this->addSql('ALTER TABLE emetteur_version DROP FOREIGN KEY FK_52C13D479E92E8C');
        $this->addSql('DROP TABLE emetteur_cgv');
        $this->addSql('DROP TABLE emetteur_version');

        $this->addSql('ALTER TABLE cgv ADD par_defaut TINYINT(1) NOT NULL DEFAULT 0');

        $this->addSql('DROP INDEX UNIQ_52127D677153098 ON emetteur');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN code');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN nom');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN actif');
        $this->addSql('ALTER TABLE emetteur DROP COLUMN par_defaut');
        $this->addSql('ALTER TABLE emetteur ADD raison_sociale VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE emetteur ADD forme_juridique VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE emetteur ADD capital NUMERIC(15, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE emetteur ADD adresse LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE emetteur ADD siren VARCHAR(9) NOT NULL');
        $this->addSql('ALTER TABLE emetteur ADD tva VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE emetteur ADD email VARCHAR(180) NOT NULL');
        $this->addSql('ALTER TABLE emetteur ADD telephone VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE emetteur ADD iban VARCHAR(34) DEFAULT NULL');
        $this->addSql('ALTER TABLE emetteur ADD bic VARCHAR(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE emetteur ADD logo VARCHAR(255) DEFAULT NULL');

        $this->addSql('ALTER TABLE parametre_facturation DROP FOREIGN KEY FK_A3748D2F79E92E8C');
        $this->addSql('DROP INDEX UNIQ_A3748D2F79E92E8C ON parametre_facturation');
        $this->addSql('ALTER TABLE parametre_facturation DROP COLUMN emetteur_id');
    }
}
