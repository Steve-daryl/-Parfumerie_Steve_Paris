-- Base de données pour Parfums de Jojo
CREATE DATABASE IF NOT EXISTS parfumerie_steve_paris CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE parfumerie_steve_paris;

-- Table des catégories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    ordre INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des produits
CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR NOT NULL,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    contenance VARCHAR(50), 
    prix_achat DECIMAL(10,2) NOT NULL,
    prix_vente DECIMAL(10,2) NOT NULL,
    categorie_id INT,
    numero_de_lot VARCHAR(100),
    date_de_peremption DATE,
    marque VARCHAR(100),
    stock INT DEFAULT 0,
    image VARCHAR(255),
    statut ENUM('disponible', 'rupture', 'sur_commande') DEFAULT 'disponible',
    actif TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_categorie (categorie_id),
    INDEX idx_actif (actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Table des administrateurs
CREATE TABLE administrateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom_complet VARCHAR(150),
    role ENUM('super_admin', 'admin', 'gestionnaire') DEFAULT 'admin',
    actif TINYINT(1) DEFAULT 1,
    derniere_connexion TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des paramètres du site
CREATE TABLE parametres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) UNIQUE NOT NULL,
    valeur TEXT,
    description VARCHAR(255),
    type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion de données de test
INSERT INTO categories (nom, description, ordre) VALUES
('Parfums Homme', 'Collection exclusive pour homme', 1),
('Parfums Femme', 'Collection raffinée pour femme', 2),
('Parfums Unisexe', 'Fragrances pour tous', 3);

INSERT INTO administrateurs (username, email, password, nom_complet, role) VALUES
('admin', 'admin@parfumsdejojo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur Principal', 'super_admin');
-- Mot de passe par défaut: password (à changer!)

INSERT INTO parametres (cle, valeur, description, type) VALUES
('site_nom', 'Parfums de Jojo', 'Nom du site', 'text'),
('site_email', 'contact@parfumsdejojo.com', 'Email de contact', 'text'),
('whatsapp_numero', '+237123456789', 'Numéro WhatsApp', 'text'),
('devise', 'FCFA', 'Devise utilisée', 'text'),
('maintenance_mode', '0', 'Mode maintenance', 'boolean');

-- Insertion de produits exemples
INSERT INTO produits (nom, description, prix, categorie_id, marque, stock, statut, vedette, badge) VALUES
('Parfum Luxe Gold', 'Un parfum d\'exception aux notes boisées et épicées', 45000, 1, 'Luxury Collection', 50, 'disponible', 1, 'Nouveau'),
('Essence Florale', 'Fragrance délicate aux notes florales', 38000, 2, 'Premium Line', 30, 'disponible', 1, 'Top Vente'),
('Mystery Night', 'Parfum mystérieux pour soirées inoubliables', 52000, 3, 'Exclusive', 20, 'disponible', 1, 'Exclusif');