<?php
// Script para obtener el refresh_token manualmente (solo se ejecutó una vez)

if (!isset($_GET['code'])) {
    echo "Falta el parámetro 'code'. Primero autoriza la app en Dropbox.";
    exit;
}

$code = $_GET['code'];
$config = json_decode(file_get_contents(__DIR__ . '/Terceros/drp_app_info.json'), true);

$dropboxKey = $config['dropboxKey'];
$dropboxSecret = $config['dropboxSecret'];
$redirectUri = "http://localhost/excusas2/php/callback.php";

// Petición para obtener refresh_token
$ch = curl_init("https://api.dropboxapi.com/oauth2/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_USERPWD, $dropboxKey . ":" . $dropboxSecret);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    "code" => $code,
    "grant_type" => "authorization_code",
    "redirect_uri" => $redirectUri,
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['refresh_token'])) {
    echo "<h3>Refresh Token generado con éxito:</h3>";
    echo "<pre>" . $data['refresh_token'] . "</pre>";
    echo "<p>Cópialo y pégalo en tu archivo <b>drp_app_info.json</b></p>";
} else {
    echo "<h3>Error:</h3>";
    var_dump($data);
}
