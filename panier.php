<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/functions.php';

$cart_items = [];
$sub_total = 0;

if (!empty($_SESSION['panier'])) {
    $product_ids = array_keys($_SESSION['panier']);
    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $stmt = $pdo->prepare("SELECT id, nom, prix_vente, image, contenance FROM produits WHERE id IN ($placeholders)");
        $stmt->execute($product_ids);
        $products_in_db = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        foreach ($_SESSION['panier'] as $productId => $item) {
            if (isset($products_in_db[$productId])) {
                $product = $products_in_db[$productId];
                $quantity = $item['quantite'];
                $total_price = $product['prix_vente'] * $quantity;

                $cart_items[] = [
                    'id' => $productId,
                    'nom' => $product['nom'],
                    'image' => $product['image'],
                    'contenance' => $product['contenance'],
                    'prix_unitaire' => $product['prix_vente'],
                    'quantite' => $quantity,
                    'prix_total_item' => $total_price
                ];
                $sub_total += $total_price;
            } else {
                unset($_SESSION['panier'][$productId]);
            }
        }
    }
}

$tva_rate = 0.20;
$tva = $sub_total * $tva_rate;
$total = $sub_total + $tva;
$total_articles = array_sum(array_column($cart_items, 'quantite'));

$site_name = $params['site_nom'] ?? 'Parfumerie Steve Paris';
$line_separator = "________________________________________";

$message_header = "{$line_separator}\n💎 {$site_name} – Commande Client 💎\n";
$message_client_info = "👤 Client : %CLIENT_NAME%\n📱 Téléphone : %CLIENT_PHONE%\n";
$message_order_details_header = "📦 Détail de la commande :\n";

$message_items = "";
foreach ($cart_items as $item) {
    $message_items .= "• " . e($item['nom']) . "\n";
    $message_items .= "  Quantité : " . e($item['quantite']) . "\n";
    $message_items .= "  Prix unitaire : " . format_price($item['prix_unitaire']) . "\n";
    $message_items .= "  Total : " . format_price($item['prix_total_item']) . "\n\n";
}

$message_summary = "📊 Résumé :\n";
$message_summary .= "• Nombre total d'articles : " . $total_articles . "\n";
$message_summary .= "• Sous-total : " . format_price($sub_total) . "\n";
$message_summary .= "• TVA (20%) : " . format_price($tva) . "\n";
$message_summary .= "• ✅ Total TTC : " . format_price($total) . "\n\n";

date_default_timezone_set('Africa/Douala');
$message_footer = "🕐 Commande générée le : " . date('d/m/Y à H:i') . "\n{$line_separator}";

$base_whatsapp_message = $message_header . $message_client_info . $message_order_details_header . $message_items . $message_summary . $message_footer;
$whatsapp_number = get_setting($pdo, 'whatsapp_numero');
?>

<div class="container cart-page">
    <div class="cart-grid-unified">
        <div class="cart-items-list-unified">
            <?php if (empty($cart_items)) : ?>
                <div class="empty-cart-message-inline">
                    <div class="empty-cart-icon"></div>
                    <h2>Votre panier est vide</h2>
                    <p>Parcourez nos collections pour trouver votre prochain parfum.</p>
                    <a href="boutique.php" class="btn-primary-gold">Continuer vos achats</a>
                </div>
            <?php else : ?>
                <div class="cart-header">
                    <h1>Mon Panier</h1>
                    <p><span><?php echo $total_articles; ?></span> article<?php echo $total_articles > 1 ? 's' : ''; ?> dans votre panier</p>
                </div>
                <?php foreach ($cart_items as $item) : ?>
                    <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                        <img src="<?php echo UPLOADS_URL . e($item['image'] ?? 'default.jpg'); ?>" alt="<?php echo e($item['nom']); ?>" class="cart-item-image">
                        <div class="cart-item-details">
                            <p class="cart-item-name"><?php echo e($item['nom']); ?></p>
                            <p class="cart-item-size"><?php echo e($item['contenance']); ?> ml</p>
                            <p class="cart-item-unit-price"><?php echo format_price($item['prix_unitaire']); ?></p>
                        </div>
                        <div class="quantity-selector">
                            <button class="quantity-btn minus" aria-label="Réduire la quantité">-</button>
                            <input type="text" value="<?php echo $item['quantite']; ?>" class="quantity-input" readonly>
                            <button class="quantity-btn plus" aria-label="Augmenter la quantité">+</button>
                        </div>
                        <p class="cart-item-total-price"><?php echo format_price($item['prix_total_item']); ?></p>
                        <button class="remove-item-btn" aria-label="Supprimer l'article">&times;</button>
                    </div>
                <?php endforeach; ?>
                <div class="cart-actions">
                    <a href="#" id="clear-cart-link">Vider le panier</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="cart-sidebar">
            <div class="form-box client-info-box">
                <h2>Informations client</h2>
                <div class="form-group">
                    <label for="nom_complet">Nom complet *</label>
                    <input type="text" id="nom_complet" placeholder="Votre nom et prénom" required>
                </div>
                <div class="form-group">
                    <label for="telephone">Téléphone (optionnel)</label>
                    <input type="tel" id="telephone" placeholder="Votre numéro de téléphone">
                </div>
            </div>

            <div class="form-box order-summary-box">
                <h2>Résumé de commande</h2>
                <?php if (!empty($cart_items)) : ?>
                    <div class="summary-row">
                        <span>Sous-total</span>
                        <span><?php echo format_price($sub_total); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>TVA (20%)</span>
                        <span><?php echo format_price($tva); ?></span>
                    </div>
                    <hr>
                    <div class="summary-row total-row">
                        <span>Total</span>
                        <span><?php echo format_price($total); ?></span>
                    </div>
                <?php endif; ?>

                <button id="whatsapp-order-btn" class="btn-whatsapp" <?php if (empty($cart_items)) echo 'disabled'; ?>>
                    Commander sur WhatsApp
                </button>
                <p class="whatsapp-notice">Vous serez redirigé vers WhatsApp avec votre commande préremplie.</p>
            </div>
            <a href="boutique.php" class="continue-shopping-link">Continuer vos achats</a>
        </div>
    </div>
</div>

<script>
    const baseWhatsappMessage = <?php echo json_encode($base_whatsapp_message); ?>;
    const whatsappNumber = '<?php echo e($whatsapp_number); ?>';
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>