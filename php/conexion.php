<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';
$dbname = 'sistema_login';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Establecer el modo de error PDO a excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    die();
}

// Función para validar usuario
function validarUsuario($username, $password) {
    global $conn;
    try {
        // Preparar la consulta para obtener el usuario y su rol
        $stmt = $conn->prepare("
            SELECT u.*, r.nombre as rol_nombre 
            FROM usuarios u 
            INNER JOIN roles r ON u.rol_id = r.id 
            WHERE u.username = :username AND u.password = :password
        ");
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si el usuario existe
        if ($usuario) {
            return [
                'success' => true,
                'usuario' => $usuario
            ];
        }
        
        return [
            'success' => false,
            'mensaje' => 'Usuario o contraseña incorrectos'
        ];

    } catch(PDOException $e) {
        return [
            'success' => false,
            'mensaje' => 'Error en la validación: ' . $e->getMessage()
        ];
    }
}

// Si se reciben datos POST, procesar la validación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Por favor, complete todos los campos'
        ]);
        exit;
    }

    $resultado = validarUsuario($username, $password);
    
    if ($resultado['success']) {
        // Iniciar sesión y guardar datos del usuario
        session_start();
        $_SESSION['usuario_id'] = $resultado['usuario']['id'];
        $_SESSION['username'] = $resultado['usuario']['username'];
        $_SESSION['rol'] = $resultado['usuario']['rol_nombre'];
    }
    
    echo json_encode($resultado);
    exit;
}
?>
