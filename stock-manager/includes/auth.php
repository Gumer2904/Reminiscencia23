<?php
require_once 'database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Registrar nuevo usuario y empresa
    public function register($data) {
        // 1. Crear empresa
        $this->db->query("INSERT INTO empresas (nombre, email) VALUES (:nombre, :email)");
        $this->db->bind(':nombre', $data['nombre_empresa']);
        $this->db->bind(':email', $data['email']);
        
        if ($this->db->execute()) {
            $id_empresa = $this->db->lastInsertId();
            
            // 2. Crear usuario administrador
            $this->db->query("INSERT INTO usuarios (id_empresa, nombre, email, password, rol) 
                             VALUES (:id_empresa, :nombre, :email, :password, 'administrador')");
            $this->db->bind(':id_empresa', $id_empresa);
            $this->db->bind(':nombre', $data['nombre']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
            
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
        }
        return false;
    }
    
    // Iniciar sesión
    public function login($email, $password) {
        $this->db->query("SELECT u.*, e.nombre as empresa_nombre 
                         FROM usuarios u 
                         JOIN empresas e ON u.id_empresa = e.id_empresa 
                         WHERE u.email = :email AND u.activo = 1");
        $this->db->bind(':email', $email);
        
        $user = $this->db->single();
        
        if ($user && password_verify($password, $user->password)) {
            // Crear sesión
            $_SESSION['user_id'] = $user->id_usuario;
            $_SESSION['user_name'] = $user->nombre;
            $_SESSION['user_email'] = $user->email;
            $_SESSION['user_role'] = $user->rol;
            $_SESSION['empresa_id'] = $user->id_empresa;
            $_SESSION['empresa_name'] = $user->empresa_nombre;
            $_SESSION['logged_in'] = true;
            
            // Actualizar último login
            $this->db->query("UPDATE usuarios SET updated_at = NOW() WHERE id_usuario = :id");
            $this->db->bind(':id', $user->id_usuario);
            $this->db->execute();
            
            return true;
        }
        return false;
    }
    
    // Verificar si está logueado
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Cerrar sesión
    public function logout() {
        $_SESSION = array();
        session_destroy();
    }
    
    // Obtener información del usuario actual
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'role' => $_SESSION['user_role'],
                'empresa_id' => $_SESSION['empresa_id'],
                'empresa_name' => $_SESSION['empresa_name']
            ];
        }
        return null;
    }
    
    // Verificar permisos
    public function checkPermission($required_role) {
        $user = $this->getCurrentUser();
        if (!$user) return false;
        
        $roles_hierarchy = [
            'consulta' => 1,
            'vendedor' => 2,
            'encargado' => 3,
            'administrador' => 4
        ];
        
        return $roles_hierarchy[$user['role']] >= $roles_hierarchy[$required_role];
    }
}

// Instancia global de autenticación
$auth = new Auth();
?>