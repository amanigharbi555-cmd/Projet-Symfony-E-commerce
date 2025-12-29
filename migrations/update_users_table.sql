-- Script SQL pour rendre les champs address, zipcode et city nullable
-- Exécutez ce script directement dans votre base de données MySQL via phpMyAdmin

ALTER TABLE users MODIFY address VARCHAR(255) NULL;
ALTER TABLE users MODIFY zipcode VARCHAR(5) NULL;
ALTER TABLE users MODIFY city VARCHAR(150) NULL;

