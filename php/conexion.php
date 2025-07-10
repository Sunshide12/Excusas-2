<?php
header('Content-Type: application/json'); // Agregado

// Configuración de conexión
$host = 'localhost';
$dbname = 'v_exc_asig_mat_est';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error de conexión: ' . $e->getMessage()
    ]);
    exit;
}

function validarUsuario($username, $password) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT e.*, r.tipo_rol as rol_nombre 
            FROM empleados e 
            INNER JOIN roles r ON e.rol_empleado = r.id_rol 
            WHERE e.num_doc_empleado = :username AND e.contra_empleado = :password
        ");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

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
        session_start();
        $_SESSION['usuario_id'] = $resultado['usuario']['id_empleado'];
        $_SESSION['username'] = $resultado['usuario']['num_doc_empleado'];
        $_SESSION['rol'] = $resultado['usuario']['rol_nombre'];
    }

    echo json_encode($resultado);
    exit;
}
?>
