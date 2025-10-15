<?php
require_once("../config/database.php");
session_start();

if(!isset($_SESSION['administrateurs_id'])){
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';

$categories_sql = [];
try {
    $sql_categories = "SELECT id, nom FROM categories ORDER BY nom ASC";
    $stmt_categories = $pdo->prepare($sql_categories);
    $stmt_categories->execute();
    $categories_sql = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gérer l'erreur si les catégories ne peuvent pas être chargées
    $message = "Erreur lors du chargement des catégories : " . $e->getMessage();
    $message_type = "error";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reference = trim($_POST['reference']);
    $nom = trim($_POST['nom']);
    $marque = trim($_POST['marque']);
    $contenance = trim($_POST['contenance']);
    $date_de_peremption = trim($_POST['date_de_peremption']);
    $description = trim($_POST['description']);
    $prix_achat = floatval($_POST['prix_achat']);
    $prix = floatval($_POST['prix']);
    $categorie_id = intval($_POST['categorie_id'] ?? 0);
    $numero_de_lot = trim($_POST['numero_de_lot'] ?? '');
    $quantite_stock = intval($_POST['quantite_stock']);
    $image_url = 'default_product.jpg'; // Valeur par défaut si aucune image n'est téléchargée

    // Validation simple (incluant les champs requis de la BDD)
    if (empty($reference) || empty($nom) || empty($description) || $prix_achat <= 0 || $prix <= 0 || $quantite_stock < 0 || $categorie_id <= 0) {
        $message = "Veuillez remplir tous les champs obligatoires correctement (Référence, Catégorie, Prix d'achat et Prix de vente requis).";
        $message_type = "error";
    } else {
        // Gérer le téléchargement de l'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../assets/images/";
            $image_file_name = uniqid() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Vérifier le type de fichier
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if($check === false) {
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
                    $image_url = $image_file_name;
                } else {
                    $message = "Une erreur s'est produite lors du téléchargement de l'image.";
                    $message_type = "error";
                }
            }
        }

        if ($message_type !== "error") { // Si aucune erreur d'upload, tenter d'insérer
            try {
                // Inclure tous les champs requis et optionnels (reference, prix_achat, categorie_id, numero_de_lot, etc.)
                $sql = "INSERT INTO produits (reference, nom, description, contenance, prix_achat, prix_vente, categorie_id, numero_de_lot, date_de_peremption, marque, stock, image, statut, actif) VALUES (:reference, :nom, :description, :contenance, :prix_achat, :prix_vente, :categorie_id, :numero_de_lot, :date_de_peremption, :marque, :stock, :image, 'disponible', 1)";
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
                $stmt->bindParam(':image', $image_url);
                $stmt->execute();

                $message = "Produit ajouté avec succès !";
                $message_type = "success";
                // Redirection après succès
                header('Location: produits.php?status=added');
                exit();
            } catch (PDOException $e) {
                $message = "Erreur lors de l'ajout du produit : " . $e->getMessage();
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
    <title>Ajouter un Produit - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/add_product.css">
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
                    <li><a href="../admin/produits.php" class="active" title="Gestion des produits"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-5 h-5" aria-hidden="true"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><path d="m7.5 4.27 9 5.15"></path></svg></a></li>
                    <li><a href="../admin/alert.php"><i class="fas fa-bell"></i></a></li>
                    <li><a href="#"><i class="fas fa-cog"></i></a></li>
                    <li><a href="../admin/logout.php" title="Déconnexion"><i class="fas fa-sign-out-alt"></i></a></li>
                </ul>
            </nav>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Ajouter un Nouveau Produit</h2>
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

                    <form action="add_product.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <div class="form-group">
                                <label for="nom">Nom du Produit:</label>
                                <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
                             </div>
                            <div class="form-group">
                                <label for="reference">Référence:</label>
                                <input type="text" id="reference" name="reference" required value="<?php echo htmlspecialchars($_POST['reference'] ?? ''); ?>">
                             </div>
                            <div class="form-group">   
                                <label for="marque">Marque:</label>
                                <input type="text" id="marque" name="marque" required value="<?php echo htmlspecialchars($_POST['marque'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="contenance">Contenance:</label>
                                <input type="text" id="contenance" name="contenance" required value="<?php echo htmlspecialchars($_POST['contenance'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="date_de_peremption">Date de Péremption:</label>
                                <input type="date" id="date_de_peremption" name="date_de_peremption" required value="<?php echo htmlspecialchars($_POST['date_de_peremption'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="prix_achat">Prix d'Achat (FCFA):</label>
                                <input type="number" id="prix_achat" name="prix_achat" step="0.01" min="0" required value="<?php echo htmlspecialchars($_POST['prix_achat'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="prix">Prix de Vente (FCFA):</label>
                                <input type="number" id="prix" name="prix" step="0.01" min="0" required value="<?php echo htmlspecialchars($_POST['prix'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="categorie">Catégorie :</label>
                                <select name="categorie_id" id="categorie" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    <?php foreach($categories_sql as $categorie): ?>
                                        <option value="<?= htmlspecialchars($categorie['id']); ?>" <?= (isset($_POST['categorie_id']) && $_POST['categorie_id'] == $categorie['id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($categorie['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="numero_de_lot">Numéro de Lot:</label>
                                <input type="text" id="numero_de_lot" name="numero_de_lot" value="<?php echo htmlspecialchars($_POST['numero_de_lot'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="quantite_stock">Quantité en Stock:</label>
                                <input type="number" id="quantite_stock" name="quantite_stock" min="0" required value="<?php echo htmlspecialchars($_POST['quantite_stock'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="image">Image du Produit:</label>
                                <input type="file" id="image" name="image" accept="image/*">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter le Produit</button>
                                <a href="produits.php" class="btn btn-secondary">Annuler</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>