<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/functions.php';

// Valider l'ID du produit
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: boutique.php');
    exit();
}
$product_id = (int)$_GET['id'];

// Récupérer toutes les informations du produit
try {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ? AND actif = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
} catch (\PDOException $e) {
    error_log("Erreur de récupération du produit : " . $e->getMessage());
    $product = false;
}

// Si le produit n'existe pas, rediriger
if (!$product) {
    header('Location: boutique.php');
    exit();
}

// Préparer les données pour l'affichage
$whatsapp_link = get_whatsapp_link(
    get_setting($pdo, 'whatsapp_numero'),
    "Bonjour, j'ai une question concernant le produit : " . e($product['nom'])
);

// Gérer la date d'expiration pour éviter l'erreur "Deprecated"
$date_expiration_display = 'Non spécifiée';
if (!empty($product['date_de_peremption'])) {
    $date_expiration_display = date('d/m/Y', strtotime($product['date_de_peremption']));
}
?>

<div class="container product-page-container">
    <!-- Lien de retour -->
    <a href="boutique.php" class="back-to-shop-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5"></path>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Retour à la boutique
    </a>

    <div class="product-detail-grid">
        <!-- Colonne de l'image -->
        <div class="product-image-wrapper">
            <img src="./assets/images/<?php echo e($product['image'] ?? 'default.jpg'); ?>" alt="<?php echo e($product['nom']); ?>">
        </div>

        <!-- Colonne des détails -->
        <div class="product-details-content">
            <p class="product-brand-detail"><?php echo e($product['marque']); ?></p>
            <h1 class="product-title-detail"><?php echo e($product['nom']); ?></h1>
            <p class="product-price-detail">
                <?php echo format_price((float)$product['prix_vente']); ?>
                <span class="product-volume-detail"><?php echo e($product['contenance']); ?> ml</span>
            </p>
            <p class="product-description-detail"><?php echo nl2br(e($product['description'])); ?></p>

            <!-- Carte "Informations produit" -->
            <div class="product-info-card">
                <h3>Informations produit</h3>
                <div class="product-specs">
                    <div class="spec-item"><span class="spec-icon">📦</span>
                        <div>
                            <p>Volume</p><strong><?php echo e($product['contenance']); ?> ml</strong>
                        </div>
                    </div>
                    <div class="spec-item"><span class="spec-icon">ℹ️</span>
                        <div>
                            <p>Référence</p><strong><?php echo e($product['reference']); ?></strong>
                        </div>
                    </div>
                    <div class="spec-item"><span class="spec-icon">📅</span>
                        <div>
                            <p>Date d'expiration</p><strong><?php echo $date_expiration_display; ?></strong>
                        </div>
                    </div>
                    <div class="spec-item"><span class="spec-icon">🛍️</span>
                        <div>
                            <p>Stock disponible</p><strong class="stock-available"><?php echo e($product['stock']); ?> unités</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="product-actions-grid">
                <!-- Carte "Quantité" -->
                <div class="quantity-card">
                    <h3>Quantité</h3>
                    <div class="quantity-selector-detail">
                        <button class="quantity-btn-detail minus">-</button>
                        <span id="quantity-display" class="quantity-display-detail">1</span>
                        <button class="quantity-btn-detail plus">+</button>
                    </div>
                    <button id="add-to-cart-btn" class="btn-add-to-cart-detail" data-id="<?php echo e($product['id']); ?>">
                        🛒 Ajouter au panier
                    </button>
                </div>

                <!-- Carte "Service Client" -->
                <div class="whatsapp-card">
                    <h3>Service client</h3>
                    <p>Une question sur ce produit ? Contactez-nous directement via WhatsApp pour un conseil personnalisé.</p>
                    <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="btn-whatsapp-detail">Contacter sur WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>