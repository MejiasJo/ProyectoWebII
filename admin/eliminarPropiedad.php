<?php
require_once __DIR__ . '/../config/conexion.php';
ini_set('display_errors','1'); ini_set('display_startup_errors','1'); error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/../includes/verificacionrol.php';
session_start();
if (!isAdmin()) {
    header('Location: ../login.php');
}


$conn = conectar();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { die('❌ ID inválido'); }


$stmt = $conn->prepare("SELECT imagen FROM propiedades WHERE id=? LIMIT 1");
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) { die('❌ Propiedad no encontrada'); }


$stmt = $conn->prepare("DELETE FROM propiedades WHERE id=?");
$stmt->bind_param("i",$id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
  
  $img = $row['imagen'];
  $fs  = __DIR__ . '/../' . $img;
  $starts = function($h,$n){ return function_exists('str_starts_with') ? str_starts_with($h,$n) : substr($h,0,strlen($n))===$n; };
  if ($img && $starts($img,'uploads/propiedades/') && basename($img)!=='placeholder.jpg' && file_exists($fs)) {
    @unlink($fs);
  }

  $back = $_SERVER['HTTP_REFERER'] ?? 'propiedad_crear.php';
  header("Location: $back");
  exit;
} else {
  die('❌ No se pudo eliminar.');
}
