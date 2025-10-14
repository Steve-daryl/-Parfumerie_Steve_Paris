<?php
// Fichier de fonctions utilitaires pour éviter la répétition de code

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
 * Sécurise une sortie HTML pour prévenir les attaques XSS.
 *
 * @param string|null $string La chaîne à sécuriser.
 * @return string La chaîne sécurisée.
 */
function e(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
