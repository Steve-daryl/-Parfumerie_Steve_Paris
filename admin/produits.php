<?php
require_once("../config/database.php");
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo"><img src="../assets/images/logo.png" width="80" height="70" alt="logo"></div>
            <nav class="nav-links">
                <ul>
                    <li><a href="../admin/dashboard.php"  title="Tableau de bor"><i class="fas fa-th-large"></i></a></li>
                    <li><a href="../admin/produits.php" class="active" title="Gestion des produits"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-5 h-5" aria-hidden="true"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><path d="m7.5 4.27 9 5.15"></path></svg><!-- <i class="fas fa-chart-line"></i> --></a></li>
                    <li><a href="#"><i class="fas fa-cog"></i></a></li>
                    <li><a href="#"><i class="fas fa-bell"></i></a></li>
                    <li><a href="../admin/logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
                </ul>
            </nav>
        </aside>
    </div>
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
    </main>
    <button><a href="../admin/produits.php" class="add-product-btn">+ Ajouter un film</a></button>

</body>
</html>