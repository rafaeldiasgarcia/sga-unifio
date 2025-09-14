<?php
session_start();
require_once 'config.php';
$erros = [];

// Verificar se há mensagem de erro na sessão
if (isset($_SESSION['erro'])) {
    $erros[] = $_SESSION['erro'];
    unset($_SESSION['erro']); // Limpa a mensagem após exibir
}

$cursos = $conexao->query("SELECT id, nome FROM cursos ORDER BY nome");
$atleticas = $conexao->query("SELECT id, nome FROM atleticas ORDER BY nome");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $tipo_usuario_detalhado = trim($_POST['tipo_usuario_detalhado']);
    $data_nascimento = trim($_POST['data_nascimento']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);
    $curso_id = $_POST['curso_id'] ?? null; // Captura o curso do aluno
    
    // Inicializa RA como null
    $ra = null;
    
    // Só processa o RA se for Aluno ou Membro das Atléticas
    if ($tipo_usuario_detalhado == 'Aluno' || $tipo_usuario_detalhado == 'Membro das Atléticas') {
        $ra = isset($_POST['ra']) ? trim($_POST['ra']) : '';
    }
    
    $cursos_professor = $_POST['cursos_professor'] ?? []; // Espera um array    // Validações
    if (empty($nome)) $erros[] = "O nome é obrigatório.";
    if (empty($email)) $erros[] = "O e-mail é obrigatório.";
    
    // Validação específica do RA apenas para Aluno e Membro das Atléticas
    if ($tipo_usuario_detalhado == 'Aluno' || $tipo_usuario_detalhado == 'Membro das Atléticas') {
        if (!isset($_POST['ra']) || empty($ra) || !preg_match('/^[0-9]{6}$/', $ra)) {
            $erros[] = "O RA/Matrícula deve conter exatamente 6 números.";
        }
    }
    if (empty($senha) || strlen($senha) < 6) $erros[] = "A senha deve ter no mínimo 6 caracteres.";
    if ($senha !== $confirmar_senha) $erros[] = "As senhas não coincidem.";
    $email_domain = substr(strrchr($email, "@"), 1);
    if ($tipo_usuario_detalhado != 'Comunidade Externa' && $email_domain !== 'unifio.edu.br') {
        $erros[] = "Para este tipo de vínculo, é obrigatório o uso de um e-mail institucional (@unifio.edu.br).";
    }

    // Lógica de inserção
    if (empty($erros)) {
        // Se for aluno ou membro das atléticas, verifica se o curso tem atlética
        $atletica_id = null;
        $atletica_join_status = 'none';
        
        if (($tipo_usuario_detalhado == 'Aluno' || $tipo_usuario_detalhado == 'Membro das Atléticas') && $curso_id) {
            $stmt_check_atletica = $conexao->prepare("SELECT atletica_id FROM cursos WHERE id = ?");
            $stmt_check_atletica->bind_param("i", $curso_id);
            $stmt_check_atletica->execute();
            $result_atletica = $stmt_check_atletica->get_result();
            if ($row_atletica = $result_atletica->fetch_assoc()) {
                $atletica_id = $row_atletica['atletica_id'];
                
                // Se o curso não tem atlética e tentou se registrar como membro
                if (!$atletica_id && $tipo_usuario_detalhado == 'Membro das Atléticas') {
                    $tipo_usuario_detalhado = 'Aluno';
                    $atletica_join_status = 'none';
                }
                // Se o curso tem atlética
                elseif ($atletica_id) {
                    if ($tipo_usuario_detalhado == 'Membro das Atléticas') {
                        $atletica_join_status = 'aprovado';
                    } elseif ($tipo_usuario_detalhado == 'Aluno') {
                        $atletica_join_status = 'pendente';
                    }
                }
            }
        }

        // Insere primeiro na tabela 'usuarios'
        $sql_insert = "INSERT INTO usuarios (nome, email, senha, ra, data_nascimento, tipo_usuario_detalhado, curso_id, role, atletica_join_status, atletica_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conexao->prepare($sql_insert);
        $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
        $role = 'usuario'; // Definindo a role explicitamente
        
        // Ajuste para permitir que o curso_id seja null se necessário
        if ($curso_id === '') {
            $curso_id = null;
        }
        
        $stmt_insert->bind_param("ssssssisss", 
            $nome, 
            $email, 
            $hashed_senha, 
            $ra, 
            $data_nascimento, 
            $tipo_usuario_detalhado, 
            $curso_id, 
            $role,
            $atletica_join_status, 
            $atletica_id);

        try {
            if ($stmt_insert->execute()) {
                $novo_usuario_id = $stmt_insert->insert_id;

                if ($tipo_usuario_detalhado == 'Professor' && !empty($cursos_professor)) {
                    $stmt_cursos = $conexao->prepare("INSERT INTO professores_cursos (professor_id, curso_id) VALUES (?, ?)");
                    foreach ($cursos_professor as $curso_id_prof) {
                        $stmt_cursos->bind_param("ii", $novo_usuario_id, $curso_id_prof);
                        $stmt_cursos->execute();
                    }
                }
                header("Location: login.php?registro=sucesso");
                exit();
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Duplicate entry error
                $error_message = $e->getMessage();
                // Verifica se o erro é relacionado ao email ou ao RA
                if (strpos($error_message, 'email') !== false) {
                    $_SESSION['erro'] = "Este e-mail já está cadastrado no sistema. Por favor, utilize outro e-mail ou faça login se já possuir uma conta.";
                } elseif (strpos($error_message, 'ra') !== false) {
                    $_SESSION['erro'] = "Este RA já está cadastrado no sistema. Por favor, verifique o número informado.";
                } else {
                    $_SESSION['erro'] = "Um dos dados informados já está em uso no sistema. Por favor, verifique as informações.";
                }
            } else {
                $_SESSION['erro'] = "Erro ao registrar. Por favor, tente novamente.";
            }
            header("Location: registro.php");
            exit();
        }
    }
}
?>
<?php include 'templates/header.php'; ?>
    <div class="row justify-content-center"><div class="col-md-7">
            <div class="card shadow-sm"><div class="card-body p-4">
                    <h2 class="text-center mb-4">Criar Conta</h2>
                    <form action="registro.php" method="post">
                    <?php if(!empty($erros)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($erros as $erro): ?>
                                <li><?php echo $erro; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                        <div class="mb-3"><label for="nome" class="form-label">Nome Completo</label><input type="text" name="nome" class="form-control" required value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>"></div>
                        <div class="mb-3"><label for="tipo_usuario_detalhado" class="form-label">Vínculo com a Instituição</label>
                            <select name="tipo_usuario_detalhado" id="tipo_usuario_detalhado" class="form-select" required>
                                <option value="" disabled <?php echo empty($_POST['tipo_usuario_detalhado']) ? 'selected' : ''; ?>>-- Selecione uma opção --</option>
                                <option value="Aluno" <?php echo ($_POST['tipo_usuario_detalhado'] ?? '') === 'Aluno' ? 'selected' : ''; ?>>Aluno (em geral)</option>
                                <option value="Membro das Atléticas" <?php echo ($_POST['tipo_usuario_detalhado'] ?? '') === 'Membro das Atléticas' ? 'selected' : ''; ?>>Membro das Atléticas</option>
                                <option value="Professor" <?php echo ($_POST['tipo_usuario_detalhado'] ?? '') === 'Professor' ? 'selected' : ''; ?>>Professor</option>
                                <option value="Comunidade Externa" <?php echo ($_POST['tipo_usuario_detalhado'] ?? '') === 'Comunidade Externa' ? 'selected' : ''; ?>>Comunidade Externa</option>
                            </select>
                        </div>
                        <div id="campo_ra" class="mb-3"><label for="ra" class="form-label">RA / Matrícula</label><input type="text" name="ra" id="ra" class="form-control" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" title="O RA deve conter exatamente 6 números." value="<?php echo htmlspecialchars($_POST['ra'] ?? ''); ?>"></div>
                        <div class="mb-3"><label for="data_nascimento" class="form-label">Data de Nascimento</label><input type="date" name="data_nascimento" class="form-control" required value="<?php echo htmlspecialchars($_POST['data_nascimento'] ?? ''); ?>"></div>
                        <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"></div>

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
                        <script>
                            // Restaura a visibilidade dos campos necessários após o POST
                            window.onload = function() {
                                const tipo = document.getElementById('tipo_usuario_detalhado').value;
                                if(tipo) {
                                    const evento = new Event('change');
                                    document.getElementById('tipo_usuario_detalhado').dispatchEvent(evento);
                                }
                            }
                        </script>
                        <div class="d-grid"><button type="submit" class="btn btn-primary">Registrar</button></div>
                        <p class="mt-3 text-center">Já possui uma conta? <a href="login.php">Faça login</a></p>
                    </form>
                </div></div>
        </div></div>
    <script>
        const tipoUsuarioSelect = document.getElementById('tipo_usuario_detalhado');
        
        function handleTipoUsuarioChange() {
            const tipo = tipoUsuarioSelect.value;
            const campoRa = document.getElementById('campo_ra');
            const inputRa = document.getElementById('ra');
            const campoCursosProf = document.getElementById('campo_cursos_professor');
            const campoCurso = document.getElementById('campo_curso');
            const emailInput = document.getElementById('email');
            
            // Reset all fields first
            campoCursosProf.style.display = 'none';
            campoCurso.style.display = 'none';
            inputRa.value = '';  // Clear RA when type changes
            
            // Handle RA field visibility and requirement
            if (tipo === 'Professor' || tipo === 'Comunidade Externa') {
                campoRa.remove();  // Remove completely from DOM
            } else {
                // If the field was removed, we need to recreate it
                if (!document.getElementById('campo_ra')) {
                    const newCampoRa = document.createElement('div');
                    newCampoRa.id = 'campo_ra';
                    newCampoRa.className = 'mb-3';
                    newCampoRa.innerHTML = `
                        <label for="ra" class="form-label">RA / Matrícula</label>
                        <input type="text" name="ra" id="ra" class="form-control" 
                               inputmode="numeric" maxlength="6" pattern="[0-9]{6}" 
                               title="O RA deve conter exatamente 6 números." required>
                    `;
                    
                    // Insert after tipo_usuario_detalhado
                    const tipoField = document.getElementById('tipo_usuario_detalhado').closest('.mb-3');
                    tipoField.insertAdjacentElement('afterend', newCampoRa);
                    
                    // Reattach the input mask
                    const newRaInput = document.getElementById('ra');
                    newRaInput.addEventListener('input', function() {
                        this.value = this.value.replace(/\D/g, '').slice(0, 6);
                    });
                }
            }
            
            // Handle course fields visibility
            if (tipo === 'Professor') {
                campoCursosProf.style.display = 'block';
            } else if (tipo === 'Aluno' || tipo === 'Membro das Atléticas') {
                campoCurso.style.display = 'block';
            }
            
            // Handle email placeholder
            emailInput.placeholder = (tipo === 'Professor' || tipo === 'Aluno' || tipo === 'Membro das Atléticas') 
                ? 'Use seu e-mail @unifio.edu.br' 
                : '';
        }

        // Add change event listener
        tipoUsuarioSelect.addEventListener('change', handleTipoUsuarioChange);

        // Initialize fields on page load
        window.addEventListener('load', function() {
            if (tipoUsuarioSelect.value) {
                handleTipoUsuarioChange();
            }
        });

        // Enforce numeric-only and max length 6 while typing for RA field
        const inputRa = document.getElementById('ra');
        if (inputRa) {
            inputRa.addEventListener('input', function() {
                // remove any non-digits and cap at 6 chars
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
            });
        }
    </script>
<?php include 'templates/footer.php'; ?>