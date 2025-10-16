<?php
require_once("../config/database.php");
session_start();

if (!isset($_SESSION['administrateurs_id'])) {
    header('Location: login.php');
    exit();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$message = '';
$message_type = '';

$categories_sql = [];
try {
    $sql_categories = "SELECT id, nom FROM categories ORDER BY nom ASC";
    $stmt_categories = $pdo->prepare($sql_categories);
    $stmt_categories->execute();
    $categories_sql = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors du chargement des catégories : " . $e->getMessage();
    $message_type = "error";
}

// Récupérer les informations du produit existant
if ($product_id > 0) {
    try {
        $sql = "SELECT id, reference, nom, description, contenance, prix_achat, prix_vente, categorie_id, numero_de_lot, date_de_peremption, marque, stock, image, statut, actif FROM produits WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $message = "Produit non trouvé.";
            $message_type = "error";
            header('Location: produits.php?status=notfound');
            exit();
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de la récupération du produit : " . $e->getMessage();
        $message_type = "error";
    }
} else {
    $message = "ID de produit manquant.";
    $message_type = "error";
    header('Location: produits.php?status=invalidid');
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product) {
    $reference = trim($_POST['reference']);
    $nom = trim($_POST['nom']);
    $marque = trim($_POST['marque']);
    $contenance = trim($_POST['contenance']);
    $date_de_peremption = trim($_POST['date_de_peremption']);
    $description = trim($_POST['description']);
    $prix_achat = floatval($_POST['prix_achat']);
    $prix = floatval($_POST['prix']);
    $categorie_id = intval($_POST['categorie_id'] ?? 0);
    $numero_de_lot = trim($_POST['numero_de_lot']);
    $quantite_stock = intval($_POST['quantite_stock']);
    $statut = trim($_POST['statut']);
    $current_image_url = $_POST['current_image_url'] ?? ($product['image'] ?? NULL);
    $new_image_url = $current_image_url;

    // Validation simple (incluant les champs requis de la BDD)
    if (empty($reference) || empty($nom) || empty($description) || $prix_achat <= 0 || $prix <= 0 || $quantite_stock < 0 || empty($statut)) {
        $message = "Veuillez remplir tous les champs obligatoires correctement.";
        $message_type = "error";
    } else {
        // Gérer le téléchargement de la nouvelle image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../assets/images/";
            $image_file_name = uniqid() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                $message = "Le fichier n'est pas une image.";
                $message_type = "error";
            } elseif (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
                $message = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
                $message_type = "error";
            } elseif ($_FILES["image"]["size"] > 5000000) { // 5MB max
                $message = "Désolé, votre fichier est trop volumineux.";
                $message_type = "error";
            } else {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $new_image_url = $image_file_name;
                    // Supprimer l'ancienne image si elle n'est pas l'image par défaut et existe
                    if ($current_image_url && $current_image_url !== 'default_product.jpg' && file_exists($target_dir . $current_image_url)) {
                        unlink($target_dir . $current_image_url);
                    }
                } else {
                    $message = "Une erreur s'est produite lors du téléchargement de la nouvelle image.";
                    $message_type = "error";
                }
            }
        }

        if ($message_type !== "error") { // Si aucune erreur d'upload, tenter la mise à jour
            try {
                $sql = "UPDATE produits SET reference = :reference, nom = :nom, description = :description, contenance = :contenance, prix_achat = :prix_achat, prix_vente = :prix_vente, categorie_id = :categorie_id, numero_de_lot = :numero_de_lot, date_de_peremption = :date_de_peremption, marque = :marque, stock = :stock, image = :image, statut = :statut WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':reference', $reference);
                $stmt->bindParam(':nom', $nom);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':contenance', $contenance);
                $stmt->bindParam(':prix_achat', $prix_achat);
                $stmt->bindParam(':prix_vente', $prix);
                $stmt->bindParam(':categorie_id', $categorie_id);
                $stmt->bindParam(':numero_de_lot', $numero_de_lot);
                $stmt->bindParam(':date_de_peremption', $date_de_peremption);
                $stmt->bindParam(':marque', $marque);
                $stmt->bindParam(':stock', $quantite_stock);
                $stmt->bindParam(':image', $new_image_url);
                $stmt->bindParam(':statut', $statut);
                $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
                $stmt->execute();

                $message = "Produit mis à jour avec succès !";
                $message_type = "success";
                header('Location: produits.php?status=updated');
                exit();
            } catch (PDOException $e) {
                $message = "Erreur lors de la mise à jour du produit : " . $e->getMessage();
                $message_type = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Produit | Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/edit_product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Barre latérale -->
        <aside class="sidebar">
            <div class="logo"><img src="../assets/images/logo_sans_arriere.png" width="70" height="auto" alt="logo"></div>
            <nav class="nav-links">
                <ul>
                    <li><a href="../admin/dashboard.php" title="Tableau de bord"><i class="fas fa-th-large"></i></a></li>
                    <li><a href="../admin/produits.php" class="active" title="Gestion des produits"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-5 h-5" aria-hidden="true">
                                <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                <path d="M12 22V12"></path>
                                <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                <path d="m7.5 4.27 9 5.15"></path>
                            </svg></a></li>
                    <li><a href="../admin/alert.php"><i class="fas fa-bell"></i></a></li>
                    <!-- <li><a href="#"><i class="fas fa-cog"></i></a></li> -->
                    <li><a href="../admin/logout.php" title="Déconnexion"><i class="fas fa-sign-out-alt"></i></a></li>
                </ul>
            </nav>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Modifier le Produit: <?php echo htmlspecialchars($product['nom'] ?? 'N/A'); ?></h2>
                <div class="header-icons">
                    <i class="fas fa-bell"></i>
                    <i class="fas fa-question-circle"></i>
                    <div class="user-profile">
                        <img src="../assets/images/sasuke uchiwa.jpg" title="profil" alt="User">
                    </div>
                </div>
            </header>

            <div class="products-form-container">
                <div class="card">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($product): ?>
                        <form action="edit_product.php?id=<?php echo $product['id']; ?>" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($product['image'] ?? ''); ?>">

                            <div class="form-group">
                                <label for="nom">Nom du Produit:</label>
                                <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ($product['nom'] ?? '')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="reference">Référence:</label>
                                <input type="text" id="reference" name="reference" required value="<?php echo htmlspecialchars($_POST['reference'] ?? ($product['reference'] ?? '')); ?>">
                            </div>
                            <div class="form-group">   
                                <label for="marque">Marque:</label>
                                <input type="text" id="marque" name="marque" required value="<?php echo htmlspecialchars($_POST['marque'] ?? ($product['marque'] ?? '')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="contenance">Contenance (ml):</label>
                                <input type="number" id="contenance" name="contenance" required value="<?php echo htmlspecialchars($_POST['contenance'] ?? ($product['contenance'] ?? '')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="date_de_peremption">Date de Péremption:</label>
                                <input type="date" id="date_de_peremption" name="date_de_peremption" required value="<?php echo htmlspecialchars($_POST['date_de_peremption'] ?? ($product['date_de_peremption'] ?? '')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($_POST['description'] ?? ($product['description'] ?? '')); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="prix_achat">Prix d'Achat (FCFA):</label>
                                <input type="number" id="prix_achat" name="prix_achat" step="0.01" min="0" required value="<?php echo htmlspecialchars($_POST['prix_achat'] ?? ($product['prix_achat'] ?? '')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="prix">Prix de Vente (FCFA):</label>
                                <input type="number" id="prix" name="prix" step="0.01" min="0" required value="<?php echo htmlspecialchars($_POST['prix'] ?? ($product['prix_vente'] ?? '')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="categorie">Catégorie :</label>
                                <select name="categorie_id" id="categorie">
                                    <option value="">Sélectionner une catégorie</option>
                                    <?php foreach($categories_sql as $categorie): ?>
                                        <option value="<?= htmlspecialchars($categorie['id']); ?>" <?= (isset($_POST['categorie_id']) && $_POST['categorie_id'] == $categorie['id']) || ($product['categorie_id'] == $categorie['id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($categorie['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="numero_de_lot">Numéro de Lot:</label>
                                <input type="text" id="numero_de_lot" name="numero_de_lot" value="<?php echo htmlspecialchars($_POST['numero_de_lot'] ?? ($product['numero_de_lot'] ?? '')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="quantite_stock">Quantité en Stock:</label>
                                <input type="number" id="quantite_stock" name="quantite_stock" min="0" required value="<?php echo htmlspecialchars($_POST['quantite_stock'] ?? ($product['stock'] ?? '')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="statut">Statut:</label>
                                <select name="statut" id="statut" required>
                                    <option value="disponible" <?= (isset($_POST['statut']) && $_POST['statut'] == 'disponible') || ($product['statut'] == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                                    <option value="rupture" <?= (isset($_POST['statut']) && $_POST['statut'] == 'rupture') || ($product['statut'] == 'rupture') ? 'selected' : ''; ?>>Rupture</option>
                                    <option value="sur_commande" <?= (isset($_POST['statut']) && $_POST['statut'] == 'sur_commande') || ($product['statut'] == 'sur_commande') ? 'selected' : ''; ?>>Sur commande</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Image Actuelle:</label>
                                <img src="../assets/images/<?php echo htmlspecialchars($product['image'] ?? 'default_product.jpg'); ?>" width="200px" height="auto" alt="<?php echo htmlspecialchars($product['nom']); ?>" class="current-product-image">
                                <br>
                                <label for="image">Nouvelle Image du Produit (laisser vide pour garder l'actuelle):</label>
                                <input type="file" id="image" name="image" accept="image/*">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer les modifications</button>
                                <a href="produits.php" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <p>Impossible de charger les informations du produit.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>