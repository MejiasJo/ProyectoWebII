<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/verificacionrol.php';

checkSession('../login.php');
if (!isAngente()) { header('Location: ../login.php'); exit(); }

$conn = conectar();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); exit('ID inválido'); }

if (isAdmin()) {
    $stmt = $conn->prepare("DELETE FROM propiedades WHERE id=?");
    $stmt->bind_param("i", $id);
} else {
    $owner = (int)$_SESSION['usuario_id'];
    $stmt = $conn->prepare("DELETE FROM propiedades WHERE id=? AND agente_id=?");
    $stmt->bind_param("ii", $id, $owner);
}
$stmt->execute();

header('Location: ./propiedad.php'); exit();
