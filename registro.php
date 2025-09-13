<?php
require_once 'config.php';
$erros = [];

// Buscar cursos e atléticas para os dropdowns
$cursos = $conexao->query("SELECT id, nome FROM cursos ORDER BY nome");
$atleticas = $conexao->query("SELECT id, nome FROM atleticas ORDER BY nome");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coleta dos dados
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $ra = trim($_POST['ra']);
    $data_nascimento = trim($_POST['data_nascimento']);
    $tipo_usuario_detalhado = trim($_POST['tipo_usuario_detalhado']);
    $senha = trim($_POST['senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);

    // Campos dinâmicos
    $atletica_id = $_POST['atletica_id'] ?? null;
    $curso_id = $_POST['curso_id'] ?? null;
    $materia_professor = $_POST['materia_professor'] ?? null;

    // --- LÓGICA DE CORREÇÃO ---
    // Se o tipo de usuário não for o específico, força os campos relacionados a serem NULOS
    if ($tipo_usuario_detalhado !== 'Aluno') {
        $curso_id = null;
    }
    if ($tipo_usuario_detalhado !== 'Membro das Atléticas') {
        $atletica_id = null;
    }
    if ($tipo_usuario_detalhado !== 'Professor') {
        $materia_professor = null;
    }
    // --- FIM DA LÓGICA DE CORREÇÃO ---

    // --- VALIDAÇÃO NO BACKEND ---
    if (empty($nome)) $erros[] = "O nome é obrigatório.";
    if (empty($email)) $erros[] = "O e-mail é obrigatório.";
    if (empty($ra)) $erros[] = "O RA/Matrícula é obrigatório.";
    if (empty($senha) || strlen($senha) < 6) $erros[] = "A senha deve ter no mínimo 6 caracteres.";
    if ($senha !== $confirmar_senha) $erros[] = "As senhas não coincidem.";

    $email_domain = substr(strrchr($email, "@"), 1);
    if ($tipo_usuario_detalhado != 'Comunidade Externa' && $email_domain !== 'unifio.edu.br') {
        $erros[] = "Para este tipo de vínculo, é obrigatório o uso de um e-mail institucional (@unifio.edu.br).";
    }

    // Verificar se e-mail ou RA já existem
    if (empty($erros)) {
        $sql_check = "SELECT id FROM usuarios WHERE email = ? OR ra = ?";
        if ($stmt_check = $conexao->prepare($sql_check)) {
            $stmt_check->bind_param("ss", $email, $ra);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $erros[] = "Este e-mail ou RA já está cadastrado.";
            }
        }
    }

    // Inserir no banco
    if (empty($erros)) {
        $sql_insert = "INSERT INTO usuarios (nome, email, senha, ra, data_nascimento, tipo_usuario_detalhado, curso_id, atletica_id, materia_professor, role) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'aluno')";

        if ($stmt_insert = $conexao->prepare($sql_insert)) {
            $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
            $stmt_insert->bind_param("ssssssiis", $nome, $email, $hashed_senha, $ra, $data_nascimento, $tipo_usuario_detalhado, $curso_id, $atletica_id, $materia_professor);

            if ($stmt_insert->execute()) {
                header("location: login.php?registro=sucesso");
                exit();
            } else {
                $erros[] = "Erro ao registrar. Tente novamente.";
            }
        }
    }
}
?>

<?php include 'templates/header.php'; ?>
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Criar Conta</h2>
                    <?php if(!empty($erros)): ?>
                        <div class="alert alert-danger"><?php foreach($erros as $erro) echo "<p class='mb-0'>$erro</p>"; ?></div>
                    <?php endif; ?>

                    <form action="registro.php" method="post">
                        <!-- Campos Comuns -->
                        <div class="mb-3"><label for="nome" class="form-label">Nome Completo</label><input type="text" name="nome" id="nome" class="form-control" required></div>
                        <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" name="email" id="email" class="form-control" required></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="ra" class="form-label">RA / Matrícula</label><input type="text" name="ra" id="ra" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label for="data_nascimento" class="form-label">Data de Nascimento</label><input type="date" name="data_nascimento" id="data_nascimento" class="form-control" required></div>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_usuario_detalhado" class="form-label">Vínculo com a Instituição</label>
                            <select name="tipo_usuario_detalhado" id="tipo_usuario_detalhado" class="form-select" required>
                                <option value="" disabled selected>-- Selecione uma opção --</option>
                                <option value="Aluno">Aluno (em geral)</option>
                                <option value="Membro das Atléticas">Membro das Atléticas</option>
                                <option value="Professor">Professor</option>
                                <option value="Comunidade Externa">Comunidade Externa</option>
                            </select>
                        </div>

                        <!-- Campos Dinâmicos (controlados por JS) -->
                        <div id="campo_curso" class="mb-3" style="display:none;">
                            <label for="curso_id" class="form-label">Qual seu curso?</label>
                            <select name="curso_id" id="curso_id" class="form-select">
                                <?php $cursos->data_seek(0); while($curso = $cursos->fetch_assoc()) echo "<option value='{$curso['id']}'>{$curso['nome']}</option>"; ?>
                            </select>
                        </div>
                        <div id="campo_atletica" class="mb-3" style="display:none;">
                            <label for="atletica_id" class="form-label">Qual sua atlética?</label>
                            <select name="atletica_id" id="atletica_id" class="form-select">
                                <?php $atleticas->data_seek(0); while($atletica = $atleticas->fetch_assoc()) echo "<option value='{$atletica['id']}'>{$atletica['nome']}</option>"; ?>
                            </select>
                        </div>
                        <div id="campo_materia" class="mb-3" style="display:none;">
                            <label for="materia_professor" class="form-label">Qual matéria você leciona?</label>
                            <input type="text" name="materia_professor" id="materia_professor" class="form-control">
                        </div>

                        <!-- Senha -->
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="senha" class="form-label">Senha</label><input type="password" name="senha" id="senha" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label for="confirmar_senha" class="form-label">Confirmar Senha</label><input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" required></div>
                        </div>
                        <div class="d-grid"><button type="submit" class="btn btn-primary">Registrar</button></div>
                        <p class="mt-3 text-center">Já possui uma conta? <a href="login.php">Faça login</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript para o formulário dinâmico -->
    <script>
        document.getElementById('tipo_usuario_detalhado').addEventListener('change', function() {
            const tipo = this.value;
            const campoCurso = document.getElementById('campo_curso');
            const campoAtletica = document.getElementById('campo_atletica');
            const campoMateria = document.getElementById('campo_materia');
            const emailInput = document.getElementById('email');

            // Esconde todos os campos dinâmicos primeiro
            campoCurso.style.display = 'none';
            campoAtletica.style.display = 'none';
            campoMateria.style.display = 'none';

            // Remove o placeholder de aviso
            emailInput.placeholder = '';

            // Mostra o campo relevante baseado na seleção
            if (tipo === 'Aluno') {
                campoCurso.style.display = 'block';
                emailInput.placeholder = 'Use seu e-mail @unifio.edu.br';
            } else if (tipo === 'Membro das Atléticas') {
                campoAtletica.style.display = 'block';
                emailInput.placeholder = 'Use seu e-mail @unifio.edu.br';
            } else if (tipo === 'Professor') {
                campoMateria.style.display = 'block';
                emailInput.placeholder = 'Use seu e-mail @unifio.edu.br';
            }
        });
    </script>

<?php include 'templates/footer.php'; ?>