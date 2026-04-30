<?php
$page_title = "Gestión de Categorías";
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

// Crear categoría
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_categoria'])) {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    
    if (empty($nombre)) {
        $error = "El nombre de la categoría es obligatorio";
    } else {
        $db->query("INSERT INTO categorias (id_empresa, nombre, descripcion) 
                   VALUES (:empresa_id, :nombre, :descripcion)");
        $db->bind(':empresa_id', $_SESSION['empresa_id']);
        $db->bind(':nombre', $nombre);
        $db->bind(':descripcion', $descripcion);
        
        if ($db->execute()) {
            $success = "Categoría creada exitosamente";
            header('Location: categorias.php?success=' . urlencode($success));
            exit();
        } else {
            $error = "Error al crear la categoría";
        }
    }
}

// Actualizar categoría
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_categoria'])) {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    
    if (empty($nombre)) {
        $error = "El nombre de la categoría es obligatorio";
    } else {
        $db->query("UPDATE categorias SET nombre = :nombre, descripcion = :descripcion 
                   WHERE id_categoria = :id AND id_empresa = :empresa_id");
        $db->bind(':id', $id);
        $db->bind(':empresa_id', $_SESSION['empresa_id']);
        $db->bind(':nombre', $nombre);
        $db->bind(':descripcion', $descripcion);
        
        if ($db->execute()) {
            $success = "Categoría actualizada exitosamente";
            header('Location: categorias.php?success=' . urlencode($success));
            exit();
        } else {
            $error = "Error al actualizar la categoría";
        }
    }
}

// Eliminar categoría
if ($action == 'delete' && $id > 0) {
    // Verificar si hay productos usando esta categoría
    $db->query("SELECT COUNT(*) as total FROM productos 
               WHERE id_categoria = :id AND id_empresa = :empresa_id");
    $db->bind(':id', $id);
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $productos = $db->single()->total;
    
    if ($productos > 0) {
        $error = "No se puede eliminar la categoría porque tiene productos asociados";
    } else {
        $db->query("DELETE FROM categorias WHERE id_categoria = :id AND id_empresa = :empresa_id");
        $db->bind(':id', $id);
        $db->bind(':empresa_id', $_SESSION['empresa_id']);
        
        if ($db->execute()) {
            $success = "Categoría eliminada exitosamente";
            header('Location: categorias.php?success=' . urlencode($success));
            exit();
        } else {
            $error = "Error al eliminar la categoría";
        }
    }
}

// Obtener categorías
$db->query("SELECT c.*, 
           (SELECT COUNT(*) FROM productos p WHERE p.id_categoria = c.id_categoria) as total_productos
           FROM categorias c 
           WHERE c.id_empresa = :empresa_id 
           ORDER BY c.nombre");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$categorias = $db->resultSet();

// Obtener categoría para editar
$categoria_edit = null;
if ($action == 'edit' && $id > 0) {
    $db->query("SELECT * FROM categorias WHERE id_categoria = :id AND id_empresa = :empresa_id");
    $db->bind(':id', $id);
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $categoria_edit = $db->single();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Categorías</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria">
            <i class="bi bi-plus-circle me-1"></i> Nueva Categoría
        </button>
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

<div class="row">
    <div class="col-md-12">
        <div class="card card-shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Productos</th>
                                <th>Creada</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $index => $categoria): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($categoria->nombre); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($categoria->descripcion ?: 'Sin descripción'); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $categoria->total_productos; ?> productos
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($categoria->created_at)); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="categorias.php?action=edit&id=<?php echo $categoria->id_categoria; ?>" 
                                           class="btn btn-outline-primary" title="Editar" data-bs-toggle="modal" 
                                           data-bs-target="#modalEditarCategoria"
                                           onclick="cargarCategoria(<?php echo $categoria->id_categoria; ?>, '<?php echo addslashes($categoria->nombre); ?>', '<?php echo addslashes($categoria->descripcion); ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="categorias.php?action=delete&id=<?php echo $categoria->id_categoria; ?>" 
                                           class="btn btn-outline-danger" 
                                           onclick="return confirmDelete('¿Eliminar esta categoría?')" title="Eliminar">
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
    </div>
</div>

<!-- Modal para nueva categoría -->
<div class="modal fade" id="modalNuevaCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Nueva Categoría</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required 
                               placeholder="Ej: Bebidas, Limpieza, Básicos">
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                  placeholder="Descripción opcional de la categoría"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" name="crear_categoria">
                        <i class="bi bi-save me-1"></i> Guardar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar categoría -->
<div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" id="edit_id" name="id" value="">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i> Editar Categoría</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nombre" class="form-label">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-white" name="actualizar_categoria">
                        <i class="bi bi-save me-1"></i> Actualizar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cargarCategoria(id, nombre, descripcion) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_descripcion').value = descripcion;
}
</script>

<?php require_once 'includes/footer.php'; ?>