<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230130143558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE film CHANGE imdb imdb VARCHAR(600) NOT NULL');
        $this->addSql('ALTER TABLE semaine DROP FOREIGN KEY FK_7B4D8BEACCE8CD43');
        $this->addSql('DROP INDEX UNIQ_7B4D8BEACCE8CD43 ON semaine');
        $this->addSql('ALTER TABLE semaine ADD proposeur VARCHAR(255) NOT NULL, DROP proposeur_id, CHANGE jour jour DATE NOT NULL, CHANGE proposition_termine proposition_termine TINYINT(1) NOT NULL, CHANGE theme theme VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE film CHANGE imdb imdb VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE semaine ADD proposeur_id INT DEFAULT NULL, DROP proposeur, CHANGE jour jour DATETIME NOT NULL, CHANGE proposition_termine proposition_termine INT NOT NULL, CHANGE theme theme VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE semaine ADD CONSTRAINT FK_7B4D8BEACCE8CD43 FOREIGN KEY (proposeur_id) REFERENCES membre (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7B4D8BEACCE8CD43 ON semaine (proposeur_id)');
    }
}
