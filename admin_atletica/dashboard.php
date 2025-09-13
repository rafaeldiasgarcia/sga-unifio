<?php
require_once '../config.php';
is_admin();
$atletica_id = $_SESSION['atletica_id'] ?? 0;

// Contar inscrições pendentes em modalidades
$sql_inscricoes_pendentes = "SELECT COUNT(id) as total FROM inscricoes_modalidade WHERE atletica_id = ? AND status = 'pendente'";
$stmt_inscricoes = $conexao->prepare($sql_inscricoes_pendentes);
$stmt_inscricoes->bind_param("i", $atletica_id);
$stmt_inscricoes->execute();
$total_inscricoes_pendentes = $stmt_inscricoes->get_result()->fetch_assoc()['total'];

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
?>

<?php include '../templates/header.php'; ?>
    <h1>Painel do Administrador da Atlética</h1>
    <p>Gerencie as inscrições, equipes e atletas da sua atlética.</p>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title"><?php echo $total_inscricoes_pendentes; ?></h3>
                            <p class="card-text">Inscrições em Modalidades</p>
                        </div>
                        <i class="bi bi-card-list" style="font-size: 3rem;"></i>
                    </div>
                    <a href="gerenciar_inscricoes.php" class="text-white stretched-link">Ver Inscrições</a>
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
                            <p class="card-text">Solicitações para Entrar</p>
                        </div>
                        <i class="bi bi-person-check-fill" style="font-size: 3rem;"></i>
                    </div>
                    <a href="gerenciar_membros.php" class="text-white stretched-link">Gerenciar Membros</a>
                </div>
            </div>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>