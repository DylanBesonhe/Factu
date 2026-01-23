<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260123090702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_facture_type ON facture');
        $this->addSql('ALTER TABLE facture CHANGE type type VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE parametre_facturation ADD annee_numero INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE facture CHANGE type type VARCHAR(20) DEFAULT \'facture\' NOT NULL');
        $this->addSql('CREATE INDEX IDX_facture_type ON facture (type)');
        $this->addSql('ALTER TABLE parametre_facturation DROP annee_numero');
    }
}
