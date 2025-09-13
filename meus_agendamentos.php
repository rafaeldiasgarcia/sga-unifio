<?php
require_once 'config.php';
check_login();

// Apenas usuários que podem criar eventos (Professores) devem ter acesso
if (!isset($_SESSION['tipo_usuario_detalhado']) || $_SESSION['tipo_usuario_detalhado'] !== 'Professor') {
    header("location: index.php");
    exit;
}

$usuario_id = $_SESSION['id'];

// Buscar todos os agendamentos feitos por este usuário
$sql = "SELECT id, titulo, data_agendamento, periodo, status, motivo_rejeicao 
        FROM agendamentos 
        WHERE usuario_id = ? 
        ORDER BY data_solicitacao DESC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$agendamentos = $stmt->get_result();
?>

<?php include 'templates/header.php'; ?>
    <h2>Meus Agendamentos</h2>
    <p>Acompanhe o status de todas as suas solicitações de uso da quadra.</p>

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
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($agendamentos->num_rows > 0): ?>
                        <?php while($evento = $agendamentos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evento['titulo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($evento['data_agendamento'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                    switch($evento['status']) {
                                        case 'aprovado': echo 'success'; break;
                                        case 'rejeitado': echo 'danger'; break;
                                        default: echo 'warning text-dark'; break;
                                    }
                                    ?>"><?php echo ucfirst($evento['status']); ?></span>
                                </td>
                                <td>
                                    <?php if ($evento['status'] == 'rejeitado' && !empty($evento['motivo_rejeicao'])): ?>
                                        <?php echo htmlspecialchars($evento['motivo_rejeicao']); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Você ainda não fez nenhuma solicitação de agendamento.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include 'templates/footer.php'; ?>