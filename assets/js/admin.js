// Admin Dashboard JavaScript

class AdminDashboard {
    constructor() {
        this.init();
        this.bindEvents();
        this.loadCharts();
        this.initDashboard();
    }

    init() {
        // Initialize tooltips and popovers
        this.initTooltips();
        
        // Initialize data tables
        this.initDataTables();
        
        // Initialize sidebar
        this.initSidebar();
        
        // Initialize modals
        this.initModals();
        
        // Initialize forms
        this.initForms();
        
        // Initialize notifications
        this.initNotifications();
    }

    bindEvents() {
        // Sidebar toggle
        document.addEventListener('click', (e) => {
            if (e.target.closest('#sidebarToggle, .sidebar-toggle')) {
                this.toggleSidebar();
            }
        });

        // Mobile menu toggle
        document.addEventListener('click', (e) => {
            if (e.target.closest('.mobile-menu-toggle')) {
                this.toggleMobileMenu();
            }
        });

        // Theme toggle
        document.addEventListener('click', (e) => {
            if (e.target.closest('#themeToggle')) {
                this.toggleTheme();
            }
        });

        // Search functionality
        const searchInput = document.querySelector('#adminSearch');
        if (searchInput) {
            searchInput.addEventListener('input', this.handleSearch.bind(this));
        }

        // Quick actions
        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.addEventListener('click', this.handleQuickAction.bind(this));
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            const sidebar = document.querySelector('.admin-sidebar');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 768 && 
                sidebar && 
                sidebar.classList.contains('show') && 
                !sidebar.contains(e.target) && 
                !toggleBtn.contains(e.target)) {
                this.closeMobileMenu();
            }
        });

        // Status change buttons
        document.querySelectorAll('.status-btn').forEach(btn => {
            btn.addEventListener('click', this.handleStatusChange.bind(this));
        });

        // Delete buttons
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', this.handleDelete.bind(this));
        });

        // Bulk actions
        document.querySelectorAll('.bulk-action-select').forEach(checkbox => {
            checkbox.addEventListener('change', this.handleBulkSelect.bind(this));
        });

        // Image upload
        document.querySelectorAll('.image-upload-input').forEach(input => {
            input.addEventListener('change', this.handleImageUpload.bind(this));
        });

        // Form submissions
        document.querySelectorAll('.admin-form').forEach(form => {
            form.addEventListener('submit', this.handleFormSubmit.bind(this));
        });

        // Search functionality
        const adminSearchInput = document.querySelector('#adminSearch');
        if (adminSearchInput) {
            adminSearchInput.addEventListener('input', this.debounce(this.handleAdminSearch.bind(this), 300));
        }

        // Filter dropdowns
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', this.handleFilterChange.bind(this));
        });

        // Export buttons
        document.querySelectorAll('.export-btn').forEach(btn => {
            btn.addEventListener('click', this.handleExport.bind(this));
        });

        // Theme toggle
        const themeToggle = document.querySelector('#themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', this.toggleTheme.bind(this));
        }
    }

    initTooltips() {
        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    initDataTables() {
        // Initialize DataTables if available
        if (typeof DataTable !== 'undefined') {
            document.querySelectorAll('.data-table').forEach(table => {
                new DataTable(table, {
                    responsive: true,
                    pageLength: 25,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
                    },
                    dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
                    order: [[0, 'desc']]
                });
            });
        }
    }

    initSidebar() {
        // Handle sidebar navigation
        const sidebarLinks = document.querySelectorAll('.sidebar-nav .nav-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // Remove active class from all links
                sidebarLinks.forEach(l => l.classList.remove('active'));
                // Add active class to clicked link
                e.target.classList.add('active');
            });
        });

        // Collapse/expand sidebar items
        document.querySelectorAll('.sidebar-submenu-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const submenu = toggle.nextElementSibling;
                if (submenu) {
                    submenu.classList.toggle('show');
                    toggle.querySelector('.fa-chevron-down').classList.toggle('rotate');
                }
            });
        });
    }

    initModals() {
        // Auto-focus first input in modals
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', function () {
                const firstInput = this.querySelector('input:not([type="hidden"]), select, textarea');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        });
    }

    initForms() {
        // Initialize form validation
        document.querySelectorAll('.needs-validation').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });

        // Rich text editors
        this.initRichTextEditors();
    }

    initRichTextEditors() {
        // Initialize TinyMCE or other rich text editors
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '.rich-text-editor',
                height: 300,
                menubar: false,
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
            });
        }
    }

    initNotifications() {
        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.classList.add('fade-out');
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });
    }

    initDashboard() {
        // Load dashboard statistics
        this.loadDashboardStats();
        
        // Auto-refresh dashboard every 5 minutes
        setInterval(() => {
            this.refreshDashboardStats();
        }, 300000);
    }

    toggleSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const mainContent = document.querySelector('.admin-main-content');
        
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }

    toggleMobileMenu() {
        const sidebar = document.querySelector('.admin-sidebar');
        sidebar.classList.toggle('mobile-show');
    }

    handleQuickAction(event) {
        const btn = event.currentTarget;
        const action = btn.dataset.action;
        const id = btn.dataset.id;
        
        switch (action) {
            case 'edit':
                this.openEditModal(id);
                break;
            case 'view':
                this.openViewModal(id);
                break;
            case 'duplicate':
                this.duplicateItem(id);
                break;
            default:
                console.log('Unknown action:', action);
        }
    }

    handleStatusChange(event) {
        const btn = event.currentTarget;
        const id = btn.dataset.id;
        const type = btn.dataset.type;
        const newStatus = btn.dataset.status;
        
        if (confirm('Bạn có chắc muốn thay đổi trạng thái?')) {
            this.updateStatus(id, type, newStatus);
        }
    }

    handleDelete(event) {
        event.preventDefault();
        const btn = event.currentTarget;
        const id = btn.dataset.id;
        const type = btn.dataset.type;
        const name = btn.dataset.name || 'mục này';
        
        if (confirm(`Bạn có chắc muốn xóa ${name}? Hành động này không thể hoàn tác.`)) {
            this.deleteItem(id, type);
        }
    }

    handleBulkSelect(event) {
        const checkbox = event.currentTarget;
        const table = checkbox.closest('table');
        
        if (checkbox.id === 'selectAll') {
            // Select/deselect all checkboxes
            const allCheckboxes = table.querySelectorAll('.bulk-action-select:not(#selectAll)');
            allCheckboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
        }
        
        this.updateBulkActionButtons();
    }

    handleImageUpload(event) {
        const input = event.currentTarget;
        const file = input.files[0];
        
        if (file) {
            // Validate file type and size
            if (!file.type.startsWith('image/')) {
                this.showAlert('Vui lòng chọn file hình ảnh!', 'danger');
                input.value = '';
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) { // 5MB
                this.showAlert('File quá lớn! Vui lòng chọn file nhỏ hơn 5MB.', 'danger');
                input.value = '';
                return;
            }
            
            // Preview image
            this.previewImage(input, file);
        }
    }

    handleFormSubmit(event) {
        const form = event.currentTarget;
        const submitBtn = form.querySelector('button[type="submit"]');
        
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }
        
        // Add loading overlay to form
        this.showFormLoading(form);
    }

    handleAdminSearch(event) {
        const query = event.target.value.trim();
        const searchType = event.target.dataset.searchType || 'global';
        
        if (query.length >= 2) {
            this.performAdminSearch(query, searchType);
        } else {
            this.clearSearchResults();
        }
    }

    handleFilterChange(event) {
        const select = event.currentTarget;
        const filterType = select.dataset.filter;
        const value = select.value;
        
        this.applyFilter(filterType, value);
    }

    handleExport(event) {
        const btn = event.currentTarget;
        const exportType = btn.dataset.exportType;
        const dataType = btn.dataset.dataType;
        
        btn.classList.add('loading');
        btn.disabled = true;
        
        this.exportData(exportType, dataType).finally(() => {
            btn.classList.remove('loading');
            btn.disabled = false;
        });
    }

    loadCharts() {
        // Load Chart.js charts if available
        if (typeof Chart !== 'undefined') {
            this.loadSalesChart();
            this.loadOrdersChart();
            this.loadCategoryChart();
        }
    }

    loadSalesChart() {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                datasets: [{
                    label: 'Doanh thu',
                    data: [12000000, 19000000, 15000000, 25000000, 22000000, 30000000],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                            }
                        }
                    }
                }
            }
        });
    }

    loadOrdersChart() {
        const ctx = document.getElementById('ordersChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'CN'],
                datasets: [{
                    label: 'Đơn hàng',
                    data: [12, 19, 15, 25, 22, 30, 18],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(201, 203, 207, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    loadCategoryChart() {
        const ctx = document.getElementById('categoryChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['iPhone', 'Samsung', 'Oppo', 'Vivo', 'Xiaomi'],
                datasets: [{
                    data: [30, 25, 20, 15, 10],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    loadDashboardStats() {
        // This would typically fetch real-time stats from the server
        this.updateStatCard('totalRevenue', '₫125,450,000');
        this.updateStatCard('totalOrders', '1,247');
        this.updateStatCard('totalProducts', '456');
        this.updateStatCard('totalUsers', '2,891');
    }

    refreshDashboardStats() {
        // Refresh dashboard statistics
        this.loadDashboardStats();
        this.showNotification('Đã cập nhật thống kê mới nhất', 'info');
    }

    updateStatCard(cardId, value) {
        const card = document.querySelector(`[data-stat="${cardId}"]`);
        if (card) {
            const valueElement = card.querySelector('.stat-value');
            if (valueElement) {
                valueElement.textContent = value;
                // Add animation
                valueElement.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    valueElement.style.transform = 'scale(1)';
                }, 200);
            }
        }
    }

    openEditModal(id) {
        // Open edit modal for specific item
        const modal = document.querySelector('#editModal');
        if (modal) {
            // Load item data via AJAX
            this.loadItemData(id).then(data => {
                this.populateEditForm(data);
                new bootstrap.Modal(modal).show();
            });
        }
    }

    openViewModal(id) {
        // Open view modal for specific item
        const modal = document.querySelector('#viewModal');
        if (modal) {
            this.loadItemData(id).then(data => {
                this.populateViewModal(data);
                new bootstrap.Modal(modal).show();
            });
        }
    }

    updateStatus(id, type, status) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('type', type);
        formData.append('status', status);
        formData.append('action', 'update_status');
        
        fetch('/admin/ajax/update-status', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showAlert('Đã cập nhật trạng thái!', 'success');
                location.reload();
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra!', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showAlert('Có lỗi xảy ra!', 'danger');
        });
    }

    deleteItem(id, type) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('type', type);
        formData.append('action', 'delete');
        
        fetch('/admin/ajax/delete-item', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showAlert('Đã xóa thành công!', 'success');
                location.reload();
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra!', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showAlert('Có lỗi xảy ra!', 'danger');
        });
    }

    updateBulkActionButtons() {
        const checkedBoxes = document.querySelectorAll('.bulk-action-select:checked:not(#selectAll)');
        const bulkActionBar = document.querySelector('.bulk-action-bar');
        
        if (checkedBoxes.length > 0) {
            bulkActionBar.classList.add('show');
            bulkActionBar.querySelector('.selected-count').textContent = checkedBoxes.length;
        } else {
            bulkActionBar.classList.remove('show');
        }
    }

    previewImage(input, file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = input.parentElement.querySelector('.image-preview');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }

    showFormLoading(form) {
        const overlay = document.createElement('div');
        overlay.className = 'form-loading-overlay';
        overlay.innerHTML = '<div class="spinner-border text-primary"></div>';
        form.style.position = 'relative';
        form.appendChild(overlay);
    }

    performAdminSearch(query, type) {
        // Perform admin search via AJAX
        console.log('Admin searching for:', query, 'type:', type);
    }

    clearSearchResults() {
        const resultsContainer = document.querySelector('#adminSearchResults');
        if (resultsContainer) {
            resultsContainer.innerHTML = '';
        }
    }

    applyFilter(filterType, value) {
        // Apply filter to current view
        const url = new URL(window.location);
        if (value) {
            url.searchParams.set(filterType, value);
        } else {
            url.searchParams.delete(filterType);
        }
        window.location.href = url.toString();
    }

    async exportData(exportType, dataType) {
        const response = await fetch(`/admin/export/${dataType}?format=${exportType}`);
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${dataType}_export.${exportType}`;
            a.click();
            window.URL.revokeObjectURL(url);
        }
    }

    async loadItemData(id) {
        const response = await fetch(`/admin/ajax/get-item/${id}`);
        return await response.json();
    }

    populateEditForm(data) {
        // Populate edit form with data
        Object.keys(data).forEach(key => {
            const input = document.querySelector(`#editModal [name="${key}"]`);
            if (input) {
                input.value = data[key];
            }
        });
    }

    populateViewModal(data) {
        // Populate view modal with data
        Object.keys(data).forEach(key => {
            const element = document.querySelector(`#viewModal [data-field="${key}"]`);
            if (element) {
                element.textContent = data[key];
            }
        });
    }

    toggleTheme() {
        const body = document.body;
        body.classList.toggle('dark-theme');
        
        // Save theme preference
        localStorage.setItem('adminTheme', body.classList.contains('dark-theme') ? 'dark' : 'light');
    }

    showAlert(message, type = 'info') {
        const alertContainer = document.querySelector('.alert-container') || document.body;
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertContainer.appendChild(alert);
        
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, 5000);
    }

    showNotification(message, type = 'info') {
        this.showAlert(message, type);
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

    // Format currency
    static formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    // Format date
    static formatDate(date) {
        return new Intl.DateTimeFormat('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    }

    // New methods for updated admin layout
    toggleTheme() {
        document.body.classList.toggle('dark-theme');
        const isDark = document.body.classList.contains('dark-theme');
        localStorage.setItem('adminTheme', isDark ? 'dark' : 'light');
        
        // Update theme icon
        const themeBtn = document.querySelector('#themeToggle i');
        if (themeBtn) {
            themeBtn.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        }
    }

    handleSearch(event) {
        const query = event.target.value.toLowerCase();
        if (query.length === 0) {
            this.clearSearchResults();
            return;
        }

        // Debounce search
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.performSearch(query);
        }, 300);
    }

    performSearch(query) {
        // Search in tables
        const tables = document.querySelectorAll('table tbody');
        tables.forEach(tbody => {
            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });

        // Search in cards
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(query) ? '' : 'none';
        });
    }

    clearSearchResults() {
        // Show all hidden elements
        document.querySelectorAll('[style*="display: none"]').forEach(el => {
            el.style.display = '';
        });
    }

    closeMobileMenu() {
        const sidebar = document.querySelector('.admin-sidebar');
        if (sidebar) {
            sidebar.classList.remove('show');
        }
    }
}

// Initialize admin dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.adminDashboard = new AdminDashboard();
    
    // Load saved sidebar state
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        document.querySelector('.admin-sidebar').classList.add('collapsed');
        document.querySelector('.admin-main-content').classList.add('expanded');
    }
    
    // Load saved theme
    const savedTheme = localStorage.getItem('adminTheme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminDashboard;
}

document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('images');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            previewImages(e.target.files);
        });
    }
});

function previewImages(files) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    Array.from(files).forEach(file => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-thumbnail me-2 mb-2';
                img.style.width = '100px';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
}

