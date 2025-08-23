<?php
include '../config/conexion.php';
include '../config/consultasDB.php';
include '../includes/alert.php';

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