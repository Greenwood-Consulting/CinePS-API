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

A compléter.

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
php bin/console nelmio:api:dump --format=json > doc/openapi.json
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
