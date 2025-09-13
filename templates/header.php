<?php require_once(__DIR__ . '/../config.php'); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGA - UNIFIO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/sga/assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="/sga/index.php"><strong>SGA UNIFIO</strong></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <li class="nav-item"><a class="nav-link" href="/sga/agenda.php">Agenda da Quadra</a></li>

                    <?php if ($_SESSION["role"] == 'aluno'): ?>
                        <li class="nav-item"><a class="nav-link" href="/sga/aluno/dashboard.php">Painel do Aluno</a></li>
                    <?php elseif ($_SESSION["role"] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="/sga/admin_atletica/dashboard.php">Painel Admin</a></li>
                    <?php elseif ($_SESSION["role"] == 'superadmin'): ?>
                        <li class="nav-item"><a class="nav-link" href="/sga/super_admin/dashboard.php">Painel Super Admin</a></li>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['tipo_usuario_detalhado']) && $_SESSION['tipo_usuario_detalhado'] == 'Professor'): ?>
                        <li class="nav-item"><a class="nav-link" href="/sga/agendar_evento.php">Agendar Evento</a></li>
                        <li class="nav-item"><a class="nav-link" href="/sga/meus_agendamentos.php">Meus Agendamentos</a></li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> Olá, <?php echo htmlspecialchars($_SESSION["nome"]); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/sga/perfil.php">Editar Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/sga/logout.php">Sair</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/sga/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="/sga/registro.php">Registrar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container mt-4">