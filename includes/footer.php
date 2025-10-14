<?php

/**
 * Pied de page commun à toutes les pages publiques du site.
 *
 * Ce script ferme la balise <main> ouverte dans le header,
 * affiche le footer avec les liens et informations, et inclut
 * le fichier JavaScript principal juste avant la fin du body.
 */
?>
</main> <!-- Fin de la balise <main> ouverte dans header.php -->

<footer class="main-footer">
    <div class="container">
        <div class="footer-copyright">
            <!-- On utilise la variable $site_name définie dans le header -->
            <p>&copy; <?php echo date('Y'); ?> <?php echo $site_name; ?>. Tous droits réservés.</p>
        </div>
        <nav class="footer-nav">
            <ul>
                <!-- Ces liens peuvent pointer vers des pages futures -->
                <li><a href="#">Mentions Légales</a></li>
                <li><a href="#">Politique de confidentialité</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
    </div>
</footer>

<!-- Inclusion du fichier JavaScript principal -->
<!-- Placé à la fin du body pour un chargement plus rapide de la page -->
<script src="<?php echo JS_PATH; ?>main.js"></script>
</body>

</html>