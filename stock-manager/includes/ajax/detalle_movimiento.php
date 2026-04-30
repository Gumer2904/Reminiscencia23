<?php
require_once '../config.php';
require_once '../database.php';

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    $db->query("SELECT m.*, u.nombre as usuario_nombre 
               FROM movimientos m 
               JOIN usuarios u ON m.id_usuario = u.id_usuario 
               WHERE m.id_movimiento = :id");
    $db->bind(':id', $id);
    $movimiento = $db->single();
    
    if ($movimiento) {
        $db->query("SELECT dm.*, p.nombre as producto_nombre, p.codigo_barras 
                   FROM detalle_movimiento dm 
                   JOIN productos p ON dm.id_producto = p.id_producto 
                   WHERE dm.id_movimiento = :id");
        $db->bind(':id', $id);
        $detalles = $db->resultSet();
        
        echo '<h5>Detalle del Movimiento #' . $movimiento->id_movimiento . '</h5>';
        echo '<p><strong>Tipo:</strong> ' . strtoupper($movimiento->tipo) . '</p>';
        echo '<p><strong>Fecha:</strong> ' . date('d/m/Y H:i:s', strtotime($movimiento->created_at)) . '</p>';
        echo '<p><strong>Usuario:</strong> ' . htmlspecialchars($movimiento->usuario_nombre) . '</p>';
        echo '<p><strong>Descripción:</strong> ' . htmlspecialchars($movimiento->descripcion) . '</p>';
        echo '<p><strong>Total:</strong> <span class="text-success fw-bold">' . number_format($movimiento->total, 2) . ' CFA</span></p>';
        
        if ($detalles) {
            echo '<h6 class="mt-3">Productos:</h6>';
            echo '<div class="table-responsive">';
            echo '<table class="table table-sm">';
            echo '<thead><tr><th>Producto</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th></tr></thead>';
            echo '<tbody>';
            
            foreach ($detalles as $det) {
                $subtotal = $det->cantidad * $det->precio_unitario;
                echo '<tr>';
                echo '<td>' . htmlspecialchars($det->producto_nombre) . '</td>';
                echo '<td>' . $det->cantidad . '</td>';
                echo '<td>' . number_format($det->precio_unitario, 2) . ' CFA</td>';
                echo '<td>' . number_format($subtotal, 2) . ' CFA</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        }
    } else {
        echo '<div class="alert alert-warning">Movimiento no encontrado</div>';
    }
} else {
    echo '<div class="alert alert-danger">ID no válido</div>';
}
?>