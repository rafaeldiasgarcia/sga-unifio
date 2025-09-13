<?php
require_once '../config.php';
is_superadmin();
$id = $_GET['id'] ?? 0;
if (!$id) { header("location: gerenciar_cursos.php"); exit; }
$mensagem = '';

// Lógica para Atualizar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_curso'])) {
    $nome = trim($_POST['nome']);
    $atletica_id = $_POST['atletica_id'] ?: null;
    $coordenador_id = $_POST['coordenador_id'] ?: null;

    $stmt = $conexao->prepare("UPDATE cursos SET nome = ?, atletica_id = ?, coordenador_id = ? WHERE id = ?");
    $stmt->bind_param("siii", $nome, $atletica_id, $coordenador_id, $id);
    if ($stmt->execute()) {
        header("location: gerenciar_cursos.php");
        exit;
    } else {
        $mensagem = "<div class='alert alert-danger'>Erro ao atualizar.</div>";
    }
}

// Busca dados do curso
$stmt = $conexao->prepare("SELECT * FROM cursos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$curso = $stmt->get_result()->fetch_assoc();

// Busca atléticas e professores coordenadores para os dropdowns
$atleticas = $conexao->query("SELECT id, nome FROM atleticas ORDER BY nome");
$coordenadores = $conexao->query("SELECT id, nome FROM usuarios WHERE is_coordenador = 1 ORDER BY nome");
?>
<?php include '../templates/header.php'; ?>
    <h2>Editando Curso</h2>
<?php echo $mensagem; ?>
    <div class="card"><div class="card-body">
            <form method="post">
                <div class="mb-3"><label class="form-label">Nome do Curso</label><input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($curso['nome']); ?>" required></div>
                <div class="mb-3"><label class="form-label">Associar à Atlética</label>
                    <select name="atletica_id" class="form-select">
                        <option value="">Nenhuma</option>
                        <?php while($a = $atleticas->fetch_assoc()): ?>
                            <option value="<?php echo $a['id']; ?>" <?php if($curso['atletica_id'] == $a['id']) echo 'selected'; ?>><?php echo htmlspecialchars($a['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Coordenador do Curso</label>
                    <select name="coordenador_id" class="form-select">
                        <option value="">Nenhum</option>
                        <?php while($c = $coordenadores->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>" <?php if($curso['coordenador_id'] == $c['id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="update_curso" class="btn btn-success">Salvar Alterações</button>
                <a href="gerenciar_cursos.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div></div>
<?php include '../templates/footer.php'; ?>