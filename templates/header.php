<?php require_once(__DIR__ . '/../src/config/config.php'); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGA - UNIFIO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/sga/public/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="/sga/public/index.php"><strong>SGA UNIFIO</strong></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>

                    <li class="nav-item"><a class="nav-link" href="/sga/public/pages/agenda.php">Agenda da Quadra</a></li>

                    <?php if ($_SESSION["role"] != 'superadmin'): ?>
                        <li class="nav-item"><a class="nav-link" href="/sga/public/usuario/dashboard.php">Painel do Usuário</a></li>
                    <?php endif; ?>

                    <?php if ($_SESSION["role"] == 'admin' && $_SESSION["tipo_usuario_detalhado"] !== 'Professor' && $_SESSION["tipo_usuario_detalhado"] !== 'Professor Coordenador'): ?>
                        <li class="nav-item"><a class="nav-link" href="/sga/public/admin_atletica/dashboard.php">Painel Admin</a></li>
                    <?php elseif ($_SESSION["role"] == 'superadmin'): ?>
                        <li class="nav-item"><a class="nav-link" href="/sga/public/super_admin/dashboard.php">Painel Super Admin</a></li>
                        <li class="nav-item"><a class="nav-link" href="/sga/public/super_admin/relatorios.php">Relatórios</a></li>
                    <?php endif; ?>

                    <?php 
                    // Verifica se pode agendar eventos (Professor, Super Admin ou Admin das Atléticas)
                    $tipo_usuario = $_SESSION['tipo_usuario_detalhado'] ?? '';
                    $role = $_SESSION['role'] ?? '';
                    $can_schedule = ($tipo_usuario === 'Professor') || 
                                    ($role === 'superadmin') || 
                                    ($role === 'admin' && $tipo_usuario === 'Membro das Atléticas');
                    
                    if ($can_schedule): ?>
                        <li class="nav-item"><a class="nav-link" href="/sga/public/pages/agendar_evento.php">Agendar Evento</a></li>
                        <li class="nav-item"><a class="nav-link" href="/sga/public/pages/meus_agendamentos.php">Meus Agendamentos</a></li>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['tipo_usuario_detalhado']) && $_SESSION['tipo_usuario_detalhado'] == 'Membro das Atléticas'): ?>
                        <li class="nav-item"><a class="nav-link" href="/sga/public/usuario/ver_atletica.php">Ver Atlética</a></li>
                    <?php endif; ?>

                    <!-- Ícone de Notificações -->
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" id="notificationDropdown">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display: none;">
                                0
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                            <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Notificações</span>
                                <button class="btn btn-sm btn-outline-secondary" id="markAllRead" style="font-size: 0.75rem;">
                                    Marcar todas como lidas
                                </button>
                            </h6>
                            <div id="notificationsList">
                                <div class="dropdown-item text-muted text-center">
                                    <i class="bi bi-bell-slash"></i> Nenhuma notificação
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> Olá, <?php echo htmlspecialchars($_SESSION["nome"]); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/sga/public/pages/perfil.php">Editar Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/sga/public/logout.php">Sair</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- <li class="nav-item"><a class="nav-link" href="/sga/public/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="/sga/public/registro.php">Registrar</a></li> -->
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container mt-4">