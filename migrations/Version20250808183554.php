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
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE proposition ADD date DATETIME');
        
        // Copie des données depuis film vers proposition
        $this->addSql('
            UPDATE proposition p
            JOIN film f ON p.film_id = f.id
            SET p.date = f.date
        ');

        $this->addSql('ALTER TABLE proposition CHANGE date date DATETIME NOT NULL');
        
        // ⚠️ remove existing date column and data
        $this->addSql('ALTER TABLE film DROP date');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE film ADD date DATE NOT NULL');

        // Copie des données depuis proposition vers film 
        $this->addSql('
            UPDATE proposition p
            JOIN film f ON p.film_id = f.id
            SET f.date = p.date
        ');

        $this->addSql('ALTER TABLE proposition DROP date');
    }
}
