<?php
require_once '../config.php';
check_login();

if ($_SESSION['role'] == 'superadmin') {
    header("location: ../super_admin/dashboard.php");
    exit;
}

$usuario_id = $_SESSION['id'];
$info_msg = '';
$senha_msg = '';
$atletica_msg = '';

// --- LÓGICA PARA ATUALIZAR DADOS PESSOAIS E CURSO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['atualizar_dados'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $data_nascimento = trim($_POST['data_nascimento']);
    $curso_id_aluno = $_POST['curso_id'] ?? null;
    $cursos_professor = $_POST['cursos_professor'] ?? [];

    if ($_SESSION['tipo_usuario_detalhado'] == 'Professor') {
        $stmt_update_user = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ?, data_nascimento = ? WHERE id = ?");
        $stmt_update_user->bind_param("sssi", $nome, $email, $data_nascimento, $usuario_id);
        $stmt_update_user->execute();

        $stmt_delete_cursos = $conexao->prepare("DELETE FROM professores_cursos WHERE professor_id = ?");
        $stmt_delete_cursos->bind_param("i", $usuario_id);
        $stmt_delete_cursos->execute();

        if (!empty($cursos_professor)) {
            $stmt_insert_cursos = $conexao->prepare("INSERT INTO professores_cursos (professor_id, curso_id) VALUES (?, ?)");
            foreach ($cursos_professor as $curso_id) {
                $stmt_insert_cursos->bind_param("ii", $usuario_id, $curso_id);
                $stmt_insert_cursos->execute();
            }
        }
        $info_msg = "<div class='alert alert-success'>Dados atualizados com sucesso!</div>";
    } else {
        // Lógica para Alunos e outros
        $sql_update = "UPDATE usuarios SET nome = ?, email = ?, data_nascimento = ?, curso_id = ? WHERE id = ?";
        $stmt_update = $conexao->prepare($sql_update);
        // CORREÇÃO: O tipo de dado para curso_id é 'i' (integer)
        $stmt_update->bind_param("sssii", $nome, $email, $data_nascimento, $curso_id_aluno, $usuario_id);
        if ($stmt_update->execute()) {
            $info_msg = "<div class='alert alert-success'>Dados atualizados com sucesso!</div>";
        } else {
            $info_msg = "<div class='alert alert-danger'>Erro ao atualizar. O e-mail pode já estar em uso.</div>";
        }
    }
    $_SESSION['nome'] = $nome;
}

// --- LÓGICA PARA ALTERAR SENHA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['alterar_senha'])) {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_nova_senha = $_POST['confirmar_nova_senha'];

    if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_nova_senha)) {
        $senha_msg = "<div class='alert alert-danger'>Todos os campos de senha são obrigatórios.</div>";
    } elseif ($nova_senha !== $confirmar_nova_senha) {
        $senha_msg = "<div class='alert alert-danger'>A nova senha e a confirmação não coincidem.</div>";
    } else {
        $sql_pass = "SELECT senha FROM usuarios WHERE id = ?";
        if ($stmt_pass = $conexao->prepare($sql_pass)) {
            $stmt_pass->bind_param("i", $usuario_id);
            $stmt_pass->execute();
            $stmt_pass->bind_result($hashed_senha);
            if ($stmt_pass->fetch() && password_verify($senha_atual, $hashed_senha)) {
                $stmt_pass->close();
                $nova_hashed_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
                $sql_update_pass = "UPDATE usuarios SET senha = ? WHERE id = ?";
                $stmt_update_pass = $conexao->prepare($sql_update_pass);
                $stmt_update_pass->bind_param("si", $nova_hashed_senha, $usuario_id);
                if ($stmt_update_pass->execute()) {
                    $senha_msg = "<div class='alert alert-success'>Senha alterada com sucesso!</div>";
                } else {
                    $senha_msg = "<div class='alert alert-danger'>Erro ao alterar a senha.</div>";
                }
            } else {
                $senha_msg = "<div class='alert alert-danger'>A senha atual está incorreta.</div>";
            }
        }
    }
}

// --- LÓGICA PARA ENTRAR/SAIR DA ATLÉTICA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['gerenciar_atletica'])) {
    $acao = $_POST['acao'];
    if ($acao == 'entrar') {
        $sql = "UPDATE usuarios SET atletica_join_status = 'pendente' WHERE id = ?";
        $atletica_msg = "<div class='alert alert-success'>Solicitação para entrar na atlética enviada! Aguarde a aprovação do admin.</div>";
    } elseif ($acao == 'sair') {
        $sql = "UPDATE usuarios SET tipo_usuario_detalhado = 'Aluno', atletica_join_status = 'none' WHERE id = ?";
        $atletica_msg = "<div class='alert alert-info'>Você saiu da atlética e agora é um aluno comum.</div>";
        $_SESSION['tipo_usuario_detalhado'] = 'Aluno';
    }

    if (isset($sql)) {
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
    }
}

// Busca os dados mais recentes do usuário
$sql_user = "SELECT u.*, c.atletica_id, a.nome as atletica_nome FROM usuarios u LEFT JOIN cursos c ON u.curso_id = c.id LEFT JOIN atleticas a ON c.atletica_id = a.id WHERE u.id = ?";
$stmt_user = $conexao->prepare($sql_user);
$stmt_user->bind_param("i", $usuario_id);
$stmt_user->execute();
$usuario = $stmt_user->get_result()->fetch_assoc();

$cursos_atuais_professor = [];
if ($usuario['tipo_usuario_detalhado'] == 'Professor') {
    $stmt_cursos_prof = $conexao->prepare("SELECT curso_id FROM professores_cursos WHERE professor_id = ?");
    $stmt_cursos_prof->bind_param("i", $usuario_id);
    $stmt_cursos_prof->execute();
    $result = $stmt_cursos_prof->get_result();
    while($row = $result->fetch_assoc()) {
        $cursos_atuais_professor[] = $row['curso_id'];
    }
}

$cursos = $conexao->query("SELECT id, nome FROM cursos ORDER BY nome");
?>

<?php include '../templates/header.php'; ?>
    <h2>Meu Perfil</h2>
    <p>Gerencie suas informações pessoais, de acesso e seu vínculo com a atlética.</p>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><strong>Dados Pessoais</strong></div>
                <div class="card-body">
                    <?php echo $info_msg; ?>
                    <form action="perfil.php" method="post">
                        <div class="mb-3"><label class="form-label">RA</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['ra']); ?>" disabled></div>
                        <div class="mb-3"><label class="form-label">Nome Completo</label><input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required></div>
                        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required></div>
                        <div class="mb-3"><label class="form-label">Data de Nascimento</label><input type="date" name="data_nascimento" class="form-control" value="<?php echo htmlspecialchars($usuario['data_nascimento']); ?>" required></div>

                        <?php if ($usuario['tipo_usuario_detalhado'] == 'Aluno' || $usuario['tipo_usuario_detalhado'] == 'Membro das Atléticas'): ?>
                            <div class="mb-3"><label for="curso_id" class="form-label">Meu Curso</label>
                                <select name="curso_id" id="curso_id" class="form-select">
                                    <option value="">-- Não especificado --</option>
                                    <?php $cursos->data_seek(0); while($curso = $cursos->fetch_assoc()): ?>
                                        <option value="<?php echo $curso['id']; ?>" <?php if($usuario['curso_id'] == $curso['id']) echo 'selected'; ?>><?php echo htmlspecialchars($curso['nome']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        <?php elseif ($usuario['tipo_usuario_detalhado'] == 'Professor'): ?>
                            <div class="mb-3"><label class="form-label">Cursos que Leciono</label>
                                <select name="cursos_professor[]" class="form-select" multiple size="5">
                                    <?php $cursos->data_seek(0); while($curso = $cursos->fetch_assoc()): ?>
                                        <option value="<?php echo $curso['id']; ?>" <?php if(in_array($curso['id'], $cursos_atuais_professor)) echo 'selected'; ?>><?php echo htmlspecialchars($curso['nome']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="form-text">Segure Ctrl (ou Cmd) para selecionar mais de um.</div>
                            </div>
                        <?php endif; ?>

                        <button type="submit" name="atualizar_dados" class="btn btn-primary">Salvar Dados</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card mb-4">
                <div class="card-header"><strong>Alterar Senha</strong></div>
                <div class="card-body">
                    <?php echo $senha_msg; ?>
                    <form action="perfil.php" method="post">
                        <div class="mb-3"><label for="senha_atual" class="form-label">Senha Atual</label><input type="password" name="senha_atual" id="senha_atual" class="form-control" required></div>
                        <div class="mb-3"><label for="nova_senha" class="form-label">Nova Senha</label><input type="password" name="nova_senha" id="nova_senha" class="form-control" required></div>
                        <div class="mb-3"><label for="confirmar_nova_senha" class="form-label">Confirmar Nova Senha</label><input type="password" name="confirmar_nova_senha" id="confirmar_nova_senha" class="form-control" required></div>
                        <button type="submit" name="alterar_senha" class="btn btn-warning">Alterar Senha</button>
                    </form>
                </div>
            </div>

            <?php if ($usuario['tipo_usuario_detalhado'] == 'Aluno' || $usuario['tipo_usuario_detalhado'] == 'Membro das Atléticas'): ?>
                <div class="card">
                    <div class="card-header"><strong>Gerenciar Atlética</strong></div>
                    <div class="card-body">
                        <?php echo $atletica_msg; ?>
                        <?php if ($usuario['tipo_usuario_detalhado'] == 'Aluno'): ?>
                            <?php if ($usuario['atletica_join_status'] == 'pendente'): ?>
                                <div class="alert alert-warning">Sua solicitação para entrar na atlética está pendente de aprovação.</div>
                            <?php elseif (!empty($usuario['atletica_nome'])): ?>
                                <p>Seu curso está vinculado à <strong><?php echo htmlspecialchars($usuario['atletica_nome']); ?></strong>.</p>
                                <form action="perfil.php" method="post">
                                    <input type="hidden" name="acao" value="entrar">
                                    <button type="submit" name="gerenciar_atletica" class="btn btn-success">Quero entrar na Atlética</button>
                                </form>
                            <?php else: ?>
                                <p class="text-muted">Seu curso não está vinculado a nenhuma atlética no momento.</p>
                            <?php endif; ?>
                        <?php elseif ($usuario['tipo_usuario_detalhado'] == 'Membro das Atléticas'): ?>
                            <p>Você atualmente é membro da atlética do seu curso.</p>
                            <form action="perfil.php" method="post">
                                <input type="hidden" name="acao" value="sair">
                                <button type="submit" name="gerenciar_atletica" class="btn btn-danger">Sair da Atlética</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>