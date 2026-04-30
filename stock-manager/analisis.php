<?php
$page_title = "Análisis";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('encargado')) {
    header('Location: dashboard.php');
    exit();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Análisis de Datos</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Productos Más Vendidos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Ventas</th>
                                <th>Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Producto A</td>
                                <td>234</td>
                                <td>2,340,000 CFA</td>
                            </tr>
                            <tr>
                                <td>Producto B</td>
                                <td>189</td>
                                <td>1,890,000 CFA</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Categorías Populares</h5>
            </div>
            <div class="card-body">
                <canvas id="categoriasChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
