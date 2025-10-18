<?php

/**
 * =================================================================
 * PAGE BOUTIQUE (VERSION FINALE CORRIGÉE AVEC FILTRES FONCTIONNELS)
 * =================================================================
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/functions.php';

// --- GESTION DES FILTRES ---

// 1. Récupérer les filtres actifs depuis l'URL (avec sécurisation)
$filtre_categorie = isset($_GET['categorie']) ? (int)$_GET['categorie'] : null;
$filtre_marque = isset($_GET['marque']) ? htmlspecialchars($_GET['marque']) : null;
$filtre_prix_min = isset($_GET['prix_min']) && is_numeric($_GET['prix_min']) ? (float)$_GET['prix_min'] : null;
$filtre_prix_max = isset($_GET['prix_max']) && is_numeric($_GET['prix_max']) ? (float)$_GET['prix_max'] : null;

// 2. Récupérer les options disponibles pour les filtres
try {
    $categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
    $marques = $pdo->query("SELECT DISTINCT marque FROM produits WHERE actif = 1 AND marque IS NOT NULL AND marque != '' ORDER BY marque ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (\PDOException $e) {
    $categories = [];
    $marques = [];
    error_log("Erreur de récupération des options de filtres: " . $e->getMessage());
}

// 3. Construire la requête SQL dynamiquement et en toute sécurité
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

// 4. Exécuter la requête pour obtenir les produits
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $products = [];
    error_log("Erreur lors de la récupération des produits filtrés : " . $e->getMessage());
}
?>

<!-- ========================================================== -->
<!-- ==     HTML DU PANNEAU DE FILTRES (LA PARTIE MANQUANTE)   == -->
<!-- ========================================================== -->
<div id="filter-overlay" class="filter-overlay"></div>
<div id="filter-panel" class="filter-panel">
    <div class="filter-panel-header">
        <h2>Filtres</h2>
        <button id="close-filters-btn" class="close-btn" aria-label="Fermer les filtres">&times;</button>
    </div>
    <div class="filter-panel-body">
        <form action="boutique.php" method="GET">
            <!-- Filtre par Catégorie -->
            <div class="filter-group">
                <label for="categorie">Catégorie</label>
                <select name="categorie" id="categorie" class="filter-select">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $categorie) : ?>
                        <option value="<?php echo e($categorie['id']); ?>" <?php if ($filtre_categorie == $categorie['id']) echo 'selected'; ?>>
                            <?php echo e($categorie['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtre par Marque -->
            <div class="filter-group">
                <label for="marque">Marque</label>
                <select name="marque" id="marque" class="filter-select">
                    <option value="">Toutes les marques</option>
                    <?php foreach ($marques as $marque) : ?>
                        <option value="<?php echo e($marque); ?>" <?php if ($filtre_marque == $marque) echo 'selected'; ?>>
                            <?php echo e($marque); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtre par Prix -->
            <div class="filter-group">
                <label>Gamme de prix (F CFA)</label>
                <div class="price-range">
                    <input type="number" name="prix_min" placeholder="Min" value="<?php echo e($filtre_prix_min); ?>" class="filter-input">
                    <span>-</span>
                    <input type="number" name="prix_max" placeholder="Max" value="<?php echo e($filtre_prix_max); ?>" class="filter-input">
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="filter-buttons">
                <button type="submit" class="btn-apply-filters">Appliquer les filtres</button>
                <a href="boutique.php" class="btn-reset-filters">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>


<!-- ========================================================== -->
<!-- ==                   CONTENU DE LA PAGE                   == -->
<!-- ========================================================== -->
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
                    <div class="product-image-link" data-product-id="<?php echo e($product['id']); ?>">
                        <a href="produit.php?id=<?php echo e($product['id']); ?>">
                            <img src="./assets/images/<?php echo e($product['image'] ?? 'default.jpg'); ?>" alt="<?php echo e($product['nom']); ?>">
                        </a>
                        <div class="product-image-overlay">
                            <a href="produit.php?id=<?php echo e($product['id']); ?>" class="overlay-icon-btn view-btn" aria-label="Voir le détail"></a>
                            <?php if ($product['stock'] > 0) : ?>
                                <button class="overlay-icon-btn add-to-cart" data-id="<?php echo e($product['id']); ?>" aria-label="Ajouter au panier"></button>
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
                            <!-- =================== MODIFICATION ICI =================== -->
                            <!-- On vérifie le stock pour afficher le bon bouton -->
                            <?php if ($product['stock'] > 0) : ?>
                                <!-- Si le stock est disponible, on affiche le bouton d'ajout normal -->
                                <button class="btn-cart-action add-to-cart" data-id="<?php echo e($product['id']); ?>">+ Ajouter</button>
                            <?php else: ?>
                                <!-- Sinon, on affiche un bouton désactivé et non cliquable -->
                                <button class="btn-cart-action" disabled>Rupture</button>
                            <?php endif; ?>
                            <!-- ================= FIN DE LA MODIFICATION ================= -->
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