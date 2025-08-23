<?php
session_start();
function checkSession($pathRedirect)
{
    if (!isset($_SESSION['usuario'])) {
        header('Location: ' . $pathRedirect);
        exit();
    }
}

function isAdmin()
{
    return isset($_SESSION['privilegio']) && $_SESSION['privilegio'] == 1;
}

function isAngente()
{
    return isset($_SESSION['privilegio']) && $_SESSION['privilegio'] == 2;
}
