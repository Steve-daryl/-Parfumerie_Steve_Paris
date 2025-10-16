<?php

/**
 * API pour la recherche de produits en direct.
 * Reçoit un terme de recherche et renvoie le HTML des cartes produits correspondantes.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php'; // Important pour utiliser format_price()

// On indique que la réponse sera du HTML
header('Content-Type: text/html; charset=utf-8');

// Récupérer le terme de recherche depuis l'URL (envoyé par JavaScript)
$searchTerm = $_GET['search'] ?? '';

// Si le terme de recherche est vide, on ne fait rien
if (trim($searchTerm) === '') {
    // Optionnel : on pourrait renvoyer tous les produits ici
    exit;
}

// Préparer le terme pour la requête SQL LIKE
$likeTerm = '%' . $searchTerm . '%';

try {
    // Requête SQL sécurisée avec un prepared statement
    // Elle cherche le terme dans le nom OU la marque du produit
    $stmt = $pdo->prepare(
        "SELECT * FROM produits 
         WHERE (nom LIKE ? OR marque LIKE ?) AND actif = 1 
         ORDER BY id DESC"
    );
    $stmt->execute([$likeTerm, $likeTerm]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Construction de la réponse HTML
    if (empty($products)) {
        echo '<p class="no-products-message">Aucun produit ne correspond à votre recherche.</p>';
    } else {
        foreach ($products as $product) {
            // On réutilise EXACTEMENT la même structure HTML que dans boutique.php
            // pour que le style CSS s'applique correctement.
            $stockBadge = '';
            if ($product['stock'] == 0) {
                $stockBadge = '<span class="stock-badge out-of-stock">Rupture</span>';
            } elseif ($product['stock'] > 0 && $product['stock'] < 10) {
                $stockBadge = '<span class="stock-badge low-stock">Stock faible</span>';
            }

            $actionButton = '';
            if ($product['stock'] > 0) {
                $actionButton = '<button class="btn-cart-action add-to-cart" data-id="' . e($product['id']) . '">+ Ajouter</button>';
            }

            echo '
            <div class="product-card">
                <a href="produit.php?id=' . e($product['id']) . '" class="product-image-link">'
                . $stockBadge .
                '<img src="./assets/images/' . e($product['image'] ?? 'default.jpg') . '" alt="' . e($product['nom']) . '">
                </a>
                <div class="product-details">
                    <div class="product-info">
                         <p class="product-brand">' . e($product['marque']) . '</p>
                         <h3 class="product-name">' . e($product['nom']) . '</h3>
                         <p class="product-size">' . e($product['contenance'] ?? 'N/A') . ' ml</p>
                    </div>
                    <div class="product-purchase-info">
                        <p class="product-price">' . format_price((float)$product['prix_vente']) . '</p>'
                . $actionButton .
                '</div>
                </div>
            </div>';
        }
    }
} catch (\PDOException $e) {
    // En cas d'erreur BDD, on n'affiche rien ou un message d'erreur discret
    error_log("Erreur de recherche API : " . $e->getMessage());
    echo '<p class="no-products-message">Une erreur est survenue lors de la recherche.</p>';
}
