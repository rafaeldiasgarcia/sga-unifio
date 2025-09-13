<?php
require_once '../config.php';
is_superadmin();

$user_id = $_GET['id'] ?? 0;
if (!$user_id || $user_id == $_SESSION['id']) {
    header("location: gerenciar_usuarios.php");
    exit;
}

$mensagem = '';

// --- LÓGICA PARA DELETAR USUÁRIO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id_to_delete = $_POST['user_id_to_delete'];
    $confirmation_password = $_POST['confirmation_password'];
    $superadmin_id = $_SESSION['id'];

    $sql_check_pass = "SELECT senha FROM usuarios WHERE id = ?";
    $stmt_check_pass = $conexao->prepare($sql_check_pass);
    $stmt_check_pass->bind_param("i", $superadmin_id);
    $stmt_check_pass->execute();
    $result = $stmt_check_pass->get_result();
    $admin_data = $result->fetch_assoc();

    if ($admin_data && password_verify($confirmation_password, $admin_data['senha'])) {
        $sql_delete = "DELETE FROM usuarios WHERE id = ?";
        if ($stmt_delete = $conexao->prepare($sql_delete)) {
            $stmt_delete->bind_param("i", $user_id_to_delete);
            if ($stmt_delete->execute()) {
                header("location: gerenciar_usuarios.php?status=deleted");
                exit;
            }
        }
    } else {
        $mensagem = "<div class='alert alert-danger'>Senha de confirmação incorreta. A exclusão foi cancelada.</div>";
    }
}

// --- LÓGICA PARA ATUALIZAR DADOS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $ra = trim($_POST['ra']);
    $role = trim($_POST['role']);
    $tipo_usuario_detalhado = trim($_POST['tipo_usuario_detalhado']);
    $curso_id = $_POST['curso_id'] ?: null;
    $atletica_id = $_POST['atletica_id'] ?: null;
    $nova_senha = trim($_POST['nova_senha']);
    $is_coordenador = isset($_POST['is_coordenador']) ? 1 : 0;
    $cursos_professor = $_POST['cursos_professor'] ?? [];

    // Validação de RA: obrigatório e exatamente 6 dígitos para quem não é Professor nem Comunidade Externa
    if ($tipo_usuario_detalhado != 'Professor' && $tipo_usuario_detalhado != 'Comunidade Externa') {
        if (empty($ra) || !preg_match('/^[0-9]{6}$/', $ra)) {
            $mensagem = "<div class='alert alert-danger'>O RA/Matrícula deve conter exatamente 6 números.</div>";
        }
    } else {
        // Para Professor/Comunidade Externa, permitir RA vazio
        if ($ra === '') {
            $ra = null;
        }
    }

    // Atualiza os dados básicos na tabela 'usuarios'
    $sql_user_update = "UPDATE usuarios SET nome=?, email=?, ra=?, role=?, tipo_usuario_detalhado=?, curso_id=?, atletica_id=?, is_coordenador=? WHERE id=?";
    $params_user = [$nome, $email, $ra, $role, $tipo_usuario_detalhado, $curso_id, $atletica_id, $is_coordenador, $user_id];
    $types_user = "sssssiisi";

    if (!empty($nova_senha)) {
        $sql_user_update = "UPDATE usuarios SET nome=?, email=?, ra=?, role=?, tipo_usuario_detalhado=?, curso_id=?, atletica_id=?, is_coordenador=?, senha=? WHERE id=?";
        $hashed_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
        $params_user = [$nome, $email, $ra, $role, $tipo_usuario_detalhado, $curso_id, $atletica_id, $is_coordenador, $hashed_senha, $user_id];
        $types_user = "sssssiissi";
    }

    if (empty($mensagem)) { // só prossegue com update se não houve erro de validação
        $stmt_user_update = $conexao->prepare($sql_user_update);
        $stmt_user_update->bind_param($types_user, ...$params_user);

        if ($stmt_user_update->execute()) {
        // Se for professor, atualiza a tabela de ligação de cursos
        if ($tipo_usuario_detalhado == 'Professor') {
            $stmt_delete_cursos = $conexao->prepare("DELETE FROM professores_cursos WHERE professor_id = ?");
            $stmt_delete_cursos->bind_param("i", $user_id);
            $stmt_delete_cursos->execute();

            if (!empty($cursos_professor)) {
                $stmt_insert_cursos = $conexao->prepare("INSERT INTO professores_cursos (professor_id, curso_id) VALUES (?, ?)");
                foreach ($cursos_professor as $c_id) {
                    $stmt_insert_cursos->bind_param("ii", $user_id, $c_id);
                    $stmt_insert_cursos->execute();
                }
            }
        }
            $mensagem = "<div class='alert alert-success'>Usuário atualizado com sucesso!</div>";
        } else {
            $mensagem = "<div class='alert alert-danger'>Erro ao atualizar. O e-mail ou RA pode já estar em uso.</div>";
        }
    }
}

// Buscar dados do usuário para preencher o formulário
$sql_user = "SELECT * FROM usuarios WHERE id = ?";
$stmt_user = $conexao->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

// Buscar cursos e atléticas para os dropdowns
$cursos = $conexao->query("SELECT id, nome FROM cursos ORDER BY nome");
$atleticas = $conexao->query("SELECT id, nome FROM atleticas ORDER BY nome");

// Buscar cursos atuais do professor (se for um)
$cursos_atuais_professor = [];
if ($user['tipo_usuario_detalhado'] == 'Professor') {
    $stmt_cursos_prof = $conexao->prepare("SELECT curso_id FROM professores_cursos WHERE professor_id = ?");
    $stmt_cursos_prof->bind_param("i", $user_id);
    $stmt_cursos_prof->execute();
    $result = $stmt_cursos_prof->get_result();
    while($row = $result->fetch_assoc()) {
        $cursos_atuais_professor[] = $row['curso_id'];
    }
}
?>

<?php include '../templates/header.php'; ?>
    <h2>Editando Usuário: <?php echo htmlspecialchars($user['nome']); ?></h2>
    <a href="gerenciar_usuarios.php" class="btn btn-secondary mb-3">Voltar para a lista</a>
<?php echo $mensagem; ?>

    <div class="card">
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($user['nome']); ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">RA/Matrícula</label><input type="text" name="ra" class="form-control" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" title="O RA deve conter exatamente 6 números." value="<?php echo htmlspecialchars($user['ra']); ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Nova Senha</label><input type="password" name="nova_senha" class="form-control" placeholder="Deixe em branco para não alterar"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Perfil Principal (Role)</label>
                        <select name="role" class="form-select">
                            <option value="usuario" <?php if($user['role'] == 'usuario') echo 'selected'; ?>>Usuário</option>
                            <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin da Atlética</option>
                            <option value="superadmin" <?php if($user['role'] == 'superadmin') echo 'selected'; ?>>Super Admin</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label">Vínculo Detalhado</label>
                        <select name="tipo_usuario_detalhado" class="form-select">
                            <option value="Aluno" <?php if($user['tipo_usuario_detalhado'] == 'Aluno') echo 'selected'; ?>>Aluno</option>
                            <option value="Membro das Atléticas" <?php if($user['tipo_usuario_detalhado'] == 'Membro das Atléticas') echo 'selected'; ?>>Membro das Atléticas</option>
                            <option value="Professor" <?php if($user['tipo_usuario_detalhado'] == 'Professor') echo 'selected'; ?>>Professor</option>
                            <option value="Comunidade Externa" <?php if($user['tipo_usuario_detalhado'] == 'Comunidade Externa') echo 'selected'; ?>>Comunidade Externa</option>
                        </select>
                    </div>

                    <?php if ($user['tipo_usuario_detalhado'] == 'Professor'): ?>
                        <div class="col-md-6 mb-3"><label class="form-label">Cursos que Leciona</label>
                            <select name="cursos_professor[]" class="form-select" multiple size="5">
                                <?php $cursos->data_seek(0); while($curso = $cursos->fetch_assoc()): ?>
                                    <option value="<?php echo $curso['id']; ?>" <?php if(in_array($curso['id'], $cursos_atuais_professor)) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($curso['nome']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="form-text">Segure Ctrl (ou Cmd) para selecionar mais de um.</div>
                        </div>
                    <?php else: ?>
                        <div class="col-md-6 mb-3"><label class="form-label">Curso do Aluno</label>
                            <select name="curso_id" class="form-select"><option value="">Nenhum</option>
                                <?php $cursos->data_seek(0); while($c = $cursos->fetch_assoc()): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php if($user['curso_id'] == $c['id']) echo 'selected'; ?>><?php echo $c['nome']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="col-md-6 mb-3"><label class="form-label">Atlética</label>
                        <select name="atletica_id" class="form-select"><option value="">Nenhuma</option>
                            <?php $atleticas->data_seek(0); while($a = $atleticas->fetch_assoc()): ?>
                                <option value="<?php echo $a['id']; ?>" <?php if($user['atletica_id'] == $a['id']) echo 'selected'; ?>><?php echo $a['nome']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <?php if ($user['tipo_usuario_detalhado'] == 'Professor'): ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_coordenador" value="1" id="is_coordenador" <?php if($user['is_coordenador']) echo 'checked'; ?>>
                        <label class="form-check-label" for="is_coordenador">
                            Marcar como Professor Coordenador
                        </label>
                    </div>
                <?php endif; ?>

                <button type="submit" name="update_user" class="btn btn-success">Salvar Alterações</button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal">Excluir Usuário</button>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Atenção!</strong> Esta ação é irreversível e irá apagar permanentemente o usuário <strong><?php echo htmlspecialchars($user['nome']); ?></strong>.</p>
                    <p>Para confirmar, por favor, digite a sua senha de Super Administrador.</p>
                    <form action="editar_usuario.php?id=<?php echo $user_id; ?>" method="post">
                        <input type="hidden" name="user_id_to_delete" value="<?php echo $user_id; ?>">
                        <div class="mb-3">
                            <label for="confirmation_password" class="form-label">Sua Senha</label>
                            <input type="password" name="confirmation_password" id="confirmation_password" class="form-control" required>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="delete_user" class="btn btn-danger">Confirmar e Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>