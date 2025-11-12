# Mobile Money API - OM Pay

[![Laravel](https://img.shields.io/badge/Laravel-10.10-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-blue.svg)](https://postgresql.org)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://docker.com)

Une API REST complète pour un système de paiement mobile développée avec Laravel. Ce projet permet de gérer les utilisateurs, clients, comptes bancaires, marchands et transactions financières.

## 📋 Table des Matières

- [À propos du projet](#-à-propos-du-projet)
- [Fonctionnalités](#-fonctionnalités)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Structure de la base de données](#-structure-de-la-base-de-données)
- [Modèles et Relations](#-modèles-et-relations)
- [API Endpoints](#-api-endpoints)
- [Déploiement](#-déploiement)
- [Développement](#-développement)
- [Tests](#-tests)
- [Contribution](#-contribution)
- [Licence](#-licence)

## 🚀 À propos du projet

Cette API mobile money permet de créer un système complet de paiement mobile similaire à Orange Money, Wave, etc. Elle gère :

- L'authentification des utilisateurs
- La gestion des comptes clients
- Les transactions financières (transferts, paiements)
- L'intégration avec les marchands
- Les paiements via QR code

Le projet est conçu pour être scalable, sécurisé et facile à déployer.

## ✨ Fonctionnalités

- ✅ Authentification JWT avec Laravel Sanctum
- ✅ Gestion des utilisateurs (clients et marchands)
- ✅ Système de comptes bancaires
- ✅ Transactions sécurisées
- ✅ Paiements marchands avec QR code
- ✅ API RESTful complète
- ✅ Containerisation Docker
- ✅ Déploiement sur Render
- ✅ Documentation API automatique (Swagger)

## 🛠 Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- **PHP 8.1 ou supérieur**
- **Composer** (gestionnaire de dépendances PHP)
- **Docker & Docker Compose** (pour la containerisation)
- **Git** (pour cloner le repository)
- **PostgreSQL** (si vous voulez utiliser une base locale)

### Installation des prérequis

#### Sur Ubuntu/Debian :
```bash
# Mise à jour du système
sudo apt update && sudo apt upgrade -y

# Installation de PHP 8.1+
sudo apt install php8.1 php8.1-cli php8.1-fpm php8.1-pgsql php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-bcmath

# Installation de Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Installation de Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo apt install docker-compose
```

#### Sur macOS :
```bash
# Installation de PHP via Homebrew
brew install php@8.1 composer

# Installation de Docker Desktop
# Téléchargez depuis https://www.docker.com/products/docker-desktop
```

#### Sur Windows :
- Installez **PHP 8.1+** depuis https://windows.php.net/download/
- Installez **Composer** depuis https://getcomposer.org/
- Installez **Docker Desktop** depuis https://www.docker.com/products/docker-desktop

## 📦 Installation

### 1. Cloner le repository

```bash
git clone https://github.com/votre-username/mobile-money-api.git
cd mobile-money-api
```

### 2. Installation des dépendances

```bash
composer install
```

### 3. Configuration de l'environnement

Copiez le fichier d'exemple d'environnement :

```bash
cp .env.example .env
```

### 4. Génération de la clé d'application

```bash
php artisan key:generate
```

## ⚙️ Configuration

### Variables d'environnement

Modifiez le fichier `.env` avec vos paramètres :

```env
# Application
APP_NAME="OM Pay API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:9000

# Base de données
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5433
DB_DATABASE=laravel_api
DB_USERNAME=postgres
DB_PASSWORD=postgres

# Cache et Sessions
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Mail (optionnel)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe
MAIL_ENCRYPTION=tls
```

### Avec Docker

Le projet est configuré pour fonctionner avec Docker. Pour démarrer :

```bash
# Construire et démarrer les conteneurs
docker-compose up -d --build

# Accéder au conteneur de l'application
docker-compose exec app bash
```

## 🗄️ Structure de la base de données

Le système utilise PostgreSQL avec les tables suivantes :

### Tables principales

1. **users** - Utilisateurs du système
2. **clients** - Informations des clients
3. **comptes** - Comptes bancaires
4. **marchands** - Informations des marchands
5. **transactions** - Historique des transactions

### Schéma détaillé

#### users
- `id` (UUID) - Clé primaire
- `username` (string) - Nom d'utilisateur unique
- `password` (string) - Mot de passe hashé
- `role` (string) - Rôle (client/marchand)
- `langue` (string) - Langue préférée
- `theme` (string) - Thème d'interface

#### clients
- `id` (UUID) - Clé primaire
- `user_id` (UUID) - Référence vers users
- `nom` (string) - Nom du client
- `prenom` (string) - Prénom du client
- `telephone` (string) - Numéro de téléphone
- `email` (string) - Email unique

#### comptes
- `id` (UUID) - Clé primaire
- `client_id` (UUID) - Référence vers clients
- `numeroCompte` (string) - Numéro de compte unique
- `solde` (decimal) - Solde du compte
- `devise` (string) - Devise (XOF par défaut)
- `dateDerniereMaj` (timestamp) - Dernière mise à jour

#### marchands
- `id` (UUID) - Clé primaire
- `user_id` (UUID) - Référence vers users
- `nom` (string) - Nom du marchand
- `codeMarchand` (string) - Code unique du marchand
- `categorie` (string) - Catégorie d'activité
- `telephone` (string) - Numéro de téléphone
- `adresse` (text) - Adresse complète
- `qrCode` (string) - Code QR pour paiements

#### transactions
- `id` (UUID) - Clé primaire
- `compte_id` (UUID) - Référence vers comptes
- `type` (string) - Type de transaction
- `montant` (decimal) - Montant de la transaction
- `devise` (string) - Devise
- `date` (timestamp) - Date de la transaction
- `statut` (string) - Statut (pending/completed/cancelled)
- `reference` (string) - Référence unique
- `marchand_id` (UUID) - Référence vers marchands (optionnel)

## 📊 Modèles et Relations

### Relations entre les modèles

```
User (1) ──── (1) Client
  │
  └── (1) ─── Marchand

Client (1) ──── (1) Compte

Compte (1) ──── (N) Transaction

Marchand (1) ──── (N) Transaction
```

### Modèles principaux

#### User
- Utilise l'authentification Laravel Sanctum
- Peut être soit un client, soit un marchand
- Gère la connexion/déconnexion

#### Client
- Lié à un utilisateur
- Possède un compte bancaire
- Peut effectuer des transferts et payer des marchands

#### Compte
- Appartient à un client
- Gère le solde et les transactions
- Méthodes : `crediter()`, `debiter()`, `afficherSolde()`

#### Marchand
- Lié à un utilisateur
- Peut recevoir des paiements
- Génère des QR codes pour les paiements

#### Transaction
- Enregistrée sur un compte
- Peut être liée à un marchand
- États : pending, completed, cancelled

## 🔌 API Endpoints

L'API suit les principes REST et utilise JSON pour les échanges.

### Authentification

```
POST /api/login     - Connexion utilisateur
POST /api/logout    - Déconnexion
```

### Gestion des comptes

```
GET    /api/comptes/{id}        - Consulter un compte
GET    /api/comptes/{id}/solde  - Consulter le solde
POST   /api/comptes/{id}/crediter - Créditer un compte
POST   /api/comptes/{id}/debiter  - Débiter un compte
```

### Transactions

```
GET    /api/transactions        - Liste des transactions
POST   /api/transactions        - Créer une transaction
GET    /api/transactions/{id}   - Détails d'une transaction
PUT    /api/transactions/{id}   - Modifier une transaction
DELETE /api/transactions/{id}   - Annuler une transaction
```

### Marchands

```
GET    /api/marchands           - Liste des marchands
POST   /api/marchands           - Créer un marchand
GET    /api/marchands/{id}      - Détails d'un marchand
PUT    /api/marchands/{id}      - Modifier un marchand
DELETE /api/marchands/{id}      - Supprimer un marchand
POST   /api/marchands/{id}/qrcode - Générer QR code
```

### Exemple d'utilisation

#### Connexion
```bash
curl -X POST http://localhost:9000/api/login \
  -H "Content-Type: application/json" \
  -d '{"username": "john_doe", "password": "secret"}'
```

#### Créer une transaction
```bash
curl -X POST http://localhost:9000/api/transactions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "compte_id": "uuid-du-compte",
    "type": "transfert",
    "montant": 50000,
    "devise": "XOF",
    "marchand_id": "uuid-du-marchand"
  }'
```

## 🚀 Déploiement

### Sur Render

1. Créez un compte sur [Render](https://render.com)
2. Connectez votre repository GitHub
3. Créez un nouveau service Web
4. Configurez les variables d'environnement dans le dashboard Render
5. Le déploiement se fait automatiquement

### Variables d'environnement pour Render

```
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=pgsql
DB_SSLMODE=require
# Les autres variables DB_* seront configurées dans Render
```

### Avec Docker en production

```bash
# Construire l'image
docker build -t om-pay-api .

# Démarrer le conteneur
docker run -d -p 9000:9000 --env-file .env om-pay-api
```

## 💻 Développement

### Structure du projet

```
├── app/
│   ├── Models/          # Modèles Eloquent
│   ├── Http/Controllers/ # Contrôleurs API
│   └── Providers/       # Service Providers
├── database/
│   ├── migrations/      # Migrations de base de données
│   ├── factories/       # Factories pour les tests
│   └── seeders/         # Seeders pour données de test
├── routes/
│   └── api.php          # Routes API
├── tests/               # Tests unitaires et fonctionnels
├── docker-compose.yml   # Configuration Docker
├── Dockerfile          # Image Docker
└── render.yml          # Configuration Render
```

### Commandes utiles

```bash
# Créer une migration
php artisan make:migration create_table_name

# Créer un modèle
php artisan make:model ModelName

# Créer un contrôleur
php artisan make:controller Api/ControllerName

# Lancer les migrations
php artisan migrate

# Lancer les seeders
php artisan db:seed

# Générer la documentation API
php artisan l5-swagger:generate
```

## 🧪 Tests

### Lancer les tests

```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test tests/Feature/ApiTest.php

# Tests avec couverture
php artisan test --coverage
```

### Écrire des tests

Exemple de test pour l'API :

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login()
    {
        $user = User::factory()->create();

        $response = $this->post('/api/login', [
            'username' => $user->username,
            'password' => 'password'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure(['token']);
    }
}
```

## 🤝 Contribution

Les contributions sont les bienvenues ! Voici comment contribuer :

1. Fork le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Pushez vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

### Standards de code

- Suivez les [PSR-12](https://www.php-fig.org/psr/psr-12/)
- Utilisez des noms descriptifs pour les variables et fonctions
- Commentez votre code
- Écrivez des tests pour les nouvelles fonctionnalités

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

---

Développé avec ❤️ par [Votre Nom] pour démontrer les capacités d'une API mobile money moderne.
