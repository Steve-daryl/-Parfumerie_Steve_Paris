/**
 * =================================================================
 * SCRIPT PRINCIPAL POUR L'INTERACTIVITÉ DU SITE (VERSION FINALE CONSOLIDÉE)
 * =================================================================
 * Ce fichier gère :
 * 1. Le menu de navigation mobile.
 * 2. L'ouverture et la fermeture du panneau de filtres.
 * 3. La recherche de produits en direct sur la page boutique.
 * 4. L'ensemble de la logique du panier d'achat.
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

    // URL de base de l'API (définie dans footer.php pour la robustesse)
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
    const productGrid = document.querySelector('.product-grid'); // Élément partagé avec la logique panier
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
            }, 300); // Délai de 300ms avant de lancer la recherche
        });
    }


    // -----------------------------------------------------------------
    // 4. LOGIQUE DU PANIER DYNAMIQUE (globale)
    // -----------------------------------------------------------------
    const cartApiUrl = `${API_BASE}panier_actions.php`;

    async function postToApi(data) {
        try {
            const response = await fetch(cartApiUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
            if (!response.ok) { console.error('Erreur serveur:', response.status, await response.text()); return { success: false, message: 'Erreur serveur.' }; }
            return await response.json();
        } catch (error) { console.error('Erreur API:', error); return { success: false, message: 'Erreur de communication.' }; }
    }
    
    function updateCartBadge(count) {
        const wrapper = document.querySelector('.cart-icon-wrapper');
        if (!wrapper) return;
        let badge = wrapper.querySelector('.cart-badge');
        if (count > 0) {
            if (!badge) { badge = document.createElement('span'); badge.className = 'cart-badge'; wrapper.appendChild(badge); }
            badge.textContent = count;
        } else if (badge) { badge.remove(); }
    }

    // 4.1. Ajout au panier depuis la PAGE BOUTIQUE
    if (productGrid) {
        productGrid.addEventListener('click', async (e) => {
            const clickedButton = e.target.closest('.add-to-cart');
            if (!clickedButton) return;
            const productId = clickedButton.dataset.id;
            const card = clickedButton.closest('.product-card');
            const mainActionButton = card.querySelector('.product-purchase-info .add-to-cart');
            if (!mainActionButton) return;
            mainActionButton.disabled = true;
            mainActionButton.textContent = '...';
            const result = await postToApi({ action: 'add', productId, quantity: 1 });
            if (result.success) {
                updateCartBadge(result.cartItemCount);
                const purchaseInfoDiv = mainActionButton.closest('.product-purchase-info');
                const priceHTML = purchaseInfoDiv.querySelector('.product-price').outerHTML;
                const newButtonHTML = `<a href="panier.php" class="btn-cart-action in-cart"><span class="cart-icon-btn"></span> (1)</a>`;
                purchaseInfoDiv.innerHTML = priceHTML + newButtonHTML;
            } else {
                mainActionButton.disabled = false;
                mainActionButton.textContent = '+ Ajouter';
                alert(result.message || 'Erreur.');
            }
        });
    }

    // 4.2. Ajout au panier depuis la PAGE DÉTAIL PRODUIT
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
                    alert(result.message || 'Erreur.');
                }
            });
        }
    }

    // 4.3. Gestion de la PAGE PANIER
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
            
            if (target.matches('#clear-cart-btn')) {
                e.preventDefault();
                if (confirm('Voulez-vous vraiment vider votre panier ?')) {
                    document.querySelector('.cart-items-list-unified').style.opacity = '0.5';
                    const result = await postToApi({ action: 'clear' });
                    if(result.success) reloadCartPage();
                }
            }
        });

        // Logique pour le bouton de commande WhatsApp avec vidage automatique
        const whatsappOrderBtn = document.getElementById('whatsapp-order-btn');
        if (whatsappOrderBtn) {
            whatsappOrderBtn.addEventListener('click', async function() {
                const clientNameInput = document.getElementById('nom_complet');
                const clientPhoneInput = document.getElementById('telephone');
                let clientName = clientNameInput.value.trim();
                let clientPhone = clientPhoneInput.value.trim();

                if (clientName === '') {
                    alert('Veuillez saisir votre nom complet pour passer la commande.');
                    clientNameInput.focus();
                    clientNameInput.style.borderColor = 'red';
                    return;
                } else {
                    clientNameInput.style.borderColor = '';
                }

                if (typeof baseWhatsappMessage !== 'undefined' && typeof whatsappNumber !== 'undefined') {
                    whatsappOrderBtn.disabled = true;
                    whatsappOrderBtn.textContent = 'Préparation...';

                    let finalMessage = baseWhatsappMessage
                        .replace('%CLIENT_NAME%', clientName)
                        .replace('%CLIENT_PHONE%', clientPhone || 'Non spécifié');
                    
                    const cleanWhatsAppNumber = whatsappNumber.replace(/[^0-9]/g, '');
                    const whatsappUrl = `https://wa.me/${cleanWhatsAppNumber}?text=${encodeURIComponent(finalMessage)}`;
                    
                    window.open(whatsappUrl, '_blank');

                    try {
                        await new Promise(resolve => setTimeout(resolve, 1500));
                        const result = await postToApi({ action: 'clear' });
                        if (result.success) {
                            console.log('Panier vidé avec succès. Rechargement de la page.');
                            reloadCartPage();
                        } else {
                            alert("La commande a été préparée, mais une erreur est survenue lors du vidage du panier.");
                            whatsappOrderBtn.disabled = false;
                            whatsappOrderBtn.textContent = 'Commander sur WhatsApp';
                        }
                    } catch (error) {
                        console.error("Erreur lors de la tentative de vidage du panier:", error);
                        whatsappOrderBtn.disabled = false;
                        whatsappOrderBtn.textContent = 'Commander sur WhatsApp';
                    }
                } else {
                    alert("Erreur: Données de commande manquantes. Veuillez rafraîchir la page.");
                }
            });
        }
    }
});