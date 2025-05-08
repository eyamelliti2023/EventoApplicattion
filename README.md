Description du projet

EventoApplication est une application web développée avec Symfony qui propose une plateforme de gestion d'événements. Elle se divise en deux interfaces principales :

Côté Administrateur : pour la gestion complète des utilisateurs, événements, réservations et réclamations.

Côté Client : pour la consultation des événements, la réservation de places et la soumission de réclamations.

L’objectif de ce projet est de fournir une solution intuitive, sécurisée et évolutive pour organiser et réserver des événements, tout en offrant une expérience utilisateur fluide.

Fonctionnalités

Côté Administrateur

Gestion des utilisateurs : création, modification, suppression

Gestion des événements : création, édition, suppression, localisation via Leaflet

Gestion des réservations : consultation, validation, mailing 

Gestion des réclamations : suivi et traitement des retours clients

Côté Client

Consultation des événements : liste filtrable et détails

Réservation d’événements : sélection du nombre de places, confirmation par e-mail

Soumission de réclamations : formulaire simple pour signaler un problème

Technologies et architecture

Framework : Symfony 6

Template & Styling : Twig, Bootswatch Vapor

Base de données : MySQL via Doctrine ORM

Services tiers : Cloudinary (upload d’images), Brevo/Sendinblue (envoi d’e-mails), ZXing (génération de QR codes)

Cartographie : Leaflet.js pour géolocalisation des lieux d’événements

Architecture : MVC (Controllers, Services, Entities, Repositories, Templates)

Installation et configuration

Cloner le dépôt :

git clone https://github.com/eyamelliti2023/EventoApplicattion.git
cd EventoApplicattion

Installer les dépendances :

composer install
npm install # si assets

Configurer les variables d’environnement :

Copier .env en .env.local

Définir la connexion à la base de données (DATABASE_URL)

Ajouter SENDINBLUE_API_KEY et CLOUDINARY_URL

Créer la base de données et exécuter les migrations :

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

Lancer le serveur de développement :

symfony server:start

Contributeurs

Eya Melliti <eya.melliti@espri.tn>

Fatma Hiddoussi <fatma.hidoussi@esprit.tn>

Remerciements

Un grand merci à l’Université Esprit pour son soutien, ses enseignements de qualité et les ressources mises à disposition tout au long de ce projet.
