<?php
require_once '../config.php';
is_admin();
$atletica_id = $_SESSION['atletica_id'];
$mensagem = '';

// Processar ação de aprovar/recusar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aluno_id'])) {
    $aluno_id = $_POST['aluno_id'];
    $acao = $_POST['acao'];

    if ($acao == 'aprovar') {
        // Promove o usuário para "Membro" e limpa o status da solicitação
        $sql = "UPDATE usuarios SET tipo_usuario_detalhado = 'Membro das Atléticas', atletica_join_status = 'none' WHERE id = ?";
        $mensagem = "<div class='alert alert-success'>Aluno aprovado e adicionado à atlética!</div>";
    } elseif ($acao == 'recusar') {
        // Apenas limpa o status da solicitação, permitindo que o aluno peça novamente no futuro
        $sql = "UPDATE usuarios SET atletica_join_status = 'none' WHERE id = ?";
        $mensagem = "<div class='alert alert-warning'>Solicitação recusada.</div>";
    }

    if (isset($sql)) {
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $aluno_id);
        $stmt->execute();
    }
}

// Buscar alunos com solicitação pendente para a atlética deste admin
$sql_pendentes = "SELECT u.id, u.nome, c.nome as curso_nome 
                  FROM usuarios u 
                  JOIN cursos c ON u.curso_id = c.id
                  WHERE c.atletica_id = ? AND u.atletica_join_status = 'pendente'";
$stmt_pendentes = $conexao->prepare($sql_pendentes);
$stmt_pendentes->bind_param("i", $atletica_id);
$stmt_pendentes->execute();
$pendentes = $stmt_pendentes->get_result();
?>

<?php include '../templates/header.php'; ?>
    <h2>Gerenciar Solicitações de Membros</h2>
    <p>Aprove ou recuse os pedidos de alunos para entrar na sua atlética.</p>
<?php echo $mensagem; ?>

    <div class="card">
        <div class="card-header">Solicitações Pendentes</div>
        <div class="card-body">
            <table class="table table-hover">
                <thead><tr><th>Aluno</th><th>Curso</th><th>Ações</th></tr></thead>
                <tbody>
                <?php if ($pendentes->num_rows > 0): ?>
                    <?php while($req = $pendentes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['nome']); ?></td>
                            <td><?php echo htmlspecialchars($req['curso_nome']); ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="aluno_id" value="<?php echo $req['id']; ?>">
                                    <button type="submit" name="acao" value="aprovar" class="btn btn-sm btn-success">Aprovar</button>
                                    <button type="submit" name="acao" value="recusar" class="btn btn-sm btn-danger">Recusar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center">Nenhuma solicitação pendente.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>