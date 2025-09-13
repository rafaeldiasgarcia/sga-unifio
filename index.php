<?php
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Redireciona para o dashboard correto baseado no perfil
$role = $_SESSION["role"];
switch ($role) {
    case 'aluno':
        header("location: aluno/dashboard.php");
        break;
    case 'admin':
        header("location: admin_atletica/dashboard.php");
        break;
    case 'superadmin':
        header("location: super_admin/dashboard.php");
        break;
    default:
        // Se por algum motivo não tiver perfil, desloga
        header("location: logout.php");
        break;
}
exit;
?>