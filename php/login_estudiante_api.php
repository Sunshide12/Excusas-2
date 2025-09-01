<?php
/**
 * API DE LOGIN PARA ESTUDIANTES
 * 
 * Este archivo maneja la autenticación de estudiantes en el sistema.
 * Funcionalidades:
 * - Validación de credenciales contra la base de datos
 * - Creación de sesión para estudiantes autenticados
 * - Respuestas en formato JSON para comunicación con el frontend
 * - Manejo de errores y validaciones de seguridad
 */

// Configuración de reporte de errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar cabecera para respuestas JSON
header('Content-Type: application/json');

// Incluir archivo de conexión a la base de datos
include_once './conexion.php'; 

/**
 * Función para validar las credenciales del estudiante
 * 
 * @param string $documento Número de documento del estudiante
 * @param string $contrasena Contraseña del estudiante
 * @return array Array con resultado de la validación y datos del estudiante
 */
function validarEstudiante($documento, $contrasena) {
    global $conn;
    try {
        // Consulta preparada para evitar inyección SQL
        $stmt = $conn->prepare("
            SELECT * FROM estudiantes 
            WHERE num_doc_estudiante = :documento AND contra_estudiante = :contrasena
        ");
        
        // Vincular parámetros de forma segura
        $stmt->bindParam(':documento', $documento);
        $stmt->bindParam(':contrasena', $contrasena);
        
        // Ejecutar consulta
        $stmt->execute();
        
        // Obtener resultado
        $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($estudiante) {
            // Estudiante encontrado - credenciales válidas
            return [
                'success' => true,
                'estudiante' => $estudiante
            ];
        }

        // Estudiante no encontrado - credenciales inválidas
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
    $documento = $_POST['documentoIdentidad'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    // Validar que todos los campos estén completos
    if (empty($documento) || empty($contrasena)) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Por favor, complete todos los campos'
        ]);
        exit;
    }

    // Validar credenciales del estudiante
    $resultado = validarEstudiante($documento, $contrasena);

    // Si la validación es exitosa, crear sesión
    if ($resultado['success']) {
        session_start(); // Iniciar sesión PHP
        
        // Almacenar datos del estudiante en la sesión
        $_SESSION['estudiante_id'] = $resultado['estudiante']['num_doc_estudiante'];
        $_SESSION['documento'] = $resultado['estudiante']['num_doc_estudiante'];
        $_SESSION['rol'] = 'Estudiante';
    }

    // Devolver resultado de la validación
    echo json_encode($resultado);
    exit;
}
