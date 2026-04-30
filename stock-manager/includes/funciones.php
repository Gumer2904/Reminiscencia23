<?php
// Funciones auxiliares para el sistema

// Formatear número como moneda
function formatoMoneda($cantidad) {
    return number_format($cantidad, 2) . ' CFA';
}

// Obtener nombre del rol
function nombreRol($rol) {
    $roles = [
        'administrador' => 'Administrador',
        'encargado' => 'Encargado',
        'vendedor' => 'Vendedor',
        'consulta' => 'Solo Consulta'
    ];
    return $roles[$rol] ?? 'Desconocido';
}

// Calcular edad del producto (días desde creación)
function edadProducto($fecha_creacion) {
    $creacion = new DateTime($fecha_creacion);
    $hoy = new DateTime();
    $diferencia = $hoy->diff($creacion);
    return $diferencia->days;
}

// Generar código de barras aleatorio
function generarCodigoBarras() {
    return '75' . str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
}

// Validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Sanitizar entrada
function sanitizar($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>