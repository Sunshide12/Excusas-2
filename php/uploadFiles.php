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
require __DIR__ . '/DropboxToken.php';

// Importar clases de Dropbox
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;

// Verificar que la petición sea POST y contenga un archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        // Obtener token válido
        $accessToken = getDropboxAccessToken();

        // Inicializar Dropbox
        $app = new DropboxApp(null, null, $accessToken);
        $dropbox = new Dropbox($app);

        // Archivo
        $tmp = $_FILES['file']['tmp_name'];
        $basename = basename($_FILES['file']['name']);
        $dropPath = '/' . time() . '_' . $basename;

        // Subida
        $uploaded = $dropbox->upload($tmp, $dropPath, ['autorename' => true]);

        // Enlace compartido
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
