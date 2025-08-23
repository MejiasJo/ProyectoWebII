<?php
include '../config/conexion.php';
include '../config/consultasDB.php';
include '../includes/seguridad.php';
include '../includes/alert.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registro']) && $_POST['registro'] == 1) {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $user = $_POST['user'];
    $pass = encryptPassword($_POST['user']);
    $rol = $_POST['rol'];
    $conn = conectar();
    
    if(insertUsuario($conn, $nombre, $telefono, $email, $user, $pass, $rol, 1)){
        alertMenssage("Usuario registrado exitosamente", "success");
    } else {
        alertMenssage("Error al registrar el usuario", "danger");
    }
    $_POST['registro'] = 0;
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inmobiliaria</title>
    <link rel="stylesheet" href="../css/alert.css">
</head>

<body>
    <div id="container">
        <button><a href="../index.php">Volver</a></button>
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
                <label for="pass">Contraseña:</label>
                <input type="text" name="pass" id="pass" required>
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
            if ($resultado->num_rows > 0):?>
            <h4>Lista de usuarios</h4>
            <table>
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
                            <a href="./editarUsuario.php?id=<?php echo $row['id'] ?>">Editar</a> |
                            <a href="./eliminarUsuario.php?id=<?php echo $row['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este usuario?');">Eliminar</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>

            <?php endif;?>
        </section>
    </div>

    <script>

    </script>
</body>
</html>