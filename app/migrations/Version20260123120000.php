<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Conformite facturation electronique 2026 - Phase 1
 * Ajout des champs TVA, SIRET, code pays sur Client et Facture
 */
final class Version20260123120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout champs conformite e-invoicing 2026 (TVA, SIRET, code pays, reference commande)';
    }

    public function up(Schema $schema): void
    {
        // Client: ajout siret, tva, code_pays_tva
        $this->addSql('ALTER TABLE client ADD siret VARCHAR(14) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD tva VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD code_pays_tva VARCHAR(2) NOT NULL DEFAULT \'FR\'');

        // Facture: ajout client_siret, client_tva, client_code_pays, reference_commande
        $this->addSql('ALTER TABLE facture ADD client_siret VARCHAR(14) DEFAULT NULL');
        $this->addSql('ALTER TABLE facture ADD client_tva VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE facture ADD client_code_pays VARCHAR(2) NOT NULL DEFAULT \'FR\'');
        $this->addSql('ALTER TABLE facture ADD reference_commande VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Facture: suppression des champs
        $this->addSql('ALTER TABLE facture DROP COLUMN reference_commande');
        $this->addSql('ALTER TABLE facture DROP COLUMN client_code_pays');
        $this->addSql('ALTER TABLE facture DROP COLUMN client_tva');
        $this->addSql('ALTER TABLE facture DROP COLUMN client_siret');

        // Client: suppression des champs
        $this->addSql('ALTER TABLE client DROP COLUMN code_pays_tva');
        $this->addSql('ALTER TABLE client DROP COLUMN tva');
        $this->addSql('ALTER TABLE client DROP COLUMN siret');
    }
}
