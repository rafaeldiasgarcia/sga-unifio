<?php
require_once '../config.php';
is_admin();
$atletica_id = $_SESSION['atletica_id'];
$mensagem = '';

// Processar ação de aprovar/recusar
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['inscricao_id'])) {
    $inscricao_id = $_POST['inscricao_id'];
    $acao = $_POST['acao']; // 'aprovar' ou 'recusar'

    if ($acao == 'aprovar' || $acao == 'recusar') {
        $novo_status = ($acao == 'aprovar') ? 'aprovado' : 'recusado';

        $sql_update = "UPDATE inscricoes_modalidade SET status = ? WHERE id = ? AND atletica_id = ?";
        $stmt_update = $conexao->prepare($sql_update);
        $stmt_update->bind_param("sii", $novo_status, $inscricao_id, $atletica_id);
        if ($stmt_update->execute()) {
            $mensagem = "<div class='alert alert-success'>Status da inscrição atualizado com sucesso.</div>";
        } else {
            $mensagem = "<div class='alert alert-danger'>Erro ao atualizar status.</div>";
        }
    }
}

// Buscar inscrições pendentes
$sql = "SELECT i.id, u.nome as aluno_nome, m.nome as modalidade_nome, i.data_inscricao
        FROM inscricoes_modalidade i
        JOIN usuarios u ON i.aluno_id = u.id
        JOIN modalidades m ON i.modalidade_id = m.id
        WHERE i.atletica_id = ? AND i.status = 'pendente'
        ORDER BY i.data_inscricao ASC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $atletica_id);
$stmt->execute();
$inscricoes = $stmt->get_result();
?>

<?php include '../templates/header.php'; ?>

    <h1>Gerenciar Inscrições Pendentes</h1>
    <p>Aprove ou recuse as candidaturas dos alunos para as modalidades da sua atlética.</p>

<?php echo $mensagem; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Modalidade</th>
                        <th>Data da Inscrição</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($inscricoes->num_rows > 0): ?>
                        <?php while($inscricao = $inscricoes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($inscricao['aluno_nome']); ?></td>
                                <td><?php echo htmlspecialchars($inscricao['modalidade_nome']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($inscricao['data_inscricao'])); ?></td>
                                <td>
                                    <form action="gerenciar_inscricoes.php" method="post" class="d-inline">
                                        <input type="hidden" name="inscricao_id" value="<?php echo $inscricao['id']; ?>">
                                        <button type="submit" name="acao" value="aprovar" class="btn btn-success btn-sm">Aprovar</button>
                                        <button type="submit" name="acao" value="recusar" class="btn btn-danger btn-sm">Recusar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhuma inscrição pendente no momento.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>