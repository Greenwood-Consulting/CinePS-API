<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250808183554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // remove existing date column and ⚠️data⚠️
        // prevoir une sauvegarde des data de la table "film" (au cas où...)
        $this->addSql('ALTER TABLE film DROP date');
    }

    public function down(Schema $schema): void
    {
        // ajoute un champ date a la table "film"
        $this->addSql('ALTER TABLE film ADD date DATE');
    }
}
