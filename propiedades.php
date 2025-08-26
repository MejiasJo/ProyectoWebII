<?php
require_once __DIR__ . '/config/conexion.php';
$conn = conectar();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  die("❌ Propiedad no válida");
}

$sql = "
  SELECT p.id, p.titulo, p.descripcion, p.ubicacion, p.fecha_pub, p.precio,
         p.imagen,
         u.nombre AS agente, u.telefono AS agente_tel, u.email AS agente_email,
         t.nombre AS tipo
  FROM propiedades p
  JOIN usuario u ON p.agente_id = u.id
  JOIN tipo_alquiler t ON p.id_tipo = t.id
  WHERE p.id = ?
  LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$prop = $result->fetch_assoc();

if (!$prop) {
  die("❌ No se encontró la propiedad");
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($prop['titulo']) ?> - Detalles</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/index.css">
  <style>
    body {
      font-family: system-ui, Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: #f9f9fb;
      color: #333
    }

    .container {
      max-width: 1000px;
      margin: auto;
      padding: 20px
    }

    .card {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1)
    }

    img.main {
      max-width: 100%;
      border-radius: 10px;
      margin-bottom: 20px
    }

    h1 {
      margin-top: 0;
      color: #00699e
    }

    .meta {
      color: #666;
      font-size: 0.9em;
      margin-bottom: 15px
    }

    .grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px
    }

    .agente {
      background: #f4f6f9;
      padding: 15px;
      border-radius: 8px
    }

    .back {
      display: inline-block;
      margin-top: 20px;
      color: #00699e;
      text-decoration: none;
      font-weight: bold
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="card">
      <img src="<?= htmlspecialchars($prop['imagen'] ?: 'https://picsum.photos/seed/' . $prop['id'] . '/800/400') ?>" alt="Imagen" class="main">
      <h1><?= htmlspecialchars($prop['titulo']) ?></h1>
      <p class="meta">
        Tipo: <?= htmlspecialchars(ucfirst($prop['tipo'])) ?> |
        Publicado el <?= htmlspecialchars($prop['fecha_pub']) ?> |
        Ubicación: <?= htmlspecialchars($prop['ubicacion']) ?>
        Precio: <?= htmlspecialchars($prop['precio']) ?> €
      </p>
      <div class="grid">
        <div>
          <h2>Descripción</h2>
          <p><?= nl2br(htmlspecialchars($prop['descripcion'])) ?></p>
        </div>
        <?php if (!empty($prop['lat']) && !empty($prop['lng'])): ?>
          <div id="mapView" style="height:360px;border-radius:10px;overflow:hidden;"></div>
          <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
          <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
          <script>
            const m = L.map('mapView').setView([<?= $prop['lat'] ?>, <?= $prop['lng'] ?>], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
              maxZoom: 19
            }).addTo(m);
            L.marker([<?= $prop['lat'] ?>, <?= $prop['lng'] ?>]).addTo(m);
          </script>
        <?php endif; ?>
        <div class="agente">
          <h3>Agente</h3>
          <p><strong><?= htmlspecialchars($prop['agente']) ?></strong></p>
          <p>📞 <?= htmlspecialchars($prop['agente_tel']) ?></p>
          <p>✉️ <a href="mailto:<?= htmlspecialchars($prop['agente_email']) ?>"><?= htmlspecialchars($prop['agente_email']) ?></a></p>
        </div>
      </div>

      <a class="back" href="index.php">← Volver al inicio</a>
    </div>
  </div>



</body>

</html>