<?php
// Configuración de la aplicación
session_start();
date_default_timezone_set('Africa/Malabo');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root123'); 
define('DB_NAME', 'stock_manager');

// Configuración de la aplicación
define('APP_NAME', 'Stock Manager');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/stock-manager/');

// Configuración de seguridad
define('MAX_LOGIN_ATTEMPTS', 5);
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos

// Manejo de errores (desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>