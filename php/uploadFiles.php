<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/Terceros/dropbox/vendor/autoload.php';
require __DIR__ . '/DropboxToken.php';

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;

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
