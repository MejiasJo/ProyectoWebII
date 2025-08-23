<?php
require_once __DIR__ . '../config/conexion.php';
require_once __DIR__ . '../config/consultasDB.php';
require_once __DIR__ . '../includes/alert.php';
require_once __DIR__ . '../includes/verificacionrol.php';
session_start();
if (!isAdmin()) {
    header('Location: ../login.php');
}

$conn = conectar();
if (getUsuario($conn, $_GET['id'])->num_rows > 0) {
    if(deleteUsuario($conn, $_GET['id'])){
        alertMenssage("Usuario eliminado exitosamente", "success");
    } else {
        alertMenssage("Error al eliminar el usuario", "danger");
    }
} else {
    alertMenssage("Usuario no encontrado", "danger");
}

header('Location: ./usuarios.php');

?>