/**
 * =================================================================
 * Script principal pour l'interactivité du site
 * - Gestion du menu mobile
 * - Gestion des filtres de la boutique
 * - Gestion de la recherche en direct
 * - Logique du panier (Ajout depuis boutique et détail, gestion du panier)
 * =================================================================
 */
document.addEventListener('DOMContentLoaded', function() {

    // -----------------------------------------------------------------
    // 1. GESTION DU MENU MOBILE
    // -----------------------------------------------------------------
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            const isVisible = mainNav.style.display === 'flex';
            mainNav.style.display = isVisible ? 'none' : 'flex';
            if (!isVisible) {
                mainNav.style.flexDirection = 'column';
                mainNav.style.position = 'absolute';
                mainNav.style.top = '70px';
                mainNav.style.right = '0';
                mainNav.style.width = '100%';
                mainNav.style.backgroundColor = 'rgba(253, 252, 248, 0.95)';
                mainNav.style.padding = '20px';
                mainNav.style.textAlign = 'center';
            }
        });
    }

    // URL de base de l'API (définie dans footer.php pour plus de robustesse)
    const API_BASE = (typeof API_BASE_URL !== 'undefined') ? API_BASE_URL : 'api/';


    // -----------------------------------------------------------------
    // 2. GESTION DU PANNEAU DE FILTRES
    // -----------------------------------------------------------------
    const openFiltersBtn = document.getElementById('open-filters-btn');
    const closeFiltersBtn = document.getElementById('close-filters-btn');
    const filterOverlay = document.getElementById('filter-overlay');

    function openFilters() { document.body.classList.add('filters-open'); }
    function closeFilters() { document.body.classList.remove('filters-open'); }

    if (openFiltersBtn) openFiltersBtn.addEventListener('click', openFilters);
    if (closeFiltersBtn) closeFiltersBtn.addEventListener('click', closeFilters);
    if (filterOverlay) filterOverlay.addEventListener('click', closeFilters);


    // -----------------------------------------------------------------
    // 3. LOGIQUE DE LA RECHERCHE EN DIRECT (PAGE BOUTIQUE)
    // -----------------------------------------------------------------
    const searchInput = document.querySelector('.shop-controls .search-bar input');
    const productGrid = document.querySelector('.product-grid'); // Partagé avec la logique panier
    let searchTimeout;

    if (searchInput && productGrid) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();

            searchTimeout = setTimeout(async () => {
                if (searchTerm.length > 1) { // Lancer à partir de 2 caractères
                    try {
                        productGrid.innerHTML = '<p class="no-products-message">Recherche en cours...</p>';
                        const response = await fetch(`${API_BASE}recherche_produits.php?search=${encodeURIComponent(searchTerm)}`);
                        if (!response.ok) throw new Error('Erreur réseau lors de la recherche.');
                        productGrid.innerHTML = await response.text();
                    } catch (error) {
                        console.error('Erreur de recherche:', error);
                        productGrid.innerHTML = '<p class="no-products-message">Erreur lors de la recherche.</p>';
                    }
                } else if (searchTerm.length === 0) {
                    window.location.href = 'boutique.php';
                }
            }, 300);
        });
    }


    // -----------------------------------------------------------------
    // 4. LOGIQUE DU PANIER DYNAMIQUE (globale)
    // -----------------------------------------------------------------
    const cartApiUrl = `${API_BASE}panier_actions.php`;

    async function postToApi(data) {
        try {
            const response = await fetch(cartApiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) {
                console.error('Erreur serveur:', response.status, await response.text());
                return { success: false, message: 'Erreur côté serveur.' };
            }
            return await response.json();
        } catch (error) {
            console.error('Erreur de communication API:', error);
            return { success: false, message: 'Erreur de communication avec le serveur.' };
        }
    }

    function updateCartBadge(count) {
        const wrapper = document.querySelector('.cart-icon-wrapper');
        if (!wrapper) return;
        let badge = wrapper.querySelector('.cart-badge');
        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'cart-badge';
                wrapper.appendChild(badge);
            }
            badge.textContent = count;
        } else if (badge) {
            badge.remove();
        }
    }

    // 4.1. Logique d'ajout au panier depuis la PAGE BOUTIQUE (Corrigée)
    if (productGrid) {
        productGrid.addEventListener('click', async (e) => {
            const clickedButton = e.target.closest('.add-to-cart');
            if (!clickedButton) return; // Si on a cliqué ailleurs, on sort

            const productId = clickedButton.dataset.id;
            const card = clickedButton.closest('.product-card');
            const mainActionButton = card.querySelector('.product-purchase-info .add-to-cart');

            // On ne fait rien si le bouton principal n'existe plus (déjà transformé)
            if (!mainActionButton) return;

            // Désactiver le bouton principal pour l'effet visuel
            mainActionButton.disabled = true;
            mainActionButton.textContent = '...';
            
            // Appeler l'API pour ajouter le produit
            const result = await postToApi({ action: 'add', productId, quantity: 1 });

            if (result.success) {
                updateCartBadge(result.cartItemCount);
                
                // Transformer le bouton principal en lien vers le panier
                const purchaseInfoDiv = mainActionButton.closest('.product-purchase-info');
                const priceHTML = purchaseInfoDiv.querySelector('.product-price').outerHTML;
                const newButtonHTML = `<a href="panier.php" class="btn-cart-action in-cart"><span class="cart-icon-btn"></span> (1)</a>`;
                purchaseInfoDiv.innerHTML = priceHTML + newButtonHTML;
            } else {
                // En cas d'erreur, réactiver le bouton principal
                mainActionButton.disabled = false;
                mainActionButton.textContent = '+ Ajouter';
                alert(result.message || 'Une erreur est survenue.');
            }
        });
    }

    // 4.2. Logique d'ajout au panier depuis la PAGE DÉTAIL PRODUIT
    const productPage = document.querySelector('.product-page-container');
    if (productPage) {
        const quantityDisplay = document.getElementById('quantity-display');
        const plusBtn = productPage.querySelector('.quantity-btn-detail.plus');
        const minusBtn = productPage.querySelector('.quantity-btn-detail.minus');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        let currentQuantity = 1;

        if (plusBtn) plusBtn.addEventListener('click', () => { currentQuantity++; quantityDisplay.textContent = currentQuantity; });
        if (minusBtn) minusBtn.addEventListener('click', () => { if (currentQuantity > 1) { currentQuantity--; quantityDisplay.textContent = currentQuantity; } });
        
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', async () => {
                const productId = addToCartBtn.dataset.id;
                addToCartBtn.disabled = true;
                addToCartBtn.textContent = 'Ajout en cours...';

                const result = await postToApi({ action: 'add', productId, quantity: currentQuantity });

                if (result.success) {
                    updateCartBadge(result.cartItemCount);
                    addToCartBtn.textContent = '✓ Ajouté au panier !';
                    addToCartBtn.classList.add('added');
                    setTimeout(() => {
                        addToCartBtn.disabled = false;
                        addToCartBtn.innerHTML = '🛒 Ajouter au panier';
                        addToCartBtn.classList.remove('added');
                    }, 2000);
                } else {
                    addToCartBtn.disabled = false;
                    addToCartBtn.innerHTML = '🛒 Ajouter au panier';
                    alert(result.message || 'Une erreur est survenue.');
                }
            });
        }
    }

    // 4.3. Logique de gestion de la PAGE PANIER
    const cartPage = document.querySelector('.cart-page');
    if (cartPage) {
        function reloadCartPage() { window.location.reload(); }
        cartPage.addEventListener('click', async (e) => {
            const target = e.target;
            const cartItem = target.closest('.cart-item');
            
            if (target.matches('.quantity-btn')) {
                const productId = cartItem.dataset.id;
                let quantity = parseInt(cartItem.querySelector('.quantity-input').value);
                if (target.classList.contains('plus')) quantity++;
                else if (target.classList.contains('minus')) quantity--;
                
                cartItem.style.opacity = '0.5';
                const action = (quantity < 1) ? 'remove' : 'update';
                const result = await postToApi({ action, productId, quantity });
                if (result.success) reloadCartPage(); else cartItem.style.opacity = '1';
            }

            if (target.matches('.remove-item-btn')) {
                if (confirm('Voulez-vous vraiment supprimer cet article ?')) {
                    cartItem.style.opacity = '0.5';
                    const result = await postToApi({ action: 'remove', productId: cartItem.dataset.id });
                    if(result.success) reloadCartPage();
                }
            }
            
            if (target.matches('#clear-cart-link')) {
                e.preventDefault();
                if (confirm('Voulez-vous vraiment vider votre panier ?')) {
                    document.querySelector('.cart-items-list').style.opacity = '0.5';
                    const result = await postToApi({ action: 'clear' });
                    if(result.success) reloadCartPage();
                }
            }
        });
    }
});