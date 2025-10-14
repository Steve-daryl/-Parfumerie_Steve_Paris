<?php

/**
 * En-tête commun à toutes les pages publiques du site.
 *
 * Inclut les configurations, la connexion BDD, et affiche la structure
 * supérieure de la page (doctype, head, header, navigation).
 */

// Inclusion des fichiers de configuration et de base de données
// __DIR__ est utilisé pour garantir que le chemin est toujours correct
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// --- Récupération des paramètres du site ---
try {
    // On récupère toutes les clés et valeurs de la table 'parametres'
    $stmt = $pdo->query("SELECT cle, valeur FROM parametres");
    // On transforme le résultat en un tableau associatif simple ('cle' => 'valeur')
    $params = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (\PDOException $e) {
    // En cas d'erreur, on initialise un tableau vide pour éviter des erreurs plus loin
    $params = [];
    error_log("Erreur lors de la récupération des paramètres : " . $e->getMessage());
}

// Assignation des paramètres à des variables pour un accès facile
// On utilise l'opérateur '??' pour fournir une valeur par défaut si le paramètre n'existe pas
$site_name = e($params['site_nom'] ?? 'Parfumerie Steve Paris');
$current_page = basename($_SERVER['PHP_SELF']); // Récupère le nom du fichier actuel (ex: 'index.php')
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le titre de la page est dynamique, basé sur le nom du site en BDD -->
    <title><?php echo $site_name; ?></title>

    <!-- Polices de caractères depuis Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- Feuille de style principale -->
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>style.css">
</head>

<body>
    <header class="main-header">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>" class="logo-link">
                <!-- Idéalement, le logo serait aussi un paramètre en BDD -->
                <img src="<?php echo IMAGES_PATH; ?>logo.png" alt="Logo de <?php echo $site_name; ?>" class="logo">
                <span class="logo-text">Parfumerie Steve Paris</span>
            </a>
            <nav class="main-nav">
                <ul>
                    <!-- On ajoute la classe 'active' au lien de la page courante -->
                    <li><a href="<?php echo BASE_URL; ?>index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Accueil</a></li>
                    <li><a href="<?php echo BASE_URL; ?>boutique.php" class="<?php echo ($current_page == 'boutique.php') ? 'active' : ''; ?>">Boutique</a></li>
                    <li><a href="<?php echo BASE_URL; ?>panier.php" class="<?php echo ($current_page == 'panier.php') ? 'active' : ''; ?>">Panier</a></li>
                </ul>
            </nav>
            <div class="header-icons">
                <a href="<?php echo BASE_URL; ?>admin/login.php" aria-label="Compte utilisateur">
                    <img src="<?php echo IMAGES_PATH; ?>user-icon.svg" alt="Compte">
                </a>
                <a href="<?php echo BASE_URL; ?>panier.php" aria-label="Panier d'achat">
                    <img src="<?php echo IMAGES_PATH; ?>shop-icon.svg" alt="Panier">
                </a>
            </div>
            <button class="menu-toggle" aria-label="Ouvrir le menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
    </header>

    <!-- La balise <main> est ouverte ici et sera fermée dans le footer -->
    <main>