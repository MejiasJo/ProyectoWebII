<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/consultasDB.php';
require_once __DIR__ . '/../includes/verificacionrol.php';
session_start();
if (!isAdmin()) {
    header('Location: ../login.php');
}

$conn = conectar();
if (getUsuario($conn, $_GET['id'])->num_rows > 0) {
    if(deleteUsuario($conn, $_GET['id'])){
        header('Location: ./usuarios.php?alert=success&message=' . urlencode('Usuario eliminado exitosamente'));
    } else {
        header('Location: ./usuarios.php?alert=danger&message=' . urlencode('Error al eliminar el usuario'));
    }
} else {
    header('Location: ./usuarios.php?alert=danger&message=' . urlencode('Usuario no encontrado'));
}
exit();

?>