<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/consultasDB.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/alert.php';
require_once __DIR__ . '/../includes/verificacionrol.php';
session_start();
if (!isAdmin()) {
    header('Location: ../login.php');
}

if (isset($_GET['alert']) && isset($_GET['message'])) {
    alertMenssage(urldecode($_GET['message']), $_GET['alert']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registro']) && $_POST['registro'] == 1) {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $user = $_POST['user'];
    $pass = encryptPassword($_POST['user']);
    $rol = $_POST['rol'];
    $conn = conectar();
    $_POST['registro'] = 0;
    if (insertUsuario($conn, $nombre, $telefono, $email, $user, $pass, $rol, 1)) {
        header('Location: ./usuarios.php?alert=success&message=' . urlencode('Usuario registrado exitosamente'));
    } else {
        header('Location: ./usuarios.php?alert=danger&message=' . urlencode('Error al registrar el usuario'));
    }
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inmobiliaria</title>
    <link rel="stylesheet" href="../assets/alert.css">
    <link rel="stylesheet" href="../assets/usuarios.css">
</head>

<body>
    <div class="container">
        <section class="bloque">
            <form action="" method="post">
                <h4>Ingresar Usuario</h4>
                <label for="nombre">Nombre Completo:</label>
                <input type="text" name="nombre" id="nombre" required>
                <label for="telefono">Telefono:</label>
                <input type="text" name="telefono" id="telefono" pattern="^[0-9]{8,12}$" required>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
                <label for="user">Usuario:</label>
                <input type="text" name="user" id="user" required>
                <div class="password-field">
                    <label for="pass">Contraseña:</label>
                    <div class="password-container">
                        <input type="password" name="pass" id="pass" required>
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
                            echo "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
                        }
                    } else {
                        echo "<option value=''>No hay roles disponibles</option>";
                    }
                    $conn->close();
                    ?>
                </select>
                <button type="submit" value="1" name="registro"> Registrar</button>
            </form>
        </section>

        <section class="bloque">
            <?php
            $conn = conectar();
            $resultado = getUsuarios($conn);
            if ($resultado->num_rows > 0): ?>
                <h4>Lista de usuarios</h4>
                <table class="tabla-usuarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Usuario</th>
                            <th>Privilegio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['nombre']; ?></td>
                                <td><?php echo $row['telefono']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['usuario']; ?></td>
                                <td><?php echo $row['privilegio_nombre']; ?></td>
                                <td>
                                    <button name="editar"><a href="./editarUsuario.php?id=<?php echo $row['id'] ?>">Editar</a></button>
                                    |
                                    <button name="eliminar"><a href="./eliminarUsuario.php?id=<?php echo $row['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este usuario?');">Eliminar</a></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
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