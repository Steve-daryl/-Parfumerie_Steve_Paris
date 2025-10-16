<?php

/**
 * En-tête commun à toutes les pages publiques du site.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Calcul du nombre total d'articles dans le panier depuis la session
$cart_item_count = !empty($_SESSION['panier']) ? array_sum(array_column($_SESSION['panier'], 'quantite')) : 0;

// Récupération des paramètres du site
try {
    $stmt = $pdo->query("SELECT cle, valeur FROM parametres");
    $params = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (\PDOException $e) {
    error_log("Erreur de récupération des paramètres: " . $e->getMessage());
    $params = [];
}

// Définition du nom du site et de la page actuelle
$site_name = e($params['site_nom'] ?? 'Parfumerie Steve Paris');
$whatsapp_numero = e($params['whatsapp_numero'] ?? '+237 6 90 98 47 58');
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_name; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>style.css">
</head>

<body>
    <header class="main-header">
        <div class="container">
            <!-- NOUVELLE STRUCTURE DU LOGO CORRIGÉE -->
            <a href="<?php echo BASE_URL; ?>" class="logo-link-v2">
                <img src="<?php echo IMAGES_PATH; ?>logo_sans_arriere.png" width="40" height="auto" alt="Logo de <?php echo $site_name; ?>" class="logo-v2">
                <span class="logo-text-v2"><?php echo $site_name; ?></span>
            </a>

            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Accueil</a></li>
                    <li><a href="<?php echo BASE_URL; ?>boutique.php" class="<?php echo ($current_page == 'boutique.php') ? 'active' : ''; ?>">Boutique</a></li>
                    <li><a href="<?php echo BASE_URL; ?>panier.php" class="<?php echo ($current_page == 'panier.php') ? 'active' : ''; ?>">Panier</a></li>
                </ul>
            </nav>
            <div class="header-icons">
                <a href="<?php echo BASE_URL; ?>admin/login.php" aria-label="Compte utilisateur">
                    <img src="<?php echo IMAGES_PATH; ?>user-icon.svg" alt="Compte">
                </a>
                <a href="<?php echo BASE_URL; ?>panier.php" class="cart-icon-wrapper" aria-label="Panier d'achat">
                    <img src="<?php echo IMAGES_PATH; ?>shop-icon.svg" alt="Panier">
                    <?php if ($cart_item_count > 0) : ?>
                        <span class="cart-badge"><?php echo $cart_item_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <button class="menu-toggle" aria-label="Ouvrir le menu">
                <span class="bar"></span><span class="bar"></span><span class="bar"></span>
            </button>
        </div>
    </header>
    <main>