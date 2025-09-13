<?php
require_once '../config.php';
is_superadmin();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_modalidade'])) {
    $nome = $_POST['nome'];
    $evento_id = $_POST['evento_id'];
    $conexao->query("INSERT INTO modalidades (nome, evento_id) VALUES ('$nome', $evento_id)");
}
$modalidades = $conexao->query("SELECT m.id, m.nome, e.nome as evento_nome FROM modalidades m JOIN eventos e ON m.evento_id = e.id");
$eventos = $conexao->query("SELECT * FROM eventos WHERE ativo = 1");
?>
<?php include '../templates/header.php'; ?>
    <h2>Gerenciar Modalidades</h2>
    <div class="card mb-4"><div class="card-body">
            <form method="post">
                <input type="text" name="nome" placeholder="Nome da Modalidade (Ex: Futsal Masculino)" required>
                <select name="evento_id" required><option value="">-- Associar ao Evento Ativo --</option>
                    <?php while($row = $eventos->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['nome']; ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="add_modalidade">Adicionar</button>
            </form>
        </div></div>
    <table class="table">
        <thead><tr><th>ID</th><th>Nome</th><th>Evento</th></tr></thead>
        <tbody>
        <?php while($row = $modalidades->fetch_assoc()): ?>
            <tr><td><?php echo $row['id']; ?></td><td><?php echo $row['nome']; ?></td><td><?php echo $row['evento_nome']; ?></td></tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php include '../templates/footer.php'; ?>