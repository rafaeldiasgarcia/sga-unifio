<?php
require_once '../config.php';
check_login(); // A função is_aluno() não é mais necessária, pois este é o painel padrão

$usuario_id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario_detalhado'];

// Busca os agendamentos criados pelo usuário (se for professor, super admin ou admin das atléticas)
$meus_agendamentos = [];
$role = $_SESSION['role'] ?? '';
$can_schedule = ($tipo_usuario == 'Professor') || 
                ($role === 'superadmin') || 
                ($role === 'admin' && $tipo_usuario === 'Membro das Atléticas');

if ($can_schedule) {
    $stmt_agendamentos = $conexao->prepare("SELECT titulo, data_agendamento, status FROM agendamentos WHERE usuario_id = ? ORDER BY data_agendamento DESC");
    $stmt_agendamentos->bind_param("i", $usuario_id);
    $stmt_agendamentos->execute();
    $meus_agendamentos = $stmt_agendamentos->get_result();
}

// Busca os eventos onde o usuário marcou presença
$stmt_presencas = $conexao->prepare("SELECT a.titulo, a.data_agendamento FROM presencas p JOIN agendamentos a ON p.agendamento_id = a.id WHERE p.usuario_id = ? ORDER BY a.data_agendamento DESC");
$stmt_presencas->bind_param("i", $usuario_id);
$stmt_presencas->execute();
$presencas_marcadas = $stmt_presencas->get_result();
?>

<?php include '../templates/header.php'; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Painel do Usuário</h1>
        <a href="/sga/regulamento.pdf" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-text"></i> Ver Regulamento
        </a>
    </div>

    <p>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION["nome"]); ?>!</p>

    <div class="row">
        <!-- Coluna da Esquerda: Informações Específicas do Perfil -->
        <div class="col-lg-8">
            <?php if ($can_schedule): ?>
                <div class="card">
                    <div class="card-header"><strong>Meus Agendamentos Criados</strong></div>
                    <div class="card-body">
                        <?php if ($meus_agendamentos->num_rows > 0): ?>
                            <ul class="list-group">
                                <?php while($evento = $meus_agendamentos->fetch_assoc()): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($evento['titulo']); ?> (<?php echo date('d/m/Y', strtotime($evento['data_agendamento'])); ?>)
                                        <span class="badge bg-<?php echo ['pendente'=>'warning text-dark', 'aprovado'=>'success', 'rejeitado'=>'danger'][$evento['status']]; ?>"><?php echo ucfirst($evento['status']); ?></span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">Você ainda não criou nenhum agendamento.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: // Para Alunos, Comunidade Externa, etc. ?>
                <div class="card">
                    <div class="card-header"><strong>Minhas Atividades</strong></div>
                    <div class="card-body">
                        <p>Acesse as seções no menu superior para gerenciar suas inscrições e atividades.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Coluna da Direita: Presenças Marcadas -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><strong>Presenças Marcadas</strong></div>
                <div class="card-body">
                    <?php if ($presencas_marcadas->num_rows > 0): ?>
                        <ul class="list-group">
                            <?php while($presenca = $presencas_marcadas->fetch_assoc()): ?>
                                <li class="list-group-item">
                                    <?php echo htmlspecialchars($presenca['titulo']); ?> - <?php echo date('d/m/Y', strtotime($presenca['data_agendamento'])); ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Você não marcou presença em nenhum evento.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>