<?php

namespace App\EventSubscriber;

use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Crée automatiquement la table doctrine_migration_versions si elle n'existe pas
 * pour éviter l'erreur "The metadata storage is not up to date"
 */
class MigrationsMetadataSubscriber implements EventSubscriberInterface
{
    private Connection $connection;
    private static bool $tableChecked = false;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9999],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Ne vérifier qu'une seule fois par processus
        if (self::$tableChecked) {
            return;
        }

        try {
            $schemaManager = $this->connection->createSchemaManager();
            $tableName = 'doctrine_migration_versions';
            
            if (!$schemaManager->tablesExist([$tableName])) {
                $this->createMetadataTable($tableName);
            }
            
            self::$tableChecked = true;
        } catch (\Exception $e) {
            // Ignorer silencieusement les erreurs pour éviter les erreurs 500
        }
    }

    private function createMetadataTable(string $tableName): void
    {
        try {
            $this->connection->executeStatement("
                CREATE TABLE IF NOT EXISTS `{$tableName}` (
                    `version` VARCHAR(191) NOT NULL,
                    `executed_at` DATETIME DEFAULT NULL,
                    `execution_time` INT DEFAULT NULL,
                    PRIMARY KEY (`version`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Exception $e) {
            // Ignorer silencieusement les erreurs
        }
    }
}
