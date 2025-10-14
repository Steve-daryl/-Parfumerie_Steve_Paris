<?php 
require_once("../config/database.php");
session_start();
if(!isset($_SESSION['id'])){
    header('Location: login.php');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Parfumerie Luxe</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- Pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Pour les graphiques (ex: Chart.js) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Barre latérale -->
        <aside class="sidebar">
            <div class="logo"><img src="../assets/images/logo.png" width="80" height="70" alt="logo"></div>
            <nav class="nav-links">
                <ul>
                    <li><a href="#" class="active" title="Tableau de bord"><i class="fas fa-th-large"></i></a></li>
                    <li><a href="../admin/produits.php" title="Gestion des produits"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-5 h-5" aria-hidden="true"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><path d="m7.5 4.27 9 5.15"></path></svg><!-- <i class="fas fa-chart-line"></i> --></a></li>
                    <li><a href="#"><i class="fas fa-cog"></i></a></li>
                    <li><a href="#"><i class="fas fa-bell"></i></a></li>
                    <li><a href="../admin/logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
                </ul>
            </nav>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Tableau de bord Steve Paris</h2>
                <div class="search-bar">
                    <input type="text" placeholder="Rechercher transaction, article, etc...">
                    <i class="fas fa-search"></i>
                </div>
                <div class="header-icons">
                    <i class="fas fa-bell"></i>
                    <i class="fas fa-question-circle"></i>
                    <div class="user-profile">
                        <img src="../assets/images/sasuke uchiwa.jpg" title="profil" alt="User">
                    </div>
                </div>
            </header>

            <div class="grid-layout">
                <!-- Cartes principales -->
                <div class="card main-card">
                    <i class="fas fa-wallet icon-bg-blue"></i>
                    <h3>Ventes mensuelles</h3>
                    <p class="amount">$2190.19</p>
                </div>
                <div class="card main-card">
                    <i class="fas fa-users icon-bg-blue"></i>
                    <h3>Nouveaux clients</h3>
                    <p class="amount">$2.23</p>
                </div>
                <div class="card main-card">
                    <i class="fas fa-boxes icon-bg-blue"></i>
                    <h3>Stock total</h3>
                    <p class="amount">$1875.10</p>
                </div>
                <div class="card main-card">
                    <i class="fas fa-shipping-fast icon-bg-blue"></i>
                    <h3>Commandes en cours</h3>
                    <p class="amount">$19.112</p>
                </div>

                <!-- Section Graphique Performance -->
                <div class="card chart-section">
                    <h3>Évolution des performances</h3>
                    <canvas id="performanceChart"></canvas>
                </div>

                <!-- Section Dépenses -->
                <div class="card expenses-section">
                    <h3>Toutes les dépenses</h3>
                    <canvas id="expensesChart"></canvas>
                </div>

                <!-- Section Produits Favoris -->
                <div class="card favorite-products">
                    <h3>Mes Produits Favoris</h3>
                    <div class="product-list">
                        <!-- Exemple de produit -->
                        <div class="product-item">
                            <img src="path/to/perfume1.jpg" alt="Parfum">
                        </div>
                        <div class="product-item">
                            <img src="path/to/perfume2.jpg" alt="Parfum">
                        </div>
                        <div class="product-item">
                            <img src="path/to/perfume3.jpg" alt="Parfum">
                        </div>
                    </div>
                </div>

                <!-- Section Alertes de Stock (Nouvelle section) -->
                <div class="card stock-alerts">
                    <h3><i class="fas fa-exclamation-triangle"></i> Alertes de Stock</h3>
                    <div class="alert-list">
                        <div class="alert-item low-stock">
                            <p>Parfum "Éclat d'Amour" - Seulement 5 unités restantes.</p>
                            <button class="btn-primary">Réapprovisionner</button>
                        </div>
                        <div class="alert-item out-of-stock">
                            <p>Parfum "Nuit Étoilée" - En rupture de stock.</p>
                            <button class="btn-secondary">Commander</button>
                        </div>
                        <div class="alert-item near-expiry">
                            <p>Testeur "Fleur de Lys" - Expire dans 30 jours.</p>
                            <button class="btn-tertiary">Promouvoir</button>
                        </div>
                    </div>
                </div>

                <!-- Section Dernières Commandes -->
                <div class="card orders-section">
                    <h3>Dernières commandes</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Produit</th>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><img src="path/to/avatar1.jpg" class="avatar"> Jean Dupont</td>
                                <td>Parfum N°5</td>
                                <td>12/03/23</td>
                                <td>$123.00</td>
                                <td><span class="status delivered">Livré</span></td>
                            </tr>
                            <tr>
                                <td><img src="path/to/avatar2.jpg" class="avatar"> Marie Curie</td>
                                <td>Coffret Dior</td>
                                <td>11/03/23</td>
                                <td>$200.00</td>
                                <td><span class="status pending">En attente</span></td>
                            </tr>
                            <!-- Plus de commandes -->
                        </tbody>
                    </table>
                </div>

                <!-- Section Offre Spéciale -->
                <div class="card special-offer">
                    <h3>Offre Spéciale du mois</h3>
                    <img src="path/to/offer-perfume.jpg" alt="Offre Parfum">
                    <p>Offre à -20% sur les Parfums Floraux</p>
                    <button class="btn-primary">Voir l'offre</button>
                </div>
            </div>
        </main>
    </div>

    <script src="js/main.js"></script>
</body>
</html>