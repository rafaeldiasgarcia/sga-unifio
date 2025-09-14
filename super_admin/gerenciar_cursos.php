<?php
require_once '../config.php';
is_superadmin();
$mensagem = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'added') {
        $mensagem = "<div class='alert alert-success'>Curso adicionado!</div>";
    } elseif ($_GET['status'] === 'deleted') {
        $mensagem = "<div class='alert alert-warning'>Curso excluído!</div>";
    }
}

// Lógica para Adicionar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_curso'])) {
    $nome = trim($_POST['nome']);
    $atletica_id = $_POST['atletica_id'] ?: null;
    $stmt = $conexao->prepare("INSERT INTO cursos (nome, atletica_id) VALUES (?, ?)");
    $stmt->bind_param("si", $nome, $atletica_id);
    if ($stmt->execute()) {
        header("Location: gerenciar_cursos.php?status=added");
        exit;
    }
}

// Lógica para Deletar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_curso'])) {
    $id = $_POST['id_to_delete'];
    // Primeiro, desvincula todos os usuários desse curso
    $stmt_update = $conexao->prepare("UPDATE usuarios SET curso_id = NULL WHERE curso_id = ?");
    $stmt_update->bind_param("i", $id);
    $stmt_update->execute();

    // Agora pode deletar o curso
    $stmt = $conexao->prepare("DELETE FROM cursos WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: gerenciar_cursos.php?status=deleted");
        exit;
    }
}

$cursos = $conexao->query("SELECT c.id, c.nome, a.nome as atletica_nome FROM cursos c LEFT JOIN atleticas a ON c.atletica_id = a.id ORDER BY c.nome");
$atleticas = $conexao->query("SELECT * FROM atleticas ORDER BY nome");
?>
<?php include '../templates/header.php'; ?>
    <h2>Gerenciar Cursos</h2>
<?php echo $mensagem; ?>
    <div class="card mb-4"><div class="card-body">
            <h5>Adicionar Novo Curso</h5>
            <form method="post" class="row g-3 align-items-center">
                <div class="col-auto"><input type="text" name="nome" class="form-control" placeholder="Nome do novo curso" required></div>
                <div class="col-auto">
                    <select name="atletica_id" class="form-select"><option value="">-- Associar à Atlética (Opcional) --</option>
                        <?php while($row = $atleticas->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-auto"><button type="submit" name="add_curso" class="btn btn-primary">Adicionar</button></div>
            </form>
        </div></div>
    <table class="table table-striped">
        <thead><tr><th>ID</th><th>Nome do Curso</th><th>Atlética Associada</th><th>Ações</th></tr></thead>
        <tbody>
        <?php while($row = $cursos->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                <td><?php echo htmlspecialchars($row['atletica_nome'] ?? 'Nenhuma'); ?></td>
                <td>
                    <a href="editar_curso.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Editar</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Tem certeza?');">
                        <input type="hidden" name="id_to_delete" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete_curso" class="btn btn-sm btn-danger">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php include '../templates/footer.php'; ?>