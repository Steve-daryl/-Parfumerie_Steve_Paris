<?php

/**
 * Fichier de connexion à la base de données.
 *
 * Ce script initialise la connexion PDO à la base de données MySQL
 * et rend l'objet $pdo disponible pour les autres scripts.
 */

// Paramètres de connexion à la base de données
$host = '127.0.0.1'; // ou 'localhost'
$dbname = 'parfumerie_steve_paris';
$user = 'root'; // Votre nom d'utilisateur pour la base de données
$password = ''; // Votre mot de passe pour la base de données
$charset = 'utf8mb4';

// Options de configuration pour PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Active le mode d'erreur par exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Définit le mode de récupération par défaut (tableau associatif)
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Désactive l'émulation des requêtes préparées pour la sécurité
];

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

try {
    // Création de l'instance PDO
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (\PDOException $e) {
    // En cas d'erreur de connexion, on arrête le script et on affiche un message
    // Il est recommandé de ne pas afficher les détails de l'erreur en production
    error_log("Erreur de connexion à la BDD : " . $e->getMessage());
    die("Erreur : Impossible de se connecter à la base de données. Veuillez réessayer plus tard.");
}
