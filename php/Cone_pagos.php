<?php

//Conectar en Base de Datos
$host='localhost'; //Server
$usuario='root'; //usuario
$passwdb=''; //password
$nombredb=''; //Base de dato
$conexion = new mysqli($host,$usuario,$passwdb,$nombredb,);
if ($conexion->Connect_error) {
    die("No se ha conectado " . $conexion->Connect_error);
}


?>