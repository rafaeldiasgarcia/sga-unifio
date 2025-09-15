<?php
require_once '../../src/config/config.php';
is_superadmin();

// Obter mês e ano para o calendário
$mes_calendario = $_GET['mes'] ?? date('n');
$ano_calendario = $_GET['ano'] ?? date('Y');
?>

<?php include '../../templates/header.php'; ?>

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

    <!-- Calendário de Eventos -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Calendário de Eventos</h5>
                </div>
                <div class="card-body">
                    <?php
                    require_once '../../templates/calendar.php';
                    gerarCalendario($conexao, $mes_calendario, $ano_calendario);
                    ?>
                </div>
            </div>
        </div>
    </div>

<?php include '../../templates/footer.php'; ?>
