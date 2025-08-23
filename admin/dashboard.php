<?php
session_start();
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/verificacionrol.php';
if (!isAdmin()) {
    header('Location: ../login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="container">
        <h1>Dashboard</h1>
        <p>Bienvenido admin dashboard!</p>
    </div>
</body>
</html>