<?php
/**
 * API DE LOGIN PARA DOCENTES Y ADMINISTRATIVOS
 * 
 * Este archivo maneja la autenticación de docentes, directores de unidad y otros empleados.
 * Funcionalidades:
 * - Validación de credenciales contra la base de datos
 * - Verificación de roles y permisos del usuario
 * - Creación de sesión con información del empleado y su rol
 * - Respuestas en formato JSON para comunicación con el frontend
 * - Manejo de errores y validaciones de seguridad
 */

// Configurar cabecera para respuestas JSON
header('Content-Type: application/json');

// Incluir archivo de conexión a la base de datos
include_once './conexion.php'; 

/**
 * Función para validar las credenciales del usuario (empleado)
 * 
 * @param string $username Número de documento del empleado
 * @param string $password Contraseña del empleado
 * @return array Array con resultado de la validación y datos del usuario
 */
function validarUsuario($username, $password) {
    global $conn;
    try {
        // Consulta preparada que obtiene información del empleado y su rol
        $stmt = $conn->prepare("
            SELECT e.*, r.tipo_rol AS rol_nombre 
            FROM empleados e 
            INNER JOIN roles r ON e.rol_empleado = r.id_rol 
            WHERE e.num_doc_empleado = :username AND e.contra_empleado = :password
        ");
        
        // Vincular parámetros de forma segura
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        
        // Ejecutar consulta
        $stmt->execute();
        
        // Obtener resultado
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Usuario encontrado - credenciales válidas
            return [
                'success' => true,
                'usuario' => $usuario
            ];
        }

        // Usuario no encontrado - credenciales inválidas
        return [
            'success' => false,
            'mensaje' => 'Usuario o contraseña incorrectos'
        ];
        
    } catch(PDOException $e) {
        // Error en la base de datos
        return [
            'success' => false,
            'mensaje' => 'Error en la validación: ' . $e->getMessage()
        ];
    }
}

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario de login
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validar que todos los campos estén completos
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Por favor, complete todos los campos'
        ]);
        exit;
    }

    // Validar credenciales del usuario
    $resultado = validarUsuario($username, $password);

    // Si la validación es exitosa, crear sesión
    if ($resultado['success']) {
        session_start(); // Iniciar sesión PHP
        
        // Almacenar datos del usuario en la sesión
        $_SESSION['usuario_id'] = $resultado['usuario']['id_empleado'];
        $_SESSION['username'] = $resultado['usuario']['num_doc_empleado'];
        $_SESSION['rol'] = $resultado['usuario']['rol_nombre'];
        $_SESSION['nombre_empleado'] = $resultado['usuario']['nombre_empleado']; // Útil para filtrar por docente
    }

    // Devolver resultado de la validación
    echo json_encode($resultado);
    exit;
}
