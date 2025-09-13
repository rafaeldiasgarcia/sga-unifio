<?php
require_once 'config.php';
$erros = [];
$cursos = $conexao->query("SELECT id, nome FROM cursos ORDER BY nome");
$atleticas = $conexao->query("SELECT id, nome FROM atleticas ORDER BY nome");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $tipo_usuario_detalhado = trim($_POST['tipo_usuario_detalhado']);
    $ra = trim($_POST['ra']);
    $data_nascimento = trim($_POST['data_nascimento']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);
    $curso_id = $_POST['curso_id'] ?? null; // Captura o curso do aluno
    $cursos_professor = $_POST['cursos_professor'] ?? []; // Espera um array

    // Validações
    if ($tipo_usuario_detalhado != 'Professor' && $tipo_usuario_detalhado != 'Comunidade Externa' && (empty($ra) || !preg_match('/^[0-9]{6}$/', $ra))) {
        $erros[] = "O RA/Matrícula deve conter exatamente 6 números.";
    }
    if (empty($nome)) $erros[] = "O nome é obrigatório.";
    if (empty($email)) $erros[] = "O e-mail é obrigatório.";
    if (empty($senha) || strlen($senha) < 6) $erros[] = "A senha deve ter no mínimo 6 caracteres.";
    if ($senha !== $confirmar_senha) $erros[] = "As senhas não coincidem.";
    $email_domain = substr(strrchr($email, "@"), 1);
    if ($tipo_usuario_detalhado != 'Comunidade Externa' && $email_domain !== 'unifio.edu.br') {
        $erros[] = "Para este tipo de vínculo, é obrigatório o uso de um e-mail institucional (@unifio.edu.br).";
    }

    // Lógica de inserção
    if (empty($erros)) {
        // Insere primeiro na tabela 'usuarios'
        // ATUALIZADO: Inclui curso_id na inserção
        $sql_insert = "INSERT INTO usuarios (nome, email, senha, ra, data_nascimento, tipo_usuario_detalhado, curso_id, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'usuario')";
        $stmt_insert = $conexao->prepare($sql_insert);
        $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
        $stmt_insert->bind_param("ssssssi", $nome, $email, $hashed_senha, $ra, $data_nascimento, $tipo_usuario_detalhado, $curso_id);

        if ($stmt_insert->execute()) {
            $novo_usuario_id = $stmt_insert->insert_id; // Pega o ID do professor recém-criado

            // Se for professor e selecionou cursos, insere na tabela de ligação
            if ($tipo_usuario_detalhado == 'Professor' && !empty($cursos_professor)) {
                $stmt_cursos = $conexao->prepare("INSERT INTO professores_cursos (professor_id, curso_id) VALUES (?, ?)");
                foreach ($cursos_professor as $curso_id_prof) { // Renomeado para evitar conflito
                    $stmt_cursos->bind_param("ii", $novo_usuario_id, $curso_id_prof);
                    $stmt_cursos->execute();
                }
            }
            header("location: login.php?registro=sucesso");
            exit();
        } else {
            $erros[] = "Erro ao registrar. O e-mail ou RA pode já estar em uso.";
        }
    }
}
?>
<?php include 'templates/header.php'; ?>
    <div class="row justify-content-center"><div class="col-md-7">
            <div class="card shadow-sm"><div class="card-body p-4">
                    <h2 class="text-center mb-4">Criar Conta</h2>
                    <?php if(!empty($erros)) echo "<div class='alert alert-danger'><ul>" . implode("", array_map(fn($e) => "<li>$e</li>", $erros)) . "</ul></div>"; ?>
                    <form action="registro.php" method="post">
                        <div class="mb-3"><label for="nome" class="form-label">Nome Completo</label><input type="text" name="nome" class="form-control" required></div>
                        <div class="mb-3"><label for="tipo_usuario_detalhado" class="form-label">Vínculo com a Instituição</label>
                            <select name="tipo_usuario_detalhado" id="tipo_usuario_detalhado" class="form-select" required>
                                <option value="" disabled selected>-- Selecione uma opção --</option>
                                <option value="Aluno">Aluno (em geral)</option>
                                <option value="Membro das Atléticas">Membro das Atléticas</option>
                                <option value="Professor">Professor</option>
                                <option value="Comunidade Externa">Comunidade Externa</option>
                            </select>
                        </div>
                        <div id="campo_ra" class="mb-3"><label for="ra" class="form-label">RA / Matrícula</label><input type="text" name="ra" id="ra" class="form-control" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" title="O RA deve conter exatamente 6 números."></div>
                        <div class="mb-3"><label for="data_nascimento" class="form-label">Data de Nascimento</label><input type="date" name="data_nascimento" class="form-control" required></div>
                        <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" name="email" id="email" class="form-control" required></div>

                        <div id="campo_curso" class="mb-3" style="display:none;">
                            <label for="curso_id" class="form-label">Qual seu curso?</label>
                            <select name="curso_id" id="curso_id" class="form-select">
                                <option value="">-- Selecione seu curso --</option>
                                <?php $cursos->data_seek(0); while($curso = $cursos->fetch_assoc()) echo "<option value='{$curso['id']}'>{$curso['nome']}</option>"; ?>
                            </select>
                        </div>

                        <div id="campo_cursos_professor" class="mb-3" style="display:none;">
                            <label class="form-label">Quais cursos você dá aula?</label>
                            <select name="cursos_professor[]" class="form-select" multiple>
                                <?php $cursos->data_seek(0); while($curso = $cursos->fetch_assoc()) echo "<option value='{$curso['id']}'>{$curso['nome']}</option>"; ?>
                            </select>
                            <div class="form-text">Segure a tecla Ctrl (ou Cmd em Mac) para selecionar mais de um.</div>
                        </div>

                        <div class="row"><div class="col-md-6 mb-3"><label for="senha" class="form-label">Senha</label><input type="password" name="senha" class="form-control" required></div><div class="col-md-6 mb-3"><label for="confirmar_senha" class="form-label">Confirmar Senha</label><input type="password" name="confirmar_senha" class="form-control" required></div></div>
                        <div class="d-grid"><button type="submit" class="btn btn-primary">Registrar</button></div>
                        <p class="mt-3 text-center">Já possui uma conta? <a href="login.php">Faça login</a></p>
                    </form>
                </div></div>
        </div></div>
    <script>
        document.getElementById('tipo_usuario_detalhado').addEventListener('change', function() {
            const tipo = this.value;
            const campoRa = document.getElementById('campo_ra');
            const inputRa = document.getElementById('ra');
            const campoCursosProf = document.getElementById('campo_cursos_professor');
            const campoCurso = document.getElementById('campo_curso');
            const emailInput = document.getElementById('email');

            // Reset geral
            campoRa.style.display = 'block';
            inputRa.required = true;
            campoCursosProf.style.display = 'none';
            campoCurso.style.display = 'none';

            if (tipo === 'Professor' || tipo === 'Comunidade Externa') {
                campoRa.style.display = 'none';
                inputRa.required = false;
            }
            if (tipo === 'Professor') {
                campoCursosProf.style.display = 'block';
            }
            if (tipo === 'Aluno') { // MOSTRA O CAMPO PARA O ALUNO
                campoCurso.style.display = 'block';
                emailInput.placeholder = 'Use seu e-mail @unifio.edu.br';
            }
        });

        // Enforce numeric-only and max length 6 while typing
        (function() {
            const inputRa = document.getElementById('ra');
            if (inputRa) {
                inputRa.addEventListener('input', function() {
                    // remove any non-digits and cap at 6 chars
                    this.value = this.value.replace(/\D/g, '').slice(0, 6);
                });
            }
        })();
    </script>
<?php include 'templates/footer.php'; ?>