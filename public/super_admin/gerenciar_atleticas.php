<?php
require_once '../../src/config/config.php';
is_superadmin();
$mensagem = '';

// Lógica para Adicionar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_atletica'])) {
    $nome = trim($_POST['nome']);
    if (!empty($nome)) {
        $stmt = $conexao->prepare("INSERT INTO atleticas (nome) VALUES (?)");
        $stmt->bind_param("s", $nome);
        if ($stmt->execute()) $mensagem = "<div class='alert alert-success'>Atlética adicionada com sucesso!</div>";
    }
}

// Lógica para Deletar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_atletica'])) {
    $id = $_POST['id_to_delete'];
    // Primeiro, desvincula todos os usuários dessa atlética
    $stmt_update = $conexao->prepare("UPDATE usuarios SET atletica_id = NULL WHERE atletica_id = ?");
    $stmt_update->bind_param("i", $id);
    $stmt_update->execute();

    // Agora pode deletar a atlética
    $stmt = $conexao->prepare("DELETE FROM atleticas WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $mensagem = "<div class='alert alert-warning'>Atlética excluída com sucesso!</div>";
}

$atleticas = $conexao->query("SELECT * FROM atleticas ORDER BY nome ASC");
?>
<?php include '../../templates/header.php'; ?>
    <h2>Gerenciar Atléticas</h2>
<?php echo $mensagem; ?>
    <div class="card mb-4"><div class="card-body">
            <h5>Adicionar Nova Atlética</h5>
            <form method="post" class="row g-3 align-items-center">
                <div class="col-auto"><input type="text" name="nome" class="form-control" placeholder="Nome da nova atlética" required></div>
                <div class="col-auto"><button type="submit" name="add_atletica" class="btn btn-primary">Adicionar</button></div>
            </form>
        </div></div>
    <table class="table table-striped">
        <thead><tr><th>ID</th><th>Nome</th><th>Ações</th></tr></thead>
        <tbody>
        <?php while($row = $atleticas->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                <td>
                    <a href="editar_atletica.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Editar</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Atenção! Excluir uma atlética pode desvincular cursos. Deseja continuar?');">
                        <input type="hidden" name="id_to_delete" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete_atletica" class="btn btn-sm btn-danger">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php include '../../templates/footer.php'; ?>
