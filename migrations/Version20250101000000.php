<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour permettre la suppression en cascade des produits
 * même s'ils sont référencés dans des commandes
 */
final class Version20250101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Modifie la contrainte de clé étrangère pour permettre la suppression en cascade des produits';
    }

    public function up(Schema $schema): void
    {
        // Supprimer l'ancienne contrainte
        $this->addSql('ALTER TABLE orders_details DROP FOREIGN KEY FK_835379F16C8A81A9');
        
        // Recréer la contrainte avec ON DELETE CASCADE
        $this->addSql('ALTER TABLE orders_details ADD CONSTRAINT FK_835379F16C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la contrainte avec CASCADE
        $this->addSql('ALTER TABLE orders_details DROP FOREIGN KEY FK_835379F16C8A81A9');
        
        // Recréer la contrainte sans CASCADE (comportement par défaut)
        $this->addSql('ALTER TABLE orders_details ADD CONSTRAINT FK_835379F16C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id)');
    }
}

