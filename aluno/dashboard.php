<?php
require_once '../config.php';
is_aluno(); // Garante que o usuário tem o 'role' de aluno, que é o padrão para todos os não-admins.

$usuario_id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario_detalhado'];
$atletica_nome = null;

// --- NOVA LÓGICA PARA BUSCAR ATLÉTICA ---
// Busca informações completas do usuário, incluindo curso e atlética direta/indireta
$sql_aluno = "SELECT u.nome, u.atletica_id as atletica_direta_id, c.atletica_id as atletica_curso_id, a_direta.nome as atletica_direta_nome, a_curso.nome as atletica_curso_nome
              FROM usuarios u
              LEFT JOIN cursos c ON u.curso_id = c.id
              LEFT JOIN atleticas a_direta ON u.atletica_id = a_direta.id
              LEFT JOIN atleticas a_curso ON c.atletica_id = a_curso.id
              WHERE u.id = ?";
$stmt_aluno = $conexao->prepare($sql_aluno);
$stmt_aluno->bind_param("i", $usuario_id);
$stmt_aluno->execute();
$aluno_info = $stmt_aluno->get_result()->fetch_assoc();

// Determina o nome da atlética (se houver)
// Prioriza a atlética direta (para "Membro das Atléticas")
if (!empty($aluno_info['atletica_direta_nome'])) {
    $atletica_nome = $aluno_info['atletica_direta_nome'];
}
// Senão, usa a atlética do curso (para "Aluno")
elseif (!empty($aluno_info['atletica_curso_nome'])) {
    $atletica_nome = $aluno_info['atletica_curso_nome'];
}
?>

<?php include '../templates/header.php'; ?>

    <!-- TÍTULO GENÉRICO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Meu Painel</h1>
        <?php if ($atletica_nome): // Só mostra a atlética se o usuário tiver uma ?>
            <div>
                <strong>Atlética:</strong> <?php echo htmlspecialchars($atletica_nome); ?>
            </div>
        <?php endif; ?>
    </div>

    <p>Bem-vindo, <?php echo htmlspecialchars($aluno_info['nome']); ?>! Aqui você pode gerenciar sua participação e atividades.</p>

    <!-- CONTEÚDO CONDICIONAL: Só mostra para Alunos e Membros de Atléticas -->
<?php if ($tipo_usuario == 'Aluno' || $tipo_usuario == 'Membro das Atléticas'): ?>
    <div class="row">
        <!-- Card de Inscrições -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-card-list"></i> Minhas Inscrições</h5>
                    <p class="card-text">Acompanhe o status das suas candidaturas nas modalidades.</p>
                    <a href="minhas_inscricoes.php" class="btn btn-outline-primary mt-3">Ver Inscrições</a>
                </div>
            </div>
        </div>

        <!-- Card para Novas Inscrições -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-pencil-square"></i> Inscrições Abertas</h5>
                    <p class="card-text">Veja as modalidades disponíveis para sua atlética e inscreva-se.</p>
                    <a href="inscrever.php" class="btn btn-primary">Inscrever-se em Modalidades</a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Mensagem para outros perfis, como Professor e Comunidade Externa -->
    <div class="alert alert-info">
        Use o menu de navegação acima para acessar as funcionalidades disponíveis para o seu perfil.
    </div>
<?php endif; ?>

<?php include '../templates/footer.php'; ?>