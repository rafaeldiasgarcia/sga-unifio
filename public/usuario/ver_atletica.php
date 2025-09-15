<?php
require_once '../../src/config/config.php';
check_login();

// Apenas Membros de Atléticas podem ver esta página
if ($_SESSION['tipo_usuario_detalhado'] !== 'Membro das Atléticas') {
    header("location: dashboard.php");
    exit;
}

$atletica_id = 0;
// Busca a atlética do usuário logado
$stmt_user = $conexao->prepare("SELECT atletica_id FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $_SESSION['id']);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
if ($user_data) {
    $atletica_id = $user_data['atletica_id'];
}

// Busca todos os membros da mesma atlética
$membros = [];
if ($atletica_id) {
    $stmt_membros = $conexao->prepare("SELECT nome, email, ra FROM usuarios WHERE atletica_id = ? AND tipo_usuario_detalhado = 'Membro das Atléticas' ORDER BY nome");
    $stmt_membros->bind_param("i", $atletica_id);
    $stmt_membros->execute();
    $membros = $stmt_membros->get_result();
}
?>
<?php include '../../templates/header.php'; ?>
    <h2>Membros da Atlética</h2>
    <div class="card"><div class="card-body">
            <table class="table">
                <thead><tr><th>Nome</th><th>Email</th><th>RA</th></tr></thead>
                <tbody>
                <?php if ($membros && $membros instanceof mysqli_result && $membros->num_rows > 0): ?>
                    <?php while($membro = $membros->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($membro['nome']); ?></td>
                            <td><?php echo htmlspecialchars($membro['email']); ?></td>
                            <td><?php echo htmlspecialchars($membro['ra']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center">Nenhum membro encontrado.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div></div>
<?php include '../../templates/footer.php'; ?>
