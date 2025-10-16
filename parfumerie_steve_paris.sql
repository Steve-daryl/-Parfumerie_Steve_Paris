-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 16 oct. 2025 à 15:48
-- Version du serveur : 10.4.22-MariaDB
-- Version de PHP : 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `parfumerie_steve_paris`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateurs`
--

CREATE TABLE `administrateurs` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nom_complet` varchar(150) DEFAULT NULL,
  `role` enum('super_admin','admin','gestionnaire') DEFAULT 'admin',
  `actif` tinyint(1) DEFAULT 1,
  `derniere_connexion` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `administrateurs`
--

INSERT INTO `administrateurs` (`id`, `username`, `email`, `password`, `nom_complet`, `role`, `actif`, `derniere_connexion`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@parfumsdejojo.com', '$2y$10$bPzzgZNdriJHjOb6z3fzjewiKrtmFhK91mjadobpZnVDb5G6b6t/i', 'Administrateur Principal', 'super_admin', 1, NULL, '2025-10-13 15:25:54', '2025-10-14 07:53:29');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ordre` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`, `description`, `ordre`, `created_at`, `updated_at`) VALUES
(1, 'Parfums Homme', 'Collection exclusive pour homme', 1, '2025-10-13 15:25:54', '2025-10-13 15:25:54'),
(2, 'Parfums Femme', 'Collection raffinée pour femme', 2, '2025-10-13 15:25:54', '2025-10-13 15:25:54'),
(3, 'Parfums Unisexe', 'Fragrances pour tous', 3, '2025-10-13 15:25:54', '2025-10-13 15:25:54');

-- --------------------------------------------------------

--
-- Structure de la table `parametres`
--

CREATE TABLE `parametres` (
  `id` int(11) NOT NULL,
  `cle` varchar(100) NOT NULL,
  `valeur` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` enum('text','number','boolean','json') DEFAULT 'text',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `parametres`
--

INSERT INTO `parametres` (`id`, `cle`, `valeur`, `description`, `type`, `updated_at`) VALUES
(1, 'site_nom', 'Steve Paris', 'Nom du site', 'text', '2025-10-15 22:28:52'),
(2, 'site_email', 'contact@parfumsdejojo.com', 'Email de contact', 'text', '2025-10-13 15:25:54'),
(3, 'whatsapp_numero', '+237 690 98 47 58', 'Numéro WhatsApp', 'text', '2025-10-15 22:36:51'),
(4, 'devise', 'FCFA', 'Devise utilisée', 'text', '2025-10-13 15:25:54'),
(5, 'maintenance_mode', '0', 'Mode maintenance', 'boolean', '2025-10-13 15:25:54');

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id` int(11) NOT NULL,
  `reference` varchar(100) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `contenance` varchar(50) DEFAULT NULL,
  `prix_achat` decimal(10,2) NOT NULL,
  `prix_vente` decimal(10,2) NOT NULL,
  `categorie_id` int(11) DEFAULT NULL,
  `numero_de_lot` varchar(100) DEFAULT NULL,
  `date_de_peremption` date DEFAULT NULL,
  `marque` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `statut` enum('disponible','rupture','sur_commande') DEFAULT 'disponible',
  `actif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `reference`, `nom`, `description`, `contenance`, `prix_achat`, `prix_vente`, `categorie_id`, `numero_de_lot`, `date_de_peremption`, `marque`, `stock`, `image`, `statut`, `actif`, `created_at`, `updated_at`) VALUES
(1, 'Vero qui accusantium', 'Steve Paris', 'Un parfum d\'exception aux notes boisées et épicées', '100', '45000.00', '60000.00', 1, 'Reprehenderit ut cup', '2025-10-14', 'eau de parfum', 2, '68f0c98d2c1cf_home_3.jpg', 'disponible', 1, '2025-10-13 15:25:54', '2025-10-16 10:33:16'),
(2, 'Natus consequatur o', 'Baccarat rouge', 'Fragrance délicate aux notes florales', '100', '38000.00', '50000.00', 2, 'Qui qui fugiat quae', '2025-03-13', 'Premium Line', 30, '68ef839d28b8c_Baccarat_rouge.PNG', 'disponible', 1, '2025-10-13 15:25:54', '2025-10-15 20:10:31'),
(3, 'Officiis fugiat del', 'KAY ALI', 'Parfum mystérieux pour soirées inoubliables', '100', '52000.00', '70000.00', 3, 'Ducimus ut irure di', '2018-04-16', 'Exclusive', 4, 'Kaly.jpg', 'disponible', 1, '2025-10-13 15:25:54', '2025-10-15 20:10:59'),
(12, 'Animi enim hic sit', 'Tom Ford', 'Minim harum perferen', '50', '94.00', '27500.00', 3, 'Commodo velit dolor', '2025-10-30', 'Atque doloribus quo', 66, '68f012e0e8123_tom ford.jpg', 'disponible', 1, '2025-10-15 21:32:16', '2025-10-16 09:58:20'),
(13, 'Incididunt ipsam lib', 'Tempore in deleniti', 'Do quis quaerat volu', '86', '95.00', '21.00', 2, 'Dolores rerum deseru', '1996-03-08', 'Nostrum assumenda et', 16, '68f0a32b7058c_image acceuil 1.jpeg', 'disponible', 1, '2025-10-16 07:47:55', '2025-10-16 07:47:55');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `administrateurs`
--
ALTER TABLE `administrateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `parametres`
--
ALTER TABLE `parametres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cle` (`cle`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categorie` (`categorie_id`),
  ADD KEY `idx_actif` (`actif`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `administrateurs`
--
ALTER TABLE `administrateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `parametres`
--
ALTER TABLE `parametres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
