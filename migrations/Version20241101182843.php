<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241101182843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14DB96F9E');
        $this->addSql('DROP INDEX IDX_CFBDFA14DB96F9E ON note');
        $this->addSql('ALTER TABLE note CHANGE proposition_id film_id INT DEFAULT NULL');

        // Data migration: replace proposition_id with corresponding film_id
        $this->addSql('UPDATE note n JOIN proposition p ON n.film_id = p.id SET n.film_id = p.film_id');

        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14567F5183 FOREIGN KEY (film_id) REFERENCES film (id)');
        $this->addSql('CREATE INDEX IDX_CFBDFA14567F5183 ON note (film_id)');
        $this->addSql('ALTER TABLE semaine ADD CONSTRAINT FK_7B4D8BEACCE8CD43 FOREIGN KEY (proposeur_id) REFERENCES membre (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14567F5183');
        $this->addSql('DROP INDEX IDX_CFBDFA14567F5183 ON note');
        $this->addSql('ALTER TABLE note CHANGE film_id proposition_id INT DEFAULT NULL');

        // Data migration: revert film_id back to proposition_id
        $this->addSql('UPDATE note n JOIN proposition p ON n.proposition_id = p.film_id SET n.proposition_id = p.id');

        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14DB96F9E FOREIGN KEY (proposition_id) REFERENCES proposition (id)');
        $this->addSql('CREATE INDEX IDX_CFBDFA14DB96F9E ON note (proposition_id)');
        $this->addSql('ALTER TABLE semaine DROP FOREIGN KEY FK_7B4D8BEACCE8CD43');
    }
}
