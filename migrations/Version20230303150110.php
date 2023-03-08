<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230303150110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE toto ADD film_prefere_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE toto ADD CONSTRAINT FK_10CCA4F14ACC4AA0 FOREIGN KEY (film_prefere_id) REFERENCES film (id)');
        $this->addSql('CREATE INDEX IDX_10CCA4F14ACC4AA0 ON toto (film_prefere_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE toto DROP FOREIGN KEY FK_10CCA4F14ACC4AA0');
        $this->addSql('DROP INDEX IDX_10CCA4F14ACC4AA0 ON toto');
        $this->addSql('ALTER TABLE toto DROP film_prefere_id');
    }
}
