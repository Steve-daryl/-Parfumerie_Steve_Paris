<?php
require_once('./config/database.php');
require_once __DIR__ . '/includes/header.php';

try {
    // Lien WhatsApp
    $whatsapp_number = $params['whatsapp_numero'] ?? '';
    $whatsapp_link = "https://wa.me/" . preg_replace('/[^0-9]/', '', $whatsapp_number) .
        "?text=Bonjour, j'aimerais en savoir plus sur vos parfums de luxe.";

    // 3 produits pour la section héros
    $img1 = "assets\images\uploads\home_1.jpeg";
    $img2 = "assets\images\uploads\home_2.jpeg";
    $img3 = "assets\images\uploads\home_3.jpg";
    } catch (\PDOException $e) {
    error_log("Erreur sur la page d'accueil : " . $e->getMessage());
    $featured_products = [];
    $whatsapp_link = '#';
}

// Assignation des images pour la grille de la section héros avec des images de secours
?>

<!-- ===== SECTION HÉROS MODERNISÉE ===== -->
<section class="hero-v2">
    <div class="container hero-v2-container">
        <div class="hero-v2-content">
            <h1 class="hero-v2-title">
              Parfums de <span class="hero-v2-highlight">Luxe</span>
            </h1>
            <p class="hero-v2-subtitle">
              Découvrez notre collection exclusive de parfums haute couture. Des parfums d'exception pour des moments inoubliables.
            </p>
            <div class="hero-v2-actions">
                <a href="boutique.php" class="btn btn-hero-primary">
                  Découvrir la Boutique
                </a>
                <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="btn btn-hero-secondary">
                  Contactez-nous
                </a>
            </div>
        </div>
        <div class="hero-v2-images">
            <div class="hero-v2-images-col1">
                <img src="<?php echo $img1; ?>" alt="Parfum de luxe 1" class="hero-img-1">
                <img src="<?php echo $img2; ?>" alt="Collection de parfums 2" class="hero-img-2">
            </div>
            <div class="hero-v2-images-col2">
                <img src="<?php echo $img3; ?>" alt="Parfums élégants 3" class="hero-img-3">
            </div>
        </div>
    </div>
</section>

<!-- ===== SECTION "POURQUOI NOUS CHOISIR" MODERNISÉE ===== -->
<section class="features-v2">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Pourquoi Choisir Steve Paris ?</h2>
            <p class="section-subtitle">Une expérience unique dans l'univers des parfums de luxe</p>
        </div>
        <div class="features-v2-grid">
            <div class="feature-v2-item">
                <div class="feature-v2-icon-wrapper">
                    <img src="<?php echo IMAGES_PATH; ?>perfume-bottle.svg" alt="Icône Parfum">
                </div>
                <h3>Parfums de Luxe</h3>
                <p>Collection exclusive des plus grandes marques : Dior, Chanel, Baccarat rouge et bien plus.</p>
            </div>
            <div class="feature-v2-item">
                <div class="feature-v2-icon-wrapper">
                    <img src="<?php echo IMAGES_PATH; ?>star.svg" alt="Icône Étoile">
                </div>
                <h3>Qualité Garantie</h3>
                <p>Tous nos produits sont authentiques avec traçabilité complète et dates d'expiration contrôlées.</p>
            </div>
            <div class="feature-v2-item">
                <div class="feature-v2-icon-wrapper">
                    <img src="<?php echo IMAGES_PATH; ?>whatsapp-chat.svg" alt="Icône Commande">
                </div>
                <h3>Commande WhatsApp</h3>
                <p>Commandez facilement via WhatsApp pour un service personnalisé et rapide.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== SECTION CTA (APPEL À L'ACTION) ===== -->
<section class="cta-section">
    <div class="container cta-container">
        <h2>Prêt à Découvrir Votre Parfum Idéal ?</h2>
        <p>Explorez notre collection et trouvez le parfum qui vous ressemble. Commandez facilement via WhatsApp.</p>
        <a href="boutique.php" class="btn btn-hero-primary">
            Explorer la Collection
        </a>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>