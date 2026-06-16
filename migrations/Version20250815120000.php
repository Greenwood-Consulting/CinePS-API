<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Convertit la colonne note pour accepter des valeurs flottantes.
 */
final class Version20250815120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Autorise des valeurs décimales pour les notes en convertissant la colonne en FLOAT.';
    }

    public function up(Schema $schema): void
    {
        // Passer la colonne note de INT à FLOAT pour permettre les notes décimales
        $this->addSql('ALTER TABLE note CHANGE note note DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revenir à un entier si nécessaire
        $this->addSql('ALTER TABLE note CHANGE note note INT DEFAULT NULL');
    }
}
