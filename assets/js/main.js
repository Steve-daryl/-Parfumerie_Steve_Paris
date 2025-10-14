/**
 * Script principal pour l'interactivité du site.
 */
document.addEventListener('DOMContentLoaded', function() {

    // Gestion de l'ouverture/fermeture du menu mobile
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            // On bascule l'affichage du menu
            if (mainNav.style.display === 'flex') {
                mainNav.style.display = 'none';
            } else {
                // Pour le rendre visible, on le stylise un peu
                mainNav.style.display = 'flex';
                mainNav.style.flexDirection = 'column';
                mainNav.style.position = 'absolute';
                mainNav.style.top = '70px'; // Hauteur du header
                mainNav.style.right = '0';
                mainNav.style.width = '100%';
                mainNav.style.backgroundColor = 'rgba(253, 252, 248, 0.95)';
                mainNav.style.padding = '20px';
                mainNav.style.textAlign = 'center';
            }
        });
    }

});