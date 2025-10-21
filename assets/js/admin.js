/**
 * ================================================================
 * SCRIPT GLOBAL DU DASHBOARD ADMIN - PARFUMERIE STEVE PARIS
 * ================================================================
 * Ce fichier gère de manière conditionnelle :
 *  1. La gestion dynamique de la page produits (filtres, recherche AJAX).
 *  2. Les graphiques statistiques du tableau de bord principal (Chart.js).
 *  3. Les interactions globales du dashboard.
 * ---------------------------------------------------------------
 * Auteur : Gemini pour Steve Paris
 * Version : 2.0 - Octobre 2025
 */

document.addEventListener('DOMContentLoaded', () => {

    /* ============================================================
     * SECTION 1 : LOGIQUE POUR LA PAGE DE GESTION DES PRODUITS
     * (Ce code ne s'exécute que si le tableau des produits est trouvé)
     * ============================================================ */
    const productsTableBody = document.getElementById('products-table-body');

    if (productsTableBody) {
        // --- Sélection des éléments du DOM pour la page produits ---
        const searchInput = document.getElementById('search-input');
        const categoryFilter = document.getElementById('filter-categorie');
        const stockFilter = document.getElementById('filter-stock');
        const productListTitle = document.getElementById('product-list-title');

        // Éléments du panneau de filtres
        const openFiltersBtn = document.getElementById('open-filters-btn');
        const closeFiltersBtn = document.getElementById('close-filters-btn');
        const filterPanel = document.getElementById('filter-panel');
        const filterOverlay = document.getElementById('filter-overlay');
        const resetFiltersBtn = document.getElementById('reset-filters-btn');

        let debounceTimer; // Variable pour le délai de la barre de recherche

        // --- Fonction principale pour charger les produits via l'API ---
        const fetchProducts = async () => {
            // Affiche un indicateur de chargement
            productsTableBody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Chargement...</td></tr>`;
            // Récupère les valeurs actuelles des filtres
            const searchTerm = searchInput.value;
            const categoryId = categoryFilter.value;
            const stockStatus = stockFilter.value;

            // Construit l'URL de l'API de manière sécurisée
            const apiUrl = new URL('../api/search_admin_products.php', window.location.href);
            apiUrl.searchParams.append('search', searchTerm);
            apiUrl.searchParams.append('categorie', categoryId);
            apiUrl.searchParams.append('stock_status', stockStatus);
            
            try {
                // Appelle l'API et récupère la réponse HTML
                const response = await fetch(apiUrl);
                if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);
                const htmlResponse = await response.text();

                // Met à jour le tableau avec les nouvelles données
                productsTableBody.innerHTML = htmlResponse;

                // Met à jour le titre avec le nombre de résultats
                // CORRECTION : On vérifie que productListTitle existe avant de le manipuler
                // AMÉLIORATION : Gestion intelligente du titre
                if (productListTitle) {
                    const errorCell = productsTableBody.querySelector('.error-cell');
                    const hasNoResultsMessage = productsTableBody.querySelector('td[colspan="8"]');
                    
                    if (errorCell) {
                        // Si l'API a renvoyé une erreur, on l'affiche dans le titre.
                        productListTitle.textContent = "Une erreur est survenue";
                    } else if (hasNoResultsMessage) {
                        productListTitle.textContent = "Aucun produit trouvé";
                    } else {
                        const rowCount = productsTableBody.querySelectorAll('tr').length;
                        productListTitle.textContent = `Liste des Produits (${rowCount})`;
                    }
                }

            } catch (error) {
                console.error('Erreur lors de la récupération des produits:', error);
                productsTableBody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 40px; color: #800020;">Une erreur est survenue. Impossible de charger les produits.</td></tr>`;
            }
        };

        // --- Gestion des événements pour les filtres et la recherche ---
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            // Attend 350ms après la dernière frappe pour lancer la recherche
            debounceTimer = setTimeout(fetchProducts, 350);
        });

        categoryFilter.addEventListener('change', fetchProducts);
        stockFilter.addEventListener('change', fetchProducts);

        // --- Logique d'interface pour le panneau de filtres ---
        const closeFilters = () => {
            filterPanel.classList.remove('active');
            filterOverlay.classList.remove('visible');
        };

        openFiltersBtn.addEventListener('click', () => {
            filterPanel.classList.add('active');
            filterOverlay.classList.add('visible');
        });

        closeFiltersBtn.addEventListener('click', closeFilters);
        filterOverlay.addEventListener('click', closeFilters);

        resetFiltersBtn.addEventListener('click', () => {
            // Vide les champs de filtre
            searchInput.value = '';
            categoryFilter.value = '';
            stockFilter.value = '';
            
            // Ferme le panneau et recharge la liste complète
            closeFilters();
            fetchProducts();
        });

        // --- Chargement initial des produits ---
        fetchProducts();
    }


    /* ============================================================
     * SECTION 2 : GRAPHIQUES DU TABLEAU DE BORD PRINCIPAL
     * (Ce code ne s'exécute que si les <canvas> des graphiques sont trouvés)
     * ============================================================ */
    const performanceCtx = document.getElementById('performanceChart');
    const expensesCtx = document.getElementById('expensesChart');
    
    // Fonction générique pour récupérer des données au format JSON
    async function fetchJsonData(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`Erreur HTTP! status: ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error("Erreur lors de la récupération des données JSON:", error);
            return null;
        }
    }
    
    // Initialisation du graphique de performance
    if (performanceCtx) {
        // NOTE: Vous devez créer le fichier 'api/get_performance_data.php' pour que cela fonctionne.
        fetchJsonData('api/get_performance_data.php').then(data => {
            if (data) {
                new Chart(performanceCtx, {
                    type: 'line',
                    data: {
                        labels: data.labels, // Ex: ['Jan', 'Fev', 'Mar', ...]
                        datasets: [
                            {
                                label: 'Ventes',
                                data: data.sales,
                                borderColor: 'rgb(75, 192, 192)',
                                tension: 0.3,
                                fill: false
                            },
                            {
                                label: 'Bénéfice',
                                data: data.profit,
                                borderColor: 'rgb(153, 102, 255)',
                                tension: 0.3,
                                fill: false
                            }
                        ]
                    },
                    options: { /* Options de style */ }
                });
            }
        });
    }

    // Initialisation du graphique des dépenses
    if (expensesCtx) {
        // NOTE: Vous devez créer le fichier 'api/get_expenses_data.php' pour que cela fonctionne.
        fetchJsonData('api/get_expenses_data.php').then(data => {
            if (data) {
                new Chart(expensesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels, // Ex: ['Achats', 'Marketing', ...]
                        datasets: [{
                            data: data.amounts,
                            backgroundColor: ['#4A90E2', '#FFC107', '#DC3545', '#28A745'],
                            hoverOffset: 4
                        }]
                    },
                    options: { /* Options de style */ }
                });
            }
        });
    }

});