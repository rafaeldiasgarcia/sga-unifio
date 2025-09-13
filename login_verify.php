<?php
require_once 'config.php';
$erro = '';

if (!isset($_SESSION['login_email'])) {
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['code']);
    $email = $_SESSION['login_email'];

    // ATUALIZADO: Busca também o tipo_usuario_detalhado
    $sql = "SELECT id, nome, role, atletica_id, tipo_usuario_detalhado FROM usuarios WHERE email = ? AND login_code = ? AND login_code_expires > NOW()";
    if ($stmt = $conexao->prepare($sql)) {
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $nome, $role, $atletica_id, $tipo_usuario_detalhado);
            $stmt->fetch();

            $conexao->query("UPDATE usuarios SET login_code = NULL, login_code_expires = NULL WHERE id = $id");

            // Inicia a sessão com todos os dados
            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $id;
            $_SESSION["nome"] = $nome;
            $_SESSION["role"] = $role;
            $_SESSION["tipo_usuario_detalhado"] = $tipo_usuario_detalhado; // <-- LINHA ADICIONADA

            if ($role == 'admin') {
                $_SESSION["atletica_id"] = $atletica_id;
            }

            unset($_SESSION['login_email']);
            unset($_SESSION['login_code_simulado']);

            header("location: index.php");
            exit;
        } else {
            $erro = "Código inválido ou expirado. Tente novamente.";
        }
    }
}
?>
    <!-- O restante do HTML do arquivo login_verify.php permanece o mesmo -->
<?php include 'templates/header.php'; ?>
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-3">Verificação de Acesso</h2>
                    <p class="text-center text-muted">Para sua segurança, um código foi "enviado" para <?php echo htmlspecialchars($_SESSION['login_email']); ?>.</p>
                    <div class="alert alert-info">
                        <strong>[AMBIENTE DE TESTE]</strong><br>
                        Seu código de acesso é: <strong><?php echo $_SESSION['login_code_simulado']; ?></strong>
                    </div>
                    <?php if ($erro) echo "<div class='alert alert-danger'>$erro</div>"; ?>
                    <form action="login_verify.php" method="post">
                        <div class="mb-3">
                            <label for="code" class="form-label">Código de 6 dígitos</label>
                            <input type="text" name="code" class="form-control" required maxlength="6" autofocus>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Verificar e Entrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php include 'templates/footer.php'; ?>