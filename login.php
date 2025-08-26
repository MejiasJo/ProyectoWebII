<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/consultasDB.php';
require_once __DIR__ . '/includes/verificacionrol.php';
require_once __DIR__ . '/includes/seguridad.php';
require_once __DIR__ . '/includes/alert.php';

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.use_strict_mode', 1);

session_start();

if (!empty($_SESSION['usuario_id'])) {
    if (isAdmin())   { header('Location: ./admin/dashboard.php');  exit; }
    if (isAngente()) { header('Location: ./agente/dashboard.php'); exit; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login']) && $_POST['login'] == '1') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';

    $conn = conectar();
    $usuario = loginUsuario($conn, $user, $pass);

    if ($usuario) {
        session_regenerate_id(true);

        $_SESSION['usuario_id']      = (int)$usuario['id'];
        $_SESSION['usuario_nombre']  = $usuario['nombre'];
        $_SESSION['privilegio']      = (int)$usuario['privilegio'];
        $_SESSION['primer_ingreso']   = (int)$usuario['primerIngreso'];
        $_SESSION['usuario']         = $usuario['usuario'] ?? $user;

        if (isPrimerIngreso()) {
            if (isAdmin())   { header('Location: ./admin/editarUsuario.php?id='.$_SESSION['usuario_id']);  exit; }
            if (isAngente()) { header('Location: ./agente/editarUsuario?id='.$_SESSION['usuario_id']); exit; }
        } else {
            if (isAdmin())   { header('Location: ./admin/dashboard.php');  exit; }
            if (isAngente()) { header('Location: ./agente/dashboard.php'); exit; }
        }
        header('Location: ./index.php'); exit;
    } else {
        alertMenssage("Usuario o contraseña incorrectos", "danger");
    }

    $_POST['login'] = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="./assets/alert.css">
    <link rel="stylesheet" href="./assets/login.css">
</head>
<body>
    <div class="container">
        <form action="" method="post" autocomplete="off">
            <h4>Iniciar Sesión</h4>

            <label for="user">Usuario:</label>
            <input type="text" name="user" id="user" required>

            <div class="password-field">
                <label for="pass">Contraseña:</label>
                <div class="password-container">
                    <input type="password" name="pass" id="pass" required>
                    <span class="toggle-password" onclick="togglePassword('pass')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </span>
                </div>
            </div>

            <button type="submit" value="1" name="login">Iniciar Sesión</button>
        </form>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.parentElement.querySelector('.toggle-password');
            if (input.type === 'password') {
                input.type = 'text';
                toggle.classList.add('active');
            } else {
                input.type = 'password';
                toggle.classList.remove('active');
            }
        }
    </script>
</body>
</html>
