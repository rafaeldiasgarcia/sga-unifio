<?php
require_once '../../src/config/config.php';
is_admin();
$atletica_id = $_SESSION['atletica_id'] ?? null;
$mensagem = '';

if (!$atletica_id) {
    echo '<div class="alert alert-danger">Erro: sua atlética não está definida. Faça login novamente ou contate o administrador.</div>';
    include '../../templates/footer.php';
    exit;
}

$open_evento_id = $_GET['open_evento'] ?? null;

// Verificar se há mensagem de sucesso da sessão
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem = "<div class='alert alert-success'>" . $_SESSION['mensagem_sucesso'] . "</div>";
    unset($_SESSION['mensagem_sucesso']);
}

// Ação: Inscrever aluno em evento esportivo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['inscrever_aluno'])) {
    $evento_id = $_POST['evento_id'];
    $aluno_id = $_POST['aluno_id'];
    
    // Verificar se o aluno pertence à atlética do admin
    $sql_verificar = "SELECT u.id FROM usuarios u 
                     JOIN cursos c ON u.curso_id = c.id 
                     WHERE u.id = ? AND c.atletica_id = ?";
    $stmt_verificar = $conexao->prepare($sql_verificar);
    $stmt_verificar->bind_param("ii", $aluno_id, $atletica_id);
    $stmt_verificar->execute();
    
    if ($stmt_verificar->get_result()->num_rows > 0) {
        // Verificar se já não está inscrito
        $sql_check = "SELECT id FROM inscricoes_eventos WHERE aluno_id = ? AND evento_id = ?";
        $stmt_check = $conexao->prepare($sql_check);
        $stmt_check->bind_param("ii", $aluno_id, $evento_id);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows == 0) {
            // Inserir inscrição
            $sql_insert = "INSERT INTO inscricoes_eventos (aluno_id, evento_id, atletica_id, status) VALUES (?, ?, ?, 'aprovado')";
            $stmt_insert = $conexao->prepare($sql_insert);
            $stmt_insert->bind_param("iii", $aluno_id, $evento_id, $atletica_id);
            
            if ($stmt_insert->execute()) {
                $_SESSION['mensagem_sucesso'] = "Aluno inscrito com sucesso no evento!";
            } else {
                $mensagem = "<div class='alert alert-danger'>Erro ao inscrever aluno no evento.</div>";
            }
        } else {
            $mensagem = "<div class='alert alert-warning'>Este aluno já está inscrito neste evento.</div>";
        }
    } else {
        $mensagem = "<div class='alert alert-danger'>Acesso negado: Este aluno não pertence à sua atlética.</div>";
    }
    
    header("Location: gerenciar_equipes.php?open_evento=" . $evento_id);
    exit;
}

// Ação: Remover inscrição de aluno
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remover_inscricao'])) {
    $inscricao_id = $_POST['inscricao_id'];
    $evento_id = $_POST['evento_id'];
    
    // Verificar se a inscrição pertence à atlética do admin
    $sql_verificar = "SELECT ie.id FROM inscricoes_eventos ie 
                     JOIN usuarios u ON ie.aluno_id = u.id 
                     JOIN cursos c ON u.curso_id = c.id 
                     WHERE ie.id = ? AND c.atletica_id = ?";
    $stmt_verificar = $conexao->prepare($sql_verificar);
    $stmt_verificar->bind_param("ii", $inscricao_id, $atletica_id);
    $stmt_verificar->execute();
    
    if ($stmt_verificar->get_result()->num_rows > 0) {
        $sql_delete = "DELETE FROM inscricoes_eventos WHERE id = ?";
        $stmt_delete = $conexao->prepare($sql_delete);
        $stmt_delete->bind_param("i", $inscricao_id);
        
        if ($stmt_delete->execute()) {
            $_SESSION['mensagem_sucesso'] = "Inscrição removida com sucesso!";
        } else {
            $mensagem = "<div class='alert alert-danger'>Erro ao remover inscrição.</div>";
        }
    } else {
        $mensagem = "<div class='alert alert-danger'>Acesso negado.</div>";
    }
    
    header("Location: gerenciar_equipes.php?open_evento=" . $evento_id);
    exit;
}

// Buscar eventos esportivos aprovados
$sql_eventos = "SELECT id, titulo, data_agendamento, esporte_tipo, descricao 
                FROM agendamentos 
                WHERE tipo_agendamento = 'esportivo' 
                AND status = 'aprovado' 
                AND data_agendamento >= CURDATE()
                ORDER BY data_agendamento ASC";
$eventos = $conexao->query($sql_eventos);
?>
?>

<?php include '../../templates/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users-cog"></i> Gerenciar Participações em Eventos Esportivos</h2>
    <div class="text-muted">
        <i class="fas fa-info-circle"></i> Inscreva membros da sua atlética nos eventos esportivos aprovados
    </div>
</div>

<?php echo $mensagem; ?>

<div class="accordion" id="accordionEventos">
    <?php if ($eventos->num_rows > 0): ?>
        <?php while($evento = $eventos->fetch_assoc()):
            $evento_id = $evento['id'];
            $button_class = ($evento_id == $open_evento_id) ? '' : 'collapsed';
            $body_class = ($evento_id == $open_evento_id) ? 'show' : '';
            
            // Formatar data
            $data_formatada = date('d/m/Y', strtotime($evento['data_agendamento']));
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button <?php echo $button_class; ?>" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapse-<?php echo $evento_id; ?>"
                            aria-expanded="<?php echo $open_evento_id == $evento_id ? 'true' : 'false'; ?>">
                        <div class="d-flex w-100 justify-content-between align-items-center me-3">
                            <span>
                                <strong><?php echo htmlspecialchars($evento['titulo']); ?></strong>
                                <span class="badge bg-primary ms-2"><?php echo htmlspecialchars($evento['esporte_tipo']); ?></span>
                            </span>
                            <span class="text-muted">
                                <i class="fas fa-calendar-alt"></i> <?php echo $data_formatada; ?>
                            </span>
                        </div>
                    </button>
                </h2>
                <div id="collapse-<?php echo $evento_id; ?>" 
                     class="accordion-collapse collapse <?php echo $body_class; ?>" 
                     data-bs-parent="#accordionEventos">
                    <div class="accordion-body">
                        <?php if (!empty($evento['descricao'])): ?>
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Descrição:</strong> <?php echo htmlspecialchars($evento['descricao']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-7">
                                <h5><i class="fas fa-users"></i> Alunos Inscritos da sua Atlética</h5>
                                <?php
                                // Buscar alunos inscritos da atlética do admin
                                $sql_inscritos = "SELECT ie.id as inscricao_id, u.nome, u.ra 
                                                 FROM inscricoes_eventos ie 
                                                 JOIN usuarios u ON ie.aluno_id = u.id 
                                                 JOIN cursos c ON u.curso_id = c.id 
                                                 WHERE ie.evento_id = ? AND c.atletica_id = ?
                                                 ORDER BY u.nome";
                                $stmt_inscritos = $conexao->prepare($sql_inscritos);
                                $stmt_inscritos->bind_param("ii", $evento_id, $atletica_id);
                                $stmt_inscritos->execute();
                                $inscritos = $stmt_inscritos->get_result();

                                if ($inscritos->num_rows > 0) {
                                    echo "<div class='table-responsive'>";
                                    echo "<table class='table table-sm table-striped'>";
                                    echo "<thead class='table-dark'>";
                                    echo "<tr><th>Nome</th><th>RA</th><th width='80'>Ação</th></tr>";
                                    echo "</thead><tbody>";
                                    
                                    while($inscrito = $inscritos->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($inscrito['nome']) . "</td>";
                                        echo "<td>" . htmlspecialchars($inscrito['ra']) . "</td>";
                                        echo "<td>";
                                        echo "<form method='post' class='d-inline' onsubmit=\"return confirm('Tem certeza que deseja remover este aluno do evento?');\">";
                                        echo "<input type='hidden' name='inscricao_id' value='{$inscrito['inscricao_id']}'>";
                                        echo "<input type='hidden' name='evento_id' value='{$evento_id}'>";
                                        echo "<button type='submit' name='remover_inscricao' class='btn btn-sm btn-outline-danger' title='Remover inscrição'>";
                                        echo "<i class='fas fa-times'></i>";
                                        echo "</button>";
                                        echo "</form>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                    echo "</tbody></table>";
                                    echo "</div>";
                                } else {
                                    echo "<div class='text-center py-3'>";
                                    echo "<i class='fas fa-user-slash fa-2x text-muted mb-2'></i>";
                                    echo "<p class='text-muted'>Nenhum aluno da sua atlética está inscrito neste evento ainda.</p>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
                            
                            <div class="col-md-5 border-start">
                                <h5><i class="fas fa-user-plus"></i> Inscrever Membros da Atlética</h5>
                                <?php
                                // Buscar alunos da atlética que ainda não estão inscritos neste evento
                                $sql_disponiveis = "SELECT u.id, u.nome, u.ra 
                                                   FROM usuarios u 
                                                   JOIN cursos c ON u.curso_id = c.id
                                                   WHERE c.atletica_id = ? 
                                                   AND u.tipo_usuario_detalhado = 'Membro das Atléticas' 
                                                   AND u.id NOT IN (
                                                       SELECT ie.aluno_id 
                                                       FROM inscricoes_eventos ie 
                                                       WHERE ie.evento_id = ?
                                                   )
                                                   ORDER BY u.nome";
                                $stmt_disponiveis = $conexao->prepare($sql_disponiveis);
                                $stmt_disponiveis->bind_param("ii", $atletica_id, $evento_id);
                                $stmt_disponiveis->execute();
                                $alunos_disponiveis = $stmt_disponiveis->get_result();

                                if ($alunos_disponiveis->num_rows > 0) {
                                    while($aluno = $alunos_disponiveis->fetch_assoc()) {
                                        echo "<div class='d-flex justify-content-between align-items-center mb-2 p-2 border rounded'>";
                                        echo "<div>";
                                        echo "<strong>" . htmlspecialchars($aluno['nome']) . "</strong><br>";
                                        echo "<small class='text-muted'>RA: " . htmlspecialchars($aluno['ra']) . "</small>";
                                        echo "</div>";
                                        echo "<form method='post' class='d-inline'>";
                                        echo "<input type='hidden' name='aluno_id' value='{$aluno['id']}'>";
                                        echo "<input type='hidden' name='evento_id' value='{$evento_id}'>";
                                        echo "<button type='submit' name='inscrever_aluno' class='btn btn-success btn-sm'>";
                                        echo "<i class='fas fa-plus'></i> Inscrever";
                                        echo "</button>";
                                        echo "</form>";
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<div class='text-center py-3'>";
                                    echo "<i class='fas fa-check-circle fa-2x text-success mb-2'></i>";
                                    echo "<p class='text-muted'>Todos os membros da atlética já estão inscritos neste evento ou não há membros disponíveis.</p>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhum evento esportivo disponível</h5>
                <p class="text-muted">Não há eventos esportivos aprovados programados para o futuro.</p>
                <small class="text-muted">Os eventos aparecem aqui após serem aprovados pelos administradores.</small>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../../templates/footer.php'; ?>
