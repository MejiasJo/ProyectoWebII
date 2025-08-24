<?php
require_once __DIR__ . '/../config/conexion.php';

// Muestra errores en desarrollo (puedes quitar esto en producción)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// Opcional: que mysqli lance excepciones
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/../includes/verificacionrol.php';
session_start();
if (!isAdmin()) {
  header('Location: ../login.php');
}


$conn = conectar();

// Cargar combos
$tipos   = $conn->query("SELECT id, nombre FROM tipo_alquiler ORDER BY id");
$agentes = $conn->query("SELECT id, nombre FROM usuario ORDER BY nombre");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titulo      = trim($_POST['titulo'] ?? '');
  $id_tipo     = (int)($_POST['id_tipo'] ?? 0);
  $agente_id   = (int)($_POST['agente_id'] ?? 0);
  $destacada   = isset($_POST['destacada']) ? 1 : 0;
  $descripcion = trim($_POST['descripcion'] ?? '');
  $ubicacion   = trim($_POST['ubicacion'] ?? '');
  $fecha_pub   = $_POST['fecha_pub'] ?? date('Y-m-d');
  $precio      = (float)($_POST['precio'] ?? 0);

  $errores = [];
  if ($titulo === '')        $errores[] = "El título es obligatorio.";
  if ($id_tipo <= 0)         $errores[] = "Selecciona un tipo (alquiler/venta).";
  if ($agente_id <= 0)       $errores[] = "Selecciona un agente.";
  if ($descripcion === '')   $errores[] = "La descripción es obligatoria.";
  if ($ubicacion === '')     $errores[] = "La ubicación es obligatoria.";
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_pub)) $errores[] = "Fecha inválida (YYYY-MM-DD).";
  if ($precio < 0)           $errores[] = "El precio no puede ser negativo.";

  // Verificar FK de tipo
  $existeTipo = $conn->prepare("SELECT 1 FROM tipo_alquiler WHERE id=?");
  $existeTipo->bind_param("i", $id_tipo);
  $existeTipo->execute();
  $existeTipo->store_result();
  if ($existeTipo->num_rows === 0) $errores[] = "El tipo seleccionado no existe.";
  $existeTipo->close();

  // Verificar FK de agente
  $existeAgente = $conn->prepare("SELECT 1 FROM usuario WHERE id=?");
  $existeAgente->bind_param("i", $agente_id);
  $existeAgente->execute();
  $existeAgente->store_result();
  if ($existeAgente->num_rows === 0) $errores[] = "El agente seleccionado no existe.";
  $existeAgente->close();

  // Subida de imagen
  $rutaImagen = '';
  if (!empty($_FILES['imagen']['name'])) {
    $dir = __DIR__ . '/../uploads/propiedades';
    if (!is_dir($dir)) {
      @mkdir($dir, 0777, true);
    }
    $nombreSeguro = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['imagen']['name']);
    $destinoFs = $dir . '/' . $nombreSeguro;

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destinoFs)) {
      $rutaImagen = 'uploads/propiedades/' . $nombreSeguro; // ruta relativa para BD
    } else {
      $errores[] = "No se pudo subir la imagen.";
    }
  } else {
    // Columna imagen es NOT NULL: usa un placeholder existente
    $rutaImagen = 'uploads/placeholder.jpg';
  } // <-- ESTE } FALTABA EN TU CÓDIGO

  if (empty($errores)) {
    $sql = "INSERT INTO propiedades
                (id_tipo, destacada, titulo, agente_id, imagen, descripcion, ubicacion, fecha_pub, precio)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
      "iisissssi",
      $id_tipo,
      $destacada,
      $titulo,
      $agente_id,
      $rutaImagen,
      $descripcion,
      $ubicacion,
      $fecha_pub,
      $precio
    );

    if ($stmt->execute()) {
      $nuevoId = $stmt->insert_id;
      $msg = "✅ Propiedad creada (ID: $nuevoId).";
    } else {

      $msg = "❌ Error al insertar: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $msg = "⚠️ Corrige:<ul><li>" . implode("</li><li>", $errores) . "</li></ul>";
  }
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="../assets/admin-propiedad.css">
  <title>Crear Propiedad</title>
</head>

<body>
  <h1>Nueva Propiedad</h1>
  <?php if (!empty($msg)): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <div>
      <label>Título *</label>
      <input type="text" name="titulo" required>
    </div>

    <div>
      <label>Tipo *</label>
      <select name="id_tipo" required>
        <option value="">-- Selecciona --</option>
        <?php while ($t = $tipos->fetch_assoc()): ?>
          <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div>
      <label>Agente *</label>
      <select name="agente_id" required>
        <option value="">-- Selecciona --</option>
        <?php while ($a = $agentes->fetch_assoc()): ?>
          <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div>
      <label>¿Destacada?</label>
      <input type="checkbox" name="destacada" value="1">
    </div>

    <div class="full">
      <label>Descripción *</label>
      <textarea name="descripcion" required></textarea>
    </div>

    <div class="full">
      <label>Ubicación *</label>
      <input type="text" name="ubicacion" required>
    </div>

    <div>
      <label>Fecha de publicación *</label>
      <input type="date" name="fecha_pub" value="<?= date('Y-m-d') ?>" required>
    </div>

    <div>
      <label>Precio *</label>
      <input type="number" name="precio" value="0" min="0" step="0.01" required>

      <div>
        <label>Imagen *</label>
        <input type="file" name="imagen" accept="image/*">
      </div>

      <div class="full actions">
        <button type="submit">Guardar</button>
         <a class="btn btn-ghost" href="javascript:history.back()">Volver↩</a>
      </div>
  </form>

  <section class="bloque">
    <?php
    $conn = conectar();

    $sql = "SELECT p.*, t.nombre AS tipo_nombre, u.nombre AS agente_nombre
        FROM propiedades p
        JOIN tipo_alquiler t ON p.id_tipo = t.id
        JOIN usuario u ON p.agente_id = u.id
        ORDER BY p.id DESC";

    $resultado = $conn->query($sql);

    if ($resultado->num_rows > 0): ?>
      <h4>Lista de propiedades</h4>
      <table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse">
        <thead>
          <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Tipo</th>
            <th>Agente</th>
            <th>Destacada</th>
            <th>Ubicación</th>
            <th>Fecha</th>
            <th>precio</th>
            <th>Imagen</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $resultado->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['titulo']) ?></td>
              <td><?= htmlspecialchars($row['tipo_nombre']) ?></td>
              <td><?= htmlspecialchars($row['agente_nombre']) ?></td>
              <td><?= $row['destacada'] ? '✅' : '❌' ?></td>
              <td><?= htmlspecialchars($row['ubicacion']) ?></td>
              <td><?= htmlspecialchars($row['fecha_pub']) ?></td>
              <td><?= htmlspecialchars($row['precio']) ?></td>
              <td>
                <img src="../<?= htmlspecialchars($row['imagen']) ?>" alt="img" style="width:80px;height:60px;object-fit:cover">
              </td>
              <td class="acciones">
                <a class="btn btn-ghost btn-sm btn-edit" href="editarPropiedad.php?id=<?= $row['id'] ?>">Editar</a>
                <a class="btn btn-danger btn-sm btn-del" href="eliminarPropiedad.php?id=<?= $row['id'] ?>"
                  onclick="return confirm('¿Seguro que quieres eliminar esta propiedad?');">Eliminar</a>
              </td>

            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No hay propiedades registradas.</p>
    <?php endif; ?>
  </section>



</body>

</html>