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

// Cargar propiedad
$stmt = $conn->prepare("SELECT * FROM propiedades WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$prop = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$prop) { die('❌ Propiedad no encontrada'); }

// Cargar combos a arrays (para reusar)
$tipos   = $conn->query("SELECT id, nombre FROM tipo_alquiler ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$agentes = $conn->query("SELECT id, nombre FROM usuario ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titulo      = trim($_POST['titulo'] ?? $prop['titulo']);
  $id_tipo     = (int)($_POST['id_tipo'] ?? $prop['id_tipo']);
  $agente_id   = (int)($_POST['agente_id'] ?? $prop['agente_id']);
  $destacada   = isset($_POST['destacada']) ? 1 : 0;
  $descripcion = trim($_POST['descripcion'] ?? $prop['descripcion']);
  $ubicacion   = trim($_POST['ubicacion'] ?? $prop['ubicacion']);
  $fecha_pub   = $_POST['fecha_pub'] ?? $prop['fecha_pub'];
  $precio      = (float)($_POST['precio'] ?? $prop['precio']);

  $errores = [];
  if ($titulo==='')      $errores[]="El título es obligatorio.";
  if ($id_tipo<=0)       $errores[]="Selecciona un tipo válido.";
  if ($agente_id<=0)     $errores[]="Selecciona un agente válido.";
  if ($descripcion==='') $errores[]="La descripción es obligatoria.";
  if ($ubicacion==='')   $errores[]="La ubicación es obligatoria.";
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$fecha_pub)) $errores[]="Fecha inválida (YYYY-MM-DD).";
  if ($precio < 0)       $errores[]="El precio no puede ser negativo.";

  // Verificar FK
  $chk = $conn->prepare("SELECT 1 FROM tipo_alquiler WHERE id=?"); $chk->bind_param("i",$id_tipo); $chk->execute(); $chk->store_result();
  if ($chk->num_rows===0) $errores[]="El tipo no existe."; $chk->close();

  $chk = $conn->prepare("SELECT 1 FROM usuario WHERE id=?"); $chk->bind_param("i",$agente_id); $chk->execute(); $chk->store_result();
  if ($chk->num_rows===0) $errores[]="El agente no existe."; $chk->close();

  // Imagen (opcional)
  $rutaImagen = $prop['imagen'];
  if (!empty($_FILES['imagen']['name'])) {
    $dir = __DIR__ . '/../uploads/propiedades';
    if (!is_dir($dir)) { @mkdir($dir,0777,true); }
    $nombreSeguro = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','_', $_FILES['imagen']['name']);
    $destinoFs = $dir . '/' . $nombreSeguro;

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destinoFs)) {
      $nueva = 'uploads/propiedades/'.$nombreSeguro;

      // Borrar imagen anterior si aplica
      $anterior = $prop['imagen'];
      $fsAnterior = __DIR__ . '/../' . $anterior;
      $starts = function($h,$n){ return function_exists('str_starts_with') ? str_starts_with($h,$n) : substr($h,0,strlen($n))===$n; };
      if ($anterior && $starts($anterior,'uploads/propiedades/') && basename($anterior)!=='placeholder.jpg' && file_exists($fsAnterior)) {
        @unlink($fsAnterior);
      }
      $rutaImagen = $nueva;
    } else {
      $errores[] = "No se pudo subir la nueva imagen.";
    }
  }

  if (empty($errores)) {
    $sql = "UPDATE propiedades
            SET id_tipo=?, destacada=?, titulo=?, agente_id=?, imagen=?, descripcion=?, ubicacion=?, fecha_pub=?, precio=?
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisissssii",
      $id_tipo, $destacada, $titulo, $agente_id, $rutaImagen, $descripcion, $ubicacion, $fecha_pub, $precio, $id
    );
    if ($stmt->execute()) {
      $msg = "✅ Propiedad actualizada.";
      // refrescar $prop
      $stmt->close();
      $stmt = $conn->prepare("SELECT * FROM propiedades WHERE id=?");
      $stmt->bind_param("i",$id); $stmt->execute();
      $prop = $stmt->get_result()->fetch_assoc();
    } else {
      $msg = "❌ Error al actualizar: ".$stmt->error;
    }
    $stmt->close();
  } else {
    $msg = "⚠️ Corrige:<ul><li>".implode("</li><li>",$errores)."</li></ul>";
  }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Editar Propiedad</title>
<style>
  body{font-family:system-ui,Arial,sans-serif;max-width:900px;margin:24px auto;padding:0 16px}
  form{display:grid;grid-template-columns:1fr 1fr;gap:12px;background:#f7f7f9;padding:16px;border-radius:10px}
  form > div{display:flex;flex-direction:column}
  textarea{min-height:110px}
  .full{grid-column:1 / -1}
  .actions{display:flex;gap:10px;align-items:center}
  .msg{margin:10px 0}
  img.preview{max-width:100%;border-radius:8px}
  a.btn{display:inline-block;padding:8px 12px;background:#00699e;color:#fff;text-decoration:none;border-radius:6px}
</style>
</head>
<body>
  <h1>Editar Propiedad #<?= (int)$prop['id'] ?></h1>
  <?php if(!empty($msg)): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

  <p><img class="preview" src="../<?= htmlspecialchars($prop['imagen']) ?>" alt="Imagen actual"></p>

  <form method="post" enctype="multipart/form-data">
    <div>
      <label>Título *</label>
      <input type="text" name="titulo" value="<?= htmlspecialchars($prop['titulo']) ?>" required>
    </div>

    <div>
      <label>Tipo *</label>
      <select name="id_tipo" required>
        <option value="">-- Selecciona --</option>
        <?php foreach($tipos as $t): ?>
          <option value="<?= (int)$t['id'] ?>" <?= $t['id']==$prop['id_tipo']?'selected':'' ?>>
            <?= htmlspecialchars($t['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label>Agente *</label>
      <select name="agente_id" required>
        <option value="">-- Selecciona --</option>
        <?php foreach($agentes as $a): ?>
          <option value="<?= (int)$a['id'] ?>" <?= $a['id']==$prop['agente_id']?'selected':'' ?>>
            <?= htmlspecialchars($a['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label>¿Destacada?</label>
      <input type="checkbox" name="destacada" value="1" <?= $prop['destacada'] ? 'checked':'' ?>>
    </div>

    <div class="full">
      <label>Descripción *</label>
      <textarea name="descripcion" required><?= htmlspecialchars($prop['descripcion']) ?></textarea>
    </div>

    <div class="full">
      <label>Ubicación *</label>
      <input type="text" name="ubicacion" value="<?= htmlspecialchars($prop['ubicacion']) ?>" required>
    </div>

    <div>
      <label>Fecha de publicación *</label>
      <input type="date" name="fecha_pub" value="<?= htmlspecialchars($prop['fecha_pub']) ?>" required>
    </div>

    <div>
      <label>Precio *</label>
      <input type="number" name="precio" value="<?= htmlspecialchars($prop['precio']) ?>" min="0" step="0.01" required>

    <div>
      <label>Reemplazar imagen</label>
      <input type="file" name="imagen" accept="image/*">
    </div>

    <div class="full actions">
      <button type="submit">Guardar cambios</button>
      <a class="btn" href="../propiedad.php?id=<?= (int)$prop['id'] ?>">Ver detalle</a>
      <a class="btn" href="javascript:history.back()">Volver</a>
    </div>
  </form>
</body>
</html>
