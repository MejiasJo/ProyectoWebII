<?php
require_once __DIR__ . '/config/consultasDB.php';
require_once __DIR__ . '/config/conexion.php';

$conn = conectar();
$cfg = getConfig($conn);

$sqlDestacadas = "
  SELECT id, titulo,
         imagen       AS imagen_principal,
         descripcion  AS descripcion_breve,
         0            AS precio
  FROM propiedades
  WHERE destacada = 1
  ORDER BY id DESC";
$destacadas = $conn->query($sqlDestacadas);

$sqlVentas = "
  SELECT id, titulo,
         imagen       AS imagen_principal,
         descripcion  AS descripcion_breve,
         0            AS precio
  FROM propiedades
  WHERE id_tipo = 2
  ORDER BY id DESC";
$ventas = $conn->query($sqlVentas);

$sqlAlquiler = "
  SELECT id, titulo,
         imagen       AS imagen_principal,
         descripcion  AS descripcion_breve,
         0            AS precio
  FROM propiedades
  WHERE id_tipo = 1
  ORDER BY id DESC";
$alquiler = $conn->query($sqlAlquiler);

$tema = $cfg['tema'] ?? 'azul-amarillo-gris';
$paleta = $tema === 'blanco-gris'
  ? ['bg'=>'#f2f2f2','fg'=>'#333','prim'=>'#939597','sec'=>'#e9ecef','dark'=>'#222']
  : ['bg'=>'#e9ecf4','fg'=>'#091337','prim'=>'#00699e','sec'=>'#c1d72e','dark'=>'#091337'];

$logoColor   = !empty($cfg['icono_blanco'])    ? 'uploads/'.$cfg['icono_blanco']    : '';
$logoNormal  = !empty($cfg['icono_principal']) ? 'uploads/'.$cfg['icono_principal'] : '';
$bannerImg   = !empty($cfg['banner_imagen'])   ? 'uploads/'.$cfg['banner_imagen']   : '';
$aboutImg    = !empty($cfg['quienes_img'])     ? 'uploads/'.$cfg['quienes_img']     : '';
$mensaje     = $cfg['banner_mensaje'] ?? 'PERMITENOS AYUDARTE A CUMPLIR TUS SUEÑOS';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>UTN Solutions Real State</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php if($logoNormal): ?><link rel="icon" href="<?= htmlspecialchars($logoNormal) ?>"><?php endif; ?>
  <link rel="stylesheet" href="assets/index.css">
  <style>
    :root{
      --bg: <?= $paleta['bg'] ?>; --fg: <?= $paleta['fg'] ?>; --prim: <?= $paleta['prim'] ?>; --sec: <?= $paleta['sec'] ?>; --dark: <?= $paleta['dark'] ?>;
    }
  </style>
</head>
<body>

<header class="topbar">
  <div class="left">
    <?php if($logoColor || $logoNormal): ?>
      <img class="logo" src="<?= htmlspecialchars($logoColor ?: $logoNormal) ?>" alt="logo">
    <?php else: ?>
      <strong class="brand">UTN SOLUTIONS<br>REAL STATE</strong>
    <?php endif; ?>
    <div class="social">
      <?php if(!empty($cfg['facebook'])): ?>
        <a aria-label="Facebook" href="<?= htmlspecialchars($cfg['facebook']) ?>" target="_blank"><?php include __DIR__.'/assets/svg/facebook.svg.php'; ?></a>
      <?php endif; ?>
      <?php if(!empty($cfg['instagram'])): ?>
        <a aria-label="Instagram" href="<?= htmlspecialchars($cfg['instagram']) ?>" target="_blank"><?php include __DIR__.'/assets/svg/instagram.svg.php'; ?></a>
      <?php endif; ?>
      <?php if(!empty($cfg['tiktok'])): ?>
        <a aria-label="TikTok" href="<?= htmlspecialchars($cfg['tiktok']) ?>" target="_blank"><?php include __DIR__.'/assets/svg/tiktok.svg.php'; ?></a>
      <?php endif; ?>
    </div>
  </div>

  <nav class="menu">
    <a href="#">INICIO</a> <span>|</span>
    <a href="#quienes">QUIÉNES SOMOS</a> <span>|</span>
    <a href="#alquileres">ALQUILERES</a> <span>|</span>
    <a href="#ventas">VENTAS</a> <span>|</span>
    <a href="#contacto">CONTÁCTENOS</a>
  </nav>

  <div class="right">
    <form class="search" action="#" method="get">
      <input type="text" name="q" placeholder="Buscar…">
      <button type="submit" aria-label="Buscar">🔍</button>
    </form>
    <div class="user-badge" title="Mi cuenta"><a href="login.php">Iniciar Sesión👤</a></div>
    <div class="user-badge" title="Mi cuenta"><a href="salir.php">Cerrar Sesión📤</a></div>
  </div>
</header>

<section class="hero" style="background-image: url('<?= htmlspecialchars($bannerImg) ?>')">
  <div class="overlay"></div>
  <h1><?= htmlspecialchars(mb_strtoupper($mensaje)) ?></h1>
</section>

<section class="about card" id="quienes">
  <h2>QUIÉNES SOMOS</h2>
  <div class="grid">
    <div class="text">
      <p><?= nl2br(htmlspecialchars($cfg['quienes_somos'] ??
'Somo una empresa de vienes raices, nacida en Centro America, 
en Costa Rica, con el objetivo 
de llegar a otro muchos lugares de centro america 
para ver y rentar cosas de alto nivel y brindarle al cliente el mejor 
servicio que pueda encontrar en todos los lugares .')) ?></p>
      <ul class="contact">
        <?php if(!empty($cfg['direccion'])): ?><li><strong>Dirección:</strong> <?= htmlspecialchars($cfg['direccion']) ?></li><?php endif; ?>
        <?php if(!empty($cfg['telefono'])):  ?><li><strong>Teléfono:</strong> <?= htmlspecialchars($cfg['telefono']) ?></li><?php endif; ?>
        <?php if(!empty($cfg['email'])):     ?><li><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($cfg['email']) ?>"><?= htmlspecialchars($cfg['email']) ?></a></li><?php endif; ?>
      </ul>
    </div>
    <div class="image">
      <img src="<?= htmlspecialchars($aboutImg) ?>" alt="Equipo">
    </div>
  </div>
</section>

<section id="destacadas">
  <h2>Propiedades Destacadas</h2>
  <div class="grid">
    <?php while($row = $destacadas->fetch_assoc()): ?>
      <div class="card">
        <img src="<?= htmlspecialchars($row['imagen_principal'] ?: 'https://picsum.photos/seed/'.$row['id'].'/640/360') ?>" alt="Portada">
        <h3><?= htmlspecialchars($row['titulo']) ?></h3>
        <p><?= htmlspecialchars($row['descripcion_breve']) ?></p>
        <?php if (!empty($row['precio'])): ?>
          <p><strong>$<?= number_format($row['precio'],2) ?></strong></p>
        <?php endif; ?>
        <a href="propiedad.php?id=<?= (int)$row['id'] ?>">Ver detalles</a>
      </div>
    <?php endwhile; ?>
  </div>
  <div class="more-wrap">
    <button class="more-btn" data-target="destacadas" aria-expanded="false">Ver más propiedades</button>
  </div>
</section>

<section id="alquileres">
  <h2>Propiedades en Alquiler</h2>
  <div class="grid">
    <?php while($row = $alquiler->fetch_assoc()): ?>
      <div class="card">
        <img src="<?= htmlspecialchars($row['imagen_principal'] ?: 'https://picsum.photos/seed/'.$row['id'].'/640/360') ?>" alt="Portada">
        <h3><?= htmlspecialchars($row['titulo']) ?></h3>
        <p><?= htmlspecialchars($row['descripcion_breve']) ?></p>
        <?php if (!empty($row['precio'])): ?>
          <p><strong>$<?= number_format($row['precio'],2) ?></strong></p>
        <?php endif; ?>
        <a href="propiedad.php?id=<?= (int)$row['id'] ?>">Ver detalles</a>
      </div>
    <?php endwhile; ?>
  </div>
  
  <div class="more-wrap">
    <button class="more-btn" data-target="alquileres" aria-expanded="false">Ver más propiedades</button>
  </div>
</section>


<section id="ventas">
  <h2>Propiedades en Venta</h2>
  <div class="grid">
    <?php while($row = $ventas->fetch_assoc()): ?>
      <div class="card">
        <img src="<?= htmlspecialchars($row['imagen_principal'] ?: 'https://picsum.photos/seed/'.$row['id'].'/640/360') ?>" alt="Portada">
        <h3><?= htmlspecialchars($row['titulo']) ?></h3>
        <p><?= htmlspecialchars($row['descripcion_breve']) ?></p>
        <?php if (!empty($row['precio'])): ?>
          <p><strong>$<?= number_format($row['precio'],2) ?></strong></p>
        <?php endif; ?>
        <a href="propiedad.php?id=<?= (int)$row['id'] ?>">Ver detalles</a>
      </div>
    <?php endwhile; ?>
  </div>
  
  <div class="more-wrap">
    <button class="more-btn" data-target="ventas" aria-expanded="false">Ver más propiedades</button>
  </div>
</section>

<footer class="footer" id="contacto">
  <small>© <?= date('Y') ?> — <a href="admin/propiedadAdmin.php">Personalizar</a></small>
</footer>


<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.more-btn').forEach(btn => {
    const targetId = btn.dataset.target;
    const section = document.getElementById(targetId);
    if (!section) return;

    const cards = section.querySelectorAll('.grid .card');

   
    if (cards.length <= 3) {
      btn.style.display = 'none';
      return;
    }

    btn.addEventListener('click', () => {
      const expanded = section.classList.toggle('expanded');
      btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      btn.textContent = expanded ? 'Ver menos' : 'Ver más propiedades';
      // Opcional: scroll suave al abrir
      if (expanded) {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
});
</script>

</body>
</html>
