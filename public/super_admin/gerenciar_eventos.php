<?php
require_once '../../src/config/config.php';
is_superadmin();
$mensagem = '';

// Lógica para Adicionar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_evento'])) {
    $nome = trim($_POST['nome']);
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    // Desativa todos os outros eventos antes de criar um novo ativo
    $conexao->query("UPDATE eventos SET ativo = 0");

    $stmt = $conexao->prepare("INSERT INTO eventos (nome, data_inicio, data_fim, ativo) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("sss", $nome, $data_inicio, $data_fim);
    if ($stmt->execute()) {
        $mensagem = "<div class='alert alert-success'>Novo evento ativo criado com sucesso!</div>";
    }
}

// Lógica para Deletar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_evento'])) {
    $id = $_POST['id_to_delete'];
    $stmt = $conexao->prepare("DELETE FROM eventos WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensagem = "<div class='alert alert-warning'>Evento excluído com sucesso!</div>";
    }
}

$eventos = $conexao->query("SELECT * FROM eventos ORDER BY data_inicio DESC");
?>

<?php include '../../templates/header.php'; ?>

    <h2>Gerenciar Eventos</h2>

<?php echo $mensagem; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Criar Novo Evento (e torná-lo o evento ativo)</h5>
            <form method="post" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="nome" class="form-control" placeholder="Nome (Ex: Intercursos 2025)" required>
                </div>
                <div class="col-md-3">
                    <label>Início:</label>
                    <input type="date" name="data_inicio" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>Fim:</label>
                    <input type="date" name="data_fim" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_evento" class="btn btn-primary w-100 mt-3">Criar Evento</button>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Início</th>
            <th>Fim</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php while($row = $eventos->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['data_inicio'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['data_fim'])); ?></td>
                <td><?php echo $row['ativo'] ? "<span class='badge bg-success'>Ativo</span>" : "<span class='badge bg-secondary'>Inativo</span>"; ?></td>
                <td>
                    <a href="editar_evento.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Editar</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Atenção! Excluir um evento também excluirá todas as modalidades e equipes associadas. Deseja continuar?');">
                        <input type="hidden" name="id_to_delete" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete_evento" class="btn btn-sm btn-danger">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

<?php include '../../templates/footer.php'; ?>
