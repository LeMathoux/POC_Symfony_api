# 🎮 POC Symfony API - Gestion de jeux vidéo

[![PHP](https://img.shields.io/badge/PHP-8.1-blue)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-6.4-black)](https://symfony.com/)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

API Symfony pour gérer des **jeux vidéo**, **catégories**, **éditeurs** et un système de **newsletter** pour les jeux sortant dans les 7 prochains jours.

---

## 🚀 Fonctionnalités

- CRUD complet pour :
  - `Video Games`
  - `Categories`
  - `Editors`
  - `users`
- Système de newsletter pour les nouvelles sorties
- Documentation interactive via **Swagger / API Platform**
- Authentification sécurisée avec JWT

---

## ⚙️ Prérequis

- PHP 8.1+
- Composer
- Symfony CLI (optionnel mais recommandé)
- Base de données compatible Doctrine (MySQL, PostgreSQL...)

---

## 📦 Installation

```bash
git clone https://github.com/LeMathoux/POC_Symfony_api.git
cd POC_Symfony_api
composer install
cp .env .env.local
# configurez votre base de données dans .env.local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Compte et Authentification
```
email : dauguet.mathis@gmail.com
password : password
role : ROLE_ADMIN
```

## générer le jeton JWT
```
Aller sur [Swagger](http://127.0.0.1:8000/api/doc)
puis dans la catégorie "Authentification" inserer les informations dans le "try out".
recuperer le token et le mettre dans "Autorize" en haut du swagger.
```

## lancer la commande symfony SendUpcomingGamesEmail
```bash
php bin/console app:send-upcoming-games-email
```


