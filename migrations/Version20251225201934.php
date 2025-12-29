<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251225201934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rendre les champs address, zipcode et city nullable dans la table users';
    }

    public function up(Schema $schema): void
    {
        // Vérifier si la table users existe avant de la modifier
        try {
            $this->connection->executeQuery('SELECT 1 FROM users LIMIT 1');
            // La table existe, on peut la modifier
            $this->addSql('ALTER TABLE users MODIFY address VARCHAR(255) NULL');
            $this->addSql('ALTER TABLE users MODIFY zipcode VARCHAR(5) NULL');
            $this->addSql('ALTER TABLE users MODIFY city VARCHAR(150) NULL');
        } catch (\Exception $e) {
            // La table n'existe pas encore, elle sera créée avec les bonnes valeurs par la migration suivante
            // On ignore cette migration
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // Syntaxe MySQL pour rendre les colonnes NOT NULL
        $this->addSql('ALTER TABLE users MODIFY address VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users MODIFY zipcode VARCHAR(5) NOT NULL');
        $this->addSql('ALTER TABLE users MODIFY city VARCHAR(150) NOT NULL');
    }
}

