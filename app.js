// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initAuthForms();
    initProductGallery();
    initCart();
    initMobileMenu();
    initFormValidations();
});

// Authentication Forms Handler
function initAuthForms() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Login failed. Please try again.', 'error');
            }
        });
    }
    
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(registerForm);
            
            try {
                const response = await fetch('auth.php?register=1', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'auth.php';
                    }, 1500);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Registration failed. Please try again.', 'error');
            }
        });
    }
}

// Product Gallery
function initProductGallery() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        card.addEventListener('click', function() {
            const productId = this.dataset.productId;
            window.location.href = `product.php?id=${productId}`;
        });
        
        // Add to cart button
        const addToCartBtn = card.querySelector('.add-to-cart');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const productId = this.dataset.productId;
                addToCart(productId);
            });
        }
    });
}

// Shopping Cart System
function initCart() {
    const cartBtn = document.getElementById('cartBtn');
    const cartDropdown = document.getElementById('cartDropdown');
    const cartItemsContainer = document.getElementById('cartItems');
    const checkoutBtn = document.getElementById('checkoutBtn');
    
    if (cartBtn) {
        cartBtn.addEventListener('click', function() {
            cartDropdown.classList.toggle('hidden');
            updateCartDisplay();
        });
    }
    
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            window.location.href = 'checkout.php';
        });
    }
    
    // Close cart when clicking outside
    document.addEventListener('click', function(e) {
        if (!cartBtn.contains(e.target) && !cartDropdown.contains(e.target)) {
            cartDropdown.classList.add('hidden');
        }
    });
}

function addToCart(productId, quantity = 1) {
    let cart = JSON.parse(localStorage.getItem('agrimarket_cart')) || [];
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({ id: productId, quantity: quantity });
    }
    
    localStorage.setItem('agrimarket_cart', JSON.stringify(cart));
    updateCartDisplay();
    showAlert('Product added to cart', 'success');
}

function updateCartDisplay() {
    const cartItemsContainer = document.getElementById('cartItems');
    const cartCount = document.getElementById('cartCount');
    const cartTotal = document.getElementById('cartTotal');
    
    if (cartItemsContainer) {
        const cart = JSON.parse(localStorage.getItem('agrimarket_cart')) || [];
        let total = 0;
        let count = 0;
        
        cartItemsContainer.innerHTML = '';
        
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<p class="text-gray-500 p-4">Your cart is empty</p>';
        } else {
            // In a real app, you would fetch product details from your database
            cart.forEach(item => {
                // This is a placeholder - replace with actual product data
                const product = {
                    name: `Product ${item.id}`,
                    price: 10.99,
                    image: 'placeholder.jpg'
                };
                
                const itemTotal = product.price * item.quantity;
                total += itemTotal;
                count += item.quantity;
                
                const cartItemHTML = `
                    <div class="flex items-center p-2 border-b">
                        <img src="${product.image}" alt="${product.name}" class="w-12 h-12 object-cover rounded">
                        <div class="ml-3 flex-1">
                            <h4 class="font-medium">${product.name}</h4>
                            <p class="text-sm text-gray-600">$${product.price.toFixed(2)} x ${item.quantity}</p>
                        </div>
                        <span class="font-medium">$${itemTotal.toFixed(2)}</span>
                    </div>
                `;
                
                cartItemsContainer.insertAdjacentHTML('beforeend', cartItemHTML);
            });
            
            if (cartTotal) {
                cartTotal.textContent = `$${total.toFixed(2)}`;
            }
        }
        
        if (cartCount) {
            cartCount.textContent = count;
            cartCount.classList.toggle('hidden', count === 0);
        }
    }
}

// Mobile Menu Toggle
function initMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

// Form Validations
function initFormValidations() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('border-red-500');
                    input.nextElementSibling?.classList.remove('hidden');
                } else {
                    input.classList.remove('border-red-500');
                    input.nextElementSibling?.classList.add('hidden');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert('Please fill all required fields', 'error');
            }
        });
    });
}

// Alert System
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
    const alertId = 'alert-' + Date.now();
    const colors = {
        success: 'bg-green-100 border-green-400 text-green-700',
        error: 'bg-red-100 border-red-400 text-red-700',
        info: 'bg-blue-100 border-blue-400 text-blue-700',
        warning: 'bg-yellow-100 border-yellow-400 text-yellow-700'
    };
    
    const alertHTML = `
        <div id="${alertId}" class="alert ${colors[type]} border px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">${message}</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="document.getElementById('${alertId}').remove()">
                <svg class="fill-current h-6 w-6" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                </svg>
            </span>
        </div>
    `;
    
    alertContainer.insertAdjacentHTML('afterbegin', alertHTML);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        const alertElement = document.getElementById(alertId);
        if (alertElement) {
            alertElement.remove();
        }
    }, 5000);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alertContainer';
    container.className = 'fixed top-4 right-4 w-80 z-50';
    document.body.appendChild(container);
    return container;
}

// Utility Functions
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
}

// Export functions for use in HTML onclick attributes
window.addToCart = addToCart;
window.togglePasswordVisibility = togglePasswordVisibility;
window.showAlert = showAlert;
