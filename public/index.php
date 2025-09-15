<?php
require_once '../src/config/config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Redireciona para o dashboard correto baseado no perfil
$role = $_SESSION["role"];

if ($role == 'superadmin') {
    // O Super Admin tem um painel único e vai direto para ele.
    header("location: super_admin/dashboard.php");
} else {
    // TODOS os outros perfis (usuario, admin) vão para o painel de usuário como página inicial.
    header("location: usuario/dashboard.php");
}
exit;
?>