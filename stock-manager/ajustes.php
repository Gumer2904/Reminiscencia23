<?php
$page_title = "Ajustes de Inventario";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('encargado')) {
    header('Location: dashboard.php');
    exit();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Ajustes de Inventario</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajusteModal">
        <i class="bi bi-plus"></i> Nuevo Ajuste
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad Anterior</th>
                        <th>Cantidad Nueva</th>
                        <th>Diferencia</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>15/01/2024</td>
                        <td>Producto A</td>
                        <td><span class="badge bg-warning">Ajuste</span></td>
                        <td>45</td>
                        <td>50</td>
                        <td><span class="text-success">+5</span></td>
                        <td>Corrección de conteo</td>
                        <td>Admin</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
