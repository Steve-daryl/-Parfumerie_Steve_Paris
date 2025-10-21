<?php

/**
 * =================================================================
 * API DE RECHERCHE ET FILTRAGE - VERSION FINALE ET GARANTIE
 * =================================================================
 * Cette version garantit l'élimination de l'erreur SQLSTATE[HY093]
 * en synchronisant parfaitement la construction de la requête SQL
 * et le tableau de paramètres.
 */

// --- 1. Initialisation ---
require_once("../config/config.php");
require_once("../config/database.php");
header('Content-Type: text/html; charset=utf-8');

// --- 2. Récupération des Paramètres ---
$search_term = trim($_GET['search'] ?? '');
$filtre_categorie_id = !empty($_GET['categorie']) ? (int)$_GET['categorie'] : null;
$filtre_stock = trim($_GET['stock_status'] ?? '');

// --- 3. Construction Synchronisée de la Requête (LA CLÉ DE LA SOLUTION) ---
$sql = "SELECT p.id, p.nom, p.description, p.prix_vente, p.stock, p.image, c.nom AS categorie_nom
        FROM produits p
        LEFT JOIN categories c ON p.categorie_id = c.id
        WHERE p.actif = 1";

// On initialise TOUJOURS le tableau de paramètres. C'est essentiel.
$params = [];

// Pour chaque condition, on ajoute la clause SQL ET la valeur au tableau EN MÊME TEMPS.
// Cette synchronisation parfaite rend l'erreur HY093 impossible.
// if (!empty($search_term)) {
//     $sql .= " AND (p.nom LIKE :search OR p.marque LIKE :search)";
//     $params[':search'] = '%' . $search_term . '%';
// }
if (!empty($search_term)) {
    $search_value = '%' . $search_term . '%';
    $sql .= " AND (p.nom LIKE :search1 OR p.marque LIKE :search2)";
    $params[':search1'] = $search_value;
    $params[':search2'] = $search_value;
}
if ($filtre_categorie_id) {
    $sql .= " AND p.categorie_id = :categorie_id";
    $params[':categorie_id'] = $filtre_categorie_id;
}
if ($filtre_stock === 'in_stock') {
    $sql .= " AND p.stock > 5";
} elseif ($filtre_stock === 'low_stock') {
    $sql .= " AND p.stock > 0 AND p.stock <= 5";
} elseif ($filtre_stock === 'out_of_stock') {
    $sql .= " AND p.stock = 0";
}
$sql .= " ORDER BY p.nom ASC";

// --- 4. Exécution Sécurisée ---
try {
    $stmt = $pdo->prepare($sql);

    // On exécute la requête en passant le tableau $params.
    // PDO se charge de lier chaque valeur à son joker.
    $stmt->execute($params);

    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<tr><td colspan="8" class="error-cell">Erreur de base de données : ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
    exit();
}

// --- 5. Génération du HTML de Réponse ---
if (count($produits) > 0) {
    $i = 1;
    foreach ($produits as $product) {
        $image_path = !empty($product['image']) ? IMAGES_PATH . e($product['image']) : IMAGES_PATH . 'default_product.jpg';
        $stock_class = ($product['stock'] == 0) ? 'zero' : (($product['stock'] <= 5) ? 'low' : 'ok');
        $short_description = mb_substr(e($product['description']), 0, 50) . (mb_strlen($product['description']) > 50 ? '...' : '');

        echo '<tr>';
        echo '<td>' . $i++ . '</td>';
        echo '<td><img src="' . $image_path . '" alt="' . e($product['nom']) . '" class="product-thumb"></td>';
        echo '<td>' . e($product['nom']) . '</td>';
        echo '<td>' . $short_description . '</td>';
        echo '<td>' . e($product['categorie_nom'] ?? 'N/A') . '</td>';
        echo '<td>' . number_format($product['prix_vente'], 0, ',', ' ') . ' FCFA</td>';
        echo '<td><span class="stock-status stock-' . e($stock_class) . '">' . e($product['stock']) . '</span></td>';
        echo '<td class="action-cell">';
        echo '<a href="edit_product.php?id=' . $product['id'] . '" class="btn btn-sm btn-info" title="Modifier"><i class="fas fa-edit"></i></a>';
        echo '<a href="delete_product.php?id=' . $product['id'] . '" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm(\'Êtes-vous sûr ?\');"><i class="fas fa-trash-alt"></i></a>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="8" style="text-align: center; padding: 40px;">Aucun produit trouvé avec les critères actuels.</td></tr>';
}
