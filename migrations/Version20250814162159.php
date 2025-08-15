<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250814162159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pre_selection_film (film_id INT NOT NULL, pre_selection_id INT NOT NULL, INDEX IDX_836B97567F5183 (film_id), INDEX IDX_836B9724D8A866 (pre_selection_id), PRIMARY KEY(film_id, pre_selection_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pre_selection (id INT AUTO_INCREMENT NOT NULL, membre_id INT NOT NULL, theme VARCHAR(255) NOT NULL, INDEX IDX_64C1DAAB6A99F74A (membre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pre_selection_film ADD CONSTRAINT FK_836B97567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pre_selection_film ADD CONSTRAINT FK_836B9724D8A866 FOREIGN KEY (pre_selection_id) REFERENCES pre_selection (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pre_selection ADD CONSTRAINT FK_64C1DAAB6A99F74A FOREIGN KEY (membre_id) REFERENCES membre (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pre_selection_film DROP FOREIGN KEY FK_836B97567F5183');
        $this->addSql('ALTER TABLE pre_selection_film DROP FOREIGN KEY FK_836B9724D8A866');
        $this->addSql('ALTER TABLE pre_selection DROP FOREIGN KEY FK_64C1DAAB6A99F74A');
        $this->addSql('DROP TABLE pre_selection_film');
        $this->addSql('DROP TABLE pre_selection');
    }
}
