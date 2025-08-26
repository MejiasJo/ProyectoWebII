<?php
session_start();
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/verificacionrol.php';
if (!isAngente()) {
    header('Location: ../login.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/dasboard.css">
</head>

<body>
    <div class="container">
        
        <h1>Dashboard</h1>
        <h2>Bienvenido Agente: <?php echo $_SESSION['usuario_nombre']; ?>!</h2>
        <section class="cards-container">
            <article class="card">
                <h3>Datos Personales</h3>
                <img src="../assets/img/admin.png" alt="">
                <button><a href="./editarUsuario.php" class="btn">Actulizar</a></button>

            </article>

            <article class="card">
                <h3>Gestión Propiedades</h3>
                <img src="../assets/img/casa.png" alt="">
                <button><a href="./propiedad.php" class="btn">Ir a Propiedades</a></button>
            </article>
        </section>
        <button><a href="../index.php">↩ Volver</a></button>
    </div>
</body>

</html>