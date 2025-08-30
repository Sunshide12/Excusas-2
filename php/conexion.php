<?php

// Configuración de conexión
$host = 'localhost';
$dbname = 'v_exc_asig_mat_est';
$username = 'root';
$password = '';

try {
    //conexion por metodo PDO comun
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
}
?>
