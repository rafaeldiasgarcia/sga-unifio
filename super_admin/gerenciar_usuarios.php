<?php
require_once '../config.php';
is_superadmin();

// Buscar todos os usuários, exceto o próprio super admin logado
$sql = "SELECT id, nome, email, role, tipo_usuario_detalhado 
        FROM usuarios 
        WHERE id != ? 
        ORDER BY nome ASC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$usuarios = $stmt->get_result();
?>

<?php include '../templates/header.php'; ?>
    <h2>Gerenciar Todos os Usuários</h2>
    <p>Visualize e edite as informações de qualquer usuário cadastrado no sistema.</p>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Nome</th><th>Email</th><th>Perfil Principal</th><th>Vínculo</th><th>Ação</th></tr></thead>
                    <tbody>
                    <?php while($user = $usuarios->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['nome']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo ucfirst($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['tipo_usuario_detalhado'] ?? 'N/A'); ?></td>
                            <td>
                                <a href="editar_usuario.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>