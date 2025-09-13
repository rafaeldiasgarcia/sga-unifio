<?php
require_once '../config.php';
is_superadmin();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_evento'])) {
    $nome = $_POST['nome'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $conexao->query("UPDATE eventos SET ativo = 0"); // Desativa eventos antigos
    $conexao->query("INSERT INTO eventos (nome, data_inicio, data_fim, ativo) VALUES ('$nome', '$data_inicio', '$data_fim', 1)");
}
$eventos = $conexao->query("SELECT * FROM eventos ORDER BY data_inicio DESC");
?>
<?php include '../templates/header.php'; ?>
    <h2>Gerenciar Eventos</h2>
    <div class="card mb-4"><div class="card-body">
            <form method="post">
                <input type="text" name="nome" placeholder="Nome do Evento (Ex: Intercursos 2025)" required>
                <input type="date" name="data_inicio" required>
                <input type="date" name="data_fim" required>
                <button type="submit" name="add_evento">Criar Novo Evento (e torná-lo ativo)</button>
            </form>
        </div></div>
    <table class="table">
        <thead><tr><th>ID</th><th>Nome</th><th>Início</th><th>Fim</th><th>Status</th></tr></thead>
        <tbody>
        <?php while($row = $eventos->fetch_assoc()): ?>
            <tr><td><?php echo $row['id']; ?></td><td><?php echo $row['nome']; ?></td><td><?php echo $row['data_inicio']; ?></td><td><?php echo $row['data_fim']; ?></td><td><?php echo $row['ativo'] ? 'Ativo' : 'Inativo'; ?></td></tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php include '../templates/footer.php'; ?>