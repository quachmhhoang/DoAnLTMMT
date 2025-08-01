// Modern Admin Dashboard JavaScript
// ===============================

class ModernAdminDashboard {
    constructor() {
        this.init();
        this.bindEvents();
        this.loadComponents();
    }

    init() {
        // Initialize tooltips
        this.initTooltips();
        
        // Initialize sidebar
        this.initSidebar();
        
        // Initialize data tables
        this.initDataTables();
        
        // Initialize theme toggle
        this.initThemeToggle();
        
        // Initialize search
        this.initSearch();
        
        // Initialize animations
        this.initAnimations();
    }

    initTooltips() {
        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    initSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        const mainContent = document.querySelector('.admin-main-content');

        // Mobile sidebar toggle
        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                document.body.classList.toggle('sidebar-open');
            });
        }

        // Desktop sidebar toggle
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                }
            }
        });

        // Active nav item highlighting
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href === currentPath || (href !== '/admin' && currentPath.startsWith(href))) {
                link.classList.add('active');
            }
        });
    }

    initDataTables() {
        // Initialize DataTables for admin tables
        if (typeof DataTable !== 'undefined') {
            const tables = document.querySelectorAll('.admin-table');
            tables.forEach(table => {
                new DataTable(table, {
                    responsive: true,
                    pageLength: 25,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
                    },
                    columnDefs: [
                        { orderable: false, targets: 'no-sort' }
                    ]
                });
            });
        }
    }

    initThemeToggle() {
        const themeToggle = document.querySelector('#themeToggle');
        if (!themeToggle) return;

        // Get saved theme or default to light
        const savedTheme = localStorage.getItem('adminTheme') || 'light';
        this.setTheme(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = document.body.classList.contains('dark-theme') ? 'dark' : 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            this.setTheme(newTheme);
            localStorage.setItem('adminTheme', newTheme);
        });
    }

    setTheme(theme) {
        const body = document.body;
        const themeIcon = document.querySelector('#themeToggle i');
        
        if (theme === 'dark') {
            body.classList.add('dark-theme');
            if (themeIcon) themeIcon.className = 'fas fa-sun';
        } else {
            body.classList.remove('dark-theme');
            if (themeIcon) themeIcon.className = 'fas fa-moon';
        }
    }

    initSearch() {
        const searchInput = document.querySelector('#adminSearch');
        if (!searchInput) return;

        searchInput.addEventListener('input', this.debounce((e) => {
            const query = e.target.value.toLowerCase();
            this.performSearch(query);
        }, 300));
    }

    performSearch(query) {
        // Implement search functionality
        if (query.length < 2) return;

        // Search in current page content
        const searchableElements = document.querySelectorAll('[data-searchable]');
        searchableElements.forEach(element => {
            const text = element.textContent.toLowerCase();
            const isMatch = text.includes(query);
            
            element.style.display = isMatch ? '' : 'none';
            
            // Highlight matching text
            if (isMatch && query.length > 0) {
                this.highlightText(element, query);
            }
        });
    }

    highlightText(element, query) {
        const text = element.textContent;
        const regex = new RegExp(`(${query})`, 'gi');
        const highlightedText = text.replace(regex, '<mark>$1</mark>');
        element.innerHTML = highlightedText;
    }

    initAnimations() {
        // Animate stat cards on load
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('animate-fade-in-up');
        });

        // Animate dashboard cards
        const dashboardCards = document.querySelectorAll('.dashboard-card');
        dashboardCards.forEach((card, index) => {
            card.style.animationDelay = `${0.5 + index * 0.2}s`;
            card.classList.add('animate-fade-in-up');
        });

        // Counter animation for stat numbers
        this.animateCounters();
    }

    animateCounters() {
        const counters = document.querySelectorAll('.stat-number');
        
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        counters.forEach(counter => observer.observe(counter));
    }

    animateCounter(element) {
        const text = element.textContent;
        const number = parseInt(text.replace(/[^\d]/g, ''));
        const suffix = text.replace(/[\d,\.]/g, '');
        const duration = 2000;
        const step = number / (duration / 16);

        let current = 0;
        const timer = setInterval(() => {
            current += step;
            if (current >= number) {
                current = number;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current).toLocaleString('vi-VN') + suffix;
        }, 16);
    }

    bindEvents() {
        // Form submissions
        this.bindFormEvents();
        
        // Modal events
        this.bindModalEvents();
        
        // Button actions
        this.bindButtonEvents();
        
        // Notification events
        this.bindNotificationEvents();
    }

    bindFormEvents() {
        const forms = document.querySelectorAll('.admin-form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                this.handleFormSubmit(e, form);
            });
        });

        // File upload handling
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.handleFileUpload(e);
            });
        });
    }

    handleFormSubmit(e, form) {
        // Add loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> Đang xử lý...';
        }

        // Validate form
        if (!this.validateForm(form)) {
            e.preventDefault();
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtn.dataset.originalText || 'Lưu';
            }
        }
    }

    validateForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'Trường này là bắt buộc');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });

        return isValid;
    }

    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        let errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            field.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }

    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    handleFileUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Create preview for images
        if (file.type.startsWith('image/')) {
            this.createImagePreview(e.target, file);
        }

        // Show file info
        this.showFileInfo(e.target, file);
    }

    createImagePreview(input, file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            let preview = input.parentNode.querySelector('.image-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.className = 'image-preview mt-2';
                input.parentNode.appendChild(preview);
            }
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="this.parentNode.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
        };
        reader.readAsDataURL(file);
    }

    showFileInfo(input, file) {
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        let infoDiv = input.parentNode.querySelector('.file-info');
        
        if (!infoDiv) {
            infoDiv = document.createElement('div');
            infoDiv.className = 'file-info mt-1 text-muted small';
            input.parentNode.appendChild(infoDiv);
        }
        
        infoDiv.textContent = `${file.name} (${fileSize} MB)`;
    }

    bindModalEvents() {
        // Auto-focus first input in modals
        document.addEventListener('shown.bs.modal', (e) => {
            const firstInput = e.target.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });
    }

    bindButtonEvents() {
        // Delete confirmation
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-delete') || e.target.closest('.btn-delete')) {
                e.preventDefault();
                this.showDeleteConfirmation(e.target);
            }
        });

        // Copy to clipboard
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-copy') || e.target.closest('.btn-copy')) {
                e.preventDefault();
                this.copyToClipboard(e.target);
            }
        });
    }

    showDeleteConfirmation(button) {
        const href = button.getAttribute('href') || button.dataset.href;
        const itemName = button.dataset.name || 'item này';
        
        if (confirm(`Bạn có chắc chắn muốn xóa ${itemName}? Hành động này không thể hoàn tác.`)) {
            if (href) {
                window.location.href = href;
            }
        }
    }

    copyToClipboard(button) {
        const text = button.dataset.copy;
        if (!text) return;

        navigator.clipboard.writeText(text).then(() => {
            this.showNotification('Đã sao chép vào clipboard', 'success');
        }).catch(() => {
            this.showNotification('Không thể sao chép', 'error');
        });
    }

    bindNotificationEvents() {
        // Auto-dismiss alerts
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });
    }

    loadComponents() {
        // Load dashboard charts if available
        if (typeof Chart !== 'undefined') {
            this.loadCharts();
        }

        // Load real-time updates
        this.initRealTimeUpdates();
    }

    loadCharts() {
        // Mini charts for stat cards
        const chartElements = document.querySelectorAll('.mini-chart');
        chartElements.forEach(element => {
            this.createMiniChart(element);
        });
    }

    createMiniChart(element) {
        const type = element.dataset.type;
        const canvas = document.createElement('canvas');
        canvas.width = 100;
        canvas.height = 30;
        element.appendChild(canvas);

        // Sample data based on type
        const data = this.generateSampleData(type);
        
        new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    borderColor: this.getChartColor(type),
                    backgroundColor: this.getChartColor(type, 0.1),
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false },
                    y: { display: false }
                },
                elements: { point: { radius: 0 } }
            }
        });
    }

    generateSampleData(type) {
        const baseValues = {
            products: [20, 25, 22, 28, 30, 26, 32],
            orders: [5, 12, 8, 15, 18, 14, 20],
            users: [100, 105, 110, 108, 115, 120, 125],
            revenue: [1000, 1200, 1100, 1400, 1600, 1300, 1800]
        };

        return {
            labels: ['', '', '', '', '', '', ''],
            values: baseValues[type] || [0, 0, 0, 0, 0, 0, 0]
        };
    }

    getChartColor(type, alpha = 1) {
        const colors = {
            products: `rgba(59, 130, 246, ${alpha})`,
            orders: `rgba(16, 185, 129, ${alpha})`,
            users: `rgba(245, 158, 11, ${alpha})`,
            revenue: `rgba(139, 92, 246, ${alpha})`
        };
        return colors[type] || `rgba(107, 114, 128, ${alpha})`;
    }

    initRealTimeUpdates() {
        // Update dashboard stats every 30 seconds
        setInterval(() => {
            this.updateDashboardStats();
        }, 30000);
    }

    updateDashboardStats() {
        // Fetch updated stats via AJAX
        fetch('/admin/api/stats')
            .then(response => response.json())
            .then(data => {
                this.updateStatCards(data);
            })
            .catch(error => {
                console.log('Stats update failed:', error);
            });
    }

    updateStatCards(data) {
        Object.keys(data).forEach(key => {
            const statNumber = document.querySelector(`.stat-card.${key}-card .stat-number`);
            if (statNumber) {
                statNumber.textContent = this.formatNumber(data[key]);
            }
        });
    }

    formatNumber(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show notification-toast`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Add to notification container or body
        const container = document.querySelector('.notification-container') || document.body;
        container.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
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
}

// CSS Animation classes
const animationCSS = `
    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .notification-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
    }
`;

// Inject animation CSS
const style = document.createElement('style');
style.textContent = animationCSS;
document.head.appendChild(style);

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.adminDashboard = new ModernAdminDashboard();
});

// Export for global access
window.ModernAdminDashboard = ModernAdminDashboard;
