<?php
$page_title = "Transferencias";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('encargado')) {
    header('Location: dashboard.php');
    exit();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Transferencias entre Almacenes</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transferenciaModal">
        <i class="bi bi-plus"></i> Nueva Transferencia
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
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Cantidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>15/01/2024</td>
                        <td>Producto A</td>
                        <td>Almacén Central</td>
                        <td>Tienda Principal</td>
                        <td>25</td>
                        <td><span class="badge bg-success">Completada</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i> Ver
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
