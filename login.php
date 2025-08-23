<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/consultasDB.php';
require_once __DIR__ . '/includes/verificacionrol.php';
require_once __DIR__ . '/includes/seguridad.php';
require_once __DIR__ . '/includes/alert.php';

session_start();

if (!empty($_SESSION['usuario_id'])) {
    if (isAdmin()) {
        header('Location: ./admin/dashboard.php');
    } else if (isAngente()) {
        header('Location: ./agente/dashboard.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login']) && $_POST['login'] == 1) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $conn = conectar();

    $usuario = loginUsuario($conn, $user, $pass);
    var_dump($usuario);
    if ($usuario) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['privilegio'] = $usuario['privilegio'];
        if (isAdmin()) {
            header('Location: ./admin/dashboard.php');
        } else if (isAngente()) {
            header('Location: ./agente/dashboard.php');
        }
    } else {
        alertMenssage("Usuario o contraseña incorrectos", "danger");
    }
    $_POST['login'] = 0;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./assets/alert.css">
</head>

<body>
    <div class="container">
        <form action="" method="post">
            <h4>Iniciar Sessión</h4>
            <label for="user">Usuario:</label>
            <input type="text" name="user" id="user" required>
            <label for="pass">Contraseña:</label>
            <input type="text" name="pass" id="pass" required>
            <button type="submit" value="1" name="login"> Iniciar Sessión</button>
        </form>
    </div>
</body>

</html>