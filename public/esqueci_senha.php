<?php
require_once '../src/config/config.php';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $stmt_update = $conexao->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
        $stmt_update->bind_param("sss", $token, $expires, $email);
        $stmt_update->execute();

        // SIMULAÇÃO DE ENVIO DE E-MAIL
        $reset_link = "http://localhost/sga/public/redefinir_senha.php?token=" . $token;
        $mensagem = "<div class='alert alert-info'><strong>[AMBIENTE DE TESTE]</strong><br>Seu link para redefinir a senha é: <a href='{$reset_link}'>{$reset_link}</a></div>";
    } else {
        $mensagem = "<div class='alert alert-warning'>Se o e-mail existir em nossa base, um link de recuperação foi enviado.</div>";
    }
}
?>
<?php include '../templates/header.php'; ?>
    <div class="row justify-content-center"><div class="col-md-6">
            <div class="card"><div class="card-body p-4">
                    <h2 class="text-center mb-4">Recuperar Senha</h2>
                    <?php echo $mensagem; ?>
                    <p>Por favor, insira seu e-mail. Se ele estiver cadastrado, enviaremos um link para você redefinir sua senha.</p>
                    <form method="post">
                        <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                        <div class="d-grid"><button type="submit" class="btn btn-primary">Enviar Link de Recuperação</button></div>
                        <p class="mt-3 text-center"><a href="login.php">Voltar para o Login</a></p>
                    </form>
                </div></div>
        </div></div>
<?php include '../templates/footer.php'; ?>
