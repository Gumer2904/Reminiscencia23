<?php
$page_title = "Configuración";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('encargado')) {
    header('Location: dashboard.php');
    exit();
}

// Procesar formulario de configuración
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $success = "Configuración actualizada correctamente";
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Configuración del Sistema</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Configuración General</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Empresa</label>
                        <input type="text" class="form-control" name="empresa_nombre" value="Stock Manager">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Moneda por Defecto</label>
                        <select class="form-select" name="moneda">
                            <option value="CFA">CFA Franc</option>
                            <option value="EUR">Euro</option>
                            <option value="USD">Dólar</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
