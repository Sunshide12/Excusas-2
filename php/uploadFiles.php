<?php
require 'vendor/autoload.php';

// Carga el archivo JSON con las credenciales
$client = new Google_Client();
$client->setAuthConfig('credenciales.json');
$client->addScope(Google_Service_Drive::DRIVE);
$service = new Google_Service_Drive($client);

// Procesar archivo subido
if (isset($_FILES['archivo'])) {
    $file_tmp = $_FILES['archivo']['tmp_name'];
    $file_name = $_FILES['archivo']['name'];
    $file_mime = mime_content_type($file_tmp);

    // Crear metadata del archivo
    $fileMetadata = new Google_Service_Drive_DriveFile([
        'name' => $file_name,
        'parents' => ['TU_ID_DE_CARPETA_EN_DRIVE'] // Reemplaza con el ID real de la carpeta
    ]);

    // Subir archivo
    $content = file_get_contents($file_tmp);
    $file = $service->files->create($fileMetadata, [
        'data' => $content,
        'mimeType' => $file_mime,
        'uploadType' => 'multipart',
        'fields' => 'id'
    ]);

    echo "Archivo subido. ID: " . $file->id;
} else {
    echo "No se seleccionó archivo.";
}

