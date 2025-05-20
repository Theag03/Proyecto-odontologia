<?php
// Configuración para InfinityFree
$hostname = 'sqlXXX.epizy.com'; // Reemplazar con tu host de InfinityFree
$username = 'epiz_XXXXXX';      // Reemplazar con tu usuario
$password = 'tu_password';      // Reemplazar con tu contraseña
$database = 'epiz_XXXXXX_agendadental'; // Reemplazar con tu nombre de BD

try {
    $conexion = new PDO("mysql:host=$hostname;dbname=$database;charset=utf8", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Configurar zona horaria si es necesario
    $conexion->exec("SET time_zone = '-04:00'");
    
} catch(PDOException $e) {
    // En producción, redirigir a una página de error en lugar de mostrar el mensaje
    die("Error de conexión: " . $e->getMessage());
}

// Función para limpiar datos de entrada
function limpiarDatos($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>