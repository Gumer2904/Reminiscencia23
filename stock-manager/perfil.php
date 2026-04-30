<?php
$page_title = "Mi Perfil";
require_once 'includes/header.php';

// Obtener datos del usuario actual
$usuario_actual = $auth->getCurrentUser();

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $password_actual = isset($_POST['password_actual']) ? $_POST['password_actual'] : '';
    $password_nueva = isset($_POST['password_nueva']) ? $_POST['password_nueva'] : '';
    $password_confirmar = isset($_POST['password_confirmar']) ? $_POST['password_confirmar'] : '';
    
    $success = "";
    $error = "";
    
    // Validar y actualizar datos
    if (!empty($nombre) && !empty($email)) {
        // Aquí iría la lógica de actualización en la base de datos
        $success = "Perfil actualizado correctamente";
    }
    
    // Cambiar contraseña si se proporcionó
    if (!empty($password_actual) && !empty($password_nueva)) {
        if ($password_nueva === $password_confirmar) {
            // Aquí iría la lógica de cambio de contraseña
            $success = "Contraseña cambiada correctamente";
        } else {
            $error = "Las contraseñas nuevas no coinciden";
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Configuración del Perfil</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Información Personal</h5>
            </div>
            <div class="card-body">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" name="nombre" 
                                       value="<?php echo htmlspecialchars($usuario_actual['nombre'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($usuario_actual['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" name="telefono" 
                                       value="<?php echo htmlspecialchars($usuario_actual['telefono'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rol</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario_actual['rol'] ?? 'Usuario'); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Cambiar Contraseña</h6>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Contraseña Actual</label>
                                <input type="password" class="form-control" name="password_actual">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" name="password_nueva">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" name="password_confirmar">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Guardar Cambios
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Foto de Perfil</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usuario_actual['nombre'] ?? 'User'); ?>&size=150&background=2563eb&color=fff" 
                         alt="Avatar" class="rounded-circle" width="150" height="150">
                </div>
                <button class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-camera"></i> Cambiar Foto
                </button>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5>Información de la Cuenta</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>ID Usuario:</strong> <?php echo $usuario_actual['id'] ?? '001'; ?>
                </div>
                <div class="mb-2">
                    <strong>Fecha de Registro:</strong> <?php echo $usuario_actual['fecha_registro'] ?? '15/01/2024'; ?>
                </div>
                <div class="mb-2">
                    <strong>Último Acceso:</strong> <?php echo $usuario_actual['ultimo_acceso'] ?? date('d/m/Y H:i'); ?>
                </div>
                <div class="mb-2">
                    <strong>Estado:</strong> 
                    <span class="badge bg-success">Activo</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
