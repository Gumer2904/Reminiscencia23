<?php
$page_title = "Control de Stock";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('encargado')) {
    header('Location: dashboard.php');
    exit();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Control de Stock</h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Estado del Inventario</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Producto A</td>
                                <td>50</td>
                                <td>10</td>
                                <td><span class="badge bg-success">Normal</span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary">
                                        <i class="bi bi-arrow-up"></i> Ajustar
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Producto B</td>
                                <td>5</td>
                                <td>10</td>
                                <td><span class="badge bg-danger">Bajo</span></td>
                                <td>
                                    <button class="btn btn-sm btn-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Reponer
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
