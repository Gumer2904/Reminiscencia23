<?php
$page_title = "Reportes del Sistema";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('encargado')) {
    header('Location: dashboard.php');
    exit();
}

// Función para generar reportes sin dependencias externas
function generarReporteCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Escribir encabezados
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
        
        // Escribir datos
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit();
}

function generarReporteHTML($data, $title, $filename) {
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>' . $title . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .header { text-align: center; margin-bottom: 30px; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>' . $title . '</h1>
            <p>Generado el ' . date('d/m/Y H:i') . '</p>
        </div>
        
        <table>';
    
    // Escribir encabezados
    if (!empty($data)) {
        echo '<tr>';
        foreach (array_keys($data[0]) as $header) {
            echo '<th>' . htmlspecialchars($header) . '</th>';
        }
        echo '</tr>';
        
        // Escribir datos
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
    }
    
    echo '</table>
        
        <div class="footer">
            <p>Reporte generado por Stock Manager</p>
        </div>
    </body>
    </html>';
    exit();
}

$action = $_GET['action'] ?? '';
$tipo_reporte = $_GET['tipo'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$id_categoria = $_GET['categoria'] ?? 0;

// Obtener estadísticas para dashboard de reportes
$db->query("SELECT 
           COUNT(DISTINCT id_cliente) as total_clientes,
           COUNT(DISTINCT id_proveedor) as total_proveedores,
           (SELECT COUNT(*) FROM productos WHERE id_empresa = :empresa_id) as total_productos,
           (SELECT SUM(stock_actual) FROM productos WHERE id_empresa = :empresa_id) as total_unidades
           FROM (
               SELECT id_cliente FROM clientes WHERE id_empresa = :empresa_id
               UNION ALL
               SELECT id_proveedor FROM proveedores WHERE id_empresa = :empresa_id
           ) t");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$estadisticas = $db->single();

// Obtener categorías para filtros
$db->query("SELECT * FROM categorias WHERE id_empresa = :empresa_id ORDER BY nombre");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$categorias = $db->resultSet();

// Obtener ventas por mes para gráfico
$db->query("SELECT 
           DATE_FORMAT(created_at, '%Y-%m') as mes,
           COUNT(*) as cantidad_ventas,
           SUM(total) as total_ventas
           FROM movimientos 
           WHERE id_empresa = :empresa_id AND tipo = 'venta'
           AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
           GROUP BY DATE_FORMAT(created_at, '%Y-%m')
           ORDER BY mes");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$ventas_mensuales = $db->resultSet();

// Obtener productos más vendidos
$db->query("SELECT 
           p.nombre,
           SUM(dm.cantidad) as total_vendido,
           SUM(dm.cantidad * dm.precio_unitario) as total_ventas,
           c.nombre as categoria_nombre
           FROM detalle_movimiento dm
           JOIN productos p ON dm.id_producto = p.id_producto
           LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
           JOIN movimientos m ON dm.id_movimiento = m.id_movimiento
           WHERE m.id_empresa = :empresa_id AND m.tipo = 'venta'
           AND m.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
           GROUP BY dm.id_producto
           ORDER BY total_vendido DESC
           LIMIT 10");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$productos_top = $db->resultSet();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Reportes del Sistema</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-primary me-2" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Imprimir
        </button>
    </div>
</div>

<!-- Filtros para reportes -->
<div class="card card-shadow mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-filter me-2"></i> Filtros de Reportes</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <input type="hidden" name="tipo" id="tipo_reporte" value="">
            
            <div class="col-md-3">
                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                       value="<?php echo $fecha_inicio; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                       value="<?php echo $fecha_fin; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="categoria" class="form-label">Categoría</label>
                <select class="form-select" id="categoria" name="categoria">
                    <option value="0">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat->id_categoria; ?>" 
                            <?php echo ($id_categoria == $cat->id_categoria) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat->nombre); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Aplicar Filtros
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Cards de estadísticas -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Productos</h6>
                <h3 class="card-title"><?php echo $estadisticas->total_productos; ?></h3>
                <p class="card-text">En inventario</p>
                <a href="#" class="text-white" onclick="generarReporte('inventario')">
                    <small><i class="bi bi-download me-1"></i> Descargar reporte</small>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Unidades</h6>
                <h3 class="card-title"><?php echo number_format($estadisticas->total_unidades); ?></h3>
                <p class="card-text">Stock total</p>
                <a href="#" class="text-white" onclick="generarReporte('stock')">
                    <small><i class="bi bi-download me-1"></i> Descargar reporte</small>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Clientes</h6>
                <h3 class="card-title"><?php echo $estadisticas->total_clientes; ?></h3>
                <p class="card-text">Registrados</p>
                <a href="#" class="text-white" onclick="generarReporte('clientes')">
                    <small><i class="bi bi-download me-1"></i> Descargar reporte</small>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Proveedores</h6>
                <h3 class="card-title"><?php echo $estadisticas->total_proveedores; ?></h3>
                <p class="card-text">Activos</p>
                <a href="#" class="text-white" onclick="generarReporte('proveedores')">
                    <small><i class="bi bi-download me-1"></i> Descargar reporte</small>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tipos de reportes -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i> Generar Reportes</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Reportes de Inventario -->
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h5 class="card-title text-primary">
                                    <i class="bi bi-box-seam me-2"></i> Inventario
                                </h5>
                                <p class="card-text">Reportes relacionados con el inventario y stock.</p>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary btn-sm" onclick="generarReporte('inventario_completo')">
                                        <i class="bi bi-file-pdf me-1"></i> Inventario Completo (PDF)
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="generarReporte('inventario_excel')">
                                        <i class="bi bi-file-excel me-1"></i> Inventario (Excel)
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="generarReporte('stock_bajo')">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Stock Bajo (PDF)
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="generarReporte('productos_agotados')">
                                        <i class="bi bi-x-circle me-1"></i> Productos Agotados (PDF)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reportes de Ventas -->
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body">
                                <h5 class="card-title text-success">
                                    <i class="bi bi-cart-check me-2"></i> Ventas
                                </h5>
                                <p class="card-text">Reportes de ventas, facturación e ingresos.</p>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary btn-sm" onclick="generarReporte('ventas_periodo')">
                                        <i class="bi bi-file-pdf me-1"></i> Ventas por Período (PDF)
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="generarReporte('ventas_excel')">
                                        <i class="bi bi-file-excel me-1"></i> Ventas (Excel)
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="generarReporte('productos_mas_vendidos')">
                                        <i class="bi bi-graph-up me-1"></i> Productos Más Vendidos (PDF)
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="generarReporte('ventas_vendedor')">
                                        <i class="bi bi-people me-1"></i> Ventas por Vendedor (PDF)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reportes de Compras -->
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-body">
                                <h5 class="card-title text-info">
                                    <i class="bi bi-cart-plus me-2"></i> Compras
                                </h5>
                                <p class="card-text">Reportes de compras, proveedores y egresos.</p>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary btn-sm" onclick="generarReporte('compras_periodo')">
                                        <i class="bi bi-file-pdf me-1"></i> Compras por Período (PDF)
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="generarReporte('compras_excel')">
                                        <i class="bi bi-file-excel me-1"></i> Compras (Excel)
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="generarReporte('compras_proveedor')">
                                        <i class="bi bi-truck me-1"></i> Compras por Proveedor (PDF)
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="generarReporte('ordenes_pendientes')">
                                        <i class="bi bi-clock me-1"></i> Órdenes Pendientes (PDF)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos y estadísticas -->
<div class="row">
    <!-- Gráfico de ventas mensuales -->
    <div class="col-md-6 mb-4">
        <div class="card card-shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i> Ventas Mensuales (Últimos 6 meses)</h5>
            </div>
            <div class="card-body">
                <canvas id="chartVentasMensuales" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Top productos más vendidos -->
    <div class="col-md-6 mb-4">
        <div class="card card-shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i> Productos Más Vendidos (Último mes)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_top as $index => $producto): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($producto->nombre); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($producto->categoria_nombre ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo $producto->total_vendido; ?> unidades
                                    </span>
                                </td>
                                <td class="fw-bold text-success">
                                    <?php echo number_format($producto->total_ventas, 2); ?> CFA
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="generarReporte('productos_mas_vendidos')">
                        <i class="bi bi-download me-1"></i> Descargar Reporte Completo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de movimientos recientes -->
<div class="row">
    <div class="col-12">
        <div class="card card-shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Movimientos Recientes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Usuario</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $db->query("SELECT m.*, u.nombre as usuario_nombre 
                                       FROM movimientos m 
                                       JOIN usuarios u ON m.id_usuario = u.id_usuario 
                                       WHERE m.id_empresa = :empresa_id 
                                       ORDER BY m.created_at DESC 
                                       LIMIT 20");
                            $db->bind(':empresa_id', $_SESSION['empresa_id']);
                            $movimientos = $db->resultSet();
                            
                            foreach ($movimientos as $mov):
                                $badge_class = '';
                                $tipo_text = '';
                                switch ($mov->tipo) {
                                    case 'venta': $badge_class = 'bg-success'; $tipo_text = 'Venta'; break;
                                    case 'compra': $badge_class = 'bg-primary'; $tipo_text = 'Compra'; break;
                                    case 'ajuste_entrada': $badge_class = 'bg-info'; $tipo_text = 'Ajuste Entrada'; break;
                                    case 'ajuste_salida': $badge_class = 'bg-warning'; $tipo_text = 'Ajuste Salida'; break;
                                }
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($mov->created_at)); ?></td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo $tipo_text; ?></span></td>
                                <td><?php echo htmlspecialchars($mov->descripcion); ?></td>
                                <td><?php echo htmlspecialchars($mov->usuario_nombre); ?></td>
                                <td class="fw-bold"><?php echo number_format($mov->total, 2); ?> CFA</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" onclick="verDetalleMovimiento(<?php echo $mov->id_movimiento; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalle de movimiento -->
<div class="modal fade" id="modalDetalleMovimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalle del Movimiento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleMovimientoContenido">
                <!-- Cargado dinámicamente -->
            </div>
        </div>
    </div>
</div>

<script>
// Gráfico de ventas mensuales
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartVentasMensuales').getContext('2d');
    
    // Preparar datos
    const meses = [];
    const ventas = [];
    
    <?php foreach ($ventas_mensuales as $venta): ?>
    meses.push('<?php echo date("M Y", strtotime($venta->mes . "-01")); ?>');
    ventas.push(<?php echo $venta->total_ventas ?? 0; ?>);
    <?php endforeach; ?>
    
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Ventas (CFA)',
                data: ventas,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
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
});

// Función para generar reportes
function generarReporte(tipo) {
    const fecha_inicio = document.getElementById('fecha_inicio').value;
    const fecha_fin = document.getElementById('fecha_fin').value;
    const categoria = document.getElementById('categoria').value;
    const formato = tipo.includes('excel') ? 'excel' : 'pdf';
    const tipo_limpio = tipo.replace('_excel', '');
    
    let url = `reportes.php?action=${formato}&tipo=${tipo_limpio}`;
    url += `&fecha_inicio=${fecha_inicio}&fecha_fin=${fecha_fin}`;
    if (categoria > 0) url += `&categoria=${categoria}`;
    
    window.open(url, '_blank');
}

// Función para ver detalle de movimiento
function verDetalleMovimiento(id) {
    fetch(`includes/ajax/detalle_movimiento.php?id=${id}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('detalleMovimientoContenido').innerHTML = data;
            const modal = new bootstrap.Modal(document.getElementById('modalDetalleMovimiento'));
            modal.show();
        })
        .catch(error => console.error('Error:', error));
}

// Función para exportar a diferentes formatos
function exportarReporte(tipo, formato) {
    const fecha_inicio = document.getElementById('fecha_inicio').value;
    const fecha_fin = document.getElementById('fecha_fin').value;
    const categoria = document.getElementById('categoria').value;
    
    let url = `reportes.php?action=${formato}&tipo=${tipo}`;
    url += `&fecha_inicio=${fecha_inicio}&fecha_fin=${fecha_fin}`;
    if (categoria > 0) url += `&categoria=${categoria}`;
    
    window.open(url, '_blank');
}
</script>

<?php
// Función para generar reporte PDF
function generarReportePDF($tipo, $fecha_inicio, $fecha_fin, $id_categoria) {
    global $db, $_SESSION;
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte - ' . htmlspecialchars($tipo) . '</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
            .title { font-size: 18px; font-weight: bold; }
            .subtitle { font-size: 14px; color: #666; }
            .filtros { margin-bottom: 15px; padding: 10px; background: #f5f5f5; border-radius: 5px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { background-color: #333; color: white; padding: 8px; text-align: left; }
            td { padding: 8px; border: 1px solid #ddd; }
            .total { font-weight: bold; background-color: #f0f0f0; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
        </style>
    </head>
    <body>';
    
    // Encabezado del reporte
    $html .= '
    <div class="header">
        <div class="title">Stock Manager - Reporte de ' . ucfirst(str_replace('_', ' ', $tipo)) . '</div>
        <div class="subtitle">' . $_SESSION['empresa_name'] . '</div>
        <div class="subtitle">Generado: ' . date('d/m/Y H:i:s') . '</div>
        <div class="filtros">
            Período: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' al ' . date('d/m/Y', strtotime($fecha_fin)) . '
        </div>
    </div>';
    
    // Contenido según tipo de reporte
    switch ($tipo) {
        case 'inventario_completo':
            $html .= generarContenidoInventarioPDF();
            break;
            
        case 'ventas_periodo':
            $html .= generarContenidoVentasPDF($fecha_inicio, $fecha_fin);
            break;
            
        case 'compras_periodo':
            $html .= generarContenidoComprasPDF($fecha_inicio, $fecha_fin);
            break;
            
        case 'stock_bajo':
            $html .= generarContenidoStockBajoPDF();
            break;
            
        case 'productos_mas_vendidos':
            $html .= generarContenidoProductosVendidosPDF($fecha_inicio, $fecha_fin);
            break;
            
        default:
            $html .= '<p>Tipo de reporte no válido</p>';
    }
    
    $html .= '
    <div class="footer">
        Generado por Stock Manager S.L. - Página {PAGENO} de {nbpg}
    </div>
    </body>
    </html>';
    
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("reporte_" . $tipo . "_" . date('Ymd_His') . ".pdf", array("Attachment" => true));
}

// Funciones auxiliares para contenido PDF (simplificadas)
function generarContenidoInventarioPDF() {
    global $db, $_SESSION;
    
    $db->query("SELECT p.*, c.nombre as categoria_nombre 
               FROM productos p 
               LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
               WHERE p.id_empresa = :empresa_id 
               ORDER BY p.nombre");
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $productos = $db->resultSet();
    
    $html = '<h3>Inventario Completo</h3>';
    $html .= '<table>';
    $html .= '<tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Precio Venta</th>
                <th>Costo Promedio</th>
                <th>Valor Stock</th>
              </tr>';
    
    $total_valor = 0;
    foreach ($productos as $prod) {
        $valor = $prod->stock_actual * $prod->costo_promedio;
        $total_valor += $valor;
        
        $html .= '<tr>
                    <td>' . ($prod->codigo_barras ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($prod->nombre) . '</td>
                    <td>' . htmlspecialchars($prod->categoria_nombre ?? 'N/A') . '</td>
                    <td class="text-center">' . $prod->stock_actual . '</td>
                    <td class="text-center">' . $prod->stock_minimo . '</td>
                    <td class="text-right">' . number_format($prod->precio_venta, 2) . ' CFA</td>
                    <td class="text-right">' . number_format($prod->costo_promedio, 2) . ' CFA</td>
                    <td class="text-right">' . number_format($valor, 2) . ' CFA</td>
                  </tr>';
    }
    
    $html .= '<tr class="total">
                <td colspan="7" class="text-right"><strong>TOTAL VALOR INVENTARIO:</strong></td>
                <td class="text-right"><strong>' . number_format($total_valor, 2) . ' CFA</strong></td>
              </tr>';
    $html .= '</table>';
    
    return $html;
}

function generarContenidoVentasPDF($fecha_inicio, $fecha_fin) {
    global $db, $_SESSION;
    
    $db->query("SELECT m.*, u.nombre as vendedor 
               FROM movimientos m 
               JOIN usuarios u ON m.id_usuario = u.id_usuario 
               WHERE m.id_empresa = :empresa_id AND m.tipo = 'venta'
               AND DATE(m.created_at) BETWEEN :fecha_inicio AND :fecha_fin
               ORDER BY m.created_at DESC");
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $db->bind(':fecha_inicio', $fecha_inicio);
    $db->bind(':fecha_fin', $fecha_fin);
    $ventas = $db->resultSet();
    
    $html = '<h3>Ventas del Período</h3>';
    $html .= '<table>';
    $html .= '<tr>
                <th>Factura</th>
                <th>Fecha</th>
                <th>Vendedor</th>
                <th>Descripción</th>
                <th>Total</th>
              </tr>';
    
    $total_ventas = 0;
    foreach ($ventas as $venta) {
        $total_ventas += $venta->total;
        
        $html .= '<tr>
                    <td>' . ($venta->numero_documento ?? 'N/A') . '</td>
                    <td>' . date('d/m/Y H:i', strtotime($venta->created_at)) . '</td>
                    <td>' . htmlspecialchars($venta->vendedor) . '</td>
                    <td>' . htmlspecialchars(substr($venta->descripcion, 0, 50)) . '</td>
                    <td class="text-right">' . number_format($venta->total, 2) . ' CFA</td>
                  </tr>';
    }
    
    $html .= '<tr class="total">

require_once 'includes/footer.php';
?>