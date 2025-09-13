<?php
require_once '../config.php';
is_superadmin();
$id = $_GET['id'] ?? 0;
if (!$id) { header("location: gerenciar_atleticas.php"); exit; }
$mensagem = '';

// Lógica para Atualizar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_atletica'])) {
    $nome = trim($_POST['nome']);
    $stmt = $conexao->prepare("UPDATE atleticas SET nome = ? WHERE id = ?");
    $stmt->bind_param("si", $nome, $id);
    if ($stmt->execute()) {
        header("location: gerenciar_atleticas.php");
        exit;
    } else {
        $mensagem = "<div class='alert alert-danger'>Erro ao atualizar.</div>";
    }
}

$stmt = $conexao->prepare("SELECT nome FROM atleticas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$atletica = $stmt->get_result()->fetch_assoc();
?>
<?php include '../templates/header.php'; ?>
    <h2>Editando Atlética</h2>
<?php echo $mensagem; ?>
    <div class="card"><div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Nome da Atlética</label>
                    <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($atletica['nome']); ?>" required>
                </div>
                <button type="submit" name="update_atletica" class="btn btn-success">Salvar Alterações</button>
                <a href="gerenciar_atleticas.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div></div>
<?php include '../templates/footer.php'; ?>