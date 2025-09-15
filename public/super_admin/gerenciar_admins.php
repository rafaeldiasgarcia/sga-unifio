<?php
require_once '../../src/config/config.php';
is_superadmin();
$mensagem = '';

// Lógica para Promover
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['promote_admin'])) {
    $aluno_id = $_POST['aluno_id'];

    // Busca a atletica_id do usuário que está sendo promovido
    $stmt_get_atletica = $conexao->prepare("SELECT atletica_id FROM usuarios WHERE id = ?");
    $stmt_get_atletica->bind_param("i", $aluno_id);
    $stmt_get_atletica->execute();
    $result = $stmt_get_atletica->get_result();
    $user_data = $result->fetch_assoc();
    $atletica_id = $user_data['atletica_id'];

    if ($atletica_id) {
        $stmt = $conexao->prepare("UPDATE usuarios SET role = 'admin' WHERE id = ?");
        $stmt->bind_param("i", $aluno_id);
        if ($stmt->execute()) {
            $mensagem = "<div class='alert alert-success'>Usuário promovido a Admin com sucesso!</div>";
        }
    } else {
        $mensagem = "<div class='alert alert-danger'>Erro: Este usuário não está associado a nenhuma atlética.</div>";
    }
}

// Lógica para Rebaixar Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['demote_admin'])) {
    $admin_id = $_POST['admin_id_to_demote'];
    // Não remove o atletica_id, apenas muda o role. Ele continua sendo membro.
    $stmt = $conexao->prepare("UPDATE usuarios SET role = 'usuario' WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    if ($stmt->execute()) $mensagem = "<div class='alert alert-warning'>Admin rebaixado para Usuário com sucesso!</div>";
}

// Admins Atuais
$admins = $conexao->query("SELECT u.id, u.nome, a.nome as atletica_nome FROM usuarios u JOIN atleticas a ON u.atletica_id = a.id WHERE u.role = 'admin' ORDER BY u.nome");
// Usuários elegíveis para promoção (apenas Membros de Atléticas que ainda não são admins)
$alunos_elegiveis = $conexao->query("SELECT u.id, u.nome, a.nome as atletica_nome FROM usuarios u JOIN atleticas a ON u.atletica_id = a.id WHERE u.role = 'usuario' AND u.tipo_usuario_detalhado = 'Membro das Atléticas' ORDER BY u.nome");
?>
<?php include '../../templates/header.php'; ?>
    <h2>Gerenciar Administradores de Atléticas</h2>
<?php echo $mensagem; ?>
    <div class="card mb-4"><div class="card-body">
            <h5>Promover Membro da Atlética para Admin</h5>
            <p class="text-muted">A lista abaixo mostra apenas usuários que são "Membro das Atléticas" e podem ser promovidos.</p>
            <form method="post" class="row g-3 align-items-center">
                <div class="col-md-10">
                    <select name="aluno_id" class="form-select" required>
                        <option value="">-- Selecione o Membro para Promover --</option>
                        <?php while($row = $alunos_elegiveis->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['nome']) . ' (Atlética: ' . htmlspecialchars($row['atletica_nome']) . ')'; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" name="promote_admin" class="btn btn-primary w-100">Promover</button></div>
            </form>
        </div></div>
    <h5>Admins Atuais</h5>
    <table class="table table-striped">
        <thead><tr><th>Nome</th><th>Atlética Administrada</th><th>Ações</th></tr></thead>
        <tbody>
        <?php while($row = $admins->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                <td><?php echo htmlspecialchars($row['atletica_nome']); ?></td>
                <td>
                    <form method="post" class="d-inline" onsubmit="return confirm('Tem certeza que deseja remover os privilégios de admin deste usuário?');">
                        <input type="hidden" name="admin_id_to_demote" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="demote_admin" class="btn btn-sm btn-danger">Rebaixar para Usuário</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php include '../../templates/footer.php'; ?>
