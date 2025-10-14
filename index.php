<?php

/**
 * Page d'accueil du site Parfumerie Steve Paris.
 * Affiche la section "héros" et les points forts de la boutique.
 */

// On inclut l'en-tête de la page
require_once __DIR__ . '/includes/header.php';

// --- Récupération des données pour la page ---
try {
    // Récupérer le numéro WhatsApp depuis les paramètres chargés dans le header
    // $whatsapp_number = $params['whatsapp_numero'] ?? '';
    $whatsapp_number = $params['whatsapp_numero'] ?? '';
    $whatsapp_link = "https://wa.me/" . preg_replace('/[^0-9]/', '', $whatsapp_number) . "?text=Bonjour, j'aimerais en savoir plus sur vos parfums de luxe.";
    // $whatsapp_link = "https://wa.me/237690984758?text=Bonjour, j'aimerais en savoir plus sur vos parfums de luxe.";

    // Récupérer les 3 produits à mettre en avant sur la page d'accueil
    $stmt_products = $pdo->prepare(
        "SELECT id, nom, image FROM produits WHERE actif = 1 ORDER BY id DESC LIMIT 3"
    );
    $stmt_products->execute();
    $featured_products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Erreur sur la page d'accueil : " . $e->getMessage());
    $featured_products = [];
}
// echo '<pre>';
// print_r($featured_products);
// echo '</pre>';

// ====================================================================================
// SECTION IMPORTANTE : ASSIGNATION DES IMAGES
//
// Le code ci-dessous prend les noms de fichiers récupérés de la base de données
// (ex: 'perfum (1).jpeg') et construit l'URL complète pour l'afficher.
// La constante UPLOADS_URL vient de config.php et vaut :
// 'http://localhost/Parfumerie_Steve_Paris/assets/images/uploads/'
// Le résultat final sera donc, par exemple :
// 'http://localhost/Parfumerie_Steve_Paris/assets/images/uploads/perfum (1).jpeg'
// ====================================================================================

$img1 = !empty($featured_products[0]['image']) ? UPLOADS_URL . e($featured_products[0]['image']) : IMAGES_PATH . 'perfum.jpeg';
$img2 = !empty($featured_products[1]['image']) ? UPLOADS_URL . e($featured_products[1]['image']) : IMAGES_PATH . 'perfum.jpeg';
$img3 = !empty($featured_products[2]['image']) ? UPLOADS_URL . e($featured_products[2]['image']) : IMAGES_PATH . 'perfum.jpeg';
// echo $img3;
?>

<!-- Section Héros -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Parfums de <span class="luxe">Luxe</span></h1>
            <p class="hero-subtitle">
                Découvrez notre collection exclusive de parfums haute couture.
                Des parfums d'exception pour des moments inoubliables.
            </p>
            <div class="hero-actions">
                <a href="boutique.php" class="btn btn-primary">
                    <img src="<?php echo IMAGES_PATH; ?>cart-icon.svg" alt="">
                    Découvrir la Boutique
                </a>
                <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="btn btn-secondary">
                    <img src="<?php echo IMAGES_PATH; ?>whatsapp-icon.svg" alt="">
                    Contactez WhatsApp
                </a>
            </div>
        </div>
        <div class="hero-images">
            <!-- Les balises img utilisent maintenant les bonnes URLs construites juste au-dessus -->
            <div class="hero-image hero-image-1"><img src="<?php echo $img1; ?>" alt="Parfum de luxe 1"></div>
            <div class="hero-image hero-image-2"><img src="<?php echo $img2; ?>" alt="Parfum de luxe 2"></div>
            <div class="hero-image hero-image-3"><img src="<?php echo $img3; ?>" alt="Parfum de luxe 3"></div>
        </div>
    </div>
</section>

<!-- Section "Pourquoi nous choisir ?" -->
<section class="features">
    <div class="container">
        <h2 class="section-title">Pourquoi Choisir Parfumerie Steve Paris ?</h2>
        <p class="section-subtitle">Une expérience unique dans l'univers des parfums de luxe</p>
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <img src="<?php echo IMAGES_PATH; ?>perfume-bottle.svg" alt="Icone Parfum">
                </div>
                <h3>Parfums de Luxe</h3>
                <p>Collection exclusive des plus grandes marques : Dior, Chanel, Guerlain et bien plus.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <img src="<?php echo IMAGES_PATH; ?>star.svg" alt="Icone Etoile">
                </div>
                <h3>Qualité Garantie</h3>
                <p>Tous nos produits sont authentiques avec traçabilité complète et dates d'expiration contrôlées.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <img src="<?php echo IMAGES_PATH; ?>whatsapp-chat.svg" alt="Icone Commande">
                </div>
                <h3>Commande WhatsApp</h3>
                <p>Commandez facilement via WhatsApp pour un service personnalisé et rapide.</p>
            </div>
        </div>
    </div>
</section>

<?php
// On inclut le pied de page
require_once __DIR__ . '/includes/footer.php';
?>