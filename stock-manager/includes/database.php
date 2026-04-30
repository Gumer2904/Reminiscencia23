<?php
require_once 'config.php';

class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = 'root123';
    private $dbname = 'stock_manager';
    
    private $dbh;
    private $stmt;
    private $error;
    
    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ];
        
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            die("Error de conexión: " . $this->error);
        }
    }
    
    // Preparar statement
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }
    
    // Vincular valores
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    
    // Ejecutar consulta
    public function execute() {
        return $this->stmt->execute();
    }
    
    // Obtener múltiples registros
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }
    
    // Obtener un solo registro
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }
    
    // Obtener cantidad de filas
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    // Obtener último ID insertado
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}

// Instancia global de la base de datos
$db = new Database();
?>