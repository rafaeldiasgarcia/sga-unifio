<?php
require_once 'config.php';
$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha e-mail e senha.";
    } else {
        $sql = "SELECT id, nome, email, senha, role FROM usuarios WHERE email = ?";
        if ($stmt = $conexao->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $nome, $db_email, $hashed_senha, $role);
                if ($stmt->fetch() && password_verify($senha, $hashed_senha)) {
                    // Senha correta! Agora preparamos para a verificação de 2 etapas.

                    // Se for Super Admin, pula a verificação de 2 etapas e entra direto
                    if ($role == 'superadmin') {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["nome"] = $nome;
                        $_SESSION["role"] = $role;
                        header("location: index.php");
                        exit;
                    }

                    // Para outros usuários, gera o código
                    $code = rand(100000, 999999);
                    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                    $update_sql = "UPDATE usuarios SET login_code = ?, login_code_expires = ? WHERE id = ?";
                    if ($update_stmt = $conexao->prepare($update_sql)) {
                        $update_stmt->bind_param("ssi", $code, $expires, $id);
                        $update_stmt->execute();

                        $_SESSION['login_email'] = $email;
                        $_SESSION['login_code_simulado'] = $code;

                        header("location: login_verify.php");
                        exit;
                    }
                } else {
                    $erro = "E-mail ou senha inválidos.";
                }
            } else {
                $erro = "E-mail ou senha inválidos.";
            }
            $stmt->close();
        }
    }
}
?>

<?php include 'templates/header.php'; ?>
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Login</h2>

                    <?php if (isset($_GET['registro']) && $_GET['registro'] == 'sucesso'): ?>
                        <div class="alert alert-success">Cadastro realizado com sucesso! Faça seu login.</div>
                    <?php endif; ?>

                    <?php if ($erro) echo "<div class='alert alert-danger'>$erro</div>"; ?>

                    <form action="login.php" method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" name="senha" class="form-control" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Entrar</button>
                        </div>
                        <p class="mt-3 text-center">
                            Não possui uma conta? <a href="registro.php">Cadastre-se</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php include 'templates/footer.php'; ?>