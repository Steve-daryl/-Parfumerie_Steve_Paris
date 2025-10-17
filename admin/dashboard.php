<?php 
require_once("../config/database.php");
session_start();
if(!isset($_SESSION['administrateurs_id'])){
    header('Location: login.php');
}

// Compter les produits en rupture de stock (statut = 'rupture' ou stock = 0, et actif = 1)
$sql_rupture = "SELECT COUNT(*) as count FROM produits WHERE (statut = 'rupture' OR stock = 0) AND actif = 1";
$stmt_rupture = $pdo->prepare($sql_rupture);
$stmt_rupture->execute();
$rupture_count = $stmt_rupture->fetch(PDO::FETCH_ASSOC)['count'];

// Compter les produits expirés (date_de_peremption < date actuelle, et actif = 1)
$sql_expire = "SELECT COUNT(*) as count FROM produits WHERE date_de_peremption < CURDATE() AND actif = 1";
$stmt_expire = $pdo->prepare($sql_expire);
$stmt_expire->execute();
$expire_count = $stmt_expire->fetch(PDO::FETCH_ASSOC)['count'];

// Compter les produits en voie d'expiration (dans les 30 prochains jours, et actif = 1)
$sql_en_voie = "SELECT COUNT(*) as count FROM produits WHERE date_de_peremption BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND actif = 1";
$stmt_en_voie = $pdo->prepare($sql_en_voie);
$stmt_en_voie->execute();
$en_voie_count = $stmt_en_voie->fetch(PDO::FETCH_ASSOC)['count'];

// Compter les produits en faible stock (stock entre 1 et 9, et actif = 1)
$sql_faible = "SELECT COUNT(*) as count FROM produits WHERE stock > 0 AND stock < 3 AND actif = 1";
$stmt_faible = $pdo->prepare($sql_faible);
$stmt_faible->execute();
$faible_count = $stmt_faible->fetch(PDO::FETCH_ASSOC)['count'];

//Compter le nombre total de produit en stock
$sql="SELECT COUNT(*) AS nombre_de_produits FROM produits;";
$stmt_stock = $pdo->prepare($sql);
$stmt_stock->execute();
$produits = $stmt_stock->fetchAll();

// different alert
$low_stock_threshold = 3;
$expiry_alert_days = 90;

// Fetch top (first) out-of-stock product
$sql_out_of_stock = "SELECT id, nom, reference, stock, date_de_peremption, image, marque
                     FROM produits
                     WHERE stock = 0 AND actif = 1
                     ORDER BY nom ASC
                     LIMIT 1";
$stmt_out_of_stock = $pdo->prepare($sql_out_of_stock);
$stmt_out_of_stock->execute();
$out_of_stock_product = $stmt_out_of_stock->fetch(PDO::FETCH_ASSOC);

// Fetch top (first) low-stock product
$sql_low_stock = "SELECT id, nom, reference, stock, date_de_peremption, image, marque
                  FROM produits
                  WHERE stock > 0 AND stock <= :low_stock_threshold AND actif = 1
                  ORDER BY nom ASC
                  LIMIT 1";
$stmt_low_stock = $pdo->prepare($sql_low_stock);
$stmt_low_stock->bindParam(':low_stock_threshold', $low_stock_threshold, PDO::PARAM_INT);
$stmt_low_stock->execute();
$low_stock_product = $stmt_low_stock->fetch(PDO::FETCH_ASSOC);

// Fetch top (first) near-expiry product
$sql_near_expiry = "SELECT id, nom, reference, stock, date_de_peremption, image, marque
                    FROM produits
                    WHERE date_de_peremption IS NOT NULL
                      AND date_de_peremption <= DATE_ADD(CURDATE(), INTERVAL :expiry_alert_days DAY)
                      AND stock > 0 AND actif = 1
                    ORDER BY date_de_peremption ASC
                    LIMIT 1";
$stmt_near_expiry = $pdo->prepare($sql_near_expiry);
$stmt_near_expiry->bindParam(':expiry_alert_days', $expiry_alert_days, PDO::PARAM_INT);
$stmt_near_expiry->execute();
$near_expiry_product = $stmt_near_expiry->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Parfumerie Luxe</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- <link rel="stylesheet" href="../assets/css/dashbordproduits.css"> -->
    <!-- Pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Pour les graphiques (ex: Chart.js) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Barre latérale -->
        <aside class="sidebar">
            <div class="logo"><img src="../assets/images/logo_sans_arriere.png" width="70" height="auto" alt="logo"></div>
            <nav class="nav-links">
                <ul>
                    <li><a href="../admin/dashboard.php" class="active" title="Tableau de bord"><i class="fas fa-th-large"></i></a></li>
                    <li><a href="../admin/produits.php" title="Gestion des produits"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-5 h-5" aria-hidden="true"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><path d="m7.5 4.27 9 5.15"></path></svg><!-- <i class="fas fa-chart-line"></i> --></a></li>
                    <li><a href="../admin/alert.php"><i class="fas fa-bell"></i></a></li>
                    <!-- <li><a href="#"><i class="fas fa-cog"></i></a></li> -->
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
                    <a href="../index.php"><i class="fas fa-users icon-bg-blue" title="Interface client"></i></a>
                    <!-- <i class="fas fa-bell"></i> -->
                    <i class="fas fa-question-circle"></i>
                    <div class="user-profile">
                        <img src="../assets/images/sasuke uchiwa.jpg" title="profil" alt="User">
                    </div>
                </div>
            </header>

            <div class="grid-layout">
                <!-- Cartes principales -->
                <div class="card main-card">
                    <i class="fas fa-boxes icon-bg-blue"></i>
                    <h3>Produits en faible stock <br>(inferieur a 3)</h3>
                    <p class="amount"><?php echo $faible_count;?> Unités</p>
                </div>
                <div class="card main-card">
                    <i class="fas fa-boxes icon-bg-blue"></i>
                    <h3>Produits en rupture de stock</h3>
                    <p class="amount"><?php echo $rupture_count;?> Unités</p>
                </div>
                <div class="card main-card">
                    <i class="fas fa-boxes icon-bg-blue"></i>
                    <h3>Produits proche d'expiration</h3>
                    <p class="amount"><?php echo $en_voie_count ;?>Unités</p>
                </div>
                <div class="card main-card">
                    <i class="fas fa-boxes icon-bg-blue"></i>
                    <h3>Produits déja expirés</h3>
                    <p class="amount"><?php echo $expire_count ;?> Unités</p>
                </div>
                <div class="card main-card">
                    <i class="fas fa-boxes icon-bg-blue"></i>
                    <h3>Stock total</h3>
                    <?php foreach ($produits as $produit):?>
                        <p class="amount"><?= $produit['nombre_de_produits'];?> Unités</p>
                        <?php endforeach?>
                </div>

                <!-- Section Graphique Performance -->
                <!-- <div class="card chart-section">
                    <h3>Évolution des performances</h3>
                    <canvas id="performanceChart"></canvas>
                </div> -->

                <!-- Section Dépenses -->
                <!-- <div class="card expenses-section">
                    <h3>Toutes les dépenses</h3>
                    <canvas id="expensesChart"></canvas>
                </div> -->

                <!-- Section Produits Favoris -->
                <!-- <div class="card favorite-products">
                    <h3>Mes Produits Favoris</h3>
                    <div class="product-list">-->
                        <!-- Exemple de produit -->
                        <!-- <div class="product-item">
                            <img src="path/to/perfume1.jpg" alt="Parfum">
                        </div>
                        <div class="product-item">
                            <img src="path/to/perfume2.jpg" alt="Parfum">
                        </div>
                        <div class="product-item">
                            <img src="path/to/perfume3.jpg" alt="Parfum">
                        </div>
                    </div>
                </div> -->

                <!-- Section Alertes de Stock (Nouvelle section) -->
                <div class="card stock-alerts">
                    <h3><i class="fas fa-exclamation-triangle"></i> Alertes de Stock</h3>
                    <div class="alert-list">
                        <?php if ($low_stock_product): ?>
                            <div class="alert-item low-stock">
                                <p>Parfum "<?php echo htmlspecialchars($low_stock_product['nom']); ?>" - Seulement <?php echo $low_stock_product['stock']; ?> unités restantes.</p>
                                <a href="edit_product.php?id=<?php echo $low_stock_product['id']; ?>" class="btn-primary">Réapprovisionner</a>
                            </div>
                        <?php endif; ?>
                        <?php if ($out_of_stock_product): ?>
                            <div class="alert-item out-of-stock">
                                <p>Parfum "<?php echo htmlspecialchars($out_of_stock_product['nom']); ?>" - En rupture de stock.</p>
                                <a href="edit_product.php?id=<?php echo $out_of_stock_product['id']; ?>" class="btn-secondary">Commander</a>
                            </div>
                        <?php endif; ?>
                        <?php if ($near_expiry_product): ?>
                            <?php
                                $expiry_date = new DateTime($near_expiry_product['date_de_peremption']);
                                $today = new DateTime();
                                $interval = $today->diff($expiry_date);
                                $days_left = $interval->days;
                                if ($interval->invert) {
                                    $days_left = -$days_left;
                                }
                                $expiry_text = ($days_left < 0) ? "Expiré il y a " . abs($days_left) . " jours." : "Expire dans " . $days_left . " jours.";
                            ?>
                            <div class="alert-item near-expiry">
                                <p><?php echo htmlspecialchars($near_expiry_product['nom']); ?> - <?php echo $expiry_text; ?></p>
                                <a href="edit_product.php?id=<?php echo $near_expiry_product['id']; ?>" class="btn-tertiary">Promouvoir</a>
                            </div>
                        <?php endif; ?>
                        <?php if (!$low_stock_product && !$out_of_stock_product && !$near_expiry_product): ?>
                            <div class="alert-item no-alert">
                                <p>Aucune alerte de stock pour le moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>