<?php
// login.php

// Inclure le fichier de configuration de la base de données
    require_once('../config/database.php');

// Votre code de gestion de la session (doit être au tout début du script)
session_start();

// ... le reste de votre code HTML (HEAD, BODY, formulaire) ...
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);

    // --- ICI COMMENCE LA VÉRIFICATION SÉCURISÉE AVEC LA BASE DE DONNÉES ---

    try {
        // Préparer la requête SQL pour récupérer l'utilisateur par son e-mail
        // Utilisez des requêtes préparées pour prévenir les injections SQL !
        $stmt = $pdo->prepare("SELECT * FROM administrateurs WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $administrateurs = $stmt->fetch();

        // Vérifier si un utilisateur a été trouvé et si le mot de passe est correct
        if ($administrateurs && password_verify($password, $administrateurs['password'])) {
            // Mot de passe correct !
            // Définir les variables de session
            $_SESSION['administrateurs_id'] = $administrateurs['id'];
            $_SESSION['administrateurs_email'] = $administrateurs['email'];

            echo "<p class='success-message'>Connexion réussie ! Bienvenue, " . $administrateurs['email'] . ".</p>";
            // Rediriger l'utilisateur vers une page sécurisée
            header("Location: dashboard.php");
            exit();
        } else {
            // E-mail ou mot de passe incorrect
            $message = "Adresse e-mail ou mot de passe incorrect.";
            // echo "<p class='error-message'>Adresse e-mail ou mot de passe incorrect.</p>";
        }
    } catch (PDOException $e) {
        // Gérer les erreurs de base de données (loggez l'erreur, n'affichez pas directement à l'utilisateur)
        error_log("Erreur PDO lors de la connexion: " . $e->getMessage());
        echo "<p class='error-message'>Une erreur s'est produite lors de la tentative de connexion. Veuillez réessayer plus tard.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se Connecter | Steve Paris</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login_admin.css"> <!-- Assurez-vous que ce fichier CSS existe -->
</head>
<body>
    <div class="background-pattern"></div>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <img src="../assets/images/logo_or.png"  width="35" height="auto" alt="Steve Paris Logo">
                <span>Steve Paris</span>
            </div>
            <h2>Se Connecter</h2>
            <!-- <?php echo password_hash("password", PASSWORD_DEFAULT);?> -->
            <?php if (!empty($message)): ?>
                <div class="message error">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" placeholder="Votre adresse e-mail" required>
                    <i class="icon email-icon"></i> <!-- Icône e-mail -->
                </div>
                <div class="input-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
                    <i class="icon password-icon"></i> <!-- Icône mot de passe -->
                </div>
                <button type="submit" class="login-button">Se Connecter</button>
            </form>
            <!-- <div class="links">
                <a href="#">Mot de passe oublié ?</a>
                <span>&bull;</span>
                <a href="#">Créer un compte</a>
            </div> -->
        </div>
    </div>
</body>
</html>