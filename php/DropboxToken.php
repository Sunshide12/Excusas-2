<?php
// Se encarga de obtener un access_token válido usando el refresh_token

function getDropboxAccessToken() {
    $config = json_decode(file_get_contents(__DIR__ . '/Terceros/drp_app_info.json'), true);

    $dropboxKey = $config['dropboxKey'];
    $dropboxSecret = $config['dropboxSecret'];
    $refreshToken = $config['refresh_token'];

    if (empty($refreshToken)) {
        throw new Exception("No se encontró token");
    }

    // Petición para renovar el access_token
    $ch = curl_init("https://api.dropboxapi.com/oauth2/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, $dropboxKey . ":" . $dropboxSecret);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        "refresh_token" => $refreshToken,
        "grant_type" => "refresh_token",
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['access_token'])) {
        return $data['access_token'];
    } else {
        throw new Exception("Error al renovar token: " . json_encode($data));
    }
}
