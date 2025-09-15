<?php
require_once '../../src/config/config.php';
is_admin();
$atletica_id = $_SESSION['atletica_id'];
$mensagem = '';

// Processar ação de aprovar/recusar/remover
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['inscricao_id'])) {
    $inscricao_id = $_POST['inscricao_id'];
    $acao = $_POST['acao']; // 'aprovar', 'recusar' ou 'remover'

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
    } elseif ($acao == 'remover') {
        // Remover aluno aprovado (mudando status para recusado)
        $sql_remove = "UPDATE inscricoes_modalidade SET status = 'recusado' WHERE id = ? AND atletica_id = ? AND status = 'aprovado'";
        $stmt_remove = $conexao->prepare($sql_remove);
        $stmt_remove->bind_param("ii", $inscricao_id, $atletica_id);
        if ($stmt_remove->execute()) {
            $mensagem = "<div class='alert alert-success'>Aluno removido da modalidade com sucesso.</div>";
        } else {
            $mensagem = "<div class='alert alert-danger'>Erro ao remover aluno.</div>";
        }
    }
}

// Buscar inscrições pendentes
$sql_pendentes = "SELECT i.id, u.nome as aluno_nome, m.nome as modalidade_nome, i.data_inscricao
        FROM inscricoes_modalidade i
        JOIN usuarios u ON i.aluno_id = u.id
        JOIN modalidades m ON i.modalidade_id = m.id
        WHERE i.atletica_id = ? AND i.status = 'pendente'
        ORDER BY i.data_inscricao ASC";
$stmt_pendentes = $conexao->prepare($sql_pendentes);
$stmt_pendentes->bind_param("i", $atletica_id);
$stmt_pendentes->execute();
$inscricoes_pendentes = $stmt_pendentes->get_result();

// Buscar alunos aprovados
$sql_aprovados = "SELECT i.id, u.nome as aluno_nome, m.nome as modalidade_nome, i.data_inscricao
        FROM inscricoes_modalidade i
        JOIN usuarios u ON i.aluno_id = u.id
        JOIN modalidades m ON i.modalidade_id = m.id
        WHERE i.atletica_id = ? AND i.status = 'aprovado'
        ORDER BY m.nome ASC, u.nome ASC";
$stmt_aprovados = $conexao->prepare($sql_aprovados);
$stmt_aprovados->bind_param("i", $atletica_id);
$stmt_aprovados->execute();
$alunos_aprovados = $stmt_aprovados->get_result();
?>

<?php include '../../templates/header.php'; ?>

    <h1>Gerenciar Participações em Eventos Esportivos</h1>
    <p>Aprove ou recuse as candidaturas dos alunos para as modalidades da sua atlética.</p>

<?php echo $mensagem; ?>

    <!-- Seção de Inscrições Pendentes -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Inscrições Pendentes</h5>
        </div>
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
                    <?php if ($inscricoes_pendentes->num_rows > 0): ?>
                        <?php while($inscricao = $inscricoes_pendentes->fetch_assoc()): ?>
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

    <!-- Seção de Alunos Aprovados -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Alunos Aprovados nas Modalidades</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Modalidade</th>
                        <th>Data de Aprovação</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($alunos_aprovados->num_rows > 0): ?>
                        <?php while($aprovado = $alunos_aprovados->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($aprovado['aluno_nome']); ?></td>
                                <td><?php echo htmlspecialchars($aprovado['modalidade_nome']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($aprovado['data_inscricao'])); ?></td>
                                <td>
                                    <form action="gerenciar_inscricoes.php" method="post" class="d-inline">
                                        <input type="hidden" name="inscricao_id" value="<?php echo $aprovado['id']; ?>">
                                        <button type="submit" name="acao" value="remover" class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('Tem certeza que deseja remover este aluno da modalidade?')">
                                            Remover
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhum aluno aprovado ainda.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include '../../templates/footer.php'; ?>
