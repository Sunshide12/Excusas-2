<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/Terceros/dropbox/vendor/autoload.php';

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // Cargar configuración
    // Se cargan las librerías o Keys necesarias 
    $config = json_decode(file_get_contents(__DIR__ . '/Terceros/drp_app_info.json'), true);

    $dropboxKey = $config['dropboxKey'];
    $dropboxSecret = $config['dropboxSecret'];
    $dropboxToken = $config['dropboxToke'];
    if (empty($dropboxToken)) {
    echo json_encode(['success' => false, 'mensaje' => 'Token de Dropbox no configurado']);
    exit;
}

    // Inicializar Dropbox
    // Aquí se usan las credenciales de la aplicación
    $app = new DropboxApp($dropboxKey, $dropboxSecret, $dropboxToken);
    $dropbox = new Dropbox($app);

    // Se trae el archivo File que se subió
    $tmp = $_FILES['file']['tmp_name'];
    $basename = basename($_FILES['file']['name']);
    $dropPath = '/' . time() . '_' . $basename;

    try {
        $uploaded = $dropbox->upload($tmp, $dropPath, ['autorename' => true]);
        $shared = $dropbox->postToAPI('/sharing/create_shared_link_with_settings', [
            'path' => $uploaded->getPathDisplay()
        ]);
        $body = $shared->getDecodedBody();
        $url = $body['url'];
        $direct = str_replace('?dl=0', '?raw=1', $url);

        echo json_encode(['success' => true, 'url' => $direct]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => 'Error en Dropbox: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Solicitud no válida o archivo no encontrado']);
}
