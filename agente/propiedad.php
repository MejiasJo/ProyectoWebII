<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/verificacionrol.php';

checkSession('../login.php');
if (!isAngente()) { header('Location: ../login.php'); exit(); }

$conn = conectar();

// Cargar combos
$tipos   = $conn->query("SELECT id, nombre FROM tipo_alquiler ORDER BY id");
$agentes = $conn->query("SELECT id, nombre FROM usuario ORDER BY nombre");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titulo      = trim($_POST['titulo'] ?? '');
  $id_tipo     = (int)($_POST['id_tipo'] ?? 0);

  // Si es agente, ignora lo que venga del form y usa el ID de la sesión
  if (isAngente()) {
    $agente_id = (int)$_SESSION['usuario_id'];
  } else {
    $agente_id = (int)($_POST['agente_id'] ?? 0);
  }

  $destacada   = isset($_POST['destacada']) ? 1 : 0;
  $descripcion = trim($_POST['descripcion'] ?? '');
  $ubicacion   = trim($_POST['ubicacion'] ?? '');  // texto legible
  $fecha_pub   = $_POST['fecha_pub'] ?? date('Y-m-d');
  $precio      = (float)($_POST['precio'] ?? 0);

  // Coordenadas desde el mapa
  $lat         = isset($_POST['lat']) ? (float)$_POST['lat'] : null;
  $lng         = isset($_POST['lng']) ? (float)$_POST['lng'] : null;

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
  $rutaImagen = 'uploads/placeholder.jpg'; // valor por defecto
  if (!empty($_FILES['imagen']['name'])) {
    $dir = __DIR__ . '/../uploads/propiedades';
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
    $nombreSeguro = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['imagen']['name']);
    $destinoFs = $dir . '/' . $nombreSeguro;

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destinoFs)) {
      $rutaImagen = 'uploads/propiedades/' . $nombreSeguro; // ruta relativa para BD
    } else {
      $errores[] = "No se pudo subir la imagen.";
    }
  }

  if (empty($errores)) {
    $sql = "INSERT INTO propiedades
            (id_tipo, destacada, titulo, agente_id, imagen, descripcion, ubicacion, fecha_pub, precio, lat, lng)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
   
    $stmt->bind_param(
      "iisissssiii",
      $id_tipo,
      $destacada,
      $titulo,
      $agente_id,
      $rutaImagen,
      $descripcion,
      $ubicacion,
      $fecha_pub,
      $precio,
      $lat,
      $lng
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
  <title>Crear Propiedad</title>
  <link rel="stylesheet" href="../assets/admin-propiedad.css">

  <!-- Leaflet (mapa) -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
  <h1>Nueva Propiedad</h1>
  <?php if (!empty($msg)): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" autocomplete="off">
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
      <input type="text" name="agente" value="<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>" disabled> 
       <input type="hidden" name="agente_id" value="<?= (int)$_SESSION['usuario_id'] ?>">
    </div>

    <div>
      <label>¿Destacada?</label>
      <input type="checkbox" name="destacada" value="1">
    </div>

    <div class="full">
      <label>Descripción *</label>
      <textarea name="descripcion" required></textarea>
    </div>

    <!-- Dirección + Mapa -->
    <div class="full">
      <label for="direccion">Dirección *</label>
      <input type="text" id="direccion" name="ubicacion" placeholder="Ej: San José, Costa Rica" required>
      <!-- Coordenadas ocultas -->
      <input type="hidden" id="lat" name="lat">
      <input type="hidden" id="lng" name="lng">
      <button type="button" id="btnBuscar" style="margin-top:6px;">Buscar en mapa</button>
      <div id="map" style="height:360px;margin-top:8px;border-radius:10px;overflow:hidden;"></div>
    </div>

    <div>
      <label>Fecha de publicación *</label>
      <input type="date" name="fecha_pub" value="<?= date('Y-m-d') ?>" required>
    </div>

    <div>
      <label>Precio *</label>
      <input type="number" name="precio" value="0" min="0" step="0.01" required>
    </div>

    <div>
      <label>Imagen *</label>
      <input type="file" name="imagen" accept="image/*">
    </div>

    <div class="full actions" style="margin-top:10px;">
      <button type="submit">Guardar</button>
      <a class="btn btn-ghost" href="./dashboard.php">Volver↩</a>
    </div>
  </form>

  <!-- Lista (la dejo como la tenías) -->
  <section class="bloque">
    <?php
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
            <th>Precio</th>
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

  <!-- JS Mapa -->
  <script>
  (function(){
    // Centro por defecto: San José, CR
    const defLat = 9.9281, defLng = -84.0907, defZoom = 12;

    const map = L.map('map').setView([defLat, defLng], defZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19, attribution:'&copy; OpenStreetMap'}).addTo(map);

    const $dir = document.getElementById('direccion');
    const $lat = document.getElementById('lat');
    const $lng = document.getElementById('lng');

    let marker = L.marker([defLat, defLng], {draggable:true}).addTo(map);

    function setFields(lat,lng){
      $lat.value = (+lat).toFixed(7);
      $lng.value = (+lng).toFixed(7);
    }
    setFields(defLat,defLng);

    function reverseGeocode(lat,lng){
      fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
        .then(r=>r.json())
        .then(d=>{ if(d && d.display_name) $dir.value = d.display_name; })
        .catch(()=>{});
    }

    marker.on('dragend', ()=>{
      const p = marker.getLatLng();
      setFields(p.lat, p.lng);
      reverseGeocode(p.lat, p.lng);
    });

    map.on('click', (e)=>{
      marker.setLatLng(e.latlng);
      setFields(e.latlng.lat, e.latlng.lng);
      reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    document.getElementById('btnBuscar').addEventListener('click', ()=>{
      const q = $dir.value.trim();
      if(!q) return;
      fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' + encodeURIComponent(q))
        .then(r=>r.json())
        .then(arr=>{
          if(arr && arr.length){
            const d = arr[0];
            const lat = parseFloat(d.lat), lng = parseFloat(d.lon);
            marker.setLatLng([lat,lng]);
            map.setView([lat,lng], 16);
            setFields(lat,lng);
            if(d.display_name) $dir.value = d.display_name;
          }
        })
        .catch(()=>{});
    });
  })();
  </script>
</body>
</html>
