<?php
include 'includes/header.php';

// Vérifier si un ID est passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Rediriger vers la boutique si l'ID est invalide
    header('Location: boutique.php');
    exit();
}

$product_id = (int)$_GET['id'];

// Récupérer les informations du produit
$stmt = $pdo->prepare("SELECT p.*, c.nom AS categorie_nom FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id WHERE p.id = ? AND p.actif = 1");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// Si le produit n'existe pas, rediriger
if (!$product) {
    header('Location: boutique.php');
    exit();
}
?>

<div class="container page-container">
    <div class="product-detail-grid">
        <div class="product-detail-image">
            <img src="<?= BASE_URL ?>/assets/images/uploads/<?= e($product['image'] ?? 'default.jpg') ?>" alt="<?= e($product['nom']) ?>">
        </div>
        <div class="product-detail-content">
            <p class="product-detail-category"><?= e($product['categorie_nom']) ?></p>
            <h1 class="product-detail-title"><?= e($product['nom']) ?></h1>
            <p class="product-detail-brand">Marque: <?= e($product['marque']) ?></p>
            <p class="product-detail-price"><?= format_price($product['prix_vente']) ?></p>

            <div class="product-detail-description">
                <h2>Description</h2>
                <p><?= nl2br(e($product['description'])) ?></p>
            </div>

            <div class="product-detail-actions">
                <a href="https://wa.me/<?= e(get_setting($pdo, 'whatsapp_numero')) ?>?text=Bonjour, je suis intéressé(e) par le parfum '<?= urlencode($product['nom']) ?>'." target="_blank" class="btn btn-whatsapp">
                    Commander via WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>


<?php include 'includes/footer.php'; ?>