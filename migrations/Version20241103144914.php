<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241103144914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE semaine CHANGE raison_film_choisi raison_proposition_choisie VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE semaine ADD CONSTRAINT FK_7B4D8BEACCE8CD43 FOREIGN KEY (proposeur_id) REFERENCES membre (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE semaine DROP FOREIGN KEY FK_7B4D8BEACCE8CD43');
        $this->addSql('ALTER TABLE semaine CHANGE raison_proposition_choisie raison_film_choisi VARCHAR(255) DEFAULT NULL');
    }
}
