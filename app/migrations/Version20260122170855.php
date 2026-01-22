<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122170855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Ajouter la colonne nullable d'abord
        $this->addSql('ALTER TABLE contrat ADD date_debut_facturation DATE DEFAULT NULL');
        // Copier la valeur de date_anniversaire pour les contrats existants
        $this->addSql('UPDATE contrat SET date_debut_facturation = date_anniversaire WHERE date_debut_facturation IS NULL');
        // Rendre la colonne NOT NULL
        $this->addSql('ALTER TABLE contrat MODIFY date_debut_facturation DATE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contrat DROP date_debut_facturation');
    }
}
