# Amap'Market - Portfolio Ewen Evin

## Présentation

Amap'Market est une plateforme web permettant aux utilisateurs de commander des paniers de fruits et légumes frais, locaux et de saison, en collaboration avec des producteurs engagés. Le site propose une expérience simple et intuitive pour consommer de manière responsable et soutenir l'agriculture locale.

## Fonctionnalités principales

- **Accueil** : Présentation du concept et des valeurs d'Amap'Market.
- **Catalogue Produits** : Consultation des produits disponibles, affichage des prix, variétés et images.
- **Panier** : Ajout de produits au panier, modification des quantités, suppression d'articles, visualisation du total.
- **Inscription / Connexion** : Création de compte, connexion sécurisée, gestion des sessions.
- **Validation du panier** : Passage de commande, mise à jour des stocks, confirmation de commande.
- **Gestion (admin/gestion_testeur)** :
  - Visualisation et gestion des produits (ajout, modification, suppression).
  - Visualisation et gestion des commandes (changement de statut, suppression).

## Types de profils

- **Client** : Peut consulter les produits, gérer son panier, passer commande.
- **Gestion** : Accès complet au menu de gestion (produits et commandes).
- **Gestion_testeur** : Accès au menu de gestion, peut ouvrir les formulaires d'ajout/modification/suppression mais ne peut pas enregistrer les modifications (actions bloquées avec une alerte).

## Accéder à la vue gestion_testeur

Pour tester la vue "Gestion" en mode testeur, utilisez les identifiants suivants lors de la connexion :

- **Email** : `jean-dupont@gmail.com`
- **Mot de passe** : `Jean123`

Ce profil permet de naviguer dans le menu de gestion sans impacter les données (aucune modification enregistrée).

## Technologies utilisées

- PHP (PDO, sessions)
- MySQL
- Bootstrap 5
- SweetAlert2 (popups et alertes)
- HTML/CSS/JavaScript

## Structure des fichiers

- `index.php` : Page d'accueil
- `produits.php` : Catalogue des produits
- `panier.php` : Gestion du panier
- `connexion.php` : Inscription et connexion
- `gestion.php` : Interface de gestion (produits et commandes)
- `README.md` : Ce fichier d'explication

## Contact

Pour toute question ou suggestion, contactez-nous à : **amap'market@amap.com**

---

Merci d'utiliser Amap'Market !
