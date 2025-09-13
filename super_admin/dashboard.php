<?php
require_once '../config.php';
is_superadmin();
?>

<?php include '../templates/header.php'; ?>

    <h1>Painel do Super Administrador</h1>
    <p>Acesso total para gerenciamento da estrutura e dos usuários do sistema.</p>

    <div class="row">
        <!-- Links de Gerenciamento de Estrutura -->
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-body"><h5 class="card-title">Gerenciar Atléticas</h5><p>Crie, edite e remova atléticas.</p><a href="gerenciar_atleticas.php" class="btn btn-secondary">Acessar</a></div></div></div>
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-body"><h5 class="card-title">Gerenciar Cursos</h5><p>Cadastre cursos e associe-os a atléticas.</p><a href="gerenciar_cursos.php" class="btn btn-secondary">Acessar</a></div></div></div>
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-body"><h5 class="card-title">Gerenciar Admins</h5><p>Promova alunos a administradores.</p><a href="gerenciar_admins.php" class="btn btn-secondary">Acessar</a></div></div></div>
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-body"><h5 class="card-title">Gerenciar Eventos</h5><p>Crie os eventos principais (ex: Intercursos).</p><a href="gerenciar_eventos.php" class="btn btn-secondary">Acessar</a></div></div></div>
        <div class="col-md-4 mb-3"><div class="card h-100"><div class="card-body"><h5 class="card-title">Gerenciar Modalidades</h5><p>Adicione esportes aos eventos.</p><a href="gerenciar_modalidades.php" class="btn btn-secondary">Acessar</a></div></div></div>

        <!-- Links de Gerenciamento de Operações -->
        <div class="col-md-4 mb-3"><div class="card h-100 border-primary border-2"><div class="card-body"><h5 class="card-title">Gerenciar Usuários</h5><p>Visualize e edite todos os usuários do sistema.</p><a href="gerenciar_usuarios.php" class="btn btn-primary">Acessar</a></div></div></div>
        <div class="col-md-4 mb-3"><div class="card h-100 border-success border-2"><div class="card-body"><h5 class="card-title">Aprovar Agendamentos</h5><p>Aprove ou recuse os pedidos de uso da quadra.</p><a href="gerenciar_agendamentos.php" class="btn btn-success">Acessar</a></div></div></div>
    </div>

<?php include '../templates/footer.php'; ?>