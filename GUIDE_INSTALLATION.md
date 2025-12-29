# Guide d'Installation - Application E-Commerce Symfony 6

Ce guide vous permettra d'installer et de lancer cette application e-commerce Symfony 6 sur une nouvelle machine.

## 📋 Prérequis

### Logiciels requis

1. **PHP 8.0.2 ou supérieur**
   - Extensions PHP requises :
     - `pdo_mysql` (pour la connexion à MySQL)
     - `gd` (pour la manipulation d'images)
     - `mbstring`
     - `xml`
     - `ctype`
     - `iconv`
     - `json`
     - `openssl`

2. **Composer** (gestionnaire de dépendances PHP)
   - Télécharger depuis : https://getcomposer.org/download/

3. **MySQL** (version 5.7 ou supérieure)
   - Ou utiliser XAMPP/WAMP qui inclut MySQL

4. **Git** (optionnel, pour cloner le projet)

## 🚀 Installation étape par étape

### Étape 1 : Cloner ou copier le projet

```bash
# Si vous utilisez Git
git clone <url-du-repo> e-commerce-symfony-6
cd e-commerce-symfony-6

# Ou simplement copier le dossier du projet sur la nouvelle machine
```

### Étape 2 : Installer les dépendances PHP

```bash
# Installer Composer si ce n'est pas déjà fait
# Windows : Télécharger composer-setup.exe depuis getcomposer.org

# Installer les dépendances du projet
composer install
```

### Étape 3 : Configuration de l'environnement

1. **Créer le fichier `.env.local`** (copie de `.env` et personnalisation) :

```bash
# Copier le fichier .env
copy .env .env.local
```

2. **Modifier le fichier `.env.local`** avec vos paramètres :

```env
# Base de données
DATABASE_URL="mysql://username:password@127.0.0.1:3306/nom_de_la_base?serverVersion=8.0.32&charset=utf8mb4"

# Remplacez :
# - username : votre nom d'utilisateur MySQL
# - password : votre mot de passe MySQL
# - nom_de_la_base : le nom de votre base de données
# - 8.0.32 : votre version MySQL (ou 5.7.0 pour MySQL 5.7)
```

3. **Générer la clé secrète de l'application** :

```bash
php bin/console secrets:generate-keys
```

### Étape 4 : Configuration de PHP

#### Vérifier les extensions PHP

```bash
php -m
```

Vous devez voir dans la liste :
- `pdo_mysql`
- `gd`
- `mbstring`
- `xml`
- `ctype`
- `iconv`
- `json`
- `openssl`

#### Activer les extensions manquantes

1. **Trouver le fichier `php.ini`** :

```bash
php --ini
```

2. **Ouvrir `php.ini`** (en tant qu'administrateur)

3. **Décommenter les extensions nécessaires** :

```ini
; Trouver et décommenter (supprimer le ;) :
extension=pdo_mysql
extension=gd
extension=mbstring
extension=openssl
```

4. **Vérifier le `extension_dir`** :

```ini
extension_dir = "C:\chemin\vers\php\ext"
```

Assurez-vous que le chemin pointe vers le dossier contenant les fichiers `.dll` des extensions.

5. **Redémarrer le serveur web** après modification de `php.ini`

### Étape 5 : Créer la base de données

1. **Créer la base de données MySQL** :

```sql
CREATE DATABASE nom_de_la_base CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Créer la table des métadonnées de migrations** :

```sql
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
    `version` VARCHAR(191) NOT NULL,
    `executed_at` DATETIME DEFAULT NULL,
    `execution_time` INT DEFAULT NULL,
    PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Étape 6 : Exécuter les migrations

```bash
# Synchroniser le stockage des métadonnées
php bin/console doctrine:migrations:sync-metadata-storage

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction
```

### Étape 7 : Charger les données de test (optionnel)

```bash
# Charger les fixtures (catégories, produits, utilisateurs)
php bin/console doctrine:fixtures:load --no-interaction
```

**Compte administrateur par défaut :**
- Email : `admin@demo.com`
- Mot de passe : `admin`

### Étape 8 : Créer les dossiers nécessaires

```bash
# Créer les dossiers pour les uploads d'images
mkdir -p public/assets/uploads/products/mini
mkdir -p public/assets/uploads/products

# Sur Windows PowerShell :
New-Item -ItemType Directory -Force -Path "public\assets\uploads\products\mini"
New-Item -ItemType Directory -Force -Path "public\assets\uploads\products"
```

### Étape 9 : Vider le cache

```bash
php bin/console cache:clear
```

### Étape 10 : Lancer le serveur de développement

```bash
php -S localhost:8000 -t public
```

L'application sera accessible à : **http://localhost:8000**

## 🔧 Vérification de l'installation

### Vérifier que tout fonctionne

1. **Accéder à l'application** : http://localhost:8000
2. **Tester la connexion** : http://localhost:8000/connexion
   - Email : `admin@demo.com`
   - Mot de passe : `admin`
3. **Accéder à l'administration** : http://localhost:8000/admin

### Vérifier les extensions PHP

```bash
# Vérifier GD
php -r "echo extension_loaded('gd') ? 'GD OK' : 'GD MANQUANT';"

# Vérifier PDO MySQL
php -r "echo extension_loaded('pdo_mysql') ? 'PDO MySQL OK' : 'PDO MySQL MANQUANT';"
```

## 📁 Structure des dossiers importants

```
e-commerce-symfony-6/
├── config/              # Configuration de l'application
├── migrations/          # Migrations de base de données
├── public/             # Point d'entrée web
│   └── assets/         # Assets statiques (CSS, JS, images)
├── src/                # Code source de l'application
│   ├── Controller/     # Contrôleurs
│   ├── Entity/         # Entités Doctrine
│   ├── Form/           # Formulaires Symfony
│   ├── Repository/     # Repositories Doctrine
│   └── Service/        # Services métier
├── templates/          # Templates Twig
├── var/                # Fichiers temporaires (cache, logs)
└── vendor/             # Dépendances Composer
```

## ⚙️ Configuration importante

### Fichiers de configuration à vérifier

1. **`.env.local`** : Variables d'environnement (base de données, secrets)
2. **`config/packages/doctrine.yaml`** : Configuration Doctrine
3. **`config/packages/doctrine_migrations.yaml`** : Configuration des migrations
4. **`config/packages/security.yaml`** : Configuration de sécurité

## 🐛 Résolution de problèmes courants

### Erreur : "Extension GD non chargée"

**Solution :**
1. Vérifier que `extension=gd` est décommenté dans `php.ini`
2. Vérifier que `php_gd.dll` existe dans le dossier `ext`
3. Redémarrer le serveur web

### Erreur : "PDO MySQL driver not found"

**Solution :**
1. Vérifier que `extension=pdo_mysql` est décommenté dans `php.ini`
2. Vérifier que `php_pdo_mysql.dll` existe dans le dossier `ext`
3. Redémarrer le serveur web

### Erreur : "The metadata storage is not up to date"

**Solution :**
```bash
php bin/console doctrine:migrations:sync-metadata-storage
```

### Erreur : "Could not find driver"

**Solution :**
- Vérifier que les extensions PDO sont activées dans `php.ini`
- Vérifier que le `extension_dir` pointe vers le bon dossier

### Erreur : "Permission denied" sur les dossiers

**Solution :**
- Donner les permissions d'écriture sur :
  - `var/` (cache et logs)
  - `public/assets/uploads/` (upload d'images)

## 📝 Notes importantes

1. **Ne jamais commiter** le fichier `.env.local` (il contient des informations sensibles)
2. **Le dossier `vendor/`** est généré par Composer, ne pas le modifier manuellement
3. **Le dossier `var/`** contient le cache, peut être supprimé et régénéré avec `php bin/console cache:clear`
4. **Les migrations** doivent être exécutées dans l'ordre

## 🔐 Sécurité en production

Avant de mettre en production :

1. **Changer le mot de passe admin** par défaut
2. **Désactiver le mode debug** : `APP_ENV=prod` dans `.env.local`
3. **Générer des secrets uniques** : `php bin/console secrets:generate-keys`
4. **Configurer un serveur web** (Apache/Nginx) au lieu du serveur PHP intégré
5. **Configurer HTTPS**
6. **Sauvegarder régulièrement la base de données**

## 📞 Support

En cas de problème :
1. Vérifier les logs dans `var/log/dev.log`
2. Vérifier que toutes les extensions PHP sont activées
3. Vérifier la configuration de la base de données dans `.env.local`
4. Vérifier que les migrations ont été exécutées

---

**Bon développement ! 🚀**

