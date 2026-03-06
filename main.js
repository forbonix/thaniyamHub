// js/main.js

const API_BASE = 'http://localhost/ThaniyamHub/backend/api';

// Utility: Show Toast Notification
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;

    if (type === 'error') {
        toast.style.backgroundColor = 'var(--error)';
    }

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Global Cart System using LocalStorage
const Cart = {
    getItems: function() {
        const cart = localStorage.getItem('thaniyam_cart');
        return cart ? JSON.parse(cart) : [];
    },
    
    addItem: function(product) {
        const cart = this.getItems();
        const existing = cart.find(item => item.id === product.id);
        
        if (existing) {
            existing.cartQuantity += 1;
        } else {
            cart.push({ ...product, cartQuantity: 1 });
        }
        
        localStorage.setItem('thaniyam_cart', JSON.stringify(cart));
        this.updateCartUI();
        showToast(`${product.name} added to cart!`);
    },
    
    removeItem: function(productId) {
        let cart = this.getItems();
        cart = cart.filter(item => item.id !== productId);
        localStorage.setItem('thaniyam_cart', JSON.stringify(cart));
        this.updateCartUI();
    },

    updateQuantity: function(productId, quantity) {
        let cart = this.getItems();
        const item = cart.find(i => i.id === productId);
        if (item) {
            item.cartQuantity = parseInt(quantity);
            if(item.cartQuantity <= 0) {
                this.removeItem(productId);
                return;
            }
        }
        localStorage.setItem('thaniyam_cart', JSON.stringify(cart));
        this.updateCartUI();
    },
    
    clear: function() {
        localStorage.removeItem('thaniyam_cart');
        this.updateCartUI();
    },

    getTotal: function() {
        const cart = this.getItems();
        return cart.reduce((sum, item) => sum + (item.price * item.cartQuantity), 0);
    },
    
    updateCartUI: function() {
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            const count = this.getItems().reduce((sum, item) => sum + item.cartQuantity, 0);
            cartCount.textContent = count;
            if (count > 0) {
                cartCount.style.display = 'inline-flex';
            } else {
                cartCount.style.display = 'none';
            }
        }
        
        // Custom event so that cart.html can update its UI if open
        window.dispatchEvent(new Event('cartUpdated'));
    }
};

// Global Auth System
const Auth = {
    getUser: function() {
        const user = localStorage.getItem('thaniyam_user');
        return user ? JSON.parse(user) : null;
    },
    
    login: function(user) {
        localStorage.setItem('thaniyam_user', JSON.stringify(user));
        this.updateAuthUI();
        showToast('Logged in successfully!');
    },
    
    logout: function() {
        localStorage.removeItem('thaniyam_user');
        this.updateAuthUI();
        showToast('Logged out successfully!');
        if(window.location.pathname.includes('cart.html') || window.location.pathname.includes('login.html')) {
            window.location.href = 'index.html';
        }
    },
    
    updateAuthUI: function() {
        const user = this.getUser();
        const authLinks = document.getElementById('auth-links');
        
        if (authLinks) {
            if (user) {
                authLinks.innerHTML = `
                    <span style="font-weight: 600; margin-right: 15px;">Hi, ${user.fullname}</span>
                    <a href="#" onclick="Auth.logout(); return false;" style="color: var(--error);">Logout</a>
                `;
            } else {
                authLinks.innerHTML = `
                    <a href="login.html?mode=login">Login</a>
                    <a href="login.html?mode=register" class="btn btn-primary" style="color: white; padding: 0.5rem 1rem;">Sign Up</a>
                `;
            }
        }
    }
};

// Initialize app UI on load
document.addEventListener('DOMContentLoaded', () => {
    Cart.updateCartUI();
    Auth.updateAuthUI();
});
