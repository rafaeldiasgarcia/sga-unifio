<?php
require_once '../config.php';
is_superadmin();
$mensagem = '';

// Processar ação de aprovar/recusar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agendamento_id'])) {
    $agendamento_id = $_POST['agendamento_id'];
    $acao = $_POST['acao'];

    if ($acao == 'aprovar') {
        $novo_status = 'aprovado';
        $sql = "UPDATE agendamentos SET status = ? WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("si", $novo_status, $agendamento_id);
    } elseif ($acao == 'rejeitar') {
        $novo_status = 'rejeitado';
        $motivo = trim($_POST['motivo_rejeicao']);
        $sql = "UPDATE agendamentos SET status = ?, motivo_rejeicao = ? WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssi", $novo_status, $motivo, $agendamento_id);
    }

    if (isset($stmt) && $stmt->execute()) {
        $mensagem = "<div class='alert alert-success'>Status do agendamento atualizado.</div>";
    }
}

// Buscar agendamentos pendentes
$sql_pendentes = "SELECT a.id, a.titulo, a.data_agendamento, a.periodo, u.nome as solicitante
                  FROM agendamentos a JOIN usuarios u ON a.usuario_id = u.id
                  WHERE a.status = 'pendente' ORDER BY a.data_solicitacao ASC";
$pendentes = $conexao->query($sql_pendentes);
?>

<?php include '../templates/header.php'; ?>
    <h2>Aprovar Agendamentos da Quadra</h2>
    <p>Gerencie as solicitações de uso da quadra feitas pelos professores.</p>
<?php echo $mensagem; ?>

    <div class="card">
        <div class="card-header">Solicitações Pendentes</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Solicitante</th><th>Título</th><th>Data</th><th>Ações</th></tr></thead>
                    <tbody>
                    <?php if ($pendentes->num_rows > 0): ?>
                        <?php while($req = $pendentes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($req['solicitante']); ?></td>
                                <td><?php echo htmlspecialchars($req['titulo']); ?><br><small class="text-muted"><?php echo htmlspecialchars($req['periodo']); ?></small></td>
                                <td><?php echo date('d/m/Y', strtotime($req['data_agendamento'])); ?></td>
                                <td>
                                    <!-- Formulário de Aprovação -->
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="agendamento_id" value="<?php echo $req['id']; ?>">
                                        <button type="submit" name="acao" value="aprovar" class="btn btn-sm btn-success">Aprovar</button>
                                    </form>

                                    <!-- Botão para abrir o Modal de Rejeição -->
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal-<?php echo $req['id']; ?>">
                                        Rejeitar
                                    </button>

                                    <!-- Modal de Rejeição (um para cada solicitação) -->
                                    <div class="modal fade" id="rejectModal-<?php echo $req['id']; ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Rejeitar Agendamento</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="post">
                                                    <div class="modal-body">
                                                        <p>Você está rejeitando o evento: <strong><?php echo htmlspecialchars($req['titulo']); ?></strong></p>
                                                        <input type="hidden" name="agendamento_id" value="<?php echo $req['id']; ?>">
                                                        <div class="mb-3">
                                                            <label for="motivo_rejeicao" class="form-label">Motivo da Rejeição (Obrigatório)</label>
                                                            <textarea name="motivo_rejeicao" class="form-control" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" name="acao" value="rejeitar" class="btn btn-danger">Confirmar Rejeição</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Nenhuma solicitação pendente.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>