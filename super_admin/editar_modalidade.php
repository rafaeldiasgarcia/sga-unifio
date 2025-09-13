<?php
require_once '../config.php';
is_superadmin();
$id = $_GET['id'] ?? 0;
if (!$id) { header("location: gerenciar_modalidades.php"); exit; }
$mensagem = '';

// Lógica para Atualizar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_modalidade'])) {
    $nome = trim($_POST['nome']);
    $evento_id = $_POST['evento_id'];

    if (!empty($nome) && !empty($evento_id)) {
        $stmt = $conexao->prepare("UPDATE modalidades SET nome = ?, evento_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $nome, $evento_id, $id);
        if ($stmt->execute()) {
            header("location: gerenciar_modalidades.php");
            exit;
        } else {
            $mensagem = "<div class='alert alert-danger'>Erro ao atualizar a modalidade.</div>";
        }
    } else {
        $mensagem = "<div class='alert alert-danger'>Todos os campos são obrigatórios.</div>";
    }
}

// Busca dados da modalidade para preencher o formulário
$stmt = $conexao->prepare("SELECT * FROM modalidades WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$modalidade = $stmt->get_result()->fetch_assoc();

if (!$modalidade) {
    // Se o ID não for válido, volta para a lista
    header("location: gerenciar_modalidades.php");
    exit;
}

// Busca todos os eventos para o dropdown
$eventos = $conexao->query("SELECT id, nome FROM eventos ORDER BY nome");
?>
<?php include '../templates/header.php'; ?>
    <h2>Editando Modalidade</h2>
<?php echo $mensagem; ?>
    <div class="card"><div class="card-body">
            <form method="post">
                <div class="mb-3"><label class="form-label">Nome da Modalidade</label><input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($modalidade['nome']); ?>" required></div>
                <div class="mb-3"><label class="form-label">Associar ao Evento</label>
                    <select name="evento_id" class="form-select" required>
                        <?php while($e = $eventos->fetch_assoc()): ?>
                            <option value="<?php echo $e['id']; ?>" <?php if($modalidade['evento_id'] == $e['id']) echo 'selected'; ?>><?php echo htmlspecialchars($e['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="update_modalidade" class="btn btn-success">Salvar Alterações</button>
                <a href="gerenciar_modalidades.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div></div>
<?php include '../templates/footer.php'; ?>