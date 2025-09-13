<?php
require_once '../config.php';
is_superadmin();
$mensagem = '';

// Lógica para Adicionar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_modalidade'])) {
    $nome = trim($_POST['nome']);
    $evento_id = $_POST['evento_id'];
    if (!empty($nome) && !empty($evento_id)) {
        $stmt = $conexao->prepare("INSERT INTO modalidades (nome, evento_id) VALUES (?, ?)");
        $stmt->bind_param("si", $nome, $evento_id);
        if ($stmt->execute()) {
            $mensagem = "<div class='alert alert-success'>Modalidade adicionada com sucesso!</div>";
        } else {
            $mensagem = "<div class='alert alert-danger'>Erro ao adicionar a modalidade.</div>";
        }
    }
}

// Lógica para Deletar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_modalidade'])) {
    $id = $_POST['id_to_delete'];
    $stmt = $conexao->prepare("DELETE FROM modalidades WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensagem = "<div class='alert alert-warning'>Modalidade excluída com sucesso!</div>";
    } else {
        $mensagem = "<div class='alert alert-danger'>Erro ao excluir a modalidade.</div>";
    }
}

$modalidades = $conexao->query("SELECT m.id, m.nome, e.nome as evento_nome FROM modalidades m JOIN eventos e ON m.evento_id = e.id ORDER BY e.nome, m.nome");
$eventos_ativos = $conexao->query("SELECT * FROM eventos WHERE ativo = 1");
?>
<?php include '../templates/header.php'; ?>
    <h2>Gerenciar Modalidades</h2>
<?php echo $mensagem; ?>
    <div class="card mb-4"><div class="card-body">
            <h5>Adicionar Nova Modalidade</h5>
            <form method="post" class="row g-3 align-items-center">
                <div class="col-md-5"><input type="text" name="nome" class="form-control" placeholder="Nome (Ex: Futsal Masculino)" required></div>
                <div class="col-md-5"><select name="evento_id" class="form-select" required><option value="">-- Associar ao Evento Ativo --</option>
                        <?php if ($eventos_ativos->num_rows > 0): ?>
                            <?php while($row = $eventos_ativos->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['nome']); ?></option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="" disabled>Nenhum evento ativo encontrado</option>
                        <?php endif; ?>
                    </select></div>
                <div class="col-md-2"><button type="submit" name="add_modalidade" class="btn btn-primary w-100">Adicionar</button></div>
            </form>
        </div></div>
    <table class="table table-striped">
        <thead><tr><th>ID</th><th>Nome da Modalidade</th><th>Evento</th><th>Ações</th></tr></thead>
        <tbody>
        <?php if ($modalidades->num_rows > 0): ?>
            <?php while($row = $modalidades->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                    <td><?php echo htmlspecialchars($row['evento_nome']); ?></td>
                    <td>
                        <a href="editar_modalidade.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Editar</a>
                        <form method="post" class="d-inline" onsubmit="return confirm('Atenção! Excluir uma modalidade também removerá as equipes associadas. Deseja continuar?');">
                            <input type="hidden" name="id_to_delete" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_modalidade" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4" class="text-center">Nenhuma modalidade cadastrada.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
<?php include '../templates/footer.php'; ?>