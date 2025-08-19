<?php
include '../config/conexion.php';
include '../config/consultasDB.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inmobiliaria</title>
</head>

<body>
    <div id="container">
        <button><a href="index.php">Volver</a></button>
        <section class="bloque">
            <form action="ingresarProfesor.php" method="post" enctype="multipart/form-data">
                <h4>Ingresar Usuario</h4>
                <label for="profesor">Nombre Completo:</label>
                <input type="text" name="profesor" id="profesor" required>
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

                <button type="submit"> Registrar</button>
            </form>
        </section>
    </div>
</body>

</html>