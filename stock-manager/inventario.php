<?php
$page_title = "Gestión de Inventario";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('encargado')) {
    header('Location: dashboard.php');
    exit();
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Registrar ajuste de inventario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_ajuste'])) {
    $tipo = $_POST['tipo']; // 'ajuste_entrada' o 'ajuste_salida'
    $id_producto = $_POST['id_producto'];
    $cantidad = intval($_POST['cantidad']);
    $motivo = trim($_POST['motivo']);
    $costo_unitario = floatval($_POST['costo_unitario']);
    
    if ($cantidad <= 0) {
        $error = "La cantidad debe ser mayor a cero";
    } else {
        // Iniciar transacción
        try {
            // 1. Registrar movimiento
            $db->query("INSERT INTO movimientos (id_empresa, tipo, descripcion, id_usuario, total) 
                       VALUES (:empresa_id, :tipo, :descripcion, :usuario_id, :total)");
            $db->bind(':empresa_id', $_SESSION['empresa_id']);
            $db->bind(':tipo', $tipo);
            $db->bind(':descripcion', "Ajuste de inventario: " . $motivo);
            $db->bind(':usuario_id', $_SESSION['user_id']);
            $db->bind(':total', $cantidad * $costo_unitario);
            $db->execute();
            
            $id_movimiento = $db->lastInsertId();
            
            // 2. Registrar detalle
            $db->query("INSERT INTO detalle_movimiento (id_movimiento, id_producto, cantidad, precio_unitario) 
                       VALUES (:movimiento_id, :producto_id, :cantidad, :precio)");
            $db->bind(':movimiento_id', $id_movimiento);
            $db->bind(':producto_id', $id_producto);
            $db->bind(':cantidad', $cantidad);
            $db->bind(':precio', $costo_unitario);
            $db->execute();
            
            // 3. Actualizar stock del producto
            $operador = ($tipo == 'ajuste_entrada') ? '+' : '-';
            $db->query("UPDATE productos SET stock_actual = stock_actual $operador :cantidad 
                       WHERE id_producto = :producto_id");
            $db->bind(':producto_id', $id_producto);
            $db->bind(':cantidad', $cantidad);
            $db->execute();
            
            $success = "Ajuste de inventario registrado exitosamente";
            header('Location: inventario.php?success=' . urlencode($success));
            exit();
            
        } catch (Exception $e) {
            $error = "Error al registrar el ajuste: " . $e->getMessage();
        }
    }
}

// Obtener historial de movimientos
$db->query("SELECT m.*, u.nombre as usuario_nombre,
           (SELECT COUNT(*) FROM detalle_movimiento dm WHERE dm.id_movimiento = m.id_movimiento) as total_items
           FROM movimientos m 
           JOIN usuarios u ON m.id_usuario = u.id_usuario 
           WHERE m.id_empresa = :empresa_id 
           ORDER BY m.created_at DESC 
           LIMIT 50");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$movimientos = $db->resultSet();

// Obtener productos para select
$db->query("SELECT id_producto, nombre, stock_actual FROM productos 
           WHERE id_empresa = :empresa_id AND activo = 1 
           ORDER BY nombre");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$productos = $db->resultSet();

// Obtener estadísticas de inventario
$db->query("SELECT 
           SUM(stock_actual) as total_unidades,
           SUM(stock_actual * costo_promedio) as valor_total,
           COUNT(CASE WHEN stock_actual <= stock_minimo THEN 1 END) as productos_bajo_stock,
           COUNT(CASE WHEN stock_actual = 0 THEN 1 END) as productos_agotados
           FROM productos 
           WHERE id_empresa = :empresa_id AND activo = 1");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$estadisticas = $db->single();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Inventario</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalAjusteInventario">
            <i class="bi bi-clipboard-plus me-1"></i> Registrar Ajuste
        </button>
        <a href="inventario.php?action=export" class="btn btn-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Exportar Excel
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_GET['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Estadísticas rápidas -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Total Unidades</h6>
                <h3 class="card-title"><?php echo number_format($estadisticas->total_unidades); ?></h3>
                <p class="card-text">Productos en inventario</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Valor Total</h6>
                <h3 class="card-title"><?php echo number_format($estadisticas->valor_total, 2); ?> CFA</h3>
                <p class="card-text">Valor del inventario</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Stock Bajo</h6>
                <h3 class="card-title"><?php echo $estadisticas->productos_bajo_stock; ?></h3>
                <p class="card-text">Necesitan reabastecimiento</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Agotados</h6>
                <h3 class="card-title"><?php echo $estadisticas->productos_agotados; ?></h3>
                <p class="card-text">Sin stock disponible</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabs para diferentes vistas -->
<ul class="nav nav-tabs mb-4" id="inventarioTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button">
            <i class="bi bi-clock-history me-1"></i> Historial de Movimientos
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock" type="button">
            <i class="bi bi-box me-1"></i> Stock Actual
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="alertas-tab" data-bs-toggle="tab" data-bs-target="#alertas" type="button">
            <i class="bi bi-exclamation-triangle me-1"></i> Alertas de Stock
        </button>
    </li>
</ul>

<div class="tab-content" id="inventarioTabContent">
    <!-- Tab 1: Historial de Movimientos -->
    <div class="tab-pane fade show active" id="historial" role="tabpanel">
        <div class="card card-shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Total</th>
                                <th>Items</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $mov): 
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
                                <td>M-<?php echo str_pad($mov->id_movimiento, 6, '0', STR_PAD_LEFT); ?></td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo $tipo_text; ?></span></td>
                                <td><?php echo htmlspecialchars($mov->descripcion); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($mov->created_at)); ?></td>
                                <td><?php echo htmlspecialchars($mov->usuario_nombre); ?></td>
                                <td class="fw-bold"><?php echo number_format($mov->total, 2); ?> CFA</td>
                                <td><?php echo $mov->total_items; ?> items</td>
                                <td>
                                    <a href="inventario.php?action=view&id=<?php echo $mov->id_movimiento; ?>" 
                                       class="btn btn-sm btn-outline-info" title="Ver detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab 2: Stock Actual -->
    <div class="tab-pane fade" id="stock" role="tabpanel">
        <?php
        $db->query("SELECT p.*, c.nombre as categoria_nombre 
                   FROM productos p 
                   LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                   WHERE p.id_empresa = :empresa_id AND p.activo = 1 
                   ORDER BY p.stock_actual ASC");
        $db->bind(':empresa_id', $_SESSION['empresa_id']);
        $productos_stock = $db->resultSet();
        ?>
        <div class="card card-shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Estado</th>
                                <th>Precio</th>
                                <th>Valor</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_stock as $prod): 
                                $valor = $prod->stock_actual * $prod->costo_promedio;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod->nombre); ?></td>
                                <td><?php echo htmlspecialchars($prod->categoria_nombre ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        if ($prod->stock_actual == 0) echo 'bg-danger';
                                        elseif ($prod->stock_actual <= $prod->stock_minimo) echo 'bg-warning';
                                        else echo 'bg-success';
                                        ?>">
                                        <?php echo $prod->stock_actual; ?>
                                    </span>
                                </td>
                                <td><?php echo $prod->stock_minimo; ?></td>
                                <td>
                                    <?php if ($prod->stock_actual == 0): ?>
                                    <span class="badge bg-danger">AGOTADO</span>
                                    <?php elseif ($prod->stock_actual <= $prod->stock_minimo): ?>
                                    <span class="badge bg-warning">BAJO</span>
                                    <?php else: ?>
                                    <span class="badge bg-success">OK</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo number_format($prod->precio_venta, 2); ?> CFA</td>
                                <td class="fw-bold"><?php echo number_format($valor, 2); ?> CFA</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="cargarProductoAjuste(<?php echo $prod->id_producto; ?>, '<?php echo addslashes($prod->nombre); ?>', <?php echo $prod->costo_promedio; ?>)"
                                            data-bs-toggle="modal" data-bs-target="#modalAjusteInventario">
                                        <i class="bi bi-pencil"></i> Ajustar
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
    
    <!-- Tab 3: Alertas de Stock -->
    <div class="tab-pane fade" id="alertas" role="tabpanel">
        <?php
        $db->query("SELECT p.*, c.nombre as categoria_nombre 
                   FROM productos p 
                   LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                   WHERE p.id_empresa = :empresa_id AND p.activo = 1 
                   AND (p.stock_actual = 0 OR p.stock_actual <= p.stock_minimo)
                   ORDER BY p.stock_actual ASC");
        $db->bind(':empresa_id', $_SESSION['empresa_id']);
        $alertas = $db->resultSet();
        ?>
        <div class="card card-shadow border-warning">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Productos que Necesitan Atención</h5>
            </div>
            <div class="card-body">
                <?php if ($alertas): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Diferencia</th>
                                <th>Último Costo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alertas as $alerta): 
                                $diferencia = $alerta->stock_minimo - $alerta->stock_actual;
                                if ($diferencia < 0) $diferencia = 0;
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($alerta->nombre); ?></strong>
                                    <?php if ($alerta->stock_actual == 0): ?>
                                    <br><small class="text-danger"><i class="bi bi-x-circle"></i> AGOTADO</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($alerta->categoria_nombre ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge <?php echo ($alerta->stock_actual == 0) ? 'bg-danger' : 'bg-warning'; ?>">
                                        <?php echo $alerta->stock_actual; ?>
                                    </span>
                                </td>
                                <td><?php echo $alerta->stock_minimo; ?></td>
                                <td class="fw-bold">+<?php echo $diferencia; ?> unidades</td>
                                <td><?php echo number_format($alerta->costo_promedio, 2); ?> CFA</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="compras.php?nueva=1&producto=<?php echo $alerta->id_producto; ?>&cantidad=<?php echo $diferencia; ?>" 
                                           class="btn btn-outline-success">
                                            <i class="bi bi-cart-plus"></i> Comprar
                                        </a>
                                        <button class="btn btn-outline-primary" 
                                                onclick="cargarProductoAjuste(<?php echo $alerta->id_producto; ?>, '<?php echo addslashes($alerta->nombre); ?>', <?php echo $alerta->costo_promedio; ?>)"
                                                data-bs-toggle="modal" data-bs-target="#modalAjusteInventario">
                                            <i class="bi bi-pencil"></i> Ajustar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-success">¡Todo en orden!</h4>
                    <p>No hay productos con stock bajo o agotado.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ajuste de inventario -->
<div class="modal fade" id="modalAjusteInventario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-clipboard-plus me-2"></i> Ajuste de Inventario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo de Ajuste *</label>
                        <select class="form-select" id="tipo" name="tipo" required onchange="actualizarEtiquetaTipo()">
                            <option value="ajuste_entrada">Entrada (Aumentar stock)</option>
                            <option value="ajuste_salida">Salida (Disminuir stock)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_producto" class="form-label">Producto *</label>
                        <select class="form-select" id="id_producto" name="id_producto" required>
                            <option value="">Seleccionar producto</option>
                            <?php foreach ($productos as $prod): ?>
                            <option value="<?php echo $prod->id_producto; ?>">
                                <?php echo htmlspecialchars($prod->nombre); ?> (Stock: <?php echo $prod->stock_actual; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">
                            <span id="etiquetaCantidad">Cantidad a Aumentar</span> *
                        </label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" 
                               required min="1" step="1" value="1">
                    </div>
                    
                    <div class="mb-3">
                        <label for="costo_unitario" class="form-label">Costo Unitario (CFA) *</label>
                        <input type="number" class="form-control" id="costo_unitario" name="costo_unitario" 
                               required step="0.01" min="0" value="0">
                    </div>
                    
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo del Ajuste *</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" required 
                                  placeholder="Ej: Revisión de inventario físico, Donación, Pérdida, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" name="registrar_ajuste">
                        <i class="bi bi-save me-1"></i> Registrar Ajuste
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function actualizarEtiquetaTipo() {
    var tipo = document.getElementById('tipo').value;
    var etiqueta = document.getElementById('etiquetaCantidad');
    if (tipo == 'ajuste_entrada') {
        etiqueta.textContent = 'Cantidad a Aumentar';
    } else {
        etiqueta.textContent = 'Cantidad a Disminuir';
    }
}

function cargarProductoAjuste(id_producto, nombre, costo) {
    document.getElementById('id_producto').value = id_producto;
    document.getElementById('costo_unitario').value = costo;
    document.getElementById('motivo').value = 'Ajuste para producto: ' + nombre;
}
</script>

<?php require_once 'includes/footer.php'; ?>