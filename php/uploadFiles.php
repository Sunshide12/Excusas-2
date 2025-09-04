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
 * - Usa $_SESSION['documento'] si el que sube es Estudiante
 * - Usa $_POST['num_doc_estudiante'] si sube Directivo/Director
 */

session_start();
header('Content-Type: application/json');

// Configuración de reporte de errores para desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// NO redirigir si no hay sesión; puede venir num_doc por POST desde directivo
require __DIR__ . '/Terceros/dropbox/vendor/autoload.php';
require __DIR__ . '/DropboxToken.php';

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;

try {
    // Validar archivo
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'mensaje' => 'Solicitud no válida o archivo no encontrado']);
        exit;
    }

    $fileTmp   = $_FILES['file']['tmp_name'];
    $origName  = $_FILES['file']['name'] ?? '';
    $extension = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    // Validación básica de extensión
    $permitidas = ['pdf', 'zip', 'jpg', 'jpeg', 'png'];
    if (!in_array($extension, $permitidas, true)) {
        echo json_encode(['success' => false, 'mensaje' => 'Extensión no permitida. Use pdf/zip/jpg/jpeg/png.']);
        exit;
    }

    // Determinar num_doc_estudiante (sesión o POST)
    $numDoc = null;

    // Si hay sesión de estudiante
    if (!empty($_SESSION['rol']) && $_SESSION['rol'] === 'Estudiante' && !empty($_SESSION['documento'])) {
        $numDoc = $_SESSION['documento'];
    }

    // Si viene por POST (directivo/director)
    if (!$numDoc && !empty($_POST['num_doc_estudiante'])) {
        $numDoc = $_POST['num_doc_estudiante'];
    }

    // Saneamos por seguridad (solo dígitos y guiones bajos)
    if ($numDoc) {
        $numDoc = preg_replace('/[^\d_]/', '', (string)$numDoc);
    }

    // Construir nombre final (si no hay doc, usar original)
    $nuevoNombre = $numDoc
        ? "{$numDoc}.{$extension}"
        : $origName;

    // Ruta en Dropbox (raíz)
    $dropboxPath = '/' . $nuevoNombre;

    // Obtener access token
    $accessToken = getDropboxAccessToken();

    // Inicializar Dropbox
    $app     = new DropboxApp(null, null, $accessToken);
    $dropbox = new Dropbox($app);

    // Subir archivo (autorename evita colisiones)
    $dropboxFile = new DropboxFile($fileTmp);
    $uploaded    = $dropbox->upload($dropboxFile, $dropboxPath, ['autorename' => true]);

    // Crear enlace compartido o recuperarlo si ya existe
    $url = null;

    try {
        // Intentar crear el enlace
        $respCreate = $dropbox->postToAPI('/sharing/create_shared_link_with_settings', [
            'path'     => $uploaded->getPathLower(),
        ]);
        $body = $respCreate->getDecodedBody();
        $url  = $body['url'] ?? null;
    } catch (\Exception $eCreate) {
        // Si ya existe un enlace, listar y usar el existente
        try {
            $respList = $dropbox->postToAPI('/sharing/list_shared_links', [
                'path'        => $uploaded->getPathLower(),
                'direct_only' => true
            ]);
            $listBody = $respList->getDecodedBody();
            if (!empty($listBody['links'][0]['url'])) {
                $url = $listBody['links'][0]['url'];
            } else {
                throw $eCreate; // re-lanzar si no hay enlaces
            }
        } catch (\Exception $eList) {
            echo json_encode(['success' => false, 'mensaje' => 'No se pudo crear/obtener enlace compartido: ' . $eList->getMessage()]);
            exit;
        }
    }

    if (!$url) {
        echo json_encode(['success' => false, 'mensaje' => 'No se obtuvo URL de Dropbox.']);
        exit;
    }

    // Transformar a enlace "directo" visualizable
    $direct = str_replace(['?dl=0', '?dl=1'], '?raw=1', $url);

    echo json_encode([
        'success' => true,
        'url'     => $direct,
        'name'    => basename($uploaded->getPathDisplay())
    ]);
    exit;

} catch (\Exception $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error en Dropbox: ' . $e->getMessage()]);
    exit;
}
