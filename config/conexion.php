<?php
function conectar() {
    $conexion = new mysqli('localhost', 'root', '', 'venta_propiedades');
    if ($conexion->connect_error) {
        die("Connection failed: " . $conexion->connect_error);
    }
    return $conexion;
}
?>