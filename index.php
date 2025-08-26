<?php
require_once __DIR__ . '/config/consultasDB.php';
require_once __DIR__ . '/config/conexion.php';

$conn = conectar();
$cfg = getConfig($conn);

$q    = trim($_GET['q'] ?? '');
$like = '%' . $q . '%';

if ($q !== '') {
  $stmt = $conn->prepare("
    SELECT id, titulo, ubicacion,
           imagen      AS imagen_principal,
           descripcion AS descripcion_breve,
           precio          AS precio
    FROM propiedades
    WHERE destacada = 1
      AND (descripcion LIKE ? OR ubicacion LIKE ?)
    ORDER BY id DESC
  ");
  $stmt->bind_param("ss", $like, $like);
  $stmt->execute();
  $destacadas = $stmt->get_result();

  $stmt2 = $conn->prepare("
    SELECT id, titulo, ubicacion,
           imagen      AS imagen_principal,
           descripcion AS descripcion_breve,
           precio          AS precio
    FROM propiedades
    WHERE id_tipo = 2
      AND (descripcion LIKE ? OR ubicacion LIKE ?)
    ORDER BY id DESC
  ");
  $stmt2->bind_param("ss", $like, $like);
  $stmt2->execute();
  $ventas = $stmt2->get_result();


  $stmt3 = $conn->prepare("
    SELECT id, titulo, ubicacion,
           imagen      AS imagen_principal,
           descripcion AS descripcion_breve,
           precio           AS precio
    FROM propiedades
    WHERE id_tipo = 1
      AND (descripcion LIKE ? OR ubicacion LIKE ?)
    ORDER BY id DESC
  ");
  $stmt3->bind_param("ss", $like, $like);
  $stmt3->execute();
  $alquiler = $stmt3->get_result();
} else {

  $destacadas = $conn->query("
    SELECT id, titulo, ubicacion,
           imagen AS imagen_principal,
           descripcion AS descripcion_breve,
           precio AS precio
    FROM propiedades
    WHERE destacada = 1
    ORDER BY id DESC
  ");

  $ventas = $conn->query("
    SELECT id, titulo, ubicacion,
           imagen AS imagen_principal,
           descripcion AS descripcion_breve,
           precio AS precio
    FROM propiedades
    WHERE id_tipo = 2
    ORDER BY id DESC
  ");

  $alquiler = $conn->query("
    SELECT id, titulo, ubicacion,
           imagen AS imagen_principal,
           descripcion AS descripcion_breve,
           precio AS precio
    FROM propiedades
    WHERE id_tipo = 1
    ORDER BY id DESC
  ");
}


$tema = $cfg['tema'] ?? 'azul-amarillo-gris';
$paleta = $tema === 'blanco-gris'
  ? ['bg' => '#f2f2f2', 'fg' => '#333', 'prim' => '#939597', 'sec' => '#e9ecef', 'dark' => '#222']
  : ['bg' => '#e9ecf4', 'fg' => '#091337', 'prim' => '#00699e', 'sec' => '#c1d72e', 'dark' => '#091337'];

$logoColor   = !empty($cfg['icono_blanco'])    ? 'uploads/' . $cfg['icono_blanco']    : '';
$logoNormal  = !empty($cfg['icono_principal']) ? 'uploads/' . $cfg['icono_principal'] : '';
$bannerImg   = !empty($cfg['banner_imagen'])   ? 'uploads/' . $cfg['banner_imagen']   : '';
$aboutImg    = !empty($cfg['quienes_img'])     ? 'uploads/' . $cfg['quienes_img']     : '';
$mensaje     = $cfg['banner_mensaje'] ?? 'PERMITENOS AYUDARTE A CUMPLIR TUS SUEÑOS';


function resaltar($texto, $q)
{
  $safe = htmlspecialchars($texto ?? '');
  if ($q === '') return $safe;
  $pat = '/' . preg_quote($q, '/') . '/i';
  return preg_replace($pat, '<mark>$0</mark>', $safe);
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>UTN Solutions Real State</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php if ($logoNormal): ?>
    <link rel="icon" href="<?= htmlspecialchars($logoNormal) ?>"><?php endif; ?>
  <link rel="stylesheet" href="assets/index.css">

</head>

<body>

  <header class="topbar">
    <div class="left">
      <?php if ($logoColor || $logoNormal): ?>
        <img class="logo" src="<?= htmlspecialchars($logoColor ?: $logoNormal) ?>" alt="logo">
      <?php else: ?>
        <strong class="brand">UTN SOLUTIONS<br>REAL STATE</strong>
      <?php endif; ?>
      <div class="social">
        <?php if (!empty($cfg['facebook'])): ?>
          <a aria-label="Facebook" href="<?= htmlspecialchars($cfg['facebook']) ?>" target="_blank"><img src="./assets/img/facebook.png" alt=""></a>
        <?php endif; ?>

        <?php if (!empty($cfg['instagram'])): ?>
          <a aria-label="Instagram" href="<?= htmlspecialchars($cfg['instagram']) ?>" target="_blank"><img src="./assets/img/instagram.png" alt=""></a>
        <?php endif; ?>
        <?php if (!empty($cfg['tiktok'])): ?>
          <a aria-label="TikTok" href="<?= htmlspecialchars($cfg['tiktok']) ?>" target="_blank"><img src="./assets/img/tiktok.png" alt=""></a>
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
      <!-- Buscador (action a index.php y valor preservado) -->
      <form class="search" action="index.php" method="get">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por descripción o ubicación…">
        <button type="submit" aria-label="Buscar">🔍</button>
      </form>
      <div class="user-badge" title="Mi cuenta"><a href="login.php">Iniciar Sesión👤</a></div>
      <div class="user-badge" title="Mi cuenta"><a href="salir.php">Cerrar Sesión📤</a></div>
    </div>
  </header>

  <!-- HERO -->
  <section class="hero" style="background-image: url('<?= htmlspecialchars($bannerImg) ?>')">
    <div class="overlay"></div>
    <h1><?= htmlspecialchars(mb_strtoupper($mensaje)) ?></h1>
  </section>

  <!-- QUIÉNES SOMOS -->
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
          <?php if (!empty($cfg['direccion'])): ?><li><strong>Dirección:</strong> <?= htmlspecialchars($cfg['direccion']) ?></li><?php endif; ?>
          <?php if (!empty($cfg['telefono'])):  ?><li><strong>Teléfono:</strong> <?= htmlspecialchars($cfg['telefono']) ?></li><?php endif; ?>
          <?php if (!empty($cfg['email'])):     ?><li><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($cfg['email']) ?>"><?= htmlspecialchars($cfg['email']) ?></a></li><?php endif; ?>
        </ul>
      </div>
      <div class="image">
        <img src="<?= htmlspecialchars($aboutImg) ?>" alt="Equipo">
      </div>
    </div>
  </section>

  <!-- DESTACADAS -->
  <section id="destacadas">
    <h2>Propiedades Destacadas <?= $q !== '' ? '— búsqueda' : '' ?></h2>
    <div class="grid">
      <?php while ($row = $destacadas->fetch_assoc()): ?>
        <div class="card">
          <img src="<?= htmlspecialchars($row['imagen_principal'] ?: 'https://picsum.photos/seed/' . $row['id'] . '/640/360') ?>" alt="Portada">
          <h3><?= htmlspecialchars($row['titulo']) ?></h3>
          <p class="loc">📍 <?= resaltar($row['ubicacion'] ?? '', $q) ?></p>
          <p><?= resaltar($row['descripcion_breve'], $q) ?></p>
          <p>₡<?= resaltar($row['precio'], $q) ?></p>
          <a href="propiedades.php?id=<?= (int)$row['id'] ?>">Ver detalles</a>
        </div>
      <?php endwhile; ?>
    </div>
    <div class="more-wrap"><button class="more-btn" data-target="destacadas" aria-expanded="false">Ver más propiedades</button></div>
  </section>


  <!-- VENTAS -->
  <section id="ventas">
    <h2>Propiedades en Venta <?= $q !== '' ? '— búsqueda' : '' ?></h2>
    <div class="grid">
      <?php while ($row = $ventas->fetch_assoc()): ?>
        <div class="card">
          <img src="<?= htmlspecialchars($row['imagen_principal'] ?: 'https://picsum.photos/seed/' . $row['id'] . '/640/360') ?>" alt="Portada">
          <h3><?= htmlspecialchars($row['titulo']) ?></h3>
          <p class="loc">📍 <?= resaltar($row['ubicacion'] ?? '', $q) ?></p>
          <p><?= resaltar($row['descripcion_breve'], $q) ?></p>
          <p>₡<?= resaltar($row['precio'], $q) ?></p>
          <a href="propiedades.php?id=<?= (int)$row['id'] ?>">Ver detalles</a>
        </div>
      <?php endwhile; ?>
    </div>
    <div class="more-wrap"><button class="more-btn" data-target="ventas" aria-expanded="false">Ver más propiedades</button></div>
  </section>


  <!-- ALQUILERES -->
  <section id="alquileres">
    <h2>Propiedades en Alquiler <?= $q !== '' ? '— búsqueda' : '' ?></h2>
    <div class="grid">
      <?php while ($row = $alquiler->fetch_assoc()): ?>
        <div class="card">
          <img src="<?= htmlspecialchars($row['imagen_principal'] ?: 'https://picsum.photos/seed/' . $row['id'] . '/640/360') ?>" alt="Portada">
          <h3><?= htmlspecialchars($row['titulo']) ?></h3>
          <p class="loc">📍 <?= resaltar($row['ubicacion'] ?? '', $q) ?></p>
          <p><?= resaltar($row['descripcion_breve'], $q) ?></p>
          <p>₡<?= resaltar($row['precio'], $q) ?></p>
          <a href="propiedades.php?id=<?= (int)$row['id'] ?>">Ver detalles</a>
        </div>
      <?php endwhile; ?>
    </div>
    <div class="more-wrap"><button class="more-btn" data-target="alquileres" aria-expanded="false">Ver más propiedades</button></div>
  </section>

  <footer class="footer" id="contacto">
    <div class="footer-content">
      <!-- Información de contacto -->
      <div class="contact-info">
        <p><img src="./assets/img/location.png" alt="Ubicación"><strong>Dirección;</strong> <?= htmlspecialchars($cfg['direccion'] ?? 'Cañas Guanacaste, 100 mts Este Parque de Cañas') ?></p>
        <p><img src="./assets/img/phone.png" alt="Telefono"><strong>Teléfono;</strong> <?= htmlspecialchars($cfg['telefono'] ?? '8890-2030') ?></p>
        <p><img src="./assets/img/letter.png" alt="correo"><strong>Email;</strong> <?= htmlspecialchars($cfg['email'] ?? 'info@utnrealestate.com') ?></p>
      </div>

      <!-- Logo y redes sociales -->
      <div class="brand-section">
        <?php if ($logoColor || $logoNormal): ?>
          <img class="logo" src="<?= htmlspecialchars($logoColor ?: $logoNormal) ?>" alt="logo">
        <?php else: ?>
          <div class="brand-text">
            <h2>UTN SOLUTIONS</h2>
            <h3>REAL ESTATE</h3>
          </div>
        <?php endif; ?>

        <div class="social-icons">
          <?php if (!empty($cfg['facebook'])): ?>
            <a href="<?= htmlspecialchars($cfg['facebook']) ?>" target="_blank">
              <img src="./assets/img/facebook.png" alt="Facebook">
            </a>
          <?php endif; ?>
          <?php if (!empty($cfg['instagram'])): ?>
            <a href="<?= htmlspecialchars($cfg['instagram']) ?>" target="_blank">
              <img src="./assets/img/instagram.png" alt="Instagram">
            </a>
          <?php endif; ?>
          <?php if (!empty($cfg['tiktok'])): ?>
            <a href="<?= htmlspecialchars($cfg['tiktok']) ?>" target="_blank">
              <img src="./assets/img/tiktok.png" alt="TikTok">
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Formulario de contacto -->
      <div class="contact-form">
        <h3>Contáctanos</h3>
        <form >
          <label for="nombre">Nombre:</label> 
          <input type="text" name="nombre" id="nombre" required> 
          <label for="email">Email:</label> 
          <input type="email" name="email" id="email" required> 
          <label for="telefono">Telefono:</label> 
          <input type="text" name="telefono" id="telefono" required> 
          <label for="mensaje">Mensaje:</label>
          <textarea name="mensaje" id="mensaje" required></textarea>
          <input type="hidden" name="destino" id="destino" value="<?= htmlspecialchars($cfg['email'] ?? '')?>">
          <button type="submit" id="btnEnvio">Enviar</button>
        </form>
      </div>
    </div>

    <!-- Línea de copyright -->
    
  </footer>
  <div class="copyright">
      <p>© Derechos Reservados <?= date('Y') ?> — <a href="admin/propiedadAdmin.php">Personalizar</a></p>
    </div>
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
          if (expanded) section.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        });
      });
    });

    const btnEnvio = document.getElementById('btnEnvio');
    btnEnvio.addEventListener('click', function(e){
      e.preventDefault();
      const email = document.getElementById('email').value;
      const nombre = document.getElementById('nombre').value;
      const telefono = document.getElementById('telefono').value;
      const mensaje = document.getElementById('mensaje').value;
      const destino = document.getElementById('destino').value;

      window.location.href = `mailto:${destino}?subject=Contacto de ${nombre}&body=Nombre: ${nombre}%0AEmail: ${email}%0ATeléfono: ${telefono}%0AMensaje: ${mensaje}`;
    });
  </script>

</body>

</html>