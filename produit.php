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
    $sql = "SELECT p.*, c.nom AS nom_categorie 
            FROM produits p 
            LEFT JOIN categories c ON p.categorie_id = c.id 
            WHERE p.id = ? AND p.actif = 1";

    $stmt = $pdo->prepare($sql);
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

// --- LOGIQUE DE STOCK ---
$is_in_stock = $product['stock'] > 0;
$stock_display_text = $is_in_stock ? e($product['stock']) . ' unités' : 'Rupture de stock';
// Préparer les données pour l'affichage
$whatsapp_link = get_whatsapp_link(
    get_setting($pdo, 'whatsapp_numero'),
    "Bonjour, j'ai une question concernant le produit : " . e($product['nom'])
);

// On récupère la bonne information ('nom_categorie') et on prévoit un cas par défaut.
$nom_categorie = $product['nom_categorie'] ?? 'Non classé';
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
            <img src="./assets/images/<?php echo  e($product['image'] ?? 'default.jpg'); ?>" alt="<?php echo e($product['nom']); ?>">
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
                    <div class="spec-item"><span class="spec-icon"><!-- Emoji 📦 remplacé par une icône SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg></span>
                        <div>
                            <p>Volume</p><strong><?php echo e($product['contenance']); ?> ml</strong>
                        </div>
                    </div>
                    <div class="spec-item"><span class="spec-icon"><!-- Emoji ℹ️ remplacé par une icône SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg></span>
                        <div>
                            <p>Référence</p><strong><?php echo e($product['reference']); ?></strong>
                        </div>
                    </div>
                    <div class="spec-item"><span class="spec-icon"><!-- Emoji 📅 remplacé par une icône SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg></span>
                        <div>
                            <p>Catégorie</p><strong><?php echo e($nom_categorie); ?></strong>
                        </div>
                    </div>
                    <div class="spec-item"><span class="spec-icon"> <!-- Emoji 🛍️ remplacé par une icône SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg></span>
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
                    <!-- =================== MODIFICATION CRUCIALE ICI =================== -->
                    <button
                        id="add-to-cart-btn"
                        class="btn-add-to-cart-detail"
                        data-id="<?php echo e($product['id']); ?>"
                        <?php echo !$is_in_stock ? 'disabled' : ''; ?>>
                        <?php echo $is_in_stock ? 'Ajouter au panier' : 'Rupture de stock'; ?>
                    </button>
                    <!-- ================================================================= -->
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