<?php
require_once("../config/database.php");
session_start();

if(!isset($_SESSION['administrateurs_id'])){
    header('Location: login.php');
    exit();
}

$low_stock_threshold = 3; // Seuil pour le faible stock (ex: 3 unités ou moins)
$expiry_alert_days = 90;   // Nombre de jours avant expiration pour déclencher une alerte

$alerts = [
    'low_stock' => [],
    'out_of_stock' => [],
    'near_expiry' => []
];
$message = '';
$message_type = '';

try {
    // Requête pour les produits en rupture de stock (stock = 0)
    $sql_out_of_stock = "SELECT id, nom, reference, stock, date_de_peremption, image, marque
                         FROM produits
                         WHERE stock = 0
                         ORDER BY nom ASC";
    $stmt_out_of_stock = $pdo->prepare($sql_out_of_stock);
    $stmt_out_of_stock->execute();
    $alerts['out_of_stock'] = $stmt_out_of_stock->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour les produits en faible stock (stock > 0 et <= $low_stock_threshold)
    $sql_low_stock = "SELECT id, nom, reference, stock, date_de_peremption, image, marque
                      FROM produits
                      WHERE stock > 0 AND stock <= :low_stock_threshold
                      ORDER BY nom ASC";
    $stmt_low_stock = $pdo->prepare($sql_low_stock);
    $stmt_low_stock->bindParam(':low_stock_threshold', $low_stock_threshold, PDO::PARAM_INT);
    $stmt_low_stock->execute();
    $alerts['low_stock'] = $stmt_low_stock->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour les produits avec date de péremption proche
    // Nous filtrons les produits qui ne sont pas déjà en rupture de stock pour éviter les doublons dans les alertes visuelles
    $sql_near_expiry = "SELECT id, nom, reference, stock, date_de_peremption, image, marque
                        FROM produits
                        WHERE date_de_peremption IS NOT NULL
                          AND date_de_peremption <= DATE_ADD(CURDATE(), INTERVAL :expiry_alert_days DAY)
                          AND stock > 0 -- Ne pas inclure les produits déjà en rupture de stock pour cette alerte
                        ORDER BY date_de_peremption ASC";
    $stmt_near_expiry = $pdo->prepare($sql_near_expiry);
    $stmt_near_expiry->bindParam(':expiry_alert_days', $expiry_alert_days, PDO::PARAM_INT);
    $stmt_near_expiry->execute();
    $alerts['near_expiry'] = $stmt_near_expiry->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des alertes : " . $e->getMessage();
    $message_type = "error";
}

// Pour des raisons de lisibilité, nous allons afficher chaque type d'alerte dans une section distincte.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert Stock - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- Le fichier dashbordproduits.css peut contenir des styles spécifiques aux alertes -->
    <link rel="stylesheet" href="../assets/css/dashbordproduits.css">
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
                    <li><a href="../admin/produits.php" title="Gestion des produits"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-5 h-5" aria-hidden="true">
                                <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                <path d="M12 22V12"></path>
                                <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                <path d="m7.5 4.27 9 5.15"></path>
                            </svg></a></li>
                    <li><a href="../admin/alert.php" class="active"><i class="fas fa-bell"></i></a></li>
                    <!-- <li><a href="#"><i class="fas fa-cog"></i></a></li> -->
                    <li><a href="../admin/logout.php" title="Déconnexion"><i class="fas fa-sign-out-alt"></i></a></li>
                </ul>
            </nav>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Alertes de Stock & Péremption</h2>
                <div class="header-icons">
                    <i class="fas fa-bell"></i>
                    <i class="fas fa-question-circle"></i>
                    <div class="user-profile">
                        <img src="../assets/images/sasuke uchiwa.jpg" title="profil" alt="User">
                    </div>
                </div>
            </header>

            <div class="alerts-container">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Section Alertes de Rupture de Stock -->
                <div class="card alert-section out-of-stock-section">
                    <h3><i class="fas fa-dizzy"></i> Rupture de Stock (<?php echo count($alerts['out_of_stock']); ?>)</h3>
                    <?php if (count($alerts['out_of_stock']) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Produit</th>
                                    <th>Référence</th>
                                    <th>Marque</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alerts['out_of_stock'] as $product): ?>
                                    <tr class="alert-item">
                                        <td>
                                            <?php $imagePath = !empty($product['image']) ? '../assets/images/' . $product['image'] : '../assets/images/default_product.jpg'; ?>
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($product['nom']); ?>" class="product-thumb">
                                        </td>
                                        <td><?php echo htmlspecialchars($product['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($product['reference']); ?></td>
                                        <td><?php echo htmlspecialchars($product['marque']); ?></td>
                                        <td><span class="stock-zero"><?php echo htmlspecialchars($product['stock']); ?></span></td>
                                        <td>
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">Réapprovisionner</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-alert">Aucun produit en rupture de stock. Tout va bien !</p>
                    <?php endif; ?>
                </div>

                <!-- Section Alertes de Faible Stock -->
                <div class="card alert-section low-stock-section">
                    <h3><i class="fas fa-exclamation-triangle"></i> Faible Stock (<?php echo count($alerts['low_stock']); ?>)</h3>
                    <?php if (count($alerts['low_stock']) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Produit</th>
                                    <th>Référence</th>
                                    <th>Marque</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alerts['low_stock'] as $product): ?>
                                    <tr class="alert-item">
                                        <td>
                                            <?php $imagePath = !empty($product['image']) ? '../assets/images/' . $product['image'] : '../assets/images/default_product.jpg'; ?>
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($product['nom']); ?>" class="product-thumb">
                                        </td>
                                        <td><?php echo htmlspecialchars($product['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($product['reference']); ?></td>
                                        <td><?php echo htmlspecialchars($product['marque']); ?></td>
                                        <td><span class="stock-low"><?php echo htmlspecialchars($product['stock']); ?></span></td>
                                        <td>
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info">Commander plus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-alert">Aucun produit en faible stock.</p>
                    <?php endif; ?>
                </div>

                <!-- Section Alertes de Péremption Proche -->
                <div class="card alert-section near-expiry-section">
                    <h3><i class="fas fa-calendar-times"></i> Péremption Proche (<?php echo count($alerts['near_expiry']); ?>)</h3>
                    <?php if (count($alerts['near_expiry']) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Produit</th>
                                    <th>Référence</th>
                                    <th>Marque</th>
                                    <th>Stock</th>
                                    <th>Date Péremption</th>
                                    <th>Jours Restants</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alerts['near_expiry'] as $product): ?>
                                    <?php
                                        $expiry_date = new DateTime($product['date_de_peremption']);
                                        $today = new DateTime();
                                        $interval = $today->diff($expiry_date);
                                        $days_left = $interval->days;
                                        if ($interval->invert) { // Si la date est passée
                                            $days_left = -$days_left;
                                        }
                                    ?>
                                    <tr class="alert-item">
                                        <td>
                                            <?php $imagePath = !empty($product['image']) ? '../assets/images/' . $product['image'] : '../assets/images/default_product.jpg'; ?>
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($product['nom']); ?>" class="product-thumb">
                                        </td>
                                        <td><?php echo htmlspecialchars($product['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($product['reference']); ?></td>
                                        <td><?php echo htmlspecialchars($product['marque']); ?></td>
                                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                                        <td><?php echo htmlspecialchars($product['date_de_peremption']); ?></td>
                                        <td>
                                            <span class="expiry-days <?php echo ($days_left <= 0) ? 'expired' : (($days_left < 30) ? 'critical' : ''); ?>">
                                                <?php
                                                    if ($days_left < 0) {
                                                        echo "Expiré il y a " . abs($days_left) . " j";
                                                    } elseif ($days_left === 0) {
                                                        echo "Expire aujourd'hui !";
                                                    } else {
                                                        echo $days_left . " jours";
                                                    }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary">Promouvoir / Gérer</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-alert">Aucun produit avec une date de péremption proche.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>