<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/verificacionrol.php';

checkSession('../login.php');
if (!isAdmin()) { header('Location: ../login.php'); exit(); }

$conn = conectar();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); exit('ID inválido'); }

if (isAdmin()) {
    $stmt = $conn->prepare("SELECT * FROM propiedades WHERE id=?");
    $stmt->bind_param("i", $id);
} else {
    $owner = (int)$_SESSION['usuario_id'];
    $stmt = $conn->prepare("SELECT * FROM propiedades WHERE id=? AND agente_id=?");
    $stmt->bind_param("ii", $id, $owner);
}
$stmt->execute();
$prop = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$prop) { http_response_code(403); exit('No autorizado o no existe'); }

$tipos = $conn->query("SELECT id, nombre FROM tipo_alquiler ORDER BY id");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tipo    = (int)($_POST['id_tipo'] ?? $prop['id_tipo']);
    $destacada  = isset($_POST['destacada']) ? 1 : 0;
    $titulo     = trim($_POST['titulo'] ?? $prop['titulo']);
    $descripcion= trim($_POST['descripcion'] ?? $prop['descripcion']);
    $ubicacion  = trim($_POST['ubicacion'] ?? $prop['ubicacion']);
    $fecha_pub  = trim($_POST['fecha_pub'] ?? $prop['fecha_pub']);
    $precio     = (float)($_POST['precio'] ?? $prop['precio']);
    $lat        = isset($_POST['lat']) ? (float)$_POST['lat'] : ($prop['lat'] ?? null);
    $lng        = isset($_POST['lng']) ? (float)$_POST['lng'] : ($prop['lng'] ?? null);

    $imagen = $prop['imagen'];
    if (!empty($_FILES['imagen']['name']) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
        $allow = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        $mime = mime_content_type($_FILES['imagen']['tmp_name']);
        if (isset($allow[$mime])) {
            $ext = $allow[$mime];
            $dir = __DIR__ . '/../uploads/propiedades/';
            if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
            $fname = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $fname)) {
                $imagen = 'uploads/propiedades/' . $fname;
            }
        }
    }

    if (isAdmin()) {
        $stmt = $conn->prepare("UPDATE propiedades SET 
          id_tipo=?, destacada=?, titulo=?, imagen=?, descripcion=?, ubicacion=?, fecha_pub=?, precio=?, lat=?, lng=?
          WHERE id=?");
        $stmt->bind_param(
          "iisssssdddi",
          $id_tipo, $destacada, $titulo, $imagen, $descripcion, $ubicacion,
          $fecha_pub, $precio, $lat, $lng, $id
        );
    } else {
        $owner = (int)$_SESSION['usuario_id'];
        $stmt = $conn->prepare("UPDATE propiedades SET 
          id_tipo=?, destacada=?, titulo=?, imagen=?, descripcion=?, ubicacion=?, fecha_pub=?, precio=?, lat=?, lng=?
          WHERE id=? AND agente_id=?");
        $stmt->bind_param(
          "iisssssdddii",
          $id_tipo, $destacada, $titulo, $imagen, $descripcion, $ubicacion,
          $fecha_pub, $precio, $lat, $lng, $id, $owner
        );
    }
    $stmt->execute();

    header('Location: ./propiedadAdmin.php'); exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Propiedad</title>
  <link rel="stylesheet" href="../assets/admin-propiedad.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
  <div class="container">
    <h1>Editar propiedad</h1>
    <form action="" method="post" enctype="multipart/form-data">
      <label>Tipo:</label>
      <select name="id_tipo" required>
        <?php while($t = $tipos->fetch_assoc()): ?>
          <option value="<?= (int)$t['id'] ?>" <?= ((int)$t['id']===(int)$prop['id_tipo'])?'selected':'' ?>>
            <?= htmlspecialchars($t['nombre']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label>Título:</label>
      <input type="text" name="titulo" value="<?= htmlspecialchars($prop['titulo']) ?>" required>

      <label>Descripción:</label>
      <textarea name="descripcion" required><?= htmlspecialchars($prop['descripcion']) ?></textarea>

      <label for="direccion">Dirección (texto):</label>
      <input type="text" id="direccion" name="ubicacion" value="<?= htmlspecialchars($prop['ubicacion']) ?>" required>

      <input type="hidden" id="lat" name="lat" value="<?= htmlspecialchars((string)($prop['lat'] ?? '')) ?>">
      <input type="hidden" id="lng" name="lng" value="<?= htmlspecialchars((string)($prop['lng'] ?? '')) ?>">

      <button type="button" id="btnBuscar">Buscar en mapa</button>
      <div id="map" style="height:360px;margin-top:8px;border-radius:10px;overflow:hidden;"></div>

      <label>Fecha publicación:</label>
      <input type="date" name="fecha_pub" value="<?= htmlspecialchars($prop['fecha_pub']) ?>" required>

      <label>Precio:</label>
      <input type="number" step="0.01" name="precio" value="<?= htmlspecialchars($prop['precio']) ?>" required>

      <label>Destacada:</label>
      <input type="checkbox" name="destacada" value="1" <?= $prop['destacada'] ? 'checked':'' ?>>

      <div class="full file-row">
        <label>Reemplazar imagen</label>
        <input class="file" type="file" name="imagen" accept="image/*">
      </div>

      <button type="submit">Guardar</button>
      <a href="./propiedadAdmin.php">Cancelar</a>
    </form>
  </div>

<script>
(function(){
  const defLat = <?= isset($prop['lat']) && $prop['lat'] !== null ? floatval($prop['lat']) : '9.9281' ?>;
  const defLng = <?= isset($prop['lng']) && $prop['lng'] !== null ? floatval($prop['lng']) : '-84.0907' ?>;
  const defZoom = <?= isset($prop['lat']) && $prop['lat'] !== null ? 16 : 12 ?>;

  const map = L.map('map').setView([defLat, defLng], defZoom);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(map);

  const $dir = document.getElementById('direccion');
  const $lat = document.getElementById('lat');
  const $lng = document.getElementById('lng');

  let marker = L.marker([defLat, defLng], {draggable:true}).addTo(map);

  function setFields(lat,lng){
    $lat.value = (+lat).toFixed(7);
    $lng.value = (+lng).toFixed(7);
  }
  setFields(defLat,defLng);

  function rev(lat,lng){
    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
      .then(r=>r.json()).then(d=>{ if(d && d.display_name) $dir.value = d.display_name; }).catch(()=>{});
  }

  marker.on('dragend', ()=>{
    const p = marker.getLatLng();
    setFields(p.lat, p.lng);
    rev(p.lat, p.lng);
  });

  map.on('click', (e)=>{
    marker.setLatLng(e.latlng);
    setFields(e.latlng.lat, e.latlng.lng);
    rev(e.latlng.lat, e.latlng.lng);
  });

  document.getElementById('btnBuscar').addEventListener('click', ()=>{
    const q = $dir.value.trim();
    if(!q) return;
    fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' + encodeURIComponent(q))
      .then(r=>r.json()).then(arr=>{
        if(arr && arr.length){
          const d = arr[0];
          const lat = parseFloat(d.lat), lng = parseFloat(d.lon);
          marker.setLatLng([lat,lng]);
          map.setView([lat,lng], 16);
          setFields(lat,lng);
          if(d.display_name) $dir.value = d.display_name;
        }
      }).catch(()=>{});
  });
})();
</script>
</body>
</html>
