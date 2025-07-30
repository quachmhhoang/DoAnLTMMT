// Modern CellPhone Store JavaScript

class CellPhoneStore {
    constructor() {
        this.init();
        this.bindEvents();
        this.loadAnimations();
    }

    init() {
        // Initialize AOS (Animate On Scroll)
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                easing: 'ease-out-cubic',
                once: true,
                offset: 100
            });
        }

        // Initialize tooltips
        this.initTooltips();
        
        // Initialize modals
        this.initModals();
        
        // Initialize cart functionality
        this.initCart();
        
        // Add loading states
        this.initLoadingStates();
        
        // Initialize search
        this.initSearch();
    }

    bindEvents() {
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add to cart animations
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', this.handleAddToCart.bind(this));
        });

        // Form validations
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', this.handleFormSubmit.bind(this));
        });

        // Quantity controls
        document.querySelectorAll('.quantity-control').forEach(control => {
            control.addEventListener('click', this.handleQuantityChange.bind(this));
        });

        // Search functionality
        const searchInput = document.querySelector('#searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce(this.handleSearch.bind(this), 300));
        }

        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', this.handleFilter.bind(this));
        });

        // Navbar scroll effect
        window.addEventListener('scroll', this.handleNavbarScroll.bind(this));
    }

    initTooltips() {
        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    initModals() {
        // Custom modal animations
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', function () {
                this.querySelector('.modal-content').classList.add('scale-in');
            });
        });
    }

    initCart() {
        this.updateCartCount();
        this.bindCartEvents();
    }

    initLoadingStates() {
        // Add loading states to buttons
        document.querySelectorAll('.btn-loading').forEach(btn => {
            btn.addEventListener('click', function() {
                this.classList.add('loading');
                this.disabled = true;
                
                // Remove loading state after 2 seconds (or when form submits)
                setTimeout(() => {
                    this.classList.remove('loading');
                    this.disabled = false;
                }, 2000);
            });
        });
    }

    initSearch() {
        const searchForm = document.querySelector('#searchForm');
        if (searchForm) {
            searchForm.addEventListener('submit', this.handleSearchSubmit.bind(this));
        }
    }

    handleAddToCart(event) {
        event.preventDefault();
        const btn = event.currentTarget;
        const form = btn.closest('form');
        
        // Add loading state
        btn.classList.add('loading');
        btn.disabled = true;

        // Animate button
        this.animateButton(btn);

        // Submit form via AJAX
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('Đã thêm vào giỏ hàng!', 'success');
                this.updateCartCount();
                this.animateCartIcon();
            } else {
                this.showNotification(data.message || 'Có lỗi xảy ra!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('Có lỗi xảy ra!', 'error');
        })
        .finally(() => {
            btn.classList.remove('loading');
            btn.disabled = false;
        });
    }

    handleFormSubmit(event) {
        const form = event.currentTarget;
        const submitBtn = form.querySelector('button[type="submit"]');
        
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }

        // Validate form
        if (!this.validateForm(form)) {
            event.preventDefault();
            if (submitBtn) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
            return false;
        }
    }

    handleQuantityChange(event) {
        const btn = event.currentTarget;
        const input = btn.parentElement.querySelector('input[type="number"]');
        const action = btn.dataset.action;
        
        let currentValue = parseInt(input.value) || 1;
        
        if (action === 'increase') {
            currentValue++;
        } else if (action === 'decrease' && currentValue > 1) {
            currentValue--;
        }
        
        input.value = currentValue;
        
        // Trigger change event
        input.dispatchEvent(new Event('change'));
    }

    handleSearch(event) {
        const query = event.target.value.trim();
        
        if (query.length >= 2) {
            this.performSearch(query);
        } else {
            this.clearSearchResults();
        }
    }

    handleSearchSubmit(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const query = form.querySelector('input[name="search"]').value.trim();
        
        if (query) {
            window.location.href = `/products?search=${encodeURIComponent(query)}`;
        }
    }

    handleFilter(event) {
        const btn = event.currentTarget;
        const filter = btn.dataset.filter;
        const value = btn.dataset.value;
        
        // Update URL with filter
        const url = new URL(window.location);
        if (value) {
            url.searchParams.set(filter, value);
        } else {
            url.searchParams.delete(filter);
        }
        
        window.location.href = url.toString();
    }

    handleNavbarScroll() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }

    performSearch(query) {
        // This would typically make an AJAX request to search
        console.log('Searching for:', query);
    }

    clearSearchResults() {
        // Clear search results
        const resultsContainer = document.querySelector('#searchResults');
        if (resultsContainer) {
            resultsContainer.innerHTML = '';
        }
    }

    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                this.showFieldError(input, 'Trường này là bắt buộc');
                isValid = false;
            } else {
                this.clearFieldError(input);
            }
        });

        // Email validation
        const emailInputs = form.querySelectorAll('input[type="email"]');
        emailInputs.forEach(input => {
            if (input.value && !this.isValidEmail(input.value)) {
                this.showFieldError(input, 'Email không hợp lệ');
                isValid = false;
            }
        });

        // Password confirmation
        const passwordInput = form.querySelector('input[name="password"]');
        const confirmPasswordInput = form.querySelector('input[name="confirm_password"]');
        
        if (passwordInput && confirmPasswordInput) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                this.showFieldError(confirmPasswordInput, 'Mật khẩu xác nhận không khớp');
                isValid = false;
            }
        }

        return isValid;
    }

    showFieldError(input, message) {
        this.clearFieldError(input);
        
        input.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        input.parentElement.appendChild(errorDiv);
    }

    clearFieldError(input) {
        input.classList.remove('is-invalid');
        const errorDiv = input.parentElement.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    updateCartCount() {
        // This would typically fetch cart count from server
        const cartBadge = document.querySelector('.cart-count');
        if (cartBadge) {
            // Animate count update
            cartBadge.style.transform = 'scale(1.3)';
            setTimeout(() => {
                cartBadge.style.transform = 'scale(1)';
            }, 200);
        }
    }

    bindCartEvents() {
        // Update quantity in cart
        document.querySelectorAll('.cart-quantity-input').forEach(input => {
            input.addEventListener('change', this.updateCartItem.bind(this));
        });

        // Remove from cart
        document.querySelectorAll('.remove-from-cart').forEach(btn => {
            btn.addEventListener('click', this.removeFromCart.bind(this));
        });
    }

    updateCartItem(event) {
        const input = event.currentTarget;
        const quantity = parseInt(input.value);
        const cartDetailId = input.dataset.cartDetailId;
        
        if (quantity > 0) {
            // Update cart item via AJAX
            this.updateCartItemOnServer(cartDetailId, quantity);
        }
    }

    removeFromCart(event) {
        event.preventDefault();
        const btn = event.currentTarget;
        const cartDetailId = btn.dataset.cartDetailId;
        
        if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            this.removeCartItemFromServer(cartDetailId);
        }
    }

    updateCartItemOnServer(cartDetailId, quantity) {
        const formData = new FormData();
        formData.append('cart_detail_id', cartDetailId);
        formData.append('quantity', quantity);
        formData.append('update_quantity', '1');
        
        fetch('/cart/update', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                location.reload(); // Reload to update totals
            }
        })
        .catch(error => {
            console.error('Error updating cart:', error);
        });
    }

    removeCartItemFromServer(cartDetailId) {
        const formData = new FormData();
        formData.append('cart_detail_id', cartDetailId);
        formData.append('remove_item', '1');
        
        fetch('/cart/update', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error removing from cart:', error);
        });
    }

    animateButton(btn) {
        btn.style.transform = 'scale(0.95)';
        setTimeout(() => {
            btn.style.transform = 'scale(1)';
        }, 150);
    }

    animateCartIcon() {
        const cartIcon = document.querySelector('.navbar .fa-shopping-cart');
        if (cartIcon) {
            cartIcon.style.transform = 'scale(1.3)';
            cartIcon.style.color = '#28a745';
            setTimeout(() => {
                cartIcon.style.transform = 'scale(1)';
                cartIcon.style.color = '';
            }, 300);
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show notification-toast`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        `;
        
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    loadAnimations() {
        // Add entrance animations to elements
        const animateElements = document.querySelectorAll('.animate-on-load');
        animateElements.forEach((element, index) => {
            setTimeout(() => {
                element.classList.add('fade-in');
            }, index * 100);
        });
    }

    // Utility function for debouncing
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Price formatting
    static formatPrice(price) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price);
    }

    // Date formatting
    static formatDate(date) {
        return new Intl.DateTimeFormat('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.cellPhoneStore = new CellPhoneStore();
});

// Additional utility functions
window.showLoading = function(element) {
    element.classList.add('loading');
    element.disabled = true;
};

window.hideLoading = function(element) {
    element.classList.remove('loading');
    element.disabled = false;
};

window.showNotification = function(message, type = 'info') {
    if (window.cellPhoneStore) {
        window.cellPhoneStore.showNotification(message, type);
    }
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CellPhoneStore;
}
