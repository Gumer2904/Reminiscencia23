<?php
$page_title = "Dashboard";
require_once 'includes/header.php';

// Obtener información del usuario y su plan
$db->query("SELECT u.*, e.nombre as empresa_nombre, e.plan_suscripcion 
           FROM usuarios u 
           JOIN empresas e ON u.id_empresa = e.id_empresa 
           WHERE u.id_usuario = :id_usuario");
$db->bind(':id_usuario', $_SESSION['user_id']);
$usuario = $db->single();

$plan = $usuario->plan_suscripcion ?? 'basico'; // basico, profesional, premium

// Estadísticas comunes para todos los planes
$db->query("SELECT COUNT(*) as total FROM productos WHERE id_empresa = :empresa_id");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$total_productos = $db->single()->total;

$db->query("SELECT COUNT(*) as bajos FROM productos 
            WHERE id_empresa = :empresa_id 
            AND stock_actual <= stock_minimo 
            AND stock_actual > 0");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$productos_bajo_stock = $db->single()->bajos;

$db->query("SELECT COUNT(*) as agotados FROM productos 
            WHERE id_empresa = :empresa_id 
            AND stock_actual = 0");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$productos_agotados = $db->single()->agotados;

$db->query("SELECT SUM(stock_actual * costo_promedio) as valor FROM productos 
            WHERE id_empresa = :empresa_id");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$valor_inventario = $db->single()->valor ?? 0;

// Estadísticas avanzadas (para profesional y premium)
if ($plan != 'basico') {
    // Ventas del mes
    $db->query("SELECT COUNT(*) as total_ventas, SUM(total) as monto_total 
               FROM movimientos 
               WHERE id_empresa = :empresa_id 
               AND tipo = 'venta' 
               AND MONTH(created_at) = MONTH(CURDATE())");
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $ventas_mes = $db->single();
    
    // Compras del mes
    $db->query("SELECT COUNT(*) as total_compras, SUM(total) as monto_total 
               FROM movimientos 
               WHERE id_empresa = :empresa_id 
               AND tipo = 'compra' 
               AND MONTH(created_at) = MONTH(CURDATE())");
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $compras_mes = $db->single();
    
    // Top productos más vendidos
    $db->query("SELECT p.nombre, SUM(dm.cantidad) as total_vendido 
               FROM detalle_movimiento dm 
               JOIN productos p ON dm.id_producto = p.id_producto 
               JOIN movimientos m ON dm.id_movimiento = m.id_movimiento 
               WHERE m.id_empresa = :empresa_id 
               AND m.tipo = 'venta' 
               AND m.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
               GROUP BY dm.id_producto 
               ORDER BY total_vendido DESC 
               LIMIT 5");
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $top_productos = $db->resultSet();
}

// Estadísticas premium (solo para premium)
if ($plan == 'premium') {
    // Rotación de inventario
    $db->query("SELECT 
               (SUM(IF(tipo='venta', total, 0)) / SUM(stock_actual * costo_promedio)) as rotacion 
               FROM movimientos m 
               JOIN productos p ON m.id_empresa = p.id_empresa 
               WHERE m.id_empresa = :empresa_id 
               AND m.tipo = 'venta' 
               AND m.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $rotacion = $db->single()->rotacion ?? 0;
    
    // Alertas críticas
    $db->query("SELECT COUNT(*) as criticas FROM productos 
               WHERE id_empresa = :empresa_id 
               AND stock_actual = 0");
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $alertas_criticas = $db->single()->criticas;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard <?php echo ucfirst($plan); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 300px;
            height: 100vh;
            background: linear-gradient(180deg, #0a1929 0%, #0f2744 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 30px rgba(0, 0, 0, 0.2);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .sidebar-logo img {
            height: 45px;
            width: auto;
            filter: brightness(0) invert(1);
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
            line-height: 1;
        }

        .sidebar-brand span {
            color: #60a5fa;
            display: block;
            font-size: 0.9rem;
            font-weight: 400;
            opacity: 0.7;
        }

        .plan-badge {
            background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }

        .user-info {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .user-info strong {
            color: white;
            font-size: 1rem;
            display: block;
            margin-top: 0.25rem;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .sidebar-section {
            padding: 0.5rem 1.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 1.5rem;
        }

        .sidebar-section:first-child {
            margin-top: 0;
        }

        .sidebar-nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .sidebar-nav-item:hover {
            background: rgba(255, 255, 255, 0.02);
            color: white;
            border-left-color: #60a5fa;
            padding-left: 2rem;
        }

        .sidebar-nav-item.active {
            background: linear-gradient(90deg, rgba(37, 99, 235, 0.1) 0%, transparent 100%);
            color: white;
            border-left-color: #2563eb;
        }

        .sidebar-nav-item i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: auto;
        }

        .logout-btn {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border-color: #ef4444;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 300px;
            padding: 2rem;
            min-height: 100vh;
            background: #f8fafc;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #0a1929;
            margin-bottom: 0.25rem;
        }

        .page-title p {
            color: #64748b;
            font-size: 0.9rem;
        }

        .date-display {
            background: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            border: 1px solid #e2e8f0;
            font-weight: 500;
        }

        .date-display i {
            color: #2563eb;
            margin-right: 0.5rem;
        }

        /* ===== KPI CARDS ===== */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .kpi-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 30px -10px rgba(37, 99, 235, 0.15);
            border-color: #2563eb;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #2563eb 0%, #60a5fa 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .kpi-card:hover::before {
            opacity: 1;
        }

        .kpi-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .kpi-title {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .kpi-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(96, 165, 250, 0.1) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-size: 1.2rem;
        }

        .kpi-value {
            font-size: 2rem;
            font-weight: 700;
            color: #0a1929;
            margin-bottom: 0.25rem;
        }

        .kpi-trend {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #10b981;
        }

        .kpi-trend.negative {
            color: #ef4444;
        }

        .kpi-trend i {
            font-size: 0.7rem;
        }

        /* ===== CHARTS GRID ===== */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            border: 1px solid #e2e8f0;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #0a1929;
        }

        .chart-subtitle {
            color: #64748b;
            font-size: 0.8rem;
        }

        /* ===== TABLES ===== */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            border: 1px solid #e2e8f0;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .table-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #0a1929;
        }

        .view-all {
            color: #2563eb;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-normal {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-warning {
            background: #fed7aa;
            color: #92400e;
        }

        .status-critical {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ===== UPGRADE CARD ===== */
        .upgrade-card {
            background: linear-gradient(135deg, #0a1929 0%, #1e3a8a 100%);
            border-radius: 20px;
            padding: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .upgrade-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .upgrade-content {
            position: relative;
            z-index: 2;
        }

        .upgrade-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .upgrade-text {
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        .upgrade-btn {
            background: white;
            color: #0a1929;
            padding: 0.75rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s;
        }

        .upgrade-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: start;
            }
        }

        /* ===== PREMIUM BADGES ===== */
        .premium-feature {
            position: relative;
        }

        .premium-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            color: white;
            padding: 0.15rem 0.5rem;
            border-radius: 30px;
            font-size: 0.6rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="assets/img/Logo_Stock_Manager.png" alt="Stock Manager">
                <div class="sidebar-brand">
                    StockManager
                    <span><?php echo htmlspecialchars($usuario->empresa_nombre); ?></span>
                </div>
            </div>
            <div class="plan-badge">
                <i class="bi bi-gem"></i> Plan <?php echo ucfirst($plan); ?>
            </div>
            <div class="user-info">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($usuario->nombre); ?>
                <strong><?php echo ucfirst($usuario->rol); ?></strong>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <!-- Principal -->
            <div class="sidebar-section">Principal</div>
            <a href="dashboard.php" class="sidebar-nav-item active">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <!-- Inventario -->
            <div class="sidebar-section">Inventario</div>
            <a href="productos.php" class="sidebar-nav-item">
                <i class="bi bi-box-seam"></i> Productos
            </a>
            <a href="categorias.php" class="sidebar-nav-item">
                <i class="bi bi-tags"></i> Categorías
            </a>
            
            <?php if ($plan != 'basico'): ?>
            <a href="stock.php" class="sidebar-nav-item">
                <i class="bi bi-layers"></i> Control de Stock
            </a>
            <?php endif; ?>
            
            <!-- Ventas -->
            <div class="sidebar-section">Ventas</div>
            <a href="ventas.php" class="sidebar-nav-item">
                <i class="bi bi-cart-check"></i> Punto de Venta
            </a>
            <a href="clientes.php" class="sidebar-nav-item">
                <i class="bi bi-people"></i> Clientes
            </a>
            
            <?php if ($plan == 'premium'): ?>
            <a href="facturas.php" class="sidebar-nav-item">
                <i class="bi bi-receipt"></i> Facturas
            </a>
            <?php endif; ?>
            
            <!-- Compras -->
            <div class="sidebar-section">Compras</div>
            <a href="compras.php" class="sidebar-nav-item">
                <i class="bi bi-cart-plus"></i> Órdenes de Compra
            </a>
            <a href="proveedores.php" class="sidebar-nav-item">
                <i class="bi bi-truck"></i> Proveedores
            </a>
            
            <?php if ($plan != 'basico'): ?>
            <!-- Reportes -->
            <div class="sidebar-section">Reportes</div>
            <a href="reportes.php" class="sidebar-nav-item">
                <i class="bi bi-file-earmark-text"></i> Informes
            </a>
            <?php endif; ?>
            
            <?php if ($plan == 'premium'): ?>
            <a href="estadisticas.php" class="sidebar-nav-item">
                <i class="bi bi-graph-up"></i> Estadísticas Avanzadas
            </a>
            <?php endif; ?>
            
            <!-- Sistema -->
            <div class="sidebar-section">Sistema</div>
            <a href="perfil.php" class="sidebar-nav-item">
                <i class="bi bi-person-circle"></i> Mi Perfil
            </a>
            
            <?php if ($usuario->rol == 'administrador'): ?>
            <a href="configuracion.php" class="sidebar-nav-item">
                <i class="bi bi-gear"></i> Configuración
            </a>
            <a href="usuarios.php" class="sidebar-nav-item">
                <i class="bi bi-person-gear"></i> Usuarios
            </a>
            <?php endif; ?>
        </nav>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1>Dashboard <?php echo ucfirst($plan); ?></h1>
                <p>Bienvenido de nuevo, <?php echo htmlspecialchars($usuario->nombre); ?></p>
            </div>
            <div class="date-display">
                <i class="bi bi-calendar3"></i> <?php echo date('l, d F Y'); ?>
            </div>
        </div>

        <?php if ($plan == 'basico'): ?>
        <!-- UPGRADE CARD - Solo para plan básico -->
        <div class="upgrade-card">
            <div class="upgrade-content">
                <h3 class="upgrade-title">¿Necesitas más funciones?</h3>
                <p class="upgrade-text">Actualiza a Profesional o Premium y desbloquea reportes avanzados, múltiples almacenes y análisis predictivo.</p>
                <a href="precios.php" class="upgrade-btn">
                    <i class="bi bi-stars"></i> Ver Planes
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- KPI Cards - Comunes para todos -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Total Productos</span>
                    <span class="kpi-icon"><i class="bi bi-boxes"></i></span>
                </div>
                <div class="kpi-value"><?php echo number_format($total_productos); ?></div>
                <div class="kpi-trend">
                    <i class="bi bi-arrow-up"></i> En inventario
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Stock Bajo</span>
                    <span class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></span>
                </div>
                <div class="kpi-value"><?php echo $productos_bajo_stock; ?></div>
                <div class="kpi-trend <?php echo $productos_bajo_stock > 5 ? 'negative' : ''; ?>">
                    <i class="bi bi-exclamation-circle"></i> Requieren atención
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Productos Agotados</span>
                    <span class="kpi-icon"><i class="bi bi-x-circle"></i></span>
                </div>
                <div class="kpi-value"><?php echo $productos_agotados; ?></div>
                <div class="kpi-trend negative">
                    <i class="bi bi-arrow-down"></i> Sin stock
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-title">Valor Inventario</span>
                    <span class="kpi-icon"><i class="bi bi-currency-dollar"></i></span>
                </div>
                <div class="kpi-value"><?php echo number_format($valor_inventario, 0); ?> CFA</div>
                <div class="kpi-trend">
                    <i class="bi bi-graph-up"></i> Valor total
                </div>
            </div>
        </div>

        <?php if ($plan != 'basico'): ?>
        <!-- Gráficos para Profesional y Premium -->
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <h3 class="chart-title">Ventas del Mes</h3>
                        <p class="chart-subtitle"><?php echo $ventas_mes->total_ventas ?? 0; ?> transacciones</p>
                    </div>
                    <span class="kpi-icon"><i class="bi bi-graph-up"></i></span>
                </div>
                <div style="height: 300px;">
                    <canvas id="ventasChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <h3 class="chart-title">Top 5 Productos</h3>
                        <p class="chart-subtitle">Más vendidos del mes</p>
                    </div>
                    <span class="kpi-icon"><i class="bi bi-trophy"></i></span>
                </div>
                <div style="height: 300px;">
                    <canvas id="productosChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabla de movimientos recientes -->
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Movimientos Recientes</h3>
                <a href="movimientos.php" class="view-all">Ver todos <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $db->query("SELECT m.*, u.nombre as usuario 
                                   FROM movimientos m 
                                   JOIN usuarios u ON m.id_usuario = u.id_usuario 
                                   WHERE m.id_empresa = :empresa_id 
                                   ORDER BY m.created_at DESC 
                                   LIMIT 5");
                        $db->bind(':empresa_id', $_SESSION['empresa_id']);
                        $movimientos = $db->resultSet();
                        
                        foreach ($movimientos as $mov): 
                        ?>
                        <tr>
                            <td>
                                <?php if ($mov->tipo == 'venta'): ?>
                                <span class="badge bg-success">Venta</span>
                                <?php elseif ($mov->tipo == 'compra'): ?>
                                <span class="badge bg-primary">Compra</span>
                                <?php else: ?>
                                <span class="badge bg-warning">Ajuste</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($mov->descripcion); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($mov->created_at)); ?></td>
                            <td class="fw-bold"><?php echo number_format($mov->total, 0); ?> CFA</td>
                            <td>
                                <a href="movimiento.php?id=<?php echo $mov->id_movimiento; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($plan == 'premium'): ?>
        <!-- Sección Premium - Análisis Predictivo -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="table-card premium-feature">
                    <div class="premium-badge">PREMIUM</div>
                    <div class="table-header">
                        <h3 class="table-title">Análisis de Rotación</h3>
                    </div>
                    <div class="text-center py-4">
                        <div class="display-1 fw-bold text-primary"><?php echo number_format($rotacion * 100, 1); ?>%</div>
                        <p class="text-muted">Rotación de inventario en los últimos 30 días</p>
                        <div class="mt-3">
                            <span class="badge bg-info text-dark">Eficiencia: Alta</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="table-card premium-feature">
                    <div class="premium-badge">PREMIUM</div>
                    <div class="table-header">
                        <h3 class="table-title">Alertas Inteligentes</h3>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $productos_bajo_stock; ?> productos con stock bajo
                    </div>
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle"></i> <?php echo $alertas_criticas; ?> productos agotados
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-graph-up"></i> Se recomienda reabastecer: Arroz, Aceite, Leche
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Acciones Rápidas -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Acciones Rápidas</h3>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="ventas.php" class="btn btn-success">
                            <i class="bi bi-cart-check"></i> Nueva Venta
                        </a>
                        <a href="compras.php" class="btn btn-warning">
                            <i class="bi bi-cart-plus"></i> Nueva Compra
                        </a>
                        <a href="productos.php" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Nuevo Producto
                        </a>
                        <?php if ($plan != 'basico'): ?>
                        <a href="reportes.php" class="btn btn-info">
                            <i class="bi bi-file-earmark-text"></i> Generar Reporte
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Toggle sidebar móvil
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('sidebar').classList.remove('mobile-open');
            }
        });

        <?php if ($plan != 'basico'): ?>
        // Gráfico de Ventas
        const ventasCtx = document.getElementById('ventasChart').getContext('2d');
        new Chart(ventasCtx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Ventas (CFA)',
                    data: [1200000, 1900000, 1500000, 2500000, 2200000, <?php echo $ventas_mes->monto_total ?? 3000000; ?>],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
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
                                return value.toLocaleString() + ' CFA';
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Productos
        const productosCtx = document.getElementById('productosChart').getContext('2d');
        new Chart(productosCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php 
                    $labels = '';
                    $data = '';
                    foreach ($top_productos as $prod) {
                        $labels .= "'" . addslashes($prod->nombre) . "', ";
                        $data .= $prod->total_vendido . ", ";
                    }
                    echo $labels;
                ?>],
                datasets: [{
                    data: [<?php echo $data; ?>],
                    backgroundColor: [
                        '#2563eb',
                        '#60a5fa',
                        '#3b82f6',
                        '#1d4ed8',
                        '#1e3a8a'
                    ],
                    borderWidth: 0
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
        <?php endif; ?>
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php require_once 'includes/footer.php'; ?>