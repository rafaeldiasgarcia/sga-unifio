<?php
require_once '../config.php';
check_login();

// Redireciona se não for professor, super admin ou admin das atléticas
$tipo_usuario = $_SESSION['tipo_usuario_detalhado'] ?? '';
$role = $_SESSION['role'] ?? '';

$can_schedule = ($tipo_usuario === 'Professor') || 
                ($role === 'superadmin') || 
                ($role === 'admin' && $tipo_usuario === 'Membro das Atléticas');

if (!$can_schedule) {
    header("location: ../index.php");
    exit;
}

$usuario_id = $_SESSION['id'];
$mensagem = '';

// Lógica para Cancelar Agendamento (AGORA ATUALIZA O STATUS)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancelar_agendamento'])) {
    $agendamento_id = $_POST['agendamento_id_to_cancel'];

    // Atualiza o status para 'rejeitado' com motivo específico
    $stmt = $conexao->prepare("UPDATE agendamentos SET status = 'rejeitado', motivo_rejeicao = 'Cancelado pelo solicitante.' WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $agendamento_id, $usuario_id);

    if ($stmt->execute()) {
        $mensagem = "<div class='alert alert-info'>Agendamento cancelado com sucesso.</div>";
    } else {
        $mensagem = "<div class='alert alert-danger'>Erro ao cancelar o agendamento.</div>";
    }
    $stmt->close();
}

// Busca os agendamentos do usuário
$sql = "SELECT id, titulo, data_agendamento, periodo, status, motivo_rejeicao 
        FROM agendamentos 
        WHERE usuario_id = ? 
        ORDER BY data_solicitacao DESC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$agendamentos = $stmt->get_result();
?>

<?php include '../templates/header.php'; ?>

    <h2>Meus Agendamentos</h2>
    <p>Acompanhe e gerencie o status de todas as suas solicitações de uso da quadra.</p>

<?php echo $mensagem; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Título do Evento</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Observações</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($agendamentos->num_rows > 0): ?>
                        <?php while($evento = $agendamentos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evento['titulo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($evento['data_agendamento'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ['pendente'=>'warning text-dark', 'aprovado'=>'success', 'rejeitado'=>'danger'][$evento['status']]; ?>"><?php echo ucfirst($evento['status']); ?></span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($evento['motivo_rejeicao'] ?? '-'); ?>
                                </td>
                                <td>
                                    <?php if ($evento['status'] == 'pendente' || $evento['status'] == 'aprovado'): ?>
                                        <a href="editar_agendamento.php?id=<?php echo $evento['id']; ?>" class="btn btn-sm btn-info">Editar</a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Tem certeza que deseja cancelar este agendamento?');">
                                            <input type="hidden" name="agendamento_id_to_cancel" value="<?php echo $evento['id']; ?>">
                                            <button type="submit" name="cancelar_agendamento" class="btn btn-sm btn-warning">Cancelar</button>
                                        </form>
                                    <?php else:
                                        echo '-';
                                    endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Você ainda não fez nenhuma solicitação de agendamento.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>