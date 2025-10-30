# CinePS-API

Backend de l'application CinePS — une API REST développée avec Symfony pour la gestion de propositions et de votes de films à visionner en groupe.

## ✨ Fonctionnalités

- Gestion des membres
- Création de semaines de vote
- Propositions de films
- Votes
- Suivi des historiques
- API REST documentée avec OpenAPI

## 🚀 Installation

### Prérequis

Modules PHP à activer si ce n'est pas déjà fait :
- extension=sodium

### Procédure d'installation

- Cloner le dépôt Git
- Faire un composer install

```bash
composer install
```

- Créer un jeu de clés pour Lexik. Par exemple en utilisant cette commande (ou d'autres commandes en fonction de l'OS d'installation)

```bash
php bin/console lexik:jwt:generate-keypair
```

## Mise à jour de l'application

### Base de données

Pour déployer sur un serveur où on a pas accès à la console PHP, une possibilité est de générer un fichier SQL de migration à partir des fichiers PHP de migration générés par Symfony.

Exemple :

```bash
php bin/console doctrine:migrations:execute DoctrineMigrations\Version20250813222957 --dry-run --write-sql --up
```

## 📘 Documentation OpenAPI

### 🔗 1. Accès via un endpoint HTTP

#### 🧪 Lancement en local

```bash
php -S localhost:8000 -t public public/index.php
```

> ⚠️ **Important** : il est essentiel d'utiliser `public/index.php` comme routeur.  
> Cela permet au serveur PHP d’interpréter correctement les routes contenant une extension comme `.json`.  
> Sinon, des erreurs 404 peuvent survenir pour des routes comme `/api/doc.json`.

La documentation de l'API est disponible sous deux formes :

Une fois le serveur local lancé, tu peux accéder à la version dynamique de la documentation au format JSON :

```http
GET http://localhost:8000/api/doc.json
```

Ce fichier suit le format **OpenAPI 3.0** et peut être utilisé dans :
- Swagger UI
- Postman
- ou tout autre outil compatible

### 📁 2. Fichier statique inclus dans le projet

Le fichier OpenAPI (`openapi.json`) est également **inclus à la racine du dépôt**.  
Tu peux l’ouvrir dans Swagger UI (en ligne ou localement) sans lancer le projet Symfony.

Swagger UI en ligne :  
🔗 [https://editor.swagger.io/](https://editor.swagger.io/)

### 3. Commande pour générer le fichier openapi.json

```bash
php bin/console nelmio:api:dump --format=json > docs/openapi.json
```

### 4. Commande utilisée pour générer le fichier redoc

```bash
npx @redocly/cli build-docs doc/openapi.json -o doc/redoc.html
```

## 📂 Structure du projet

```text
├── config/              # Configuration Symfony  
├── public/              # Point d'entrée web  
├── src/  
│   ├── Controller/      # Contrôleurs Symfony (REST)  
│   └── Entity/          # Entités Doctrine  
├── openapi.json         # Fichier OpenAPI généré  
├── composer.json        # Dépendances PHP  
└── README.md            # Ce fichier  
```

## 🧰 Outils utilisés

- PHP 8.2
- Symfony 6
- Doctrine ORM
- NelmioApiDocBundle
- JWT Authentication
- Swagger / OpenAPI

## 🧩 Contribution

À compléter

## 📄 Licence

À compléter

## 🌿 Convention de nommage des branches

feature/..  
fix/..  
refacto/..  
tests/..  

## 🤖 Tests automatisés

### 📦 Installation du package de tests

Pour installer les dépendances nécessaires aux tests automatisés, exécute la commande suivante :

```bash
composer require --dev symfony/test-pack
```

### 🧪 Exécution des tests automatisés

Pour exécuter les tests, utilisez la commande suivante :

```bash
php bin/phpunit
```
