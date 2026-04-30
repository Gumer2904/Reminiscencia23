<?php
$page_title = "Registrar Nueva Empresa";
require_once 'includes/config.php';
require_once 'includes/database.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header('Location: dashboard.php');
    exit();
}

// Procesar registro
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_empresa = trim($_POST['nombre_empresa']);
    $nombre_usuario = trim($_POST['nombre_usuario']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // Validaciones
    if (empty($nombre_empresa) || empty($nombre_usuario) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } elseif ($password !== $password_confirm) {
        $error = 'Las contraseñas no coinciden';
    } else {
        // Verificar si el email ya existe
        $db->query("SELECT id_usuario FROM usuarios WHERE email = :email");
        $db->bind(':email', $email);
        $existe = $db->single();
        
        if ($existe) {
            $error = 'El email ya está registrado';
        } else {
            // Crear empresa
            $db->query("INSERT INTO empresas (nombre, email) VALUES (:nombre, :email)");
            $db->bind(':nombre', $nombre_empresa);
            $db->bind(':email', $email);
            
            if ($db->execute()) {
                $id_empresa = $db->lastInsertId();
                
                // Crear usuario administrador
                $db->query("INSERT INTO usuarios (id_empresa, nombre, email, password, rol) 
                           VALUES (:id_empresa, :nombre, :email, :password, 'administrador')");
                $db->bind(':id_empresa', $id_empresa);
                $db->bind(':nombre', $nombre_usuario);
                $db->bind(':email', $email);
                $db->bind(':password', password_hash($password, PASSWORD_DEFAULT));
                
                if ($db->execute()) {
                    $success = 'Empresa y usuario creados exitosamente. Puede iniciar sesión.';
                    header('Location: login.php?success=' . urlencode($success));
                    exit();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


     <!-- CSS LOCALES -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/aos.css">



    <style>
        body { background: #f5f5f5; }
        .register-card { max-width: 500px; margin: 50px auto; }
        
        /* Logo Upload Styles */
        .logo-upload-container {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .logo-upload-container:hover {
            border-color: #0d6efd;
            background: #e7f3ff;
        }
        
        .logo-preview {
            width: 120px;
            height: 120px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            overflow: hidden;
            position: relative;
        }
        
        .logo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .logo-preview i {
            font-size: 3rem;
            color: #6c757d;
        }
        
        .logo-preview span {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            color: white;
            font-size: 0.75rem;
            padding: 2px;
            text-align: center;
        }
        
        .logo-upload {
            margin-top: 10px;
        }
        
        .logo-upload .btn {
            min-width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card register-card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4><i class="bi bi-building"></i> <?php echo APP_NAME; ?></h4>
                <p class="mb-0">Registro de Nueva Empresa</p>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Empresa *</label>
                        <input type="text" class="form-control" name="nombre_empresa" required 
                               value="<?php echo $_POST['nombre_empresa'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Logo de la Empresa</label>
                        <div class="text-muted small mb-2">
                            <i class="bi bi-info-circle"></i> Sube el logo o icono de tu empresa (Opcional)
                        </div>
                        <div class="logo-upload-container">
                            <div class="logo-preview" id="logoPreview">
                                <i class="bi bi-building"></i>
                                <span>Sin logo</span>
                            </div>
                            <div class="logo-upload">
                                <input type="file" id="logoInput" name="logo_empresa" accept="image/*" style="display: none;">
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('logoInput').click()">
                                    <i class="bi bi-upload"></i> Seleccionar Logo
                                </button>
                                <small class="d-block text-muted mt-1">Formatos: PNG, JPG, GIF (Máx. 2MB)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre del Administrador *</label>
                        <input type="text" class="form-control" name="nombre_usuario" required 
                               value="<?php echo $_POST['nombre_usuario'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required 
                               value="<?php echo $_POST['email'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirmar Contraseña *</label>
                        <input type="password" class="form-control" name="password_confirm" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Registrar Empresa
                        </button>
                    </div>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p>¿Ya tiene una empresa registrada?</p>
                    <a href="login.php" class="btn btn-outline-secondary">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Logo preview functionality
        document.getElementById('logoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('logoPreview');
            
            if (file) {
                // Validar archivo
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                const maxSize = 2 * 1024 * 1024; // 2MB
                
                if (!validTypes.includes(file.type)) {
                    alert('Por favor, selecciona un archivo de imagen válido (PNG, JPG, GIF)');
                    e.target.value = '';
                    return;
                }
                
                if (file.size > maxSize) {
                    alert('El archivo es demasiado grande. Máximo permitido: 2MB');
                    e.target.value = '';
                    return;
                }
                
                // Mostrar vista previa
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Logo preview">`;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Limpiar preview si se cancela la selección
        document.getElementById('logoInput').addEventListener('click', function() {
            // No hacer nada, solo permitir la selección
        });
    </script>
</body>
</html>