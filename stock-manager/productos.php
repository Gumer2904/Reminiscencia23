<?php
$page_title = "Gestión de Productos";
require_once 'includes/header.php';

// Procesar acciones
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Crear nuevo producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_producto'])) {
    $data = [
        'codigo_barras' => trim($_POST['codigo_barras']),
        'nombre' => trim($_POST['nombre']),
        'descripcion' => trim($_POST['descripcion']),
        'id_categoria' => $_POST['id_categoria'] ?: null,
        'precio_venta' => floatval($_POST['precio_venta']),
        'costo_promedio' => floatval($_POST['costo_promedio']),
        'stock_actual' => intval($_POST['stock_actual']),
        'stock_minimo' => intval($_POST['stock_minimo']),
        'tiene_iva' => isset($_POST['tiene_iva']) ? 1 : 0
    ];
    
    $db->query("INSERT INTO productos (id_empresa, codigo_barras, nombre, descripcion, id_categoria, 
                precio_venta, costo_promedio, stock_actual, stock_minimo, tiene_iva) 
                VALUES (:empresa_id, :codigo_barras, :nombre, :descripcion, :id_categoria, 
                :precio_venta, :costo_promedio, :stock_actual, :stock_minimo, :tiene_iva)");
    
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    foreach ($data as $key => $value) {
        $db->bind(':' . $key, $value);
    }
    
    if ($db->execute()) {
        $success = "Producto creado exitosamente";
        header('Location: productos.php?success=' . urlencode($success));
        exit();
    } else {
        $error = "Error al crear el producto";
    }
}

// Eliminar producto
if ($action == 'delete' && $id > 0) {
    $db->query("DELETE FROM productos WHERE id_producto = :id AND id_empresa = :empresa_id");
    $db->bind(':id', $id);
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    
    if ($db->execute()) {
        $success = "Producto eliminado exitosamente";
        header('Location: productos.php?success=' . urlencode($success));
        exit();
    } else {
        $error = "Error al eliminar el producto";
    }
}

// Obtener productos
$db->query("SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
            WHERE p.id_empresa = :empresa_id 
            ORDER BY p.nombre");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$productos = $db->resultSet();

// Obtener categorías para el select
$db->query("SELECT * FROM categorias WHERE id_empresa = :empresa_id ORDER BY nombre");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$categorias = $db->resultSet();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Productos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Producto
        </button>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_GET['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card card-shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio Venta</th>
                        <th>Costo</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto->codigo_barras ?? 'N/A'); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($producto->nombre); ?></strong>
                            <?php if ($producto->descripcion): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($producto->descripcion, 0, 50)); ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($producto->categoria_nombre ?? 'Sin categoría'); ?></td>
                        <td class="text-success fw-bold"><?php echo number_format($producto->precio_venta, 2); ?> CFA</td>
                        <td><?php echo number_format($producto->costo_promedio, 2); ?> CFA</td>
                        <td>
                            <span class="badge 
                                <?php 
                                if ($producto->stock_actual == 0) echo 'bg-danger';
                                elseif ($producto->stock_actual <= $producto->stock_minimo) echo 'bg-warning';
                                else echo 'bg-success';
                                ?>">
                                <?php echo $producto->stock_actual; ?>
                            </span>
                            / <?php echo $producto->stock_minimo; ?>
                        </td>
                        <td>
                            <?php if ($producto->activo): ?>
                            <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="productos.php?action=edit&id=<?php echo $producto->id_producto; ?>" 
                                   class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="productos.php?action=view&id=<?php echo $producto->id_producto; ?>" 
                                   class="btn btn-outline-info" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="productos.php?action=delete&id=<?php echo $producto->id_producto; ?>" 
                                   class="btn btn-outline-danger" 
                                   onclick="return confirmDelete('¿Eliminar este producto?')" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para nuevo producto -->
<div class="modal fade" id="modalNuevoProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="codigo_barras" class="form-label">Código de Barras</label>
                            <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" 
                                   placeholder="Opcional - Escanear código">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre del Producto *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_categoria" class="form-label">Categoría</label>
                            <select class="form-select" id="id_categoria" name="id_categoria">
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria->id_categoria; ?>">
                                    <?php echo htmlspecialchars($categoria->nombre); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="stock_actual" class="form-label">Stock Inicial</label>
                            <input type="number" class="form-control" id="stock_actual" name="stock_actual" 
                                   value="0" min="0" step="1">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                            <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" 
                                   value="5" min="1" step="1">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="precio_venta" class="form-label">Precio de Venta *</label>
                            <div class="input-group">
                                <span class="input-group-text">CFA</span>
                                <input type="number" class="form-control" id="precio_venta" name="precio_venta" 
                                       required step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="costo_promedio" class="form-label">Costo Promedio</label>
                            <div class="input-group">
                                <span class="input-group-text">CFA</span>
                                <input type="number" class="form-control" id="costo_promedio" name="costo_promedio" 
                                       value="0" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="tiene_iva" name="tiene_iva" checked>
                        <label class="form-check-label" for="tiene_iva">Incluye IVA</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" name="crear_producto">
                        <i class="bi bi-save me-1"></i> Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>