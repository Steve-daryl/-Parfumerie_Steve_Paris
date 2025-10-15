<?php

/**
 * Fichier de fonctions utilitaires pour éviter la répétition de code.
 * 
 * Note : La fonction e() est déclarée dans config.php et ne doit pas 
 * être redéclarée ici pour éviter un conflit.
 */

/**
 * Récupère un paramètre depuis la table `parametres` de la BDD.
 *
 * @param PDO $pdo L'objet de connexion PDO.
 * @param string $key La clé du paramètre à récupérer.
 * @return string|null La valeur du paramètre ou null si non trouvée.
 */
function get_setting(PDO $pdo, string $key): ?string
{
    $stmt = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn();
}

/**
 * Formate un nombre en devise (ex: FCFA).
 *
 * @param float $number Le nombre à formater.
 * @param string $currency La devise (par défaut 'FCFA').
 * @return string Le prix formaté.
 */
function format_price(float $number, string $currency = 'FCFA'): string
{
    return number_format($number, 0, ',', ' ') . ' ' . $currency;
}

/**
 * Tronque une chaîne de caractères à une longueur donnée sans couper les mots.
 *
 * @param string $text Le texte à tronquer.
 * @param int $length La longueur maximale.
 * @param string $ellipsis Le suffixe à ajouter si le texte est tronqué.
 * @return string Le texte tronqué.
 */
function truncate_text(string $text, int $length = 100, string $ellipsis = '...'): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    $text = mb_substr($text, 0, $length);
    $last_space = mb_strrpos($text, ' ');
    if ($last_space !== false) {
        $text = mb_substr($text, 0, $last_space);
    }
    return $text . $ellipsis;
}

/**
 * Récupère toutes les catégories actives depuis la base de données.
 *
 * @param PDO $pdo L'objet de connexion PDO.
 * @return array Tableau des catégories.
 */
function get_all_categories(PDO $pdo): array
{
    try {
        $stmt = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Erreur lors de la récupération des catégories : " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère un produit par son ID.
 *
 * @param PDO $pdo L'objet de connexion PDO.
 * @param int $product_id L'ID du produit.
 * @return array|null Les données du produit ou null.
 */
function get_product_by_id(PDO $pdo, int $product_id): ?array
{
    try {
        $stmt = $pdo->prepare(
            "SELECT p.*, c.nom AS categorie_nom FROM produits p 
             LEFT JOIN categories c ON p.categorie_id = c.id 
             WHERE p.id = ? AND p.actif = 1"
        );
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (\PDOException $e) {
        error_log("Erreur lors de la récupération du produit : " . $e->getMessage());
        return null;
    }
}

/**
 * Construire une URL de filtrage avec les paramètres de query.
 *
 * @param array $filters Les filtres à appliquer.
 * @param array $existing_params Les paramètres existants (optionnel).
 * @return string L'URL construite.
 */
function build_filter_url(array $filters = [], array $existing_params = []): string
{
    // Fusionner les paramètres existants avec les nouveaux filtres
    $params = array_merge($existing_params, $filters);

    // Supprimer les valeurs vides
    $params = array_filter($params, function ($value) {
        return $value !== '' && $value !== null;
    });

    // Construire et retourner l'URL
    return !empty($params) ? '?' . http_build_query($params) : '';
}

/**
 * Génère un lien WhatsApp pour contacter la boutique.
 *
 * @param string $phone_number Le numéro de téléphone WhatsApp.
 * @param string $message Le message à envoyer.
 * @return string L'URL WhatsApp complète.
 */
function get_whatsapp_link(string $phone_number, string $message = ''): string
{
    $clean_number = preg_replace('/[^0-9]/', '', $phone_number);
    $msg = !empty($message) ? '?text=' . urlencode($message) : '';
    return "https://wa.me/{$clean_number}{$msg}";
}

/**
 * Récupère l'URL de l'image d'un produit.
 *
 * @param string $image_filename Le nom du fichier image.
 * @param string $uploads_url L'URL du répertoire uploads.
 * @param string $default_image L'image par défaut.
 * @return string L'URL de l'image.
 */
function get_product_image_url(string $image_filename, string $uploads_url, string $default_image): string
{
    if (!empty($image_filename)) {
        return $uploads_url . e($image_filename);
    }
    return $default_image;
}

/**
 * Vérifie si une valeur est un nombre valide.
 *
 * @param mixed $value La valeur à vérifier.
 * @return bool True si c'est un nombre valide.
 */
function is_valid_id($value): bool
{
    return isset($value) && is_numeric($value) && (int)$value > 0;
}
