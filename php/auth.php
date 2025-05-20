<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar tiempo de inactividad (30 minutos)
$inactividad = 1800; // 30 minutos en segundos
if (isset($_SESSION['ultimo_acceso'])) {
    $tiempo_inactivo = time() - $_SESSION['ultimo_acceso'];
    if ($tiempo_inactivo >= $inactividad) {
        session_unset();
        session_destroy();
        header('Location: login.php?timeout=1');
        exit();
    }
}
$_SESSION['ultimo_acceso'] = time();

// Función para verificar roles
function verificarRol($rolesPermitidos) {
    if (!in_array($_SESSION['rol'], $rolesPermitidos)) {
        header('Location: acceso-denegado.php');
        exit();
    }
}
?>