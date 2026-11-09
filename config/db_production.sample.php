<?php
/**
 * Configuration Base de Données Hostinger / Production - RMC BTP SARL
 * 
 * Instructions pour Hostinger :
 * 1. Renommez ce fichier en "db_production.php" sur votre hébergement Hostinger.
 * 2. Renseignez les identifiants créés dans votre panneau Hostinger (hPanel -> Bases de données MySQL).
 */

define('DB_HOST', 'localhost'); // Sur Hostinger, le serveur SQL est 'localhost'
define('DB_USER', 'u123456789_rmcbtp'); // Votre nom d'utilisateur MySQL Hostinger
define('DB_PASS', 'VotreMotDePasseSecret123!'); // Votre mot de passe de base MySQL
define('DB_NAME', 'u123456789_rmcbtp'); // Le nom complet de votre base de données Hostinger
define('SITE_URL', 'https://rmcbtpsarl.com'); // L'URL finale de votre site web en HTTPS
