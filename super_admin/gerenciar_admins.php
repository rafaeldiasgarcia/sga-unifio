<?php
require_once '../config.php';
is_superadmin();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['promote_admin'])) {
    $aluno_id = $_POST['aluno_id'];
    $atletica_id = $_POST['atletica_id'];
    $conexao->query("UPDATE usuarios SET role = 'admin', atletica_id = $atletica_id WHERE id = $aluno_id");
}
$admins = $conexao->query("SELECT u.id, u.nome, a.nome as atletica_nome FROM usuarios u JOIN atleticas a ON u.atletica_id = a.id WHERE u.role = 'admin'");
$alunos = $conexao->query("SELECT id, nome, email FROM usuarios WHERE role = 'aluno'");
$atleticas = $conexao->query("SELECT * FROM atleticas");
?>
<?php include '../templates/header.php'; ?>
    <h2>Gerenciar Administradores de Atléticas</h2>
    <div class="card mb-4"><div class="card-body">
            <h5>Promover Aluno para Admin</h5>
            <form method="post">
                <select name="aluno_id" required><option value="">-- Selecione o Aluno --</option>
                    <?php while($row = $alunos->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['nome'] . ' (' . $row['email'] . ')'; ?></option>
                    <?php endwhile; ?>
                </select>
                <select name="atletica_id" required><option value="">-- Para a Atlética --</option>
                    <?php while($row = $atleticas->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['nome']; ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="promote_admin">Promover</button>
            </form>
        </div></div>
    <h5>Admins Atuais</h5>
    <table class="table">
        <thead><tr><th>ID</th><th>Nome</th><th>Atlética</th></tr></thead>
        <tbody>
        <?php while($row = $admins->fetch_assoc()): ?>
            <tr><td><?php echo $row['id']; ?></td><td><?php echo $row['nome']; ?></td><td><?php echo $row['atletica_nome']; ?></td></tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php include '../templates/footer.php'; ?>