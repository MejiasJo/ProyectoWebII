<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/consultasDB.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/verificacionrol.php';
require_once __DIR__ . '/../includes/alert.php';
session_start();
if (!isAngente()) {
    header('Location: ../login.php');
}

$conn = conectar();
if (getUsuario($conn, $_SESSION['usuario_id'])->num_rows > 0) {
    $usuario = getUsuario($conn, $_SESSION['usuario_id'])->fetch_assoc();
} else {
    alertMenssage("Usuario no encontrado", "danger");
    header('Location: ./usuarios.php');
}
$conn->close();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actulizar']) && $_POST['actulizar'] == 1) {
    $id = $_SESSION['usuario_id'];
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $user = $_POST['user'];
    $rol = $_POST['rol'];
    
    $conn = conectar();

    $_POST['actulizar'] = 0;
    if (!empty($_POST['pass'])) {
        $pass = encryptPassword($_POST['pass']);
        $resultado = updateUsuarioComplento($conn, $id, $nombre, $telefono, $email, $user, $pass, $rol, 0);
    } else {
        $resultado = updateUsuarioSinPass($conn, $id, $nombre, $telefono, $email, $user, $rol);
    }

    $mensaje = $resultado ? 'Datos actualizados exitosamente' : 'Error al actualizar los datos';
    $tipo = $resultado ? 'success' : 'danger';
    
    alertMenssage($mensaje, $tipo);
}

?> 

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/alert.css">
    <link rel="stylesheet" href="../assets/usuarios.css">
</head>

<body>
    <div class="container">
        <section class="bloque">
            <form action="" method="post">
                <h4>Actulizar Datos</h4>
                <label for="nombre">Nombre Completo:</label>
                <input type="text" name="nombre" id="nombre" value="<?= $usuario['nombre'] ?>" required>
                <label for="telefono">Telefono:</label>
                <input type="text" name="telefono" id="telefono" pattern="^[0-9]{8,12}$" value="<?= $usuario['telefono'] ?>" required>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= $usuario['email'] ?>" required>
                <label for="user">Usuario:</label>
                <input type="text" name="user" id="user" value="<?= $usuario['usuario'] ?>" required>
                <div class="password-field">
                    <label for="pass">Contraseña:</label>
                    <div class="password-container">
                        <input type="password" name="pass" id="pass">
                        <span class="toggle-password" onclick="togglePassword('pass')">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
                            </svg>
                        </span>
                    </div>
                </div>
                <label for="rol">Rol:</label>
                <select name="rol" id="rol" required>
                    <?php
                    $conn = conectar();
                    $resultado = getRoles($conn);
                    if ($resultado->num_rows > 0) {
                        while ($row = $resultado->fetch_assoc()) {
                            if ($row['id'] == $usuario['privilegio']) {
                                echo "<option default value='" . $row['id'] . "' selected>" . $row['nombre'] . "</option>";
                            }
                        }
                    } else {
                        echo "<option value=''>No hay roles disponibles</option>";
                    }
                    $conn->close();
                    ?>
                </select>
                <button type="submit" value="1" name="actulizar">Actulizar</button>
            </form>
        </section>
        <button><a href="./dashboard.php">↩ Volver</a></button>
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