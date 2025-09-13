<?php
require_once 'config.php';
$token = $_GET['token'] ?? '';
$erro = '';
$sucesso = '';

if (empty($token)) { header("location: login.php"); exit; }

$stmt = $conexao->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    $erro = "Token inválido ou expirado. Por favor, solicite um novo link.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($erro)) {
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if (strlen($senha) < 6) {
        $erro = "A senha deve ter no mínimo 6 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
        $stmt_update = $conexao->prepare("UPDATE usuarios SET senha = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ?");
        $stmt_update->bind_param("ss", $hashed_senha, $token);
        $stmt_update->execute();
        $sucesso = "Senha redefinida com sucesso! Você já pode fazer o login.";
    }
}
?>
<?php include 'templates/header.php'; ?>
    <div class="row justify-content-center"><div class="col-md-6">
            <div class="card"><div class="card-body p-4">
                    <h2 class="text-center mb-4">Redefinir Senha</h2>
                    <?php if ($erro) echo "<div class='alert alert-danger'>$erro</div>"; ?>
                    <?php if ($sucesso) echo "<div class='alert alert-success'>$sucesso</div><div class='text-center'><a href='login.php' class='btn btn-primary'>Ir para Login</a></div>"; ?>

                    <?php if (empty($erro) && empty($sucesso)): ?>
                        <form method="post">
                            <div class="mb-3"><label for="senha" class="form-label">Nova Senha</label><input type="password" name="senha" class="form-control" required></div>
                            <div class="mb-3"><label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label><input type="password" name="confirmar_senha" class="form-control" required></div>
                            <div class="d-grid"><button type="submit" class="btn btn-primary">Salvar Nova Senha</button></div>
                        </form>
                    <?php endif; ?>
                </div></div>
        </div></div>
<?php include 'templates/footer.php'; ?>