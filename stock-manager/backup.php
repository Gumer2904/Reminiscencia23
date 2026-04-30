<?php
$page_title = "Backup/Restore";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('admin')) {
    header('Location: dashboard.php');
    exit();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Backup y Restauración</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Crear Backup</h5>
            </div>
            <div class="card-body">
                <p>Genera una copia de seguridad completa de todos los datos del sistema.</p>
                <button class="btn btn-primary" onclick="crearBackup()">
                    <i class="bi bi-cloud-download"></i> Crear Backup Ahora
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Restaurar Backup</h5>
            </div>
            <div class="card-body">
                <p>Restaura el sistema desde un archivo de backup previo.</p>
                <input type="file" class="form-control mb-3" accept=".sql">
                <button class="btn btn-warning">
                    <i class="bi bi-cloud-upload"></i> Restaurar Backup
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5>Backups Recientes</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tamaño</th>
                        <th>Tipo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>15/01/2024 10:30</td>
                        <td>2.5 MB</td>
                        <td>Completo</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> Descargar
                            </button>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function crearBackup() {
    alert('Iniciando proceso de backup...');
    // Aquí iría el código real para crear backup
}
</script>

<?php require_once 'includes/footer.php'; ?>
