<?php
require_once '../config.php';
is_aluno();

$aluno_id = $_SESSION['id'];
$mensagem = '';

// Buscar a atlética do aluno para garantir que ele possa se inscrever
$sql_atletica = "SELECT c.atletica_id FROM usuarios u JOIN cursos c ON u.curso_id = c.id WHERE u.id = ?";
$stmt_atletica = $conexao->prepare($sql_atletica);
$stmt_atletica->bind_param("i", $aluno_id);
$stmt_atletica->execute();
$resultado = $stmt_atletica->get_result()->fetch_assoc();
$atletica_id = $resultado['atletica_id'] ?? null;

// Lógica para processar uma NOVA inscrição
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modalidade_id'])) {
    if ($atletica_id) {
        $modalidade_id = $_POST['modalidade_id'];

        // A verificação se já está inscrito é uma dupla segurança, mas a interface já deve prevenir
        $sql_insert = "INSERT INTO inscricoes_modalidade (aluno_id, modalidade_id, atletica_id) VALUES (?, ?, ?)";
        $stmt_insert = $conexao->prepare($sql_insert);
        $stmt_insert->bind_param("iii", $aluno_id, $modalidade_id, $atletica_id);
        if ($stmt_insert->execute()) {
            $mensagem = "<div class='alert alert-success'>Inscrição realizada com sucesso! Aguarde a aprovação.</div>";
        } else {
            $mensagem = "<div class='alert alert-warning'>Você já se inscreveu nesta modalidade.</div>";
        }
    } else {
        $mensagem = "<div class='alert alert-danger'>Você não está associado a nenhuma atlética para se inscrever.</div>";
    }
}

// --- MUDANÇA PRINCIPAL AQUI ---
// Buscar modalidades do evento ativo E o status de inscrição do aluno para cada uma
$sql_modalidades = "SELECT 
                        m.id, 
                        m.nome,
                        i.status 
                    FROM modalidades m 
                    JOIN eventos e ON m.evento_id = e.id 
                    LEFT JOIN inscricoes_modalidade i ON m.id = i.modalidade_id AND i.aluno_id = ?
                    WHERE e.ativo = 1";
$stmt_modalidades = $conexao->prepare($sql_modalidades);
$stmt_modalidades->bind_param("i", $aluno_id);
$stmt_modalidades->execute();
$modalidades = $stmt_modalidades->get_result();
?>

<?php include '../templates/header.php'; ?>

    <h1>Inscrever-se em Modalidades</h1>
    <p>Escolha uma modalidade abaixo para se candidatar a uma vaga na equipe da sua atlética.</p>

<?php echo $mensagem; ?>

<?php if (!$atletica_id): ?>
    <div class="alert alert-warning">Você não está vinculado a uma atlética. Apenas alunos de cursos com atléticas associadas podem se inscrever.</div>
<?php elseif ($modalidades->num_rows > 0): ?>
    <div class="list-group">
        <?php while($modalidade = $modalidades->fetch_assoc()): ?>
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <h5 class="mb-1"><?php echo htmlspecialchars($modalidade['nome']); ?></h5>

                    <!-- LÓGICA DO BOTÃO DINÂMICO -->
                    <?php if ($modalidade['status'] === null): // Aluno nunca se inscreveu ?>
                        <form action="inscrever.php" method="post" class="d-inline">
                            <input type="hidden" name="modalidade_id" value="<?php echo $modalidade['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-primary">Quero Participar</button>
                        </form>
                    <?php elseif ($modalidade['status'] == 'pendente'): ?>
                        <button class="btn btn-sm btn-warning" disabled>Inscrição Pendente</button>
                    <?php elseif ($modalidade['status'] == 'aprovado'): ?>
                        <button class="btn btn-sm btn-success" disabled>Inscrição Aprovada</button>
                    <?php elseif ($modalidade['status'] == 'recusado'): ?>
                        <button class="btn btn-sm btn-danger" disabled>Recusado</button>
                    <?php endif; ?>

                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">Nenhuma modalidade disponível para inscrição no momento.</div>
<?php endif; ?>

<?php include '../templates/footer.php'; ?>