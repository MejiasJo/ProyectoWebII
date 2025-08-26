<?php
function checkSession($pathRedirect)
{
    if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario']) || !isset($_SESSION['privilegio']) || empty($_SESSION['privilegio'])) {
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

function isPrimerIngreso()
{
    return isset($_SESSION['primer_ingreso']) && $_SESSION['primer_ingreso'] == 1;
}