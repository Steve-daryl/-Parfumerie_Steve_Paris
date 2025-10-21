<?php
require_once("../config/database.php");
session_start();

if(!isset($_SESSION['administrateurs_id'])){
    header('Location: login.php');
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = $_GET['id'];

    try {
        // D'abord, récupérer le nom de l'image pour la supprimer du serveur
        $stmt = $pdo->prepare("SELECT image FROM produits WHERE id = :id");
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product_image = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product_image && !empty($product_image['image']) && $product_image['image'] != 'default_product.jpg') {
            $image_path = '../assets/images/' . $product_image['image'];
            if (file_exists($image_path)) {
                unlink($image_path); // Supprimer le fichier image du dossier
            }
        }

        // Ensuite, supprimer le produit de la base de données
        $stmt = $pdo->prepare("DELETE FROM produits WHERE id = :id");
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['message'] = "Produit supprimé avec succès !"; // Message de succès
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression du produit : " . $e->getMessage(); // Message d'erreur
    }
} else {
    $_SESSION['error'] = "ID du produit manquant pour la suppression.";
}

header('Location: produits.php');
exit();
?>