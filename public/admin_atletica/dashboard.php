<?php
require_once '../../src/config/config.php';
is_admin();
$atletica_id = $_SESSION['atletica_id'] ?? 0;

// Contar eventos onde a atlética confirmou presença
$sql_eventos_confirmados = "SELECT COUNT(id) as total FROM agendamentos WHERE atletica_id_confirmada = ? AND atletica_confirmada = 1 AND data_agendamento >= CURDATE()";
$stmt_eventos = $conexao->prepare($sql_eventos_confirmados);
$stmt_eventos->bind_param("i", $atletica_id);
$stmt_eventos->execute();
$total_eventos_confirmados = $stmt_eventos->get_result()->fetch_assoc()['total'];

// Contar total de atletas aprovados
$sql_aprovados = "SELECT COUNT(DISTINCT aluno_id) as total FROM inscricoes_modalidade WHERE atletica_id = ? AND status = 'aprovado'";
$stmt_aprovados = $conexao->prepare($sql_aprovados);
$stmt_aprovados->bind_param("i", $atletica_id);
$stmt_aprovados->execute();
$total_aprovados = $stmt_aprovados->get_result()->fetch_assoc()['total'];

// Contar solicitações de membros pendentes
$sql_membros_pendentes = "SELECT COUNT(u.id) as total FROM usuarios u JOIN cursos c ON u.curso_id = c.id WHERE c.atletica_id = ? AND u.atletica_join_status = 'pendente'";
$stmt_membros = $conexao->prepare($sql_membros_pendentes);
$stmt_membros->bind_param("i", $atletica_id);
$stmt_membros->execute();
$total_membros_pendentes = $stmt_membros->get_result()->fetch_assoc()['total'];

// Obter mês e ano para o calendário
$mes_calendario = $_GET['mes'] ?? date('n');
$ano_calendario = $_GET['ano'] ?? date('Y');
?>

<?php include '../../templates/header.php'; ?>
    <h1>Painel do Administrador da Atlética</h1>
    <p>Gerencie as inscrições, equipes e atletas da sua atlética.</p>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title"><?php echo $total_eventos_confirmados; ?></h3>
                            <p class="card-text">Eventos Confirmados</p>
                            <small>Presença da atlética confirmada</small>
                        </div>
                        <i class="bi bi-calendar-check-fill" style="font-size: 3rem;"></i>
                    </div>
                    <a href="../pages/agenda.php" class="text-white stretched-link">Ver Agenda</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title"><?php echo $total_aprovados; ?></h3>
                            <p class="card-text">Atletas Aprovados</p>
                            <small>Gerenciar times e equipes</small>
                        </div>
                        <i class="bi bi-people-fill" style="font-size: 3rem;"></i>
                    </div>
                    <a href="gerenciar_equipes.php" class="text-white stretched-link">Gerenciar Equipes</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title"><?php echo $total_membros_pendentes; ?></h3>
                            <p class="card-text">Solicitações de Entrada</p>
                            <small>Alunos querendo entrar na atlética</small>
                        </div>
                        <i class="bi bi-person-check-fill" style="font-size: 3rem;"></i>
                    </div>
                    <a href="gerenciar_membros.php" class="text-white stretched-link">Aceitar Solicitações</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda linha com funcionalidades adicionais -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title">Modalidades</h3>
                            <p class="card-text">Gerenciar Inscrições</p>
                            <small>Aprovar participações em eventos</small>
                        </div>
                        <i class="bi bi-card-list" style="font-size: 3rem;"></i>
                    </div>
                    <a href="gerenciar_inscricoes.php" class="text-white stretched-link">Ver Inscrições</a>
                </div>
            </div>
        </div>
        <div class="col-md-8 mb-4">
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
