<?php
require_once("../config/database.php");
session_start();

// Vérifier si l'utilisateur est connecté, sinon rediriger vers la page de connexion
if(!isset($_SESSION['administrateurs_id'])){
    header('Location: login.php');
    exit(); // Toujours appeler exit après un header('Location')
}

$search_query = '';
$stmt = null; // Initialiser $stmt

// Gérer la recherche de produits
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = '%' . $_GET['search'] . '%';
    $sql = "SELECT id, nom, description, prix_vente, stock, image FROM produits WHERE nom LIKE :search OR description LIKE :search ORDER BY nom ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':search', $search_query, PDO::PARAM_STR);
} else {
    // Récupérer tous les produits si aucune recherche n'est effectuée
    $sql = "SELECT id, nom, description, prix_vente, stock, image FROM produits ORDER BY nom ASC";
    $stmt = $pdo->prepare($sql);
}

// Exécuter la requête et récupérer les produits
try {
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des produits : " . $e->getMessage();
    $produits = []; // Assurez-vous que $produits est toujours un tableau
}
$i=1;
// Gérer l'ajout/modification/suppression ici (ces parties nécessiteraient des formulaires et des scripts séparés ou des modales)
// Pour l'exemple, nous allons juste afficher la liste.
// Une logique complète pour ces actions serait plus complexe.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashbordproduits.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
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
                    <!-- <li><a href="#"><i class="fas fa-cog"></i></a></li> -->
                    <li><a href="../admin/logout.php" title="Déconnexion"><i class="fas fa-sign-out-alt"></i></a></li>
                </ul>
            </nav>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Gestion des Produits</h2>
                <div class="search-bar">
                    <form action="produits.php" method="GET">
                        <input type="text" name="search" placeholder="Rechercher un produit..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <!-- <button type="submit"><i class="fas fa-search"></i></button> -->
                    </form>
                </div>
                <div class="header-icons">
                    <a href="../index.php"><i class="fas fa-users icon-bg-blue" title="Interface client"></i></a>
                    <!-- <i class="fas fa-bell"></i> -->
                    <i class="fas fa-question-circle"></i>
                    <div class="user-profile">
                    <img src="../assets/images/sasuke uchiwa.jpg" title="profil" alt="User">
                    </div>
                </div>
            </header>

            <div class="products-management">
                <div class="action-buttons">
                    <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter un produit</a>
                    <!-- Les boutons modifier et supprimer seront dans le tableau pour chaque produit -->
                </div>

                <div class="card product-list-card">
                    <h3>Liste des Produits</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($produits) > 0): ?>
                                <?php foreach ($produits as $product): ?>
                                    <tr>
                                        <td><?= $i++?></td>
                                        <td><img src="../assets/images/<?php echo htmlspecialchars($product['image'] ?? 'default_product.jpg'); ?>" alt="<?php echo htmlspecialchars($product['nom']); ?>" class="product-thumb"></td>
                                        <td><?php echo htmlspecialchars($product['nom']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($product['description'], 0, 70)) . (strlen($product['description']) > 70 ? '...' : ''); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($product['prix_vente'], 2)); ?> FCFA</td>
                                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                                        <td>
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info" title="Modifier"><i class="fas fa-edit"></i></a>
                                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">Aucun produit trouvé.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>