<?php
$page_title = "Estadísticas";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('encargado')) {
    header('Location: dashboard.php');
    exit();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Estadísticas del Sistema</h1>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary">1,234</h3>
                <p class="mb-0">Ventas Totales</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success">456</h3>
                <p class="mb-0">Productos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info">89</h3>
                <p class="mb-0">Clientes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning">23</h3>
                <p class="mb-0">Proveedores</p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5>Gráfico de Ventas</h5>
    </div>
    <div class="card-body">
        <canvas id="ventasChart" height="100"></canvas>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
