<?php
$page_title = "Gestión de Ventas";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('vendedor')) {
    header('Location: dashboard.php');
    exit();
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Procesar nueva venta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_venta'])) {
    $id_cliente = isset($_POST['id_cliente']) ? $_POST['id_cliente'] : null;
    $metodo_pago = isset($_POST['metodo_pago']) ? $_POST['metodo_pago'] : '';
    $items = isset($_POST['items_json']) ? json_decode($_POST['items_json'], true) : [];
    $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
    
    if (empty($items)) {
        $error = "Debe agregar al menos un producto a la venta";
    } else {
        // Calcular totales
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['cantidad'] * $item['precio'];
        }
        $iva = $subtotal * 0.15; // 15% de IVA
        $total = $subtotal + $iva;
        
        // Iniciar transacción
        try {
            // 1. Registrar movimiento de venta
            $db->query("INSERT INTO movimientos (id_empresa, tipo, descripcion, id_usuario, total) 
                       VALUES (:empresa_id, 'venta', :descripcion, :usuario_id, :total)");
            $db->bind(':empresa_id', $_SESSION['empresa_id']);
            $db->bind(':descripcion', "Venta #" . date('YmdHis') . ($id_cliente ? " - Cliente: $id_cliente" : ""));
            $db->bind(':usuario_id', $_SESSION['user_id']);
            $db->bind(':total', $total);
            $db->execute();
            
            $id_movimiento = $db->lastInsertId();
            
            // 2. Registrar detalles y actualizar stock
            foreach ($items as $item) {
                // Registrar detalle
                $db->query("INSERT INTO detalle_movimiento (id_movimiento, id_producto, cantidad, precio_unitario) 
                           VALUES (:movimiento_id, :producto_id, :cantidad, :precio)");
                $db->bind(':movimiento_id', $id_movimiento);
                $db->bind(':producto_id', $item['id']);
                $db->bind(':cantidad', $item['cantidad']);
                $db->bind(':precio', $item['precio']);
                $db->execute();
                
                // Actualizar stock
                $db->query("UPDATE productos SET stock_actual = stock_actual - :cantidad 
                           WHERE id_producto = :producto_id AND stock_actual >= :cantidad");
                $db->bind(':producto_id', $item['id']);
                $db->bind(':cantidad', $item['cantidad']);
                if (!$db->execute()) {
                    throw new Exception("Stock insuficiente para el producto ID: " . $item['id']);
                }
            }
            
            // 3. Generar número de factura
            $numero_factura = "FAC-" . date('Y') . "-" . str_pad($id_movimiento, 6, '0', STR_PAD_LEFT);
            $db->query("UPDATE movimientos SET numero_documento = :numero WHERE id_movimiento = :id");
            $db->bind(':numero', $numero_factura);
            $db->bind(':id', $id_movimiento);
            $db->execute();
            
            $success = "Venta registrada exitosamente. Factura: " . $numero_factura;
            header('Location: ventas.php?success=' . urlencode($success) . '&factura=' . $id_movimiento);
            exit();
            
        } catch (Exception $e) {
            $error = "Error al registrar la venta: " . $e->getMessage();
        }
    }
}

// Ver detalle de venta
if ($action == 'view' && $id > 0) {
    $db->query("SELECT m.*, u.nombre as vendedor FROM movimientos m 
               JOIN usuarios u ON m.id_usuario = u.id_usuario 
               WHERE m.id_movimiento = :id AND m.id_empresa = :empresa_id AND m.tipo = 'venta'");
    $db->bind(':id', $id);
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $venta = $db->single();
    
    if ($venta) {
        $db->query("SELECT dm.*, p.nombre as producto_nombre, p.codigo_barras 
                   FROM detalle_movimiento dm 
                   JOIN productos p ON dm.id_producto = p.id_producto 
                   WHERE dm.id_movimiento = :id");
        $db->bind(':id', $id);
        $detalles = $db->resultSet();
    }
}

// Obtener ventas recientes
$db->query("SELECT m.*, u.nombre as vendedor 
           FROM movimientos m 
           JOIN usuarios u ON m.id_usuario = u.id_usuario 
           WHERE m.id_empresa = :empresa_id AND m.tipo = 'venta' 
           ORDER BY m.created_at DESC 
           LIMIT 20");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$ventas = $db->resultSet();

// Obtener productos para venta
$db->query("SELECT id_producto, codigo_barras, nombre, precio_venta, stock_actual 
           FROM productos 
           WHERE id_empresa = :empresa_id AND activo = 1 AND stock_actual > 0 
           ORDER BY nombre");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$productos_venta = $db->resultSet();

// Obtener clientes
$db->query("SELECT * FROM clientes WHERE id_empresa = :empresa_id ORDER BY nombre");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$clientes = $db->resultSet();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Ventas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalNuevaVenta">
            <i class="bi bi-cart-plus me-1"></i> Nueva Venta
        </button>
        <a href="ventas.php?action=report" class="btn btn-success">
            <i class="bi bi-printer me-1"></i> Imprimir Reporte
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_GET['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    <?php if (isset($_GET['factura'])): ?>
    <div class="mt-2">
        <a href="ventas.php?action=view&id=<?php echo $_GET['factura']; ?>" class="btn btn-sm btn-success">
            <i class="bi bi-eye me-1"></i> Ver Factura
        </a>
        <a href="ventas.php?action=print&id=<?php echo $_GET['factura']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
            <i class="bi bi-printer me-1"></i> Imprimir
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Estadísticas de ventas -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <?php
                $db->query("SELECT COUNT(*) as total FROM movimientos 
                           WHERE id_empresa = :empresa_id AND tipo = 'venta' AND DATE(created_at) = CURDATE()");
                $db->bind(':empresa_id', $_SESSION['empresa_id']);
                $ventas_hoy = $db->single()->total;
                ?>
                <h6 class="card-subtitle mb-2">Ventas Hoy</h6>
                <h3 class="card-title"><?php echo $ventas_hoy; ?></h3>
                <p class="card-text">Transacciones del día</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <?php
                $db->query("SELECT SUM(total) as total FROM movimientos 
                           WHERE id_empresa = :empresa_id AND tipo = 'venta' AND DATE(created_at) = CURDATE()");
                $db->bind(':empresa_id', $_SESSION['empresa_id']);
                $total_hoy = $db->single()->total ?? 0;
                ?>
                <h6 class="card-subtitle mb-2">Ingresos Hoy</h6>
                <h3 class="card-title"><?php echo number_format($total_hoy, 2); ?> CFA</h3>
                <p class="card-text">Total del día</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <?php
                $db->query("SELECT SUM(total) as total FROM movimientos 
                           WHERE id_empresa = :empresa_id AND tipo = 'venta' 
                           AND MONTH(created_at) = MONTH(CURDATE())");
                $db->bind(':empresa_id', $_SESSION['empresa_id']);
                $total_mes = $db->single()->total ?? 0;
                ?>
                <h6 class="card-subtitle mb-2">Ingresos Mes</h6>
                <h3 class="card-title"><?php echo number_format($total_mes, 2); ?> CFA</h3>
                <p class="card-text">Total del mes actual</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <?php
                $db->query("SELECT AVG(total) as promedio FROM movimientos 
                           WHERE id_empresa = :empresa_id AND tipo = 'venta'");
                $db->bind(':empresa_id', $_SESSION['empresa_id']);
                $promedio = $db->single()->promedio ?? 0;
                ?>
                <h6 class="card-subtitle mb-2">Ticket Promedio</h6>
                <h3 class="card-title"><?php echo number_format($promedio, 2); ?> CFA</h3>
                <p class="card-text">Por transacción</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="ventasTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="historial-ventas-tab" data-bs-toggle="tab" data-bs-target="#historial-ventas" type="button">
            <i class="bi bi-list-check me-1"></i> Historial de Ventas
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="punto-venta-tab" data-bs-toggle="tab" data-bs-target="#punto-venta" type="button">
            <i class="bi bi-cash-register me-1"></i> Punto de Venta
        </button>
    </li>
    <?php if ($action == 'view' && isset($venta)): ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="detalle-venta-tab" data-bs-toggle="tab" data-bs-target="#detalle-venta" type="button">
            <i class="bi bi-receipt me-1"></i> Factura #<?php echo $venta->numero_documento ?? 'N/A'; ?>
        </button>
    </li>
    <?php endif; ?>
</ul>

<div class="tab-content" id="ventasTabContent">
    <!-- Tab 1: Historial de Ventas -->
    <div class="tab-pane fade show active" id="historial-ventas" role="tabpanel">
        <div class="card card-shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Factura</th>
                                <th>Fecha</th>
                                <th>Vendedor</th>
                                <th>Descripción</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $v): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $v->numero_documento ?? 'PENDIENTE'; ?></strong>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($v->created_at)); ?></td>
                                <td><?php echo htmlspecialchars($v->vendedor); ?></td>
                                <td><?php echo htmlspecialchars($v->descripcion); ?></td>
                                <td class="fw-bold text-success"><?php echo number_format($v->total, 2); ?> CFA</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="ventas.php?action=view&id=<?php echo $v->id_movimiento; ?>" 
                                           class="btn btn-outline-info" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="ventas.php?action=print&id=<?php echo $v->id_movimiento; ?>" 
                                           class="btn btn-outline-primary" title="Imprimir" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <?php if ($auth->checkPermission('administrador')): ?>
                                        <a href="ventas.php?action=anular&id=<?php echo $v->id_movimiento; ?>" 
                                           class="btn btn-outline-danger" title="Anular"
                                           onclick="return confirmDelete('¿Anular esta venta?')">
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
    
    <!-- Tab 2: Punto de Venta -->
    <div class="tab-pane fade" id="punto-venta" role="tabpanel">
        <div class="row">
            <div class="col-md-8">
                <!-- Lista de productos -->
                <div class="card card-shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-box me-2"></i> Productos Disponibles</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php foreach ($productos_venta as $prod): ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="card product-card" 
                                     onclick="agregarAlCarrito(<?php echo $prod->id_producto; ?>, '<?php echo addslashes($prod->nombre); ?>', <?php echo $prod->precio_venta; ?>, <?php echo $prod->stock_actual; ?>)"
                                     style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <h6 class="card-title"><?php echo htmlspecialchars($prod->nombre); ?></h6>
                                        <p class="card-text text-success fw-bold">
                                            <?php echo number_format($prod->precio_venta, 2); ?> CFA
                                        </p>
                                        <small class="text-muted">
                                            Stock: <?php echo $prod->stock_actual; ?> |
                                            <?php echo $prod->codigo_barras ?? 'Sin código'; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Carrito de venta -->
                <div class="card card-shadow sticky-top" style="top: 20px;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-cart me-2"></i> Carrito de Venta</h5>
                    </div>
                    <div class="card-body">
                        <div id="carritoVenta">
                            <p class="text-muted text-center">No hay productos en el carrito</p>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label for="id_cliente" class="form-label">Cliente (opcional)</label>
                            <select class="form-select" id="id_cliente" name="id_cliente">
                                <option value="">Consumidor Final</option>
                                <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente->id_cliente; ?>">
                                    <?php echo htmlspecialchars($cliente->nombre); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="metodo_pago" class="form-label">Método de Pago</label>
                            <select class="form-select" id="metodo_pago" name="metodo_pago">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="mixto">Mixto</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" onclick="finalizarVenta()">
                                <i class="bi bi-check-circle me-1"></i> Finalizar Venta
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="vaciarCarrito()">
                                <i class="bi bi-trash me-1"></i> Vaciar Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab 3: Detalle de Venta (si está viendo una factura) -->
    <?php if ($action == 'view' && isset($venta)): ?>
    <div class="tab-pane fade" id="detalle-venta" role="tabpanel">
        <div class="card card-shadow">
            <div class="card-body">
                <!-- Encabezado de factura -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>FACTURA DE VENTA</h4>
                        <p class="mb-1"><strong>Número:</strong> <?php echo $venta->numero_documento; ?></p>
                        <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($venta->created_at)); ?></p>
                        <p class="mb-1"><strong>Vendedor:</strong> <?php echo htmlspecialchars($venta->vendedor); ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h1 class="text-success"><?php echo number_format($venta->total, 2); ?> CFA</h1>
                        <p class="text-muted">Total de la factura</p>
                    </div>
                </div>
                
                <!-- Detalle de productos -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $subtotal = 0;
                            foreach ($detalles as $index => $det): 
                                $subtotal_item = $det->cantidad * $det->precio_unitario;
                                $subtotal += $subtotal_item;
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($det->producto_nombre); ?>
                                    <?php if ($det->codigo_barras): ?>
                                    <br><small class="text-muted">Código: <?php echo $det->codigo_barras; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $det->cantidad; ?></td>
                                <td><?php echo number_format($det->precio_unitario, 2); ?> CFA</td>
                                <td><?php echo number_format($subtotal_item, 2); ?> CFA</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                <td><strong><?php echo number_format($subtotal, 2); ?> CFA</strong></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>IVA (15%):</strong></td>
                                <td><strong><?php echo number_format($venta->total - $subtotal, 2); ?> CFA</strong></td>
                            </tr>
                            <tr class="table-success">
                                <td colspan="4" class="text-end"><strong>TOTAL:</strong></td>
                                <td><strong><?php echo number_format($venta->total, 2); ?> CFA</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="text-center">
                    <button class="btn btn-primary me-2" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Imprimir Factura
                    </button>
                    <a href="ventas.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Volver al listado
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal para nueva venta (formulario oculto) -->
<div class="modal fade" id="modalNuevaVenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="" id="formVenta">
                <input type="hidden" id="items_json" name="items_json" value="[]">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Confirmar Venta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="resumenVenta">
                        <!-- Se llena con JavaScript -->
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Revise los detalles antes de confirmar la venta.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" name="registrar_venta">
                        <i class="bi bi-check-circle me-1"></i> Confirmar Venta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variables globales
let carrito = [];

function agregarAlCarrito(id, nombre, precio, stock) {
    // Verificar si ya está en el carrito
    let index = carrito.findIndex(item => item.id === id);
    
    if (index !== -1) {
        // Incrementar cantidad si hay stock
        if (carrito[index].cantidad < stock) {
            carrito[index].cantidad++;
        } else {
            alert('No hay suficiente stock disponible');
            return;
        }
    } else {
        // Agregar nuevo item
        carrito.push({
            id: id,
            nombre: nombre,
            precio: precio,
            cantidad: 1,
            stock: stock
        });
    }
    
    actualizarCarrito();
}

function actualizarCarrito() {
    const contenedor = document.getElementById('carritoVenta');
    let html = '';
    let total = 0;
    
    if (carrito.length === 0) {
        html = '<p class="text-muted text-center">No hay productos en el carrito</p>';
    } else {
        html = '<table class="table table-sm">';
        html += '<thead><tr><th>Producto</th><th>Precio</th><th>Cant</th><th>Subtotal</th><th></th></tr></thead>';
        html += '<tbody>';
        
        carrito.forEach((item, index) => {
            const subtotal = item.precio * item.cantidad;
            total += subtotal;
            
            html += `<tr>
                <td>${item.nombre}</td>
                <td>${item.precio.toFixed(2)} CFA</td>
                <td>
                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary btn-sm" onclick="modificarCantidad(${index}, -1)">-</button>
                        <input type="number" class="form-control form-control-sm text-center" 
                               value="${item.cantidad}" min="1" max="${item.stock}" 
                               onchange="cambiarCantidad(${index}, this.value)" style="width: 60px;">
                        <button class="btn btn-outline-secondary btn-sm" onclick="modificarCantidad(${index}, 1)">+</button>
                    </div>
                </td>
                <td>${subtotal.toFixed(2)} CFA</td>
                <td>
                    <button class="btn btn-outline-danger btn-sm" onclick="eliminarDelCarrito(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
        });
        
        const iva = total * 0.15;
        const totalConIva = total + iva;
        
        html += '</tbody>';
        html += `<tfoot class="table-light">
            <tr>
                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                <td><strong>${total.toFixed(2)} CFA</strong></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="3" class="text-end"><strong>IVA (15%):</strong></td>
                <td><strong>${iva.toFixed(2)} CFA</strong></td>
                <td></td>
            </tr>
            <tr class="table-success">
                <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                <td><strong>${totalConIva.toFixed(2)} CFA</strong></td>
                <td></td>
            </tr>
        </tfoot>`;
        html += '</table>';
    }
    
    contenedor.innerHTML = html;
}

function modificarCantidad(index, cambio) {
    const nuevoValor = carrito[index].cantidad + cambio;
    if (nuevoValor >= 1 && nuevoValor <= carrito[index].stock) {
        carrito[index].cantidad = nuevoValor;
        actualizarCarrito();
    }
}

function cambiarCantidad(index, valor) {
    valor = parseInt(valor);
    if (valor >= 1 && valor <= carrito[index].stock) {
        carrito[index].cantidad = valor;
        actualizarCarrito();
    } else {
        // Restaurar valor anterior
        actualizarCarrito();
    }
}

function eliminarDelCarrito(index) {
    carrito.splice(index, 1);
    actualizarCarrito();
}

function vaciarCarrito() {
    if (confirm('¿Vaciar todo el carrito?')) {
        carrito = [];
        actualizarCarrito();
    }
}

function finalizarVenta() {
    if (carrito.length === 0) {
        alert('Agregue productos al carrito primero');
        return;
    }
    
    // Actualizar JSON hidden
    document.getElementById('items_json').value = JSON.stringify(carrito);
    
    // Mostrar resumen en modal
    let resumen = '<h5>Resumen de Venta</h5>';
    resumen += '<table class="table table-sm">';
    resumen += '<thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead>';
    resumen += '<tbody>';
    
    let total = 0;
    carrito.forEach(item => {
        const subtotal = item.precio * item.cantidad;
        total += subtotal;
        resumen += `<tr>
            <td>${item.nombre}</td>
            <td>${item.cantidad}</td>
            <td>${item.precio.toFixed(2)} CFA</td>
            <td>${subtotal.toFixed(2)} CFA</td>
        </tr>`;
    });
    
    const iva = total * 0.15;
    const totalConIva = total + iva;
    
    resumen += '</tbody>';
    resumen += `<tfoot class="table-light">
        <tr>
            <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
            <td><strong>${total.toFixed(2)} CFA</strong></td>
        </tr>
        <tr>
            <td colspan="3" class="text-end"><strong>IVA (15%):</strong></td>
            <td><strong>${iva.toFixed(2)} CFA</strong></td>
        </tr>
        <tr class="table-success">
            <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
            <td><strong>${totalConIva.toFixed(2)} CFA</strong></td>
        </tr>
    </tfoot>`;
    resumen += '</table>';
    
    document.getElementById('resumenVenta').innerHTML = resumen;
    
    // Copiar datos del formulario al modal
    const cliente = document.getElementById('id_cliente').value;
    const metodo = document.getElementById('metodo_pago').value;
    const observaciones = document.getElementById('observaciones').value;
    
    resumen += `<div class="mt-3">
        <p><strong>Cliente:</strong> ${cliente || 'Consumidor Final'}</p>
        <p><strong>Método de Pago:</strong> ${metodo.toUpperCase()}</p>
        <p><strong>Observaciones:</strong> ${observaciones || 'Ninguna'}</p>
    </div>`;
    
    document.getElementById('resumenVenta').innerHTML = resumen;
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalNuevaVenta'));
    modal.show();
}
</script>

<?php require_once 'includes/footer.php'; ?>