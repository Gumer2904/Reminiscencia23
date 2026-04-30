<?php
$page_title = "Gestión de Usuarios";
require_once 'includes/header.php';

// Verificar permisos - solo administradores
if (!$auth->checkPermission('administrador')) {
    header('Location: dashboard.php');
    exit();
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Crear nuevo usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_usuario'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $rol = $_POST['rol'];
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = "Todos los campos obligatorios deben ser completados";
    } elseif ($password !== $password_confirm) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        // Verificar si el email ya existe
        $db->query("SELECT id_usuario FROM usuarios WHERE email = :email AND id_empresa = :empresa_id");
        $db->bind(':email', $email);
        $db->bind(':empresa_id', $_SESSION['empresa_id']);
        $existe = $db->single();
        
        if ($existe) {
            $error = "El email ya está registrado en el sistema";
        } else {
            // Crear usuario
            $db->query("INSERT INTO usuarios (id_empresa, nombre, email, password, rol, telefono, direccion) 
                       VALUES (:empresa_id, :nombre, :email, :password, :rol, :telefono, :direccion)");
            $db->bind(':empresa_id', $_SESSION['empresa_id']);
            $db->bind(':nombre', $nombre);
            $db->bind(':email', $email);
            $db->bind(':password', password_hash($password, PASSWORD_DEFAULT));
            $db->bind(':rol', $rol);
            $db->bind(':telefono', $telefono);
            $db->bind(':direccion', $direccion);
            
            if ($db->execute()) {
                $id_nuevo = $db->lastInsertId();
                
                // Registrar en logs
                registrarLog('creacion_usuario', "Usuario creado: $nombre ($email) con rol: $rol");
                
                $success = "Usuario creado exitosamente";
                header('Location: usuarios.php?success=' . urlencode($success));
                exit();
            } else {
                $error = "Error al crear el usuario";
            }
        }
    }
}

// Actualizar usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_usuario'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $rol = $_POST['rol'];
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    $id_usuario = $_POST['id_usuario'];
    
    // Verificar permisos (no puede desactivarse a sí mismo)
    if ($id_usuario == $_SESSION['user_id'] && !$activo) {
        $error = "No puede desactivar su propio usuario";
    } else {
        // Actualizar usuario
        $db->query("UPDATE usuarios SET 
                   nombre = :nombre,
                   email = :email,
                   rol = :rol,
                   telefono = :telefono,
                   direccion = :direccion,
                   activo = :activo
                   WHERE id_usuario = :id AND id_empresa = :empresa_id");
        
        $db->bind(':id', $id_usuario);
        $db->bind(':empresa_id', $_SESSION['empresa_id']);
        $db->bind(':nombre', $nombre);
        $db->bind(':email', $email);
        $db->bind(':rol', $rol);
        $db->bind(':telefono', $telefono);
        $db->bind(':direccion', $direccion);
        $db->bind(':activo', $activo);
        
        if ($db->execute()) {
            // Si se actualiza la contraseña
            if (!empty($_POST['nueva_password'])) {
                $nueva_password = $_POST['nueva_password'];
                if (strlen($nueva_password) >= 6) {
                    $db->query("UPDATE usuarios SET password = :password WHERE id_usuario = :id");
                    $db->bind(':id', $id_usuario);
                    $db->bind(':password', password_hash($nueva_password, PASSWORD_DEFAULT));
                    $db->execute();
                }
            }
            
            registrarLog('actualizacion_usuario', "Usuario actualizado: $nombre ($email)");
            $success = "Usuario actualizado exitosamente";
            header('Location: usuarios.php?success=' . urlencode($success));
            exit();
        } else {
            $error = "Error al actualizar el usuario";
        }
    }
}

// Cambiar estado de usuario (activar/desactivar)
if ($action == 'toggle' && $id > 0) {
    if ($id == $_SESSION['user_id']) {
        $error = "No puede cambiar el estado de su propio usuario";
    } else {
        // Obtener estado actual
        $db->query("SELECT activo FROM usuarios WHERE id_usuario = :id AND id_empresa = :empresa_id");
        $db->bind(':id', $id);
        $db->bind(':empresa_id', $_SESSION['empresa_id']);
        $usuario = $db->single();
        
        if ($usuario) {
            $nuevo_estado = $usuario->activo ? 0 : 1;
            
            $db->query("UPDATE usuarios SET activo = :activo WHERE id_usuario = :id");
            $db->bind(':id', $id);
            $db->bind(':activo', $nuevo_estado);
            
            if ($db->execute()) {
                $estado_text = $nuevo_estado ? 'activado' : 'desactivado';
                registrarLog('cambio_estado_usuario', "Usuario ID $id $estado_text");
                $success = "Usuario $estado_text exitosamente";
                header('Location: usuarios.php?success=' . urlencode($success));
                exit();
            }
        }
    }
}

// Eliminar usuario
if ($action == 'delete' && $id > 0) {
    if ($id == $_SESSION['user_id']) {
        $error = "No puede eliminar su propio usuario";
    } else {
        $db->query("DELETE FROM usuarios WHERE id_usuario = :id AND id_empresa = :empresa_id");
        $db->bind(':id', $id);
        $db->bind(':empresa_id', $_SESSION['empresa_id']);
        
        if ($db->execute()) {
            registrarLog('eliminacion_usuario', "Usuario ID $id eliminado");
            $success = "Usuario eliminado exitosamente";
            header('Location: usuarios.php?success=' . urlencode($success));
            exit();
        } else {
            $error = "Error al eliminar el usuario";
        }
    }
}

// Obtener todos los usuarios
$db->query("SELECT * FROM usuarios WHERE id_empresa = :empresa_id ORDER BY nombre");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$usuarios = $db->resultSet();

// Obtener usuario para editar
$usuario_edit = null;
if ($action == 'edit' && $id > 0) {
    $db->query("SELECT * FROM usuarios WHERE id_usuario = :id AND id_empresa = :empresa_id");
    $db->bind(':id', $id);
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $usuario_edit = $db->single();
}

// Obtener logs del sistema
$db->query("SELECT l.*, u.nombre as usuario_nombre 
           FROM logs_sistema l 
           LEFT JOIN usuarios u ON l.id_usuario = u.id_usuario 
           WHERE l.id_empresa = :empresa_id 
           ORDER BY l.created_at DESC 
           LIMIT 50");
$db->bind(':empresa_id', $_SESSION['empresa_id']);
$logs = $db->resultSet();

// Función para registrar logs
function registrarLog($accion, $descripcion) {
    global $db, $_SESSION;
    
    $db->query("INSERT INTO logs_sistema (id_empresa, id_usuario, accion, descripcion, ip_address, user_agent) 
               VALUES (:empresa_id, :usuario_id, :accion, :descripcion, :ip, :user_agent)");
    
    $db->bind(':empresa_id', $_SESSION['empresa_id']);
    $db->bind(':usuario_id', $_SESSION['user_id']);
    $db->bind(':accion', $accion);
    $db->bind(':descripcion', $descripcion);
    $db->bind(':ip', $_SERVER['REMOTE_ADDR']);
    $db->bind(':user_agent', $_SERVER['HTTP_USER_AGENT']);
    $db->execute();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Usuarios</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
            <i class="bi bi-person-plus me-1"></i> Nuevo Usuario
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

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="usuariosTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="lista-tab" data-bs-toggle="tab" data-bs-target="#lista" type="button">
            <i class="bi bi-people me-1"></i> Lista de Usuarios
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles" type="button">
            <i class="bi bi-shield-check me-1"></i> Permisos y Roles
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button">
            <i class="bi bi-journal-text me-1"></i> Logs del Sistema
        </button>
    </li>
</ul>

<div class="tab-content" id="usuariosTabContent">
    <!-- Tab 1: Lista de Usuarios -->
    <div class="tab-pane fade show active" id="lista" role="tabpanel">
        <div class="card card-shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>Último Login</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $index => $user): 
                                $badge_class = '';
                                switch ($user->rol) {
                                    case 'administrador': $badge_class = 'bg-danger'; break;
                                    case 'encargado': $badge_class = 'bg-warning'; break;
                                    case 'vendedor': $badge_class = 'bg-primary'; break;
                                    case 'consulta': $badge_class = 'bg-info'; break;
                                }
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user->nombre); ?></strong>
                                    <?php if ($user->id_usuario == $_SESSION['user_id']): ?>
                                    <span class="badge bg-success">Tú</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user->email); ?></td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ucfirst($user->rol); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user->telefono ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($user->activo): ?>
                                    <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user->ultimo_login): ?>
                                    <?php echo date('d/m/Y H:i', strtotime($user->ultimo_login)); ?>
                                    <?php else: ?>
                                    <span class="text-muted">Nunca</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" 
                                                onclick="editarUsuario(<?php echo $user->id_usuario; ?>, '<?php echo addslashes($user->nombre); ?>', '<?php echo addslashes($user->email); ?>', '<?php echo $user->rol; ?>', '<?php echo addslashes($user->telefono ?? ''); ?>', '<?php echo addslashes($user->direccion ?? ''); ?>', <?php echo $user->activo; ?>)"
                                                data-bs-toggle="modal" data-bs-target="#modalEditarUsuario"
                                                title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        
                                        <?php if ($user->id_usuario != $_SESSION['user_id']): ?>
                                        <a href="usuarios.php?action=toggle&id=<?php echo $user->id_usuario; ?>" 
                                           class="btn btn-outline-<?php echo $user->activo ? 'warning' : 'success'; ?>"
                                           title="<?php echo $user->activo ? 'Desactivar' : 'Activar'; ?>"
                                           onclick="return confirm('¿<?php echo $user->activo ? 'Desactivar' : 'Activar'; ?> este usuario?')">
                                            <i class="bi bi-power"></i>
                                        </a>
                                        
                                        <a href="usuarios.php?action=delete&id=<?php echo $user->id_usuario; ?>" 
                                           class="btn btn-outline-danger" title="Eliminar"
                                           onclick="return confirmDelete('¿Eliminar permanentemente este usuario?')">
                                            <i class="bi bi-trash"></i>
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
    
    <!-- Tab 2: Permisos y Roles -->
    <div class="tab-pane fade" id="roles" role="tabpanel">
        <div class="card card-shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i> Configuración de Permisos</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Descripción de Roles</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6><span class="badge bg-danger">Administrador</span></h6>
                                    <p class="small">Acceso total al sistema. Puede gestionar usuarios, configuraciones y todos los módulos.</p>
                                    <ul class="small">
                                        <li>Gestión completa de usuarios</li>
                                        <li>Configuración del sistema</li>
                                        <li>Acceso a todos los reportes</li>
                                        <li>Anular ventas/compras</li>
                                        <li>Gestión de backup</li>
                                    </ul>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><span class="badge bg-warning">Encargado</span></h6>
                                    <p class="small">Gestiona inventario, compras y reportes. No puede gestionar usuarios.</p>
                                    <ul class="small">
                                        <li>Gestión de productos y categorías</li>
                                        <li>Registro de compras y ajustes</li>
                                        <li>Acceso a reportes</li>
                                        <li>Gestión de proveedores</li>
                                        <li>No puede gestionar usuarios</li>
                                    </ul>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><span class="badge bg-primary">Vendedor</span></h6>
                                    <p class="small">Solo puede realizar ventas y consultar información básica.</p>
                                    <ul class="small">
                                        <li>Registro de ventas</li>
                                        <li>Consulta de productos y stock</li>
                                        <li>Gestión de clientes</li>
                                        <li>No puede modificar precios</li>
                                        <li>Acceso limitado a reportes</li>
                                    </ul>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><span class="badge bg-info">Consulta</span></h6>
                                    <p class="small">Solo puede ver información, no realizar modificaciones.</p>
                                    <ul class="small">
                                        <li>Consulta de productos</li>
                                        <li>Visualización de reportes</li>
                                        <li>No puede realizar ventas/compras</li>
                                        <li>Solo modo lectura</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Estadísticas de Usuarios</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                // Estadísticas por rol
                                $db->query("SELECT rol, COUNT(*) as total, 
                                           SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos
                                           FROM usuarios 
                                           WHERE id_empresa = :empresa_id 
                                           GROUP BY rol");
                                $db->bind(':empresa_id', $_SESSION['empresa_id']);
                                $estadisticas_roles = $db->resultSet();
                                ?>
                                
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Rol</th>
                                            <th>Total</th>
                                            <th>Activos</th>
                                            <th>Inactivos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($estadisticas_roles as $stat): 
                                            $inactivos = $stat->total - $stat->activos;
                                        ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?php echo ucfirst($stat->rol); ?></span></td>
                                            <td><?php echo $stat->total; ?></td>
                                            <td><span class="badge bg-success"><?php echo $stat->activos; ?></span></td>
                                            <td><span class="badge bg-secondary"><?php echo $inactivos; ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                
                                <hr>
                                
                                <h6>Configuración de Seguridad</h6>
                                <form method="POST" action="usuarios.php?action=config_seguridad">
                                    <div class="mb-3">
                                        <label class="form-label">Tiempo de Expiración de Sesión</label>
                                        <select class="form-select" name="session_timeout">
                                            <option value="1800">30 minutos</option>
                                            <option value="3600" selected>1 hora</option>
                                            <option value="7200">2 horas</option>
                                            <option value="14400">4 horas</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="force_password_change" name="force_password_change">
                                        <label class="form-check-label" for="force_password_change">
                                            Forzar cambio de contraseña cada 90 días
                                        </label>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="login_attempts" name="login_attempts" checked>
                                        <label class="form-check-label" for="login_attempts">
                                            Bloquear después de 5 intentos fallidos
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Guardar Configuración
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab 3: Logs del Sistema -->
    <div class="tab-pane fade" id="logs" role="tabpanel">
        <div class="card card-shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i> Registro de Actividades</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button class="btn btn-sm btn-outline-primary" onclick="exportarLogs()">
                        <i class="bi bi-download me-1"></i> Exportar Logs
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="limpiarLogs()">
                        <i class="bi bi-trash me-1"></i> Limpiar Logs Antiguos
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Descripción</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($log->created_at)); ?></td>
                                <td><?php echo htmlspecialchars($log->usuario_nombre ?? 'Sistema'); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        if (strpos($log->accion, 'creacion') !== false) echo 'bg-success';
                                        elseif (strpos($log->accion, 'eliminacion') !== false) echo 'bg-danger';
                                        elseif (strpos($log->accion, 'actualizacion') !== false) echo 'bg-warning';
                                        else echo 'bg-info';
                                        ?>">
                                        <?php echo $log->accion; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log->descripcion); ?></td>
                                <td><small class="text-muted"><?php echo $log->ip_address; ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (empty($logs)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-journal-x text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-3 text-muted">No hay registros de actividad</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nuevo usuario -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i> Nuevo Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label">Confirmar Contraseña *</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rol" class="form-label">Rol *</label>
                        <select class="form-select" id="rol" name="rol" required>
                            <option value="">Seleccionar rol</option>
                            <option value="administrador">Administrador</option>
                            <option value="encargado">Encargado</option>
                            <option value="vendedor">Vendedor</option>
                            <option value="consulta">Consulta</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono">
                    </div>
                    
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" name="crear_usuario">
                        <i class="bi bi-save me-1"></i> Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" id="edit_id_usuario" name="id_usuario" value="">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i> Editar Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nombre" class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_rol" class="form-label">Rol *</label>
                        <select class="form-select" id="edit_rol" name="rol" required>
                            <option value="">Seleccionar rol</option>
                            <option value="administrador">Administrador</option>
                            <option value="encargado">Encargado</option>
                            <option value="vendedor">Vendedor</option>
                            <option value="consulta">Consulta</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="edit_telefono" name="telefono">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="edit_direccion" name="direccion" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_nueva_password" class="form-label">Nueva Contraseña (dejar vacío para no cambiar)</label>
                        <input type="password" class="form-control" id="edit_nueva_password" name="nueva_password">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_activo" name="activo" value="1">
                        <label class="form-check-label" for="edit_activo">Usuario Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-white" name="actualizar_usuario">
                        <i class="bi bi-save me-1"></i> Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarUsuario(id, nombre, email, rol, telefono, direccion, activo) {
    document.getElementById('edit_id_usuario').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_rol').value = rol;
    document.getElementById('edit_telefono').value = telefono;
    document.getElementById('edit_direccion').value = direccion;
    document.getElementById('edit_activo').checked = (activo == 1);
}

function exportarLogs() {
    window.open('usuarios.php?action=export_logs', '_blank');
}

function limpiarLogs() {
    if (confirm('¿Eliminar logs con más de 30 días de antigüedad?')) {
        window.location.href = 'usuarios.php?action=clean_logs';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>