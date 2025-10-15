<?php

/**
 * Page de la boutique - Affiche tous les produits disponibles et gère les filtres.
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/functions.php';

// --- GESTION DES FILTRES ---
$filtre_categorie = isset($_GET['categorie']) ? (int)$_GET['categorie'] : null;
$filtre_marque = isset($_GET['marque']) ? htmlspecialchars($_GET['marque']) : null;
$filtre_prix_min = isset($_GET['prix_min']) && is_numeric($_GET['prix_min']) ? (float)$_GET['prix_min'] : null;
$filtre_prix_max = isset($_GET['prix_max']) && is_numeric($_GET['prix_max']) ? (float)$_GET['prix_max'] : null;

try {
    $categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
    $marques = $pdo->query("SELECT DISTINCT marque FROM produits WHERE actif = 1 AND marque IS NOT NULL ORDER BY marque ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (\PDOException $e) { /* ... */
}

$sql = "SELECT * FROM produits WHERE actif = 1";
$params = [];
if ($filtre_categorie) {
    $sql .= " AND categorie_id = ?";
    $params[] = $filtre_categorie;
}
if ($filtre_marque) {
    $sql .= " AND marque = ?";
    $params[] = $filtre_marque;
}
if ($filtre_prix_min !== null) {
    $sql .= " AND prix_vente >= ?";
    $params[] = $filtre_prix_min;
}
if ($filtre_prix_max !== null) {
    $sql .= " AND prix_vente <= ?";
    $params[] = $filtre_prix_max;
}
$sql .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) { /* ... */
}
?>

<!-- PANNEAU DE FILTRES (HTML identique) -->
<div id="filter-overlay" class="filter-overlay"></div>
<div id="filter-panel" class="filter-panel">
    <!-- ... Contenu du panneau de filtres ... -->
</div>


<div class="container shop-page">
    <div class="shop-header">
        <h1>Notre Collection</h1>
        <p>Découvrez nos parfums de luxe authentiques</p>
    </div>

    <div class="shop-controls">
        <div class="search-bar">
            <input type="text" placeholder="Rechercher par nom ou marque...">
        </div>
        <button id="open-filters-btn" class="filter-button">
            Filtres
        </button>
    </div>

    <div class="product-grid">
        <?php if (empty($products)) : ?>
            <p class="no-products-message">Aucun produit ne correspond à votre sélection.</p>
        <?php else : ?>
            <?php foreach ($products as $product) : ?>
                <div class="product-card">
                    <!-- Le conteneur de l'image -->
                    <div class="product-image-link" data-product-id="<?php echo e($product['id']); ?>">
                        <a href="produit.php?id=<?php echo e($product['id']); ?>">
                            <img src="<?php echo UPLOADS_URL . e($product['image'] ?? 'default.jpg'); ?>" alt="<?php echo e($product['nom']); ?>">
                        </a>

                        <div class="product-image-overlay">
                            <a href="produit.php?id=<?php echo e($product['id']); ?>" class="overlay-icon-btn view-btn" aria-label="Voir le détail">
                                <!-- Icône Oeil -->
                            </a>
                            <?php if ($product['stock'] > 0) : ?>
                                <!-- IMPORTANT : Ajout de la classe 'add-to-cart' ici -->
                                <button class="overlay-icon-btn add-to-cart" data-id="<?php echo e($product['id']); ?>" aria-label="Ajouter au panier">
                                    <!-- Icône Plus -->
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if ($product['stock'] == 0) : ?>
                            <span class="stock-badge out-of-stock">Rupture</span>
                        <?php elseif ($product['stock'] > 0 && $product['stock'] < 10) : ?>
                            <span class="stock-badge low-stock">Stock faible</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-details">
                        <div class="product-info">
                            <p class="product-brand"><?php echo e($product['marque']); ?></p>
                            <h3 class="product-name"><?php echo e($product['nom']); ?></h3>
                            <p class="product-size"><?php echo e($product['contenance'] ?? 'N/A'); ?> ml</p>
                        </div>
                        <div class="product-purchase-info" data-product-id="<?php echo e($product['id']); ?>">
                            <p class="product-price"><?php echo format_price((float)$product['prix_vente']); ?></p>
                            <?php if ($product['stock'] > 0) : ?>
                                <button class="btn-cart-action add-to-cart" data-id="<?php echo e($product['id']); ?>">+ Ajouter</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>