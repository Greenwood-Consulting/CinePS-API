<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230308113120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE toto (id INT AUTO_INCREMENT NOT NULL, film_prefere_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, age INT NOT NULL, INDEX IDX_10CCA4F14ACC4AA0 (film_prefere_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE toto ADD CONSTRAINT FK_10CCA4F14ACC4AA0 FOREIGN KEY (film_prefere_id) REFERENCES film (id)');
        $this->addSql('ALTER TABLE film ADD resume VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE toto DROP FOREIGN KEY FK_10CCA4F14ACC4AA0');
        $this->addSql('DROP TABLE toto');
        $this->addSql('ALTER TABLE film DROP resume');
    }
}
