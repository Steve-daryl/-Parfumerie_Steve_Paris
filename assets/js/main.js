/**
 * =================================================================
 * SCRIPT GLOBAL FUSIONNÉ (ADMIN + SITE)
 * =================================================================
 * Combine :
 *  - Tableau de bord (Admin) : Recherche & filtres AJAX.
 *  - Site principal : Menu mobile, filtres, recherche, panier.
 * =================================================================
 */

document.addEventListener('DOMContentLoaded', () => {

    /* ==============================================================
     * 1. LOGIQUE POUR LE PANNEAU DE FILTRES (COMMUNE)
     * ============================================================== */
    const openFiltersBtn = document.getElementById('open-filters-btn');
    const closeFiltersBtn = document.getElementById('close-filters-btn');
    const filterOverlay = document.getElementById('filter-overlay');

    function openFilters() {
        document.body.classList.add('filters-open');
    }
    function closeFilters() {
        document.body.classList.remove('filters-open');
    }

    if (openFiltersBtn) openFiltersBtn.addEventListener('click', openFilters);
    if (closeFiltersBtn) closeFiltersBtn.addEventListener('click', closeFilters);
    if (filterOverlay) filterOverlay.addEventListener('click', closeFilters);


    /* ==============================================================
     * 2. LOGIQUE ADMIN : RECHERCHE ET FILTRAGE EN AJAX
     * ============================================================== */
    const searchInputAdmin = document.getElementById('search-input');
    const categoryFilter = document.getElementById('filter-categorie');
    const stockFilter = document.getElementById('filter-stock');
    const productsTableBody = document.getElementById('products-table-body');
    const productListTitle = document.getElementById('product-list-title');
    const resetFiltersBtn = document.getElementById('reset-filters-btn');
    let searchTimeoutAdmin;

    async function loadProducts() {
        if (!productsTableBody) return;

        const searchTerm = searchInputAdmin.value.trim();
        const category = categoryFilter.value;
        const stockStatus = stockFilter.value;

        const apiUrl = new URL('../api/search_admin_products.php', window.location.href);
        apiUrl.searchParams.append('search', searchTerm);
        apiUrl.searchParams.append('categorie', category);
        apiUrl.searchParams.append('stock_status', stockStatus);

        productsTableBody.innerHTML =
            '<tr><td colspan="7" style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin"></i> Chargement...</td></tr>';

        try {
            const response = await fetch(apiUrl);
            if (!response.ok) throw new Error('Erreur réseau lors du chargement des produits.');
            const html = await response.text();
            productsTableBody.innerHTML = html;

            const rowCount = productsTableBody.querySelectorAll('tr').length;
            const hasNoResults = productsTableBody.querySelector('td[colspan="7"]');
            productListTitle.textContent = `Liste des Produits (${hasNoResults ? 0 : rowCount})`;

        } catch (error) {
            console.error('Erreur:', error);
            productsTableBody.innerHTML =
                '<tr><td colspan="7" style="text-align:center;color:red;padding:40px;">Une erreur est survenue.</td></tr>';
        }
    }

    if (searchInputAdmin) {
        searchInputAdmin.addEventListener('input', () => {
            clearTimeout(searchTimeoutAdmin);
            searchTimeoutAdmin = setTimeout(loadProducts, 300);
        });
    }
    if (categoryFilter) categoryFilter.addEventListener('change', loadProducts);
    if (stockFilter) stockFilter.addEventListener('change', loadProducts);
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', () => {
            searchInputAdmin.value = '';
            categoryFilter.value = '';
            stockFilter.value = '';
            loadProducts();
            closeFilters();
        });
    }
    if (productsTableBody) loadProducts();


    /* ==============================================================
     * 3. SITE : MENU MOBILE, RECHERCHE BOUTIQUE, PANIER
     * ============================================================== */

    // 3.1 Menu mobile
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', () => {
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

    const API_BASE = (typeof API_BASE_URL !== 'undefined') ? API_BASE_URL : 'api/';


    // 3.2 Recherche en direct (page boutique)
    const searchInput = document.querySelector('.shop-controls .search-bar input');
    const productGrid = document.querySelector('.product-grid');
    let searchTimeout;

    if (searchInput && productGrid) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();

            searchTimeout = setTimeout(async () => {
                if (searchTerm.length > 1) {
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


    // 3.3 Gestion du panier
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
                return { success: false, message: 'Erreur serveur.' };
            }
            return await response.json();
        } catch (error) {
            console.error('Erreur API:', error);
            return { success: false, message: 'Erreur de communication.' };
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

    // Ajout depuis la boutique
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

    // Page produit
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

    // Page panier
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
                    if (result.success) reloadCartPage();
                }
            }

            if (target.matches('#clear-cart-btn')) {
                e.preventDefault();
                if (confirm('Voulez-vous vraiment vider votre panier ?')) {
                    document.querySelector('.cart-items-list-unified').style.opacity = '0.5';
                    const result = await postToApi({ action: 'clear' });
                    if (result.success) reloadCartPage();
                }
            }
        });

        // Commande WhatsApp
        const whatsappOrderBtn = document.getElementById('whatsapp-order-btn');
        if (whatsappOrderBtn) {
            whatsappOrderBtn.addEventListener('click', async function () {
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
                        if (result.success) reloadCartPage();
                        else {
                            alert("Erreur lors du vidage du panier après commande.");
                            whatsappOrderBtn.disabled = false;
                            whatsappOrderBtn.textContent = 'Commander sur WhatsApp';
                        }
                    } catch (error) {
                        console.error("Erreur vidage panier:", error);
                        whatsappOrderBtn.disabled = false;
                        whatsappOrderBtn.textContent = 'Commander sur WhatsApp';
                    }
                } else {
                    alert("Erreur: Données manquantes. Rafraîchissez la page.");
                }
            });
        }
    }
});
