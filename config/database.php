<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'cluster_admin';
    private $username = 'localhost';
    private $password = 'kronos185';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Funciones auxiliares
function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
}

function esComite($rol) {
    return in_array($rol, ['presidente', 'secretario', 'vocal']);
}

function formatearDinero($cantidad) {
    return '$' . number_format($cantidad, 2);
}

function formatearFecha($fecha) {
    return date('d/m/Y', strtotime($fecha));
}

function generarNumeroRecibo() {
    return 'REC-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}
?>