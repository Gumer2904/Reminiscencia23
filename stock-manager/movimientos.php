<?php
$page_title = "Movimientos";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('encargado')) {
    header('Location: dashboard.php');
    exit();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Historial de Movimientos</h1>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Usuario</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>15/01/2024 10:30</td>
                        <td><span class="badge bg-success">Entrada</span></td>
                        <td>Producto A</td>
                        <td>+50</td>
                        <td>Admin</td>
                        <td>Compra al proveedor</td>
                    </tr>
                    <tr>
                        <td>15/01/2024 14:20</td>
                        <td><span class="badge bg-danger">Salida</span></td>
                        <td>Producto B</td>
                        <td>-5</td>
                        <td>Vendedor</td>
                        <td>Venta a cliente</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
