<?php

require_once __DIR__ . '/../config/consultasDB.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '../includes/verificacionrol.php';
session_start();
if (!isAdmin()) {
    header('Location: ../login.php');
}

$conn = conectar();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (updateConfig($conn, $_POST, $_FILES)) {
        $msg = "¡Configuración actualizada correctamente!";
    } else {
        $msg = "Error al actualizar la configuración.";
    }
}

$cfg = getConfig($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Personalizar Inicio</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { max-width: 700px; margin: 2em auto; font-family: sans-serif; }
        form { background: #fff; padding: 2em; border-radius: 8px; }
        label { display: block; margin-top: 1em; font-weight: bold; }
        input[type="text"], input[type="email"], textarea, select { width: 100%; padding: 0.5em; }
        .preview { margin: 0.5em 0; }
        .msg { margin: 1em 0; color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Personalizar Página de Inicio</h1>
    <?php if($msg): ?>
        <div class="msg"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Tema de colores:
            <select name="tema">
                <option value="azul-amarillo-gris" <?= ($cfg['tema'] ?? '') === 'azul-amarillo-gris' ? 'selected' : '' ?>>Azul/Amarillo/Gris</option>
                <option value="blanco-gris" <?= ($cfg['tema'] ?? '') === 'blanco-gris' ? 'selected' : '' ?>>Blanco/Gris</option>
            </select>
        </label>

        <label>Icono principal:
            <?php if(!empty($cfg['icono_principal'])): ?>
                <div class="preview"><img src="/uploads/<?= htmlspecialchars($cfg['icono_principal']) ?>" alt="icono principal" height="40"></div>
            <?php endif; ?>
            <input type="file" name="icono_principal" accept="image/*">
        </label>

        <label>Icono blanco:
            <?php if(!empty($cfg['icono_blanco'])): ?>
                <div class="preview"><img src="/uploads/<?= htmlspecialchars($cfg['icono_blanco']) ?>" alt="icono blanco" height="40"></div>
            <?php endif; ?>
            <input type="file" name="icono_blanco" accept="image/*">
             <label>Mensaje:
            <input type="text" name="icono_blanco" maxlength="150" value="<?= htmlspecialchars($cfg['Mensaje'] ?? '') ?>">
        </label>
        </label>

        <label>Banner imagen:
            <?php if(!empty($cfg['banner_imagen'])): ?>
                <div class="preview"><img src="/uploads/<?= htmlspecialchars($cfg['banner_imagen']) ?>" alt="banner" height="60"></div>
            <?php endif; ?>
            <input type="file" name="banner_imagen" accept="image/*">
        </label>

        <label>Mensaje del banner:
            <input type="text" name="banner_mensaje" maxlength="150" value="<?= htmlspecialchars($cfg['banner_mensaje'] ?? '') ?>">
        </label>

        <label>¿Quiénes somos? (texto):
            <textarea name="quienes_somos" rows="4"><?= htmlspecialchars($cfg['quienes_somos'] ?? '') ?></textarea>
        </label>

        <label>Imagen "¿Quiénes somos?":
            <?php if(!empty($cfg['quienes_img'])): ?>
                <div class="preview"><img src="/uploads/<?= htmlspecialchars($cfg['quienes_img']) ?>" alt="quienes somos" height="60"></div>
            <?php endif; ?>
            <input type="file" name="quienes_img" accept="image/*">
        </label>

        <label>Facebook:
            <input type="text" name="facebook" value="<?= htmlspecialchars($cfg['facebook'] ?? '') ?>">
        </label>
        <label>Instagram:
            <input type="text" name="instagram" value="<?= htmlspecialchars($cfg['instagram'] ?? '') ?>">
        </label>
        <label>TikTok:
            <input type="text" name="tiktok" value="<?= htmlspecialchars($cfg['tiktok'] ?? '') ?>">
        </label>
        <label>Dirección:
            <input type="text" name="direccion" value="<?= htmlspecialchars($cfg['direccion'] ?? '') ?>">
        </label>
        <label>Teléfono:
            <input type="text" name="telefono" value="<?= htmlspecialchars($cfg['telefono'] ?? '') ?>">
        </label>
        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($cfg['email'] ?? '') ?>">
        </label>
        <button type="submit">Guardar cambios</button>
    </form>
    <p><a href="../index.php">← Volver al inicio</a></p>
</body>
</html>