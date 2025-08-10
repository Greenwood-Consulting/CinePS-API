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
        // ajoute un champ date a la table "proposition"
        $this->addSql('ALTER TABLE proposition ADD date DATETIME');
        
        // Copie des données depuis "film" vers "proposition"
        $this->addSql('
            UPDATE proposition p
            JOIN film f ON p.film_id = f.id
            SET p.date = f.date
        ');

        // applique la contrainte "not null", apres la copie des données
        $this->addSql('ALTER TABLE proposition CHANGE date date DATETIME NOT NULL');
        
        //  remove existing date column and ⚠️data⚠️
        $this->addSql('ALTER TABLE film DROP date');
    }

    public function down(Schema $schema): void
    {
        // ajoute un champ date a la table "film"
        $this->addSql('ALTER TABLE film ADD date DATE');
        
        // Copie des données depuis "proposition" vers "film"
        $this->addSql('
            UPDATE film f
            JOIN proposition p ON p.film_id = f.id
            SET f.date = p.date
        ');

        // applique la contrainte "not null", apres la copie des données
        // Si des films ont été créés sans être liés a une proposition, cette requete sera en erreur
        // mais ce n'est pas grave, le champ date correspond a la date de proposition du film
        // si le champ date (de proposition) reste sur la table film, alors nullable est preferable
        // $this->addSql('ALTER TABLE film CHANGE date date DATE NOT NULL');

        // remove existing date column and ⚠️data⚠️
        $this->addSql('ALTER TABLE proposition DROP date');
    }
}
