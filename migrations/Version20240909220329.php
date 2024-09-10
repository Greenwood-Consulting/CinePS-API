<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240909220329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE semaine ADD proposition_gagnante_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE semaine ADD CONSTRAINT FK_7B4D8BEA2B4FEC79 FOREIGN KEY (proposition_gagnante_id) REFERENCES proposition (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7B4D8BEA2B4FEC79 ON semaine (proposition_gagnante_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE semaine DROP FOREIGN KEY FK_7B4D8BEA2B4FEC79');
        $this->addSql('DROP INDEX UNIQ_7B4D8BEA2B4FEC79 ON semaine');
        $this->addSql('ALTER TABLE semaine DROP proposition_gagnante_id');
    }
}
