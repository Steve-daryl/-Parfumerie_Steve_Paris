<?php

/**
 * Fichier de configuration générale.
 *
 * Ce script définit les constantes globales, configure le rapport d'erreurs
 * et démarre la session PHP pour l'ensemble du site.
 */

// --- Configuration du rapport d'erreurs (pour le développement) ---
// Affiche toutes les erreurs pour faciliter le débogage.
// En production, il est recommandé de mettre 'display_errors' à '0'
// et de journaliser les erreurs dans un fichier.
ini_set('display_errors', 1);
error_reporting(E_ALL);


// --- Démarrage de la session ---
// Démarre une nouvelle session ou reprend une session existante.
// Essentiel pour le panier, la connexion admin, etc.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// --- Définition des constantes de chemin ---

// URL de base du site.
// Important : Assurez-vous que le slash (/) est bien à la fin.
// Adaptez cette URL à votre environnement (localhost ou serveur de production).
define('BASE_URL', 'http://localhost/-Parfumerie_Steve_Paris/');

// Chemin absolu vers la racine du projet sur le serveur.
// Utile pour les inclusions de fichiers PHP (require, include).
define('ROOT_PATH', dirname(__DIR__) . '/');

// Chemins vers les ressources publiques (assets).
define('ASSETS_PATH', BASE_URL . 'assets/');
define('CSS_PATH', ASSETS_PATH . 'css/');
define('JS_PATH', ASSETS_PATH . 'js/');
define('IMAGES_PATH', ASSETS_PATH . 'images/');
// Le chemin pour les images uploadées depuis l'admin
define('UPLOADS_URL', BASE_URL . 'assets/images/uploads/');


// --- Fonctions utilitaires globales (optionnel) ---
/**
 * Raccourci pour htmlspecialchars pour sécuriser l'affichage des données.
 * @param string|null $string La chaîne à échapper.
 * @return string La chaîne échappée.
 */
function e(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
