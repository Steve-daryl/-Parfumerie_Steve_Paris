<?php
include 'includes/header.php';

// Récupérer tous les produits actifs
$stmt = $pdo->prepare("SELECT * FROM produits WHERE actif = 1 ORDER BY created_at DESC");
$stmt->execute();
$products = $stmt->fetchAll();
?>

<div class="container page-container">
    <h1 class="page-title">Notre Collection</h1>
    <p class="page-subtitle">Parcourez notre sélection exclusive de parfums de luxe.</p>

    <div class="product-grid">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="produit.php?id=<?= e($product['id']) ?>">
                        <img src="<?= BASE_URL ?>/assets/images/uploads/<?= e($product['image'] ?? 'default.jpg') ?>" alt="<?= e($product['nom']) ?>" class="product-card-image">
                        <div class="product-card-content">
                            <h3 class="product-card-title"><?= e($product['nom']) ?></h3>
                            <p class="product-card-brand"><?= e($product['marque']) ?></p>
                            <p class="product-card-price"><?= format_price($product['prix_vente']) ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun produit disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>