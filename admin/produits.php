<?php
// On inclut les fichiers de configuration pour les chemins et la session
require_once("../config/config.php");
require_once("../config/database.php");
// session_start();

// Vérifier si l'utilisateur est connecté, sinon rediriger vers la page de connexion
if (!isset($_SESSION['administrateurs_id'])) {
    header('Location: login.php');
    exit(); // Toujours appeler exit après un header('Location')
}

// On récupère les catégories UNIQUEMENT pour les afficher dans le panneau de filtres
try {
    $categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $categories = [];
    $error_message = "Erreur de récupération des catégories: " . $e->getMessage();
}

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
    <!-- PANNEAU DE FILTRES (HTML PRÊT POUR LE JAVASCRIPT) -->
    <div id="filter-overlay" class="filter-overlay"></div>
    <div id="filter-panel" class="filter-panel admin-filter-panel">
        <div class="filter-panel-header">
            <h2>Filtres</h2>
            <button id="close-filters-btn" class="close-btn" aria-label="Fermer les filtres">&times;</button>
        </div>
        <div class="filter-panel-body">
            <div class="filter-group">
                <label for="filter-categorie">Filtrer par Catégorie</label>
                <select id="filter-categorie" class="filter-select">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $categorie) : ?>
                        <option value="<?php echo e($categorie['id']); ?>"><?php echo e($categorie['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter-stock">Filtrer par Stock</label>
                <select id="filter-stock" class="filter-select">
                    <option value="">Tous les statuts</option>
                    <option value="in_stock">En stock</option>
                    <option value="low_stock">Stock faible (<= 5)</option>
                    <option value="out_of_stock">Rupture de stock</option>
                </select>
            </div>
            <div class="filter-buttons">
                <button id="reset-filters-btn" class="btn-reset-filters">Réinitialiser les filtres</button>
            </div>
        </div>
    </div>

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
                <h2>Gestion des Produits</h2>



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
                <div class="top-bar-controls">
                    <div class="action-buttons">
                        <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter un produit</a>
                        <!-- Les boutons modifier et supprimer seront dans le tableau pour chaque produit -->
                    </div>
                    <div class="search-and-filter">
                        <!-- Barre de recherche SANS la balise <form> -->
                        <div class="search-bar">
                            <input type="text" id="search-input" placeholder="Rechercher par nom ou marque...">
                            <i class="fas fa-search"></i>
                        </div>
                        <!-- Bouton pour ouvrir le panneau de filtres -->
                        <button id="open-filters-btn" class="filter-button">
                            <i class="fas fa-filter"></i> Filtres
                        </button>
                    </div>
                </div>

                <div class="card product-list-card">
                    <h3 id="product-list-title">Liste des Produits</h3>
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
                        <!-- Ce tbody est la CIBLE de notre JavaScript -->
                        <tbody id="products-table-body">
                            <!-- Le JavaScript remplira cette section -->
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">Chargement des produits...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>

</html>