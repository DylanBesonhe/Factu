<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260123075758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout factures ponctuelles et avoirs: type, factureParente, motifAvoir';
    }

    public function up(Schema $schema): void
    {
        // Ajouter les nouveaux champs
        $this->addSql('ALTER TABLE facture ADD type VARCHAR(20) DEFAULT \'facture\' NOT NULL, ADD motif_avoir LONGTEXT DEFAULT NULL, ADD facture_parente_id INT DEFAULT NULL, CHANGE periode_debut periode_debut DATE DEFAULT NULL, CHANGE periode_fin periode_fin DATE DEFAULT NULL, CHANGE contrat_id contrat_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE866410286B5194 FOREIGN KEY (facture_parente_id) REFERENCES facture (id)');
        $this->addSql('CREATE INDEX IDX_FE866410286B5194 ON facture (facture_parente_id)');
        $this->addSql('CREATE INDEX IDX_facture_type ON facture (type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE866410286B5194');
        $this->addSql('DROP INDEX IDX_FE866410286B5194 ON facture');
        $this->addSql('ALTER TABLE facture DROP type, DROP motif_avoir, DROP facture_parente_id, CHANGE periode_debut periode_debut DATE NOT NULL, CHANGE periode_fin periode_fin DATE NOT NULL, CHANGE contrat_id contrat_id INT NOT NULL');
    }
}
