<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sistema_login";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}else {
    echo "Conexión exitosa";
}

$usuario = $_POST['usuario'];
$contrasena = $_POST['contrasena'];

$sql = "SELECT * FROM usuarios WHERE username = '$usuario' AND password = '$contrasena'";
$result = $conn->query($sql); 

if ($result->num_rows > 0){
    // El usuario si existe, Entras
    echo "Si existe";
    
} else {
    // El usuario no existe, No entras
    echo "No existe";
}

?>
