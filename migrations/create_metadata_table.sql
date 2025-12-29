-- Script SQL pour créer la table doctrine_migration_versions manuellement
-- Exécutez ce script dans votre base de données MySQL via phpMyAdmin

CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
    `version` VARCHAR(191) NOT NULL,
    `executed_at` DATETIME DEFAULT NULL,
    `execution_time` INT DEFAULT NULL,
    PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

