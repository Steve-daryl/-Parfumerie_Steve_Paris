<?php

/**
 * Pied de page commun à toutes les pages publiques du site.
 */
?>
</main> <!-- Fin de la balise <main> ouverte dans header.php -->

<footer class="main-footer">
    <div class="container">
        <div class="footer-copyright">
            <p>&copy; <?php echo date('Y'); ?> <?php echo $site_name; ?>. Tous droits réservés.</p>
        </div>
        <nav class="footer-nav">
            <ul>
                <li><a href="#">Mentions Légales</a></li>
                <li><a href="#">Politique de confidentialité</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
    </div>
</footer>

<!-- On définit l'URL de base de l'API pour que le JavaScript la connaisse. -->
<script>
    const API_BASE_URL = '<?php echo BASE_URL; ?>api/';
</script>

<!-- Inclusion du fichier JavaScript principal -->
<script src="<?php echo JS_PATH; ?>main.js"></script>
</body>

</html>