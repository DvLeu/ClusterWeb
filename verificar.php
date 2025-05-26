<?php
try {
    $pdo = new PDO("mysql:host=localhost", "root", "kronos185");
    echo "✅ Conexión MySQL exitosa<br>";
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS cluster_admin");
    echo "✅ Base de datos cluster_admin lista<br>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>