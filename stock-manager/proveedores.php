<?php
$page_title = "Proveedores";
require_once 'includes/header.php';

// Verificar permisos
if (!$auth->checkPermission('encargado')) {
    header('Location: dashboard.php');
    exit();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Proveedores</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#proveedorModal">
        <i class="bi bi-plus"></i> Nuevo Proveedor
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>001</td>
                        <td>Proveedor A</td>
                        <td>info@proveedora.com</td>
                        <td>+240 222 123 456</td>
                        <td>25 productos</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-success">
                                <i class="bi bi-cart-plus"></i> Comprar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
