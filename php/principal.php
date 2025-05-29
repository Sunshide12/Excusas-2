<?php
session_start();

// Si no hay sesión iniciada, redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}
?>
