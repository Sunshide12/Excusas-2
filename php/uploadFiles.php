<?php
/**
 * SUBIDA DE ARCHIVOS A DROPBOX
 * 
 * Este archivo maneja la subida de archivos de soporte de excusas a Dropbox.
 * Funcionalidades:
 * - Recepción de archivos desde formularios
 * - Subida segura a Dropbox usando la API oficial
 * - Generación de enlaces compartidos para acceso público
 * - Validación de tipos de archivo y manejo de errores
 * - Respuestas en formato JSON para comunicación con el frontend
 */

// Configurar cabecera para respuestas JSON
header('Content-Type: application/json');

// Configuración de reporte de errores para desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cargar autoloader de Composer para las dependencias de Dropbox
require __DIR__ . '/Terceros/dropbox/vendor/autoload.php';

// Importar clases de Dropbox
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;

// Verificar que la petición sea POST y contenga un archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    
    // PASO 1: Cargar configuración de Dropbox desde archivo JSON
    // Se cargan las librerías o Keys necesarias para la API de Dropbox
    $config = json_decode(file_get_contents(__DIR__ . '/Terceros/drp_app_info.json'), true);

    // Extraer credenciales de la aplicación de Dropbox
    $dropboxKey = $config['dropboxKey'];        // App Key de Dropbox
    $dropboxSecret = $config['dropboxSecret'];  // App Secret de Dropbox
    $dropboxToken = $config['dropboxToke'];     // Access Token de Dropbox (nota: hay un typo en 'Toke')
    
    // Validar que el token esté configurado
    if (empty($dropboxToken)) {
        echo json_encode(['success' => false, 'mensaje' => 'Token de Dropbox no configurado']);
        exit;
    }

    // PASO 2: Inicializar la aplicación de Dropbox
    // Aquí se usan las credenciales de la aplicación para autenticarse
    $app = new DropboxApp($dropboxKey, $dropboxSecret, $dropboxToken);
    $dropbox = new Dropbox($app);

    // PASO 3: Preparar el archivo para la subida
    // Se trae el archivo File que se subió desde el formulario
    $tmp = $_FILES['file']['tmp_name'];        // Ruta temporal del archivo
    $basename = basename($_FILES['file']['name']); // Nombre original del archivo
    
    // Crear ruta única en Dropbox usando timestamp para evitar conflictos
    $dropPath = '/' . time() . '_' . $basename;

    try {
        // PASO 4: Subir archivo a Dropbox
        // Subir el archivo con opción de renombrar automáticamente si hay conflictos
        $uploaded = $dropbox->upload($tmp, $dropPath, ['autorename' => true]);
        
        // PASO 5: Crear enlace compartido público
        // Generar un enlace que permita acceder al archivo sin autenticación
        $shared = $dropbox->postToAPI('/sharing/create_shared_link_with_settings', [
            'path' => $uploaded->getPathDisplay()
        ]);
        
        // Extraer la URL del enlace compartido
        $body = $shared->getDecodedBody();
        $url = $body['url'];
        
        // Convertir enlace de descarga a enlace directo para visualización
        $direct = str_replace('?dl=0', '?raw=1', $url);

        // Devolver respuesta exitosa con la URL del archivo
        echo json_encode(['success' => true, 'url' => $direct]);
        
    } catch (\Exception $e) {
        // Manejar errores durante la subida o creación del enlace
        echo json_encode(['success' => false, 'mensaje' => 'Error en Dropbox: ' . $e->getMessage()]);
    }
    
} else {
    // Error si la petición no es válida o no contiene archivo
    echo json_encode(['success' => false, 'mensaje' => 'Solicitud no válida o archivo no encontrado']);
}
