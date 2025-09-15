<?php
require_once '../../src/config/config.php';
is_superadmin();
$mensagem = '';

// Processar ação de aprovar/recusar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agendamento_id'])) {
    $agendamento_id = $_POST['agendamento_id'];
    $acao = $_POST['acao'];

    if ($acao == 'aprovar') {
        // 1. VERIFICAÇÃO DE CONFLITO: Busca a data e o período do evento que se quer aprovar
        $stmt_check = $conexao->prepare("SELECT data_agendamento, periodo FROM agendamentos WHERE id = ?");
        $stmt_check->bind_param("i", $agendamento_id);
        $stmt_check->execute();
        $evento_para_aprovar = $stmt_check->get_result()->fetch_assoc();

        // 2. Procura por outros eventos JÁ APROVADOS na mesma data e período
        $stmt_conflict = $conexao->prepare("SELECT id FROM agendamentos WHERE data_agendamento = ? AND periodo = ? AND status = 'aprovado'");
        $stmt_conflict->bind_param("ss", $evento_para_aprovar['data_agendamento'], $evento_para_aprovar['periodo']);
        $stmt_conflict->execute();

        // 3. Se encontrar algum conflito, exibe o erro. Senão, aprova.
        if ($stmt_conflict->get_result()->num_rows > 0) {
            $mensagem = "<div class='alert alert-danger'><strong>Falha na Aprovação!</strong> Já existe um evento aprovado para esta data e período.</div>";
        } else {
            $sql = "UPDATE agendamentos SET status = 'aprovado', motivo_rejeicao = NULL WHERE id = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("i", $agendamento_id);
            if ($stmt->execute()) {
                $mensagem = "<div class='alert alert-success'>Agendamento aprovado com sucesso!</div>";
            }
        }
    } elseif ($acao == 'rejeitar') {
        $novo_status = 'rejeitado';
        $motivo = trim($_POST['motivo_rejeicao']);
        if (!empty($motivo)) {
            $sql = "UPDATE agendamentos SET status = ?, motivo_rejeicao = ? WHERE id = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("ssi", $novo_status, $motivo, $agendamento_id);
            if ($stmt->execute()) {
                $mensagem = "<div class='alert alert-warning'>Agendamento rejeitado com sucesso.</div>";
            }
        } else {
            $mensagem = "<div class='alert alert-danger'>O motivo da rejeição é obrigatório.</div>";
        }
    }
}

// Buscar agendamentos pendentes para exibir na tabela
$sql_pendentes = "SELECT a.id, a.titulo, a.data_agendamento, a.periodo, u.nome as solicitante
                  FROM agendamentos a JOIN usuarios u ON a.usuario_id = u.id
                  WHERE a.status = 'pendente' ORDER BY a.data_solicitacao ASC";
$pendentes = $conexao->query($sql_pendentes);
?>

<?php include '../../templates/header.php'; ?>
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
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="openRejectModal('<?php echo $req['id']; ?>', '<?php echo htmlspecialchars($req['titulo'], ENT_QUOTES); ?>')">
                                        Rejeitar
                                    </button>
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

    <!-- Modal de Rejeição (único) -->
    <div class="modal fade" id="rejectModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rejeitar Agendamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <p>Você está rejeitando o evento: <strong id="evento-titulo"></strong></p>
                        <input type="hidden" name="agendamento_id" id="agendamento_id">
                        <div class="mb-3">
                            <label for="motivo_rejeicao" class="form-label">Motivo da Rejeição (Obrigatório)</label>
                            <textarea name="motivo_rejeicao" id="motivo_rejeicao" class="form-control" rows="3" required></textarea>
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

    <script>
    function openRejectModal(id, titulo) {
        document.getElementById('agendamento_id').value = id;
        document.getElementById('evento-titulo').textContent = titulo;
        document.getElementById('motivo_rejeicao').value = '';
        var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    }
    </script>

<?php include '../../templates/footer.php'; ?>
