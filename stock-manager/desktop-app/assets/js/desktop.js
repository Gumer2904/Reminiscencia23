// Desktop App JavaScript
const { ipcRenderer } = require('electron');

class StockManagerDesktop {
    constructor() {
        this.currentPage = 'dashboard';
        this.sidebarOpen = true;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupChart();
        this.loadSettings();
        this.setupAutoSync();
    }

    setupEventListeners() {
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => this.toggleSidebar());
        }

        // Navigation
        document.querySelectorAll('.sidebar-menu .nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.dataset.page;
                this.navigateToPage(page);
            });
        });

        // Top bar actions
        const addProductBtn = document.getElementById('addProductBtn');
        if (addProductBtn) {
            addProductBtn.addEventListener('click', () => this.showAddProductModal());
        }

        const syncBtn = document.getElementById('syncBtn');
        if (syncBtn) {
            syncBtn.addEventListener('click', () => this.syncData());
        }

        const notificationBtn = document.getElementById('notificationBtn');
        if (notificationBtn) {
            notificationBtn.addEventListener('click', () => this.showNotifications());
        }

        // IPC listeners
        ipcRenderer.on('menu-new-product', () => this.showAddProductModal());
        ipcRenderer.on('menu-import-data', () => this.importData());
        ipcRenderer.on('menu-export-data', () => this.exportData());
        ipcRenderer.on('menu-backup', () => this.createBackup());
        ipcRenderer.on('menu-navigate', (event, page) => this.navigateToPage(page));
        ipcRenderer.on('menu-generate-pdf', () => this.generatePDFReport());
        ipcRenderer.on('menu-export-excel', () => this.exportToExcel());
        ipcRenderer.on('menu-clear-cache', () => this.clearCache());

        // Search functionality
        const searchInput = document.querySelector('.search-box input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => this.handleKeyboardShortcuts(e));
    }

    toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
            this.sidebarOpen = !this.sidebarOpen;
            this.saveSettings();
        }
    }

    navigateToPage(page) {
        // Hide all pages
        document.querySelectorAll('.page').forEach(p => p.classList.add('d-none'));
        
        // Show selected page
        const targetPage = document.getElementById(`${page}Page`);
        if (targetPage) {
            targetPage.classList.remove('d-none');
        }

        // Update navigation
        document.querySelectorAll('.sidebar-menu .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        const activeLink = document.querySelector(`[data-page="${page}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }

        // Update page title
        const pageTitle = document.getElementById('pageTitle');
        if (pageTitle) {
            pageTitle.textContent = this.getPageTitle(page);
        }

        this.currentPage = page;
        this.loadPageData(page);
    }

    getPageTitle(page) {
        const titles = {
            dashboard: 'Dashboard',
            inventory: 'Inventario',
            sales: 'Ventas',
            reports: 'Reportes',
            suppliers: 'Proveedores',
            settings: 'Configuración'
        };
        return titles[page] || 'Stock Manager';
    }

    loadPageData(page) {
        switch (page) {
            case 'dashboard':
                this.loadDashboardData();
                break;
            case 'inventory':
                this.loadInventoryData();
                break;
            case 'sales':
                this.loadSalesData();
                break;
            case 'reports':
                this.loadReportsData();
                break;
            case 'suppliers':
                this.loadSuppliersData();
                break;
            case 'settings':
                this.loadSettingsData();
                break;
        }
    }

    async loadDashboardData() {
        try {
            // Simulate API calls
            const stats = await this.getDashboardStats();
            const recentSales = await this.getRecentSales();
            const lowStockProducts = await this.getLowStockProducts();

            this.updateDashboardStats(stats);
            this.updateRecentSales(recentSales);
            this.updateLowStockProducts(lowStockProducts);
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            this.showNotification('Error al cargar datos del dashboard', 'error');
        }
    }

    async getDashboardStats() {
        // Simulate API call
        return new Promise(resolve => {
            setTimeout(() => {
                resolve({
                    totalProducts: 2847,
                    inventoryValue: 45678.90,
                    lowStock: 23,
                    todaySales: 12345.67
                });
            }, 500);
        });
    }

    async getRecentSales() {
        return new Promise(resolve => {
            setTimeout(() => {
                resolve([
                    { product: 'Laptop Dell XPS', time: 'Hace 2 horas', amount: 1299.99 },
                    { product: 'Mouse Logitech', time: 'Hace 3 horas', amount: 29.99 },
                    { product: 'Teclado Mecánico', time: 'Hace 5 horas', amount: 89.99 }
                ]);
            }, 500);
        });
    }

    async getLowStockProducts() {
        return new Promise(resolve => {
            setTimeout(() => {
                resolve([
                    { name: 'Mouse Logitech', current: 3, min: 10, status: 'critical' },
                    { name: 'USB 32GB', current: 18, min: 20, status: 'warning' }
                ]);
            }, 500);
        });
    }

    updateDashboardStats(stats) {
        const statCards = document.querySelectorAll('.stat-value');
        if (statCards.length >= 4) {
            statCards[0].textContent = stats.totalProducts.toLocaleString();
            statCards[1].textContent = `$${stats.inventoryValue.toLocaleString()}`;
            statCards[2].textContent = stats.lowStock;
            statCards[3].textContent = `$${stats.todaySales.toLocaleString()}`;
        }
    }

    updateRecentSales(sales) {
        const salesList = document.querySelector('.sales-list');
        if (salesList) {
            salesList.innerHTML = sales.map(sale => `
                <div class="sale-item">
                    <div class="sale-info">
                        <div class="sale-product">${sale.product}</div>
                        <div class="sale-time">${sale.time}</div>
                    </div>
                    <div class="sale-amount">$${sale.amount.toFixed(2)}</div>
                </div>
            `).join('');
        }
    }

    updateLowStockProducts(products) {
        const lowStockList = document.querySelector('.low-stock-list');
        if (lowStockList) {
            lowStockList.innerHTML = products.map(product => `
                <div class="low-stock-item">
                    <div class="product-info">
                        <div class="product-name">${product.name}</div>
                        <div class="stock-info">
                            <span class="current-stock">${product.current}</span>
                            <span class="min-stock">/ ${product.min}</span>
                        </div>
                    </div>
                    <div class="stock-status ${product.status}">
                        <i class="bi bi-${product.status === 'critical' ? 'exclamation-circle' : 'exclamation-triangle'}"></i>
                    </div>
                </div>
            `).join('');
        }
    }

    setupChart() {
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            const salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                    datasets: [{
                        label: 'Ventas',
                        data: [1200, 1900, 1500, 2500, 2200, 3000, 2800],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Store chart reference
            this.salesChart = salesChart;
        }
    }

    async syncData() {
        const syncBtn = document.getElementById('syncBtn');
        if (syncBtn) {
            syncBtn.disabled = true;
            syncBtn.innerHTML = '<i class="bi bi-arrow-clockwise rotating"></i>';
        }

        try {
            // Simulate sync process
            await new Promise(resolve => setTimeout(resolve, 2000));
            this.showNotification('Datos sincronizados correctamente', 'success');
            this.loadPageData(this.currentPage);
        } catch (error) {
            this.showNotification('Error al sincronizar datos', 'error');
        } finally {
            if (syncBtn) {
                syncBtn.disabled = false;
                syncBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
            }
        }
    }

    showAddProductModal() {
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Agregar Nuevo Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addProductForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre del Producto</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SKU</label>
                                    <input type="text" class="form-control" name="sku" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select" name="category" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="electronics">Electrónica</option>
                                        <option value="accessories">Accesorios</option>
                                        <option value="storage">Almacenamiento</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Precio</label>
                                    <input type="number" class="form-control" name="price" step="0.01" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stock Actual</label>
                                    <input type="number" class="form-control" name="stock" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stock Mínimo</label>
                                    <input type="number" class="form-control" name="minStock" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="saveProductBtn">Guardar Producto</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();

        // Handle form submission
        const saveBtn = document.getElementById('saveProductBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', async () => {
                const formData = new FormData(document.getElementById('addProductForm'));
                const productData = Object.fromEntries(formData);
                
                try {
                    await this.saveProduct(productData);
                    bootstrapModal.hide();
                    this.showNotification('Producto agregado correctamente', 'success');
                    this.loadPageData('inventory');
                } catch (error) {
                    this.showNotification('Error al agregar producto', 'error');
                }
            });
        }

        // Clean up modal
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }

    async saveProduct(productData) {
        // Simulate API call
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                if (Math.random() > 0.1) {
                    resolve(productData);
                } else {
                    reject(new Error('Error al guardar producto'));
                }
            }, 1000);
        });
    }

    showNotifications() {
        this.showNotification('Tienes 3 notificaciones nuevas', 'info');
    }

    handleSearch(query) {
        // Implement search functionality
        console.log('Searching for:', query);
    }

    handleKeyboardShortcuts(e) {
        // Ctrl/Cmd + N: New Product
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            this.showAddProductModal();
        }
        
        // Ctrl/Cmd + S: Sync
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            this.syncData();
        }
    }

    async importData() {
        try {
            const result = await ipcRenderer.invoke('show-open-dialog', {
                properties: ['openFile'],
                filters: [
                    { name: 'CSV Files', extensions: ['csv'] },
                    { name: 'Excel Files', extensions: ['xlsx', 'xls'] }
                ]
            });

            if (!result.canceled && result.filePaths.length > 0) {
                this.showNotification('Importando datos...', 'info');
                // Implement import logic
                this.showNotification('Datos importados correctamente', 'success');
            }
        } catch (error) {
            this.showNotification('Error al importar datos', 'error');
        }
    }

    async exportData() {
        try {
            const result = await ipcRenderer.invoke('show-save-dialog', {
                defaultPath: 'stock-manager-export.csv',
                filters: [
                    { name: 'CSV Files', extensions: ['csv'] },
                    { name: 'Excel Files', extensions: ['xlsx'] }
                ]
            });

            if (!result.canceled) {
                this.showNotification('Exportando datos...', 'info');
                // Implement export logic
                this.showNotification('Datos exportados correctamente', 'success');
            }
        } catch (error) {
            this.showNotification('Error al exportar datos', 'error');
        }
    }

    async createBackup() {
        try {
            this.showNotification('Creando backup...', 'info');
            // Implement backup logic
            await new Promise(resolve => setTimeout(resolve, 2000));
            this.showNotification('Backup creado correctamente', 'success');
        } catch (error) {
            this.showNotification('Error al crear backup', 'error');
        }
    }

    async generatePDFReport() {
        try {
            this.showNotification('Generando reporte PDF...', 'info');
            // Implement PDF generation
            await new Promise(resolve => setTimeout(resolve, 2000));
            this.showNotification('Reporte PDF generado correctamente', 'success');
        } catch (error) {
            this.showNotification('Error al generar reporte PDF', 'error');
        }
    }

    async exportToExcel() {
        try {
            this.showNotification('Exportando a Excel...', 'info');
            // Implement Excel export
            await new Promise(resolve => setTimeout(resolve, 2000));
            this.showNotification('Datos exportados a Excel correctamente', 'success');
        } catch (error) {
            this.showNotification('Error al exportar a Excel', 'error');
        }
    }

    clearCache() {
        try {
            localStorage.clear();
            sessionStorage.clear();
            this.showNotification('Caché limpiado correctamente', 'success');
        } catch (error) {
            this.showNotification('Error al limpiar caché', 'error');
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    async loadSettings() {
        try {
            const settings = await ipcRenderer.invoke('store-get', 'app-settings');
            if (settings) {
                if (settings.sidebarOpen !== undefined) {
                    this.sidebarOpen = settings.sidebarOpen;
                    if (!this.sidebarOpen) {
                        document.querySelector('.sidebar')?.classList.add('collapsed');
                    }
                }
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }

    saveSettings() {
        ipcRenderer.invoke('store-set', 'app-settings', {
            sidebarOpen: this.sidebarOpen,
            currentPage: this.currentPage
        });
    }

    setupAutoSync() {
        // Auto sync every 5 minutes
        setInterval(() => {
            if (this.currentPage === 'dashboard') {
                this.loadDashboardData();
            }
        }, 5 * 60 * 1000);
    }

    // Placeholder methods for other pages
    async loadInventoryData() {
        console.log('Loading inventory data...');
    }

    async loadSalesData() {
        console.log('Loading sales data...');
    }

    async loadReportsData() {
        console.log('Loading reports data...');
    }

    async loadSuppliersData() {
        console.log('Loading suppliers data...');
    }

    async loadSettingsData() {
        console.log('Loading settings data...');
    }
}

// Add rotating animation for sync button
const style = document.createElement('style');
style.textContent = `
    .rotating {
        animation: rotate 1s linear infinite;
    }
    
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.stockManager = new StockManagerDesktop();
});
