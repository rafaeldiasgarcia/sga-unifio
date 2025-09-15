<?php
require_once '../../src/config/config.php';
is_superadmin();
$id = $_GET['id'] ?? 0;
if (!$id) { header("location: gerenciar_eventos.php"); exit; }
$mensagem = '';

// Lógica para Atualizar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_evento'])) {
    $nome = trim($_POST['nome']);
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // Se este evento for marcado como ativo, desativa todos os outros
    if ($ativo == 1) {
        $conexao->query("UPDATE eventos SET ativo = 0 WHERE id != $id");
    }

    $stmt = $conexao->prepare("UPDATE eventos SET nome = ?, data_inicio = ?, data_fim = ?, ativo = ? WHERE id = ?");
    $stmt->bind_param("sssii", $nome, $data_inicio, $data_fim, $ativo, $id);
    if ($stmt->execute()) {
        header("location: gerenciar_eventos.php");
        exit;
    }
}

$stmt = $conexao->prepare("SELECT * FROM eventos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();
?>
<?php include '../../templates/header.php'; ?>
    <h2>Editando Evento</h2>
    <div class="card"><div class="card-body">
            <form method="post">
                <div class="mb-3"><label class="form-label">Nome do Evento</label><input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($evento['nome']); ?>" required></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Data de Início</label><input type="date" name="data_inicio" class="form-control" value="<?php echo $evento['data_inicio']; ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Data de Fim</label><input type="date" name="data_fim" class="form-control" value="<?php echo $evento['data_fim']; ?>" required></div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="ativo" value="1" id="ativo" <?php if($evento['ativo']) echo 'checked'; ?>>
                    <label class="form-check-label" for="ativo">Marcar como Evento Ativo (isso desativará os outros)</label>
                </div>
                <button type="submit" name="update_evento" class="btn btn-success">Salvar Alterações</button>
                <a href="gerenciar_eventos.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div></div>
<?php include '../../templates/footer.php'; ?>
