<?php

/**
 * API pour gérer les actions du panier (ajouter, mettre à jour, supprimer, vider).
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? null;
$productId = isset($data['productId']) ? (int)$data['productId'] : null;

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$response = ['success' => false];

try {
    switch ($action) {
        case 'add':
            // MISE À JOUR : On peut maintenant ajouter une quantité spécifique
            $quantityToAdd = isset($data['quantity']) ? (int)$data['quantity'] : 1;
            if ($productId && $quantityToAdd > 0) {
                // Si le produit est déjà dans le panier, on additionne la quantité
                if (isset($_SESSION['panier'][$productId])) {
                    $_SESSION['panier'][$productId]['quantite'] += $quantityToAdd;
                } else {
                    // Sinon, on l'ajoute avec la quantité spécifiée
                    $_SESSION['panier'][$productId] = ['quantite' => $quantityToAdd];
                }
                $response['success'] = true;
            }
            break;

        case 'update':
            $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;
            if ($productId && $quantity > 0) {
                $_SESSION['panier'][$productId]['quantite'] = $quantity;
                $response['success'] = true;
            } elseif ($productId && $quantity <= 0) {
                unset($_SESSION['panier'][$productId]);
                $response['success'] = true;
            }
            break;

        case 'remove':
            if ($productId && isset($_SESSION['panier'][$productId])) {
                unset($_SESSION['panier'][$productId]);
                $response['success'] = true;
            }
            break;

        case 'clear':
            $_SESSION['panier'] = [];
            $response['success'] = true;
            break;
    }

    // Recalculer le nombre total d'articles pour la pastille du header
    $totalItems = 0;
    if (!empty($_SESSION['panier'])) {
        $totalItems = array_sum(array_column($_SESSION['panier'], 'quantite'));
    }
    $response['cartItemCount'] = $totalItems;
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
