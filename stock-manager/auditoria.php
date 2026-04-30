<?php
$page_title = "Auditoría";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('admin')) {
    header('Location: dashboard.php');
    exit();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Auditoría del Sistema</h1>
</div>

<div class="card">
    <div class="card-header">
        <h5>Registro de Actividades</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Módulo</th>
                        <th>IP</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>15/01/2024 10:30:15</td>
                        <td>admin</td>
                        <td><span class="badge bg-success">Login</span></td>
                        <td>Autenticación</td>
                        <td>192.168.1.100</td>
                        <td>Inicio de sesión exitoso</td>
                    </tr>
                    <tr>
                        <td>15/01/2024 10:35:22</td>
                        <td>admin</td>
                        <td><span class="badge bg-info">Creación</span></td>
                        <td>Productos</td>
                        <td>192.168.1.100</td>
                        <td>Creado producto: Producto Nuevo</td>
                    </tr>
                    <tr>
                        <td>15/01/2024 10:40:18</td>
                        <td>vendedor</td>
                        <td><span class="badge bg-warning">Modificación</span></td>
                        <td>Ventas</td>
                        <td>192.168.1.105</td>
                        <td>Modificada venta #001</td>
                    </tr>
                    <tr>
                        <td>15/01/2024 10:45:33</td>
                        <td>admin</td>
                        <td><span class="badge bg-danger">Eliminación</span></td>
                        <td>Usuarios</td>
                        <td>192.168.1.100</td>
                        <td>Eliminado usuario: usuario_temp</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
