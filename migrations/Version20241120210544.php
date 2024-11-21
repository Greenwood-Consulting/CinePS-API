<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241120210544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE semaine DROP FOREIGN KEY FK_7B4D8BEA2B4FEC79');
        $this->addSql('DROP INDEX UNIQ_7B4D8BEA2B4FEC79 ON semaine');
        $this->addSql('ALTER TABLE semaine CHANGE raison_proposition_choisie raison_proposition_choisie VARCHAR(500) DEFAULT NULL');

        // Ajouter la nouvelle colonne filmVu_id
        $this->addSql('ALTER TABLE semaine ADD film_vu_id INT DEFAULT NULL');

        // Transférer les données de propositionGagnante à filmVu
        $this->addSql('UPDATE semaine s SET film_vu_id = (SELECT film_id FROM proposition p WHERE p.id = s.proposition_gagnante_id)');

        // Supprimer l'ancienne colonne propositionGagnante_id
        $this->addSql('ALTER TABLE semaine DROP COLUMN proposition_gagnante_id');

        $this->addSql('ALTER TABLE semaine ADD CONSTRAINT FK_7B4D8BEAEC3D50AE FOREIGN KEY (film_vu_id) REFERENCES film (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7B4D8BEAEC3D50AE ON semaine (film_vu_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE semaine DROP FOREIGN KEY FK_7B4D8BEAEC3D50AE');
        $this->addSql('DROP INDEX UNIQ_7B4D8BEAEC3D50AE ON semaine');
        $this->addSql('ALTER TABLE semaine CHANGE raison_proposition_choisie raison_proposition_choisie VARCHAR(255) DEFAULT NULL, CHANGE film_vu_id proposition_gagnante_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE semaine ADD CONSTRAINT FK_7B4D8BEA2B4FEC79 FOREIGN KEY (proposition_gagnante_id) REFERENCES proposition (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7B4D8BEA2B4FEC79 ON semaine (proposition_gagnante_id)');
    }
}
