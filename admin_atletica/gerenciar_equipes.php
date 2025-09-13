<?php
require_once '../config.php';
is_admin();
$atletica_id = $_SESSION['atletica_id'];
$mensagem = '';

$open_modalidade_id = $_GET['open_modalidade'] ?? null;

// Ação: Criar nova equipe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['criar_equipe'])) {
    $nome_equipe = trim($_POST['nome_equipe']);
    $modalidade_id = $_POST['modalidade_id'];
    if (!empty($nome_equipe) && !empty($modalidade_id)) {
        $sql_insert = "INSERT INTO equipes (nome, modalidade_id, atletica_id) VALUES (?, ?, ?)";
        $stmt_insert = $conexao->prepare($sql_insert);
        $stmt_insert->bind_param("sii", $nome_equipe, $modalidade_id, $atletica_id);
        $stmt_insert->execute();
        header("Location: gerenciar_equipes.php?open_modalidade=" . $modalidade_id);
        exit;
    }
}

// Ação: Alocar aluno em equipe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['alocar_aluno'])) {
    $equipe_id = $_POST['equipe_id'];
    $aluno_id = $_POST['aluno_id'];
    $modalidade_id = $_POST['modalidade_id_hidden'];

    $sql_alocar = "INSERT INTO equipe_membros (equipe_id, aluno_id) VALUES (?, ?)";
    $stmt_alocar = $conexao->prepare($sql_alocar);
    $stmt_alocar->bind_param("ii", $equipe_id, $aluno_id);
    $stmt_alocar->execute();
    header("Location: gerenciar_equipes.php?open_modalidade=" . $modalidade_id);
    exit;
}

// --- NOVA LÓGICA: Excluir uma equipe inteira ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_team'])) {
    $equipe_id_to_delete = $_POST['equipe_id_to_delete'];
    $modalidade_id = $_POST['modalidade_id_hidden'];

    // Deleta primeiro os membros para não violar a chave estrangeira
    $sql_delete_members = "DELETE FROM equipe_membros WHERE equipe_id = ?";
    $stmt_delete_members = $conexao->prepare($sql_delete_members);
    $stmt_delete_members->bind_param("i", $equipe_id_to_delete);
    $stmt_delete_members->execute();

    // Agora deleta a equipe
    $sql_delete_team = "DELETE FROM equipes WHERE id = ? AND atletica_id = ?";
    $stmt_delete_team = $conexao->prepare($sql_delete_team);
    $stmt_delete_team->bind_param("ii", $equipe_id_to_delete, $atletica_id);
    $stmt_delete_team->execute();

    header("Location: gerenciar_equipes.php?open_modalidade=" . $modalidade_id);
    exit;
}

// --- NOVA LÓGICA: Remover um membro de uma equipe ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_member'])) {
    $membro_id_to_remove = $_POST['membro_id_to_remove'];
    $modalidade_id = $_POST['modalidade_id_hidden'];

    $sql_remove = "DELETE FROM equipe_membros WHERE id = ?";
    $stmt_remove = $conexao->prepare($sql_remove);
    $stmt_remove->bind_param("i", $membro_id_to_remove);
    $stmt_remove->execute();

    header("Location: gerenciar_equipes.php?open_modalidade=" . $modalidade_id);
    exit;
}

// Buscar modalidades do evento ativo
$sql_modalidades = "SELECT m.id, m.nome FROM modalidades m JOIN eventos e ON m.evento_id = e.id WHERE e.ativo = 1";
$modalidades = $conexao->query($sql_modalidades);
?>

<?php include '../templates/header.php'; ?>
    <h1>Gerenciar Equipes e Atletas</h1>
    <p>Crie equipes para as modalidades e aloque os membros da sua atlética.</p>
<?php echo $mensagem; ?>

    <div class="accordion" id="accordionModalidades">
        <?php if ($modalidades->num_rows > 0): ?>
            <?php while($modalidade = $modalidades->fetch_assoc()):
                $modalidade_id = $modalidade['id'];
                $button_class = ($modalidade_id == $open_modalidade_id) ? '' : 'collapsed';
                $body_class = ($modalidade_id == $open_modalidade_id) ? 'show' : '';
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button <?php echo $button_class; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $modalidade_id; ?>"><strong><?php echo htmlspecialchars($modalidade['nome']); ?></strong></button></h2>
                    <div id="collapse-<?php echo $modalidade_id; ?>" class="accordion-collapse collapse <?php echo $body_class; ?>" data-bs-parent="#accordionModalidades">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <h5>Equipes Criadas</h5>
                                    <?php
                                    $sql_equipes = "SELECT id, nome FROM equipes WHERE modalidade_id = ? AND atletica_id = ?";
                                    $stmt_equipes = $conexao->prepare($sql_equipes);
                                    $stmt_equipes->bind_param("ii", $modalidade_id, $atletica_id);
                                    $stmt_equipes->execute();
                                    $equipes = $stmt_equipes->get_result();

                                    if ($equipes->num_rows > 0) {
                                        while($equipe = $equipes->fetch_assoc()) {
                                            echo "<div class='d-flex justify-content-between align-items-center'>";
                                            echo "<p class='mb-1'><strong>" . htmlspecialchars($equipe['nome']) . "</strong></p>";
                                            // NOVO: Formulário para deletar a equipe
                                            echo "<form method='post' onsubmit=\"return confirm('Tem certeza que deseja excluir esta equipe e remover todos os seus membros?');\">
                                                <input type='hidden' name='equipe_id_to_delete' value='{$equipe['id']}'>
                                                <input type='hidden' name='modalidade_id_hidden' value='{$modalidade_id}'>
                                                <button type='submit' name='delete_team' class='btn btn-sm btn-outline-danger'><i class='bi bi-trash'></i></button>
                                              </form>";
                                            echo "</div>";

                                            // ATUALIZADO: Busca o ID do membro para permitir a remoção
                                            $sql_membros = "SELECT u.nome, em.id as membro_id FROM equipe_membros em JOIN usuarios u ON em.aluno_id = u.id WHERE em.equipe_id = ?";
                                            $stmt_membros = $conexao->prepare($sql_membros);
                                            $stmt_membros->bind_param("i", $equipe['id']);
                                            $stmt_membros->execute();
                                            $membros = $stmt_membros->get_result();
                                            if ($membros->num_rows > 0) {
                                                echo "<ul class='list-group list-group-flush mb-3'>";
                                                while($membro = $membros->fetch_assoc()) {
                                                    echo "<li class='list-group-item d-flex justify-content-between align-items-center py-1'>" . htmlspecialchars($membro['nome']);
                                                    // NOVO: Formulário para remover o membro
                                                    echo "<form method='post' onsubmit=\"return confirm('Tem certeza que deseja remover este membro da equipe?');\">
                                                        <input type='hidden' name='membro_id_to_remove' value='{$membro['membro_id']}'>
                                                        <input type='hidden' name='modalidade_id_hidden' value='{$modalidade_id}'>
                                                        <button type='submit' name='remove_member' class='btn btn-sm btn-link text-danger'><i class='bi bi-x-circle'></i></button>
                                                      </form>";
                                                    echo "</li>";
                                                }
                                                echo "</ul>";
                                            } else {
                                                echo "<p class='text-muted'>Nenhum aluno alocado.</p>";
                                            }
                                        }
                                    } else {
                                        echo "<p class='text-muted'>Nenhuma equipe criada.</p>";
                                    }
                                    ?>
                                    <hr>
                                    <h5>Criar Nova Equipe</h5>
                                    <form method="post" class="row g-2"><input type="hidden" name="modalidade_id" value="<?php echo $modalidade_id; ?>"><div class="col-auto"><input type="text" name="nome_equipe" class="form-control" placeholder="Ex: Futsal Titular" required></div><div class="col-auto"><button type="submit" name="criar_equipe" class="btn btn-secondary">Criar</button></div></form>
                                </div>
                                <div class="col-md-5 border-start">
                                    <h5>Alocar Membros da Atlética</h5>
                                    <?php
                                    $sql_alunos = "SELECT u.id, u.nome FROM usuarios u 
                                               JOIN cursos c ON u.curso_id = c.id
                                               WHERE c.atletica_id = ? AND u.tipo_usuario_detalhado = 'Membro das Atléticas' AND u.id NOT IN 
                                               (SELECT em.aluno_id FROM equipe_membros em JOIN equipes eq ON em.equipe_id = eq.id WHERE eq.modalidade_id = ?)
                                               ORDER BY u.nome";
                                    $stmt_alunos = $conexao->prepare($sql_alunos);
                                    $stmt_alunos->bind_param("ii", $atletica_id, $modalidade_id);
                                    $stmt_alunos->execute();
                                    $alunos_para_alocar = $stmt_alunos->get_result();

                                    $equipes->data_seek(0);
                                    if ($alunos_para_alocar->num_rows > 0 && $equipes->num_rows > 0) {
                                        while($aluno = $alunos_para_alocar->fetch_assoc()) {
                                            echo "<div class='d-flex justify-content-between align-items-center mb-2 p-2 border rounded'><span>" . htmlspecialchars($aluno['nome']) . "</span><form method='post' class='d-inline'><input type='hidden' name='aluno_id' value='" . $aluno['id'] . "'><input type='hidden' name='modalidade_id_hidden' value='" . $modalidade_id . "'><select name='equipe_id' class='form-select form-select-sm d-inline w-auto me-2'>";
                                            $equipes->data_seek(0);
                                            while($equipe = $equipes->fetch_assoc()) {
                                                echo "<option value='" . $equipe['id'] . "'>" . htmlspecialchars($equipe['nome']) . "</option>";
                                            }
                                            echo "</select><button type='submit' name='alocar_aluno' class='btn btn-success btn-sm'>Alocar</button></form></div>";
                                        }
                                    } else {
                                        echo "<p class='text-muted'>Todos os membros da atlética já foram alocados nesta modalidade, ou não há equipes criadas.</p>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">Nenhuma modalidade disponível no evento ativo.</div>
        <?php endif; ?>
    </div>

<?php include '../templates/footer.php'; ?>