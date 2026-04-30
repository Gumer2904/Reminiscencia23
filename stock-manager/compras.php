<?php
$page_title = "Gestión de Compras";
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

// Crear nueva orden de compra
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_orden'])) {
    $id_proveedor = $_POST['id_proveedor'];
    $fecha_esperada = $_POST['fecha_esperada'];
    $observaciones = trim($_POST['observaciones']);
    $items = json_decode($_POST['items_json'], true);
    
    if (empty($items)) {
        $error = "Debe agregar al menos un producto a la orden";
    } else {
        // Calcular total
        $total = 0;
        foreach ($items as $item) {
            $total += $item['cantidad'] * $item['costo'];
        }
        
        // Registrar orden de compra
        $db->query("INSERT INTO movimientos (id_empresa, tipo, descripcion, id_usuario, total) 
                   VALUES (:empresa_id, 'compra', :descripcion, :usuario_id, :total)");
        $db->bind(':empresa_id', $_SESSION['empresa_id']);
        $db->bind(':descripcion', "Orden de compra a Proveedor ID: $id_proveedor - $observaciones");
        $db->bind(':usuario_id', $_SESSION['user_id']);
        $db->bind(':total', $total);
        $db->execute();
        
        $id_movimiento = $db->lastInsertId();
        
        // Generar número de orden
        $numero_orden = "OC-" . date('Y') . "-" . str_pad($id_movimiento, 6, '0', STR_PAD_LEFT);
        $db->query("UPDATE movimientos SET numero_documento = :numero WHERE id_movimiento = :id");
        $db->bind(':numero', $numero_orden);
        $db->bind(':id', $id_movimiento);
        $db->execute();
        
        $success = "Orden de compra creada: " . $numero_orden;
        header('Location: compras.php?success=' . urlencode($success) . '&orden=' . $id_movimiento);
        exit();
    }
}

// Recibir compra (actualizar stock)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['recibir_compra'])) {
    $id_movimiento = $_POST['id_movimiento'];
    
    // Obtener detalles de la compra
    $db->query("SELECT dm.* FROM detalle_movimiento dm 
               JOIN movimientos m ON dm.id_movimiento = m.id_movimiento 
               WHERE m.id_movimiento = :id AND m.id_empresa = :empresa_id AND m.tipo = 'compra'");
    $db->bind(':id', $id_movimiento);
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $detalles = $db->resultSet();
    
    // Actualizar stock y costos
    foreach ($detalles as $detalle) {
        // Actualizar stock
        $db->query("UPDATE productos SET stock_actual = stock_actual + :cantidad 
                   WHERE id_producto = :producto_id");
        $db->bind(':producto_id', $detalle->id_producto);
        $db->bind(':cantidad', $detalle->cantidad);
        $db->execute();
        
        // Actualizar costo promedio (promedio ponderado)
        $db->query("UPDATE productos p 
                   JOIN (SELECT id_producto, 
                        SUM(cantidad * precio_unitario) / SUM(cantidad) as nuevo_costo 
                        FROM detalle_movimiento 
                        WHERE id_producto = :producto_id 
                        AND id_movimiento IN (
                            SELECT id_movimiento FROM movimientos WHERE tipo = 'compra'
                        ) 
                        GROUP BY id_producto) t 
                   ON p.id_producto = t.id_producto 
                   SET p.costo_promedio = t.nuevo_costo 
                   WHERE p.id_producto = :producto_id");
        $db->bind(':producto_id', $detalle->id_producto);
        $db->execute();
    }
    
    // Marcar como recibida
    $db->query("UPDATE movimientos SET descripcion = CONCAT(descripcion, ' [RECIBIDA]') 
               WHERE id_movimiento = :id");
    $db->bind(':id', $id_movimiento);
    $db->execute();
    
    $success = "Compra recibida y stock actualizado";
    header('Location: compras.php?success=' . urlencode($success));
    exit();
}

// Obtener órdenes de compra
$db->query("SELECT m.*, u.nombre as usuario_nombre 
           FROM movimientos m 
           JOIN usuarios u ON m.id_usuario = u.id_usuario 
           WHERE m.id_empresa = :empresa_id AND m.tipo = 'compra' 
           ORDER BY m.created_at DESC");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$compras = $db->resultSet();

// Obtener productos para orden de compra
$db->query("SELECT p.*, c.nombre as categoria_nombre 
           FROM productos p 
           LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
           WHERE p.id_empresa = :empresa_id AND p.activo = 1 
           ORDER BY p.nombre");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$productos_compra = $db->resultSet();

// Obtener proveedores (necesitas crear esta tabla)
$db->query("SELECT * FROM proveedores WHERE id_empresa = :empresa_id ORDER BY nombre");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$proveedores = $db->resultSet();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Compras</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalNuevaOrden">
            <i class="bi bi-cart-plus me-1"></i> Nueva Orden
        </button>
        <a href="compras.php?action=pending" class="btn btn-warning text-white">
            <i class="bi bi-clock-history me-1"></i> Pendientes
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

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <?php
                $db->query("SELECT COUNT(*) as total FROM movimientos 
                           WHERE id_empresa = :empresa_id AND tipo = 'compra' 
                           AND DATE(created_at) = CURDATE()");
                $db->bind(':empresa_id', $_SESSION['empresa_id']);
                $compras_hoy = $db->single()->total;
                ?>
                <h6 class="card-subtitle mb-2">Compras Hoy</h6>
                <h3 class="card-title"><?php echo $compras_hoy; ?></h3>
                <p class="card-text">Órdenes del día</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <?php
                $db->query("SELECT SUM(total) as total FROM movimientos 
                           WHERE id_empresa = :empresa_id AND tipo = 'compra' 
                           AND MONTH(created_at) = MONTH(CURDATE())");
                $db->bind(':empresa_id', $_SESSION['empresa_id']);
                $total_mes = $db->single()->total ?? 0;
                ?>
                <h6 class="card-subtitle mb-2">Compras Mes</h6>
                <h3 class="card-title"><?php echo number_format($total_mes, 2); ?> CFA</h3>
                <p class="card-text">Total del mes</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <?php
                $db->query("SELECT COUNT(DISTINCT id_proveedor) as proveedores FROM compras 
                           WHERE id_empresa = :empresa_id");
                $db->bind(':empresa_id', $_SESSION['empresa_id']);
                $total_proveedores = $db->single()->proveedores ?? 0;
                ?>
                <h6 class="card-subtitle mb-2">Proveedores</h6>
                <h3 class="card-title"><?php echo $total_proveedores; ?></h3>
                <p class="card-text">Activos</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="comprasTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="ordenes-tab" data-bs-toggle="tab" data-bs-target="#ordenes" type="button">
            <i class="bi bi-list-check me-1"></i> Órdenes de Compra
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="nueva-tab" data-bs-toggle="tab" data-bs-target="#nueva" type="button">
            <i class="bi bi-cart-plus me-1"></i> Nueva Orden
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="proveedores-tab" data-bs-toggle="tab" data-bs-target="#proveedores" type="button">
            <i class="bi bi-truck me-1"></i> Proveedores
        </button>
    </li>
</ul>

<div class="tab-content" id="comprasTabContent">
    <!-- Tab 1: Órdenes de Compra -->
    <div class="tab-pane fade show active" id="ordenes" role="tabpanel">
        <div class="card card-shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Descripción</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compras as $compra): 
                                $estado = 'PENDIENTE';
                                $badge_class = 'bg-warning';
                                if (strpos($compra->descripcion, '[RECIBIDA]') !== false) {
                                    $estado = 'RECIBIDA';
                                    $badge_class = 'bg-success';
                                }
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo $compra->numero_documento ?? 'N/A'; ?></strong>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($compra->created_at)); ?></td>
                                <td><?php echo htmlspecialchars($compra->usuario_nombre); ?></td>
                                <td><?php echo htmlspecialchars($compra->descripcion); ?></td>
                                <td class="fw-bold"><?php echo number_format($compra->total, 2); ?> CFA</td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo $estado; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="compras.php?action=view&id=<?php echo $compra->id_movimiento; ?>" 
                                           class="btn btn-outline-info" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($estado == 'PENDIENTE'): ?>
                                        <button class="btn btn-outline-success" 
                                                onclick="recibirCompra(<?php echo $compra->id_movimiento; ?>)" 
                                                title="Marcar como recibida">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($auth->checkPermission('administrador')): ?>
                                        <a href="compras.php?action=cancel&id=<?php echo $compra->id_movimiento; ?>" 
                                           class="btn btn-outline-danger" title="Cancelar"
                                           onclick="return confirmDelete('¿Cancelar esta orden?')">
                                            <i class="bi bi-x-circle"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab 2: Nueva Orden -->
    <div class="tab-pane fade" id="nueva" role="tabpanel">
        <div class="row">
            <div class="col-md-8">
                <!-- Lista de productos para comprar -->
                <div class="card card-shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-box me-2"></i> Productos para Comprar</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php foreach ($productos_compra as $prod): ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="card product-card-compra"
                                     onclick="agregarAOrden(<?php echo $prod->id_producto; ?>, '<?php echo addslashes($prod->nombre); ?>', <?php echo $prod->costo_promedio; ?>, <?php echo $prod->stock_actual; ?>, <?php echo $prod->stock_minimo; ?>)"
                                     style="cursor: pointer;">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($prod->nombre); ?></h6>
                                        <p class="card-text mb-1">
                                            <small class="text-muted">Categoría: <?php echo htmlspecialchars($prod->categoria_nombre ?? 'N/A'); ?></small>
                                        </p>
                                        <p class="card-text mb-1">
                                            <small>Stock: 
                                                <span class="<?php echo ($prod->stock_actual <= $prod->stock_minimo) ? 'text-danger fw-bold' : 'text-success'; ?>">
                                                    <?php echo $prod->stock_actual; ?>
                                                </span>
                                                / Mín: <?php echo $prod->stock_minimo; ?>
                                            </small>
                                        </p>
                                        <p class="card-text mb-0">
                                            <small>Costo: <?php echo number_format($prod->costo_promedio, 2); ?> CFA</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Formulario de orden -->
                <div class="card card-shadow sticky-top" style="top: 20px;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i> Nueva Orden de Compra</h5>
                    </div>
                    <div class="card-body">
                        <form id="formOrdenCompra" method="POST" action="">
                            <input type="hidden" id="items_json" name="items_json" value="[]">
                            
                            <div class="mb-3">
                                <label for="id_proveedor" class="form-label">Proveedor *</label>
                                <select class="form-select" id="id_proveedor" name="id_proveedor" required>
                                    <option value="">Seleccionar proveedor</option>
                                    <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?php echo $prov->id_proveedor; ?>">
                                        <?php echo htmlspecialchars($prov->nombre); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modalNuevoProveedor">
                                        <i class="bi bi-plus-circle"></i> Agregar nuevo proveedor
                                    </a>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="fecha_esperada" class="form-label">Fecha Esperada de Entrega</label>
                                <input type="date" class="form-control" id="fecha_esperada" name="fecha_esperada" 
                                       value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                          placeholder="Instrucciones especiales, condiciones de pago, etc."></textarea>
                            </div>
                            
                            <hr>
                            
                            <!-- Carrito de compra -->
                            <h6>Productos a Comprar</h6>
                            <div id="carritoCompra" class="mb-3">
                                <p class="text-muted text-center small">No hay productos en la orden</p>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-primary" onclick="crearOrdenCompra()">
                                    <i class="bi bi-check-circle me-1"></i> Crear Orden de Compra
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="vaciarOrden()">
                                    <i class="bi bi-trash me-1"></i> Vaciar Orden
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab 3: Proveedores -->
    <div class="tab-pane fade" id="proveedores" role="tabpanel">
        <div class="card card-shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-truck me-2"></i> Proveedores</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Dirección</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proveedores as $prov): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prov->nombre); ?></td>
                                <td><?php echo htmlspecialchars($prov->contacto ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($prov->telefono ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($prov->email ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(substr($prov->direccion ?? 'N/A', 0, 50)); ?>...</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" 
                                                onclick="editarProveedor(<?php echo $prov->id_proveedor; ?>)"
                                                title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="compras.php?action=delete_prov&id=<?php echo $prov->id_proveedor; ?>" 
                                           class="btn btn-outline-danger" title="Eliminar"
                                           onclick="return confirmDelete('¿Eliminar este proveedor?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoProveedor">
                        <i class="bi bi-plus-circle me-1"></i> Agregar Nuevo Proveedor
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nuevo proveedor -->
<div class="modal fade" id="modalNuevoProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="compras.php?action=add_proveedor">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Nuevo Proveedor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="prov_nombre" class="form-label">Nombre del Proveedor *</label>
                        <input type="text" class="form-control" id="prov_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="prov_contacto" class="form-label">Persona de Contacto</label>
                        <input type="text" class="form-control" id="prov_contacto" name="contacto">
                    </div>
                    <div class="mb-3">
                        <label for="prov_telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="prov_telefono" name="telefono">
                    </div>
                    <div class="mb-3">
                        <label for="prov_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="prov_email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="prov_direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="prov_direccion" name="direccion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" name="guardar_proveedor">
                        <i class="bi bi-save me-1"></i> Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variables para orden de compra
let ordenCompra = [];

function agregarAOrden(id, nombre, costo, stock, stock_minimo) {
    // Calcular cantidad sugerida (diferencia entre mínimo y actual)
    let cantidad_sugerida = stock_minimo - stock;
    if (cantidad_sugerida < 1) cantidad_sugerida = 1;
    
    // Verificar si ya está en la orden
    let index = ordenCompra.findIndex(item => item.id === id);
    
    if (index !== -1) {
        // Incrementar cantidad
        ordenCompra[index].cantidad++;
    } else {
        // Agregar nuevo item
        ordenCompra.push({
            id: id,
            nombre: nombre,
            costo: costo,
            cantidad: cantidad_sugerida,
            stock_actual: stock,
            stock_minimo: stock_minimo
        });
    }
    
    actualizarOrdenCompra();
}

function actualizarOrdenCompra() {
    const contenedor = document.getElementById('carritoCompra');
    let html = '';
    let total = 0;
    
    if (ordenCompra.length === 0) {
        html = '<p class="text-muted text-center small">No hay productos en la orden</p>';
    } else {
        html = '<table class="table table-sm">';
        html += '<thead><tr><th>Producto</th><th>Costo</th><th>Cant</th><th>Subtotal</th><th></th></tr></thead>';
        html += '<tbody>';
        
        ordenCompra.forEach((item, index) => {
            const subtotal = item.costo * item.cantidad;
            total += subtotal;
            
            html += `<tr>
                <td>
                    <small>${item.nombre}</small><br>
                    <small class="text-muted">Stock: ${item.stock_actual} / Mín: ${item.stock_minimo}</small>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           value="${item.costo.toFixed(2)}" step="0.01" min="0"
                           onchange="cambiarCosto(${index}, this.value)" style="width: 100px;">
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary btn-sm" onclick="modificarCantidadOrden(${index}, -1)">-</button>
                        <input type="number" class="form-control form-control-sm text-center" 
                               value="${item.cantidad}" min="1" style="width: 60px;"
                               onchange="cambiarCantidadOrden(${index}, this.value)">
                        <button class="btn btn-outline-secondary btn-sm" onclick="modificarCantidadOrden(${index}, 1)">+</button>
                    </div>
                </td>
                <td>${subtotal.toFixed(2)} CFA</td>
                <td>
                    <button class="btn btn-outline-danger btn-sm" onclick="eliminarDeOrden(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
        });
        
        html += '</tbody>';
        html += `<tfoot class="table-light">
            <tr class="table-success">
                <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                <td><strong>${total.toFixed(2)} CFA</strong></td>
                <td></td>
            </tr>
        </tfoot>`;
        html += '</table>';
    }
    
    contenedor.innerHTML = html;
    
    // Actualizar JSON hidden
    document.getElementById('items_json').value = JSON.stringify(ordenCompra);
}

function modificarCantidadOrden(index, cambio) {
    const nuevoValor = ordenCompra[index].cantidad + cambio;
    if (nuevoValor >= 1) {
        ordenCompra[index].cantidad = nuevoValor;
        actualizarOrdenCompra();
    }
}

function cambiarCantidadOrden(index, valor) {
    valor = parseInt(valor);
    if (valor >= 1) {
        ordenCompra[index].cantidad = valor;
        actualizarOrdenCompra();
    }
}

function cambiarCosto(index, valor) {
    valor = parseFloat(valor);
    if (valor >= 0) {
        ordenCompra[index].costo = valor;
        actualizarOrdenCompra();
    }
}

function eliminarDeOrden(index) {
    ordenCompra.splice(index, 1);
    actualizarOrdenCompra();
}

function vaciarOrden() {
    if (confirm('¿Vaciar toda la orden de compra?')) {
        ordenCompra = [];
        actualizarOrdenCompra();
    }
}

function crearOrdenCompra() {
    if (ordenCompra.length === 0) {
        alert('Agregue productos a la orden primero');
        return;
    }
    
    const proveedor = document.getElementById('id_proveedor').value;
    if (!proveedor) {
        alert('Seleccione un proveedor');
        return;
    }
    
    if (confirm('¿Crear orden de compra con ' + ordenCompra.length + ' productos?')) {
        document.getElementById('formOrdenCompra').submit();
    }
}

function recibirCompra(id_movimiento) {
    if (confirm('¿Marcar esta compra como recibida?\nSe actualizará el stock de los productos.')) {
        // Crear formulario dinámico
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id_movimiento';
        input.value = id_movimiento;
        
        const input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'recibir_compra';
        input2.value = '1';
        
        form.appendChild(input);
        form.appendChild(input2);
        document.body.appendChild(form);
        form.submit();
    }
}

function editarProveedor(id) {
    // Implementar edición de proveedor
    alert('Función de edición de proveedor - ID: ' + id);
}
</script>

<?php require_once 'includes/footer.php'; ?>