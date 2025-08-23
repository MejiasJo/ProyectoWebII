<?php
require_once __DIR__ . '../config/conexion.php';
require_once __DIR__ . '../config/consultasDB.php';
require_once __DIR__ . '../includes/seguridad.php';
require_once __DIR__ . '../includes/alert.php';
require_once __DIR__ . '../includes/verificacionrol.php';
session_start();
if (!isAdmin()) {
    header('Location: ../login.php');
}

$conn = conectar();
if (getUsuario($conn, $_GET['id'])->num_rows > 0) {
    $usuario = getUsuario($conn, $_GET['id'])->fetch_assoc();
} else {
    alertMenssage("Usuario no encontrado", "danger");
    header('Location: ./usuarios.php');
}
$conn->close();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actulizar']) && $_POST['actulizar'] == 1) {
    $id = $_GET['id'];
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $user = $_POST['user'];
    $pass = encryptPassword($_POST['user']);
    $rol = $_POST['rol'];
    $conn = conectar();
    
    if(updateUsuario($conn, $id, $nombre, $telefono, $email, $user, $pass, $rol, 1)){
        alertMenssage("Usuario actulizado exitosamente", "success");
    } else {
        alertMenssage("Error al actulizar el usuario", "danger");
    }
    $_POST['actulizar'] = 0;
    $conn->close();
    header('Location: ./usuarios.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/alert.css">
</head>

<body>
    <div class="container">
        <section class="bloque">
            <form action="" method="post">
                <h4>Actulizar Usuario</h4>
                <label for="nombre">Nombre Completo:</label>
                <input type="text" name="nombre" id="nombre" value="<?= $usuario['nombre'] ?>" required>
                <label for="telefono">Telefono:</label>
                <input type="text" name="telefono" id="telefono" pattern="^[0-9]{8,12}$" value="<?= $usuario['telefono'] ?>" required>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= $usuario['email'] ?>" required>
                <label for="user">Usuario:</label>
                <input type="text" name="user" id="user" value="<?= $usuario['usuario'] ?>" required>
                Verificar como se puede dejar la contraseña en blanco para no cambiarla
                <label for="pass">Contraseña:</label>
                <input type="text" name="pass" id="pass">
                <label for="rol">Rol:</label>
                <select name="rol" id="rol" required>
                    <?php
                    $conn = conectar();
                    $resultado = getRoles($conn);
                    if ($resultado->num_rows > 0) {
                        while ($row = $resultado->fetch_assoc()) {
                            if ($row['id'] == $usuario['privilegio']) {
                                echo "<option default value='" . $row['id'] . "' selected>" . $row['nombre'] . "</option>";
                            } else {
                                echo "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
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
    </div>
</body>

</html>