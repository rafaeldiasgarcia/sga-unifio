<?php
require_once '../config.php';
is_superadmin();
$mensagem = '';

// Verificar se há mensagem de sucesso da sessão
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem = "<div class='alert alert-success'>" . $_SESSION['mensagem_sucesso'] . "</div>";
    unset($_SESSION['mensagem_sucesso']);
}

// Lógica para Adicionar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_modalidade'])) {
    $nome = trim($_POST['nome']);
    
    if (!empty($nome)) {
        // Verificar se a modalidade já existe
        $check_stmt = $conexao->prepare("SELECT id FROM modalidades WHERE nome = ?");
        $check_stmt->bind_param("s", $nome);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            $mensagem = "<div class='alert alert-warning'>Esta modalidade já existe!</div>";
        } else {
            $stmt = $conexao->prepare("INSERT INTO modalidades (nome) VALUES (?)");
            $stmt->bind_param("s", $nome);
            if ($stmt->execute()) {
                $mensagem = "<div class='alert alert-success'>Modalidade '<strong>" . htmlspecialchars($nome) . "</strong>' adicionada com sucesso!</div>";
            } else {
                $mensagem = "<div class='alert alert-danger'>Erro ao adicionar a modalidade.</div>";
            }
        }
    } else {
        $mensagem = "<div class='alert alert-danger'>O nome da modalidade é obrigatório.</div>";
    }
}

// Lógica para Deletar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_modalidade'])) {
    $id = $_POST['id_to_delete'];
    
    // Primeiro, verificar se existem equipes associadas a esta modalidade
    $check_equipes = $conexao->prepare("SELECT COUNT(*) as total FROM equipes WHERE modalidade_id = ?");
    $check_equipes->bind_param("i", $id);
    $check_equipes->execute();
    $result = $check_equipes->get_result()->fetch_assoc();
    
    if ($result['total'] > 0) {
        $mensagem = "<div class='alert alert-warning'>Não é possível excluir esta modalidade pois existem {$result['total']} equipe(s) associada(s) a ela. Remova as equipes primeiro.</div>";
    } else {
        // Buscar nome da modalidade para mostrar na mensagem
        $get_name = $conexao->prepare("SELECT nome FROM modalidades WHERE id = ?");
        $get_name->bind_param("i", $id);
        $get_name->execute();
        $modalidade_data = $get_name->get_result()->fetch_assoc();
        
        $stmt = $conexao->prepare("DELETE FROM modalidades WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $nome_excluida = $modalidade_data ? $modalidade_data['nome'] : 'Modalidade';
            $mensagem = "<div class='alert alert-success'>Modalidade '<strong>" . htmlspecialchars($nome_excluida) . "</strong>' excluída com sucesso!</div>";
        } else {
            $mensagem = "<div class='alert alert-danger'>Erro ao excluir a modalidade.</div>";
        }
    }
}

// Buscar dados para exibição
$modalidades = $conexao->query("SELECT m.id, m.nome, 
                                       (SELECT COUNT(*) FROM equipes WHERE modalidade_id = m.id) as total_equipes
                                FROM modalidades m 
                                ORDER BY m.nome");
?>

<?php include '../templates/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gerenciar Modalidades</h2>
    <div class="text-muted">
        <i class="fas fa-info-circle"></i> As modalidades aparecem automaticamente no formulário de agendamento de eventos
    </div>
</div>

<?php echo $mensagem; ?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Adicionar Nova Modalidade</h5>
    </div>
    <div class="card-body">
        <form method="post" class="row g-3 align-items-center">
            <div class="col-md-8">
                <label for="nome" class="form-label">Nome da Modalidade</label>
                <input type="text" name="nome" id="nome" class="form-control" 
                       placeholder="Ex: Futsal, Vôlei, Basquete, Handebol..." required>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" name="add_modalidade" class="btn btn-primary w-100">
                    <i class="fas fa-plus"></i> Adicionar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list"></i> Modalidades Cadastradas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Nome da Modalidade</th>
                        <th style="width: 120px;" class="text-center">Equipes</th>
                        <th style="width: 140px;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($modalidades->num_rows > 0): ?>
                    <?php while($row = $modalidades->fetch_assoc()): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?php echo $row['id']; ?></span></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['nome']); ?></strong>
                            </td>
                            <td class="text-center">
                                <?php if ($row['total_equipes'] > 0): ?>
                                    <span class="badge bg-primary"><?php echo $row['total_equipes']; ?> equipe(s)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">0 equipes</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center flex-wrap">
                                    <a href="editar_modalidade.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="Editar modalidade <?php echo htmlspecialchars($row['nome']); ?>"
                                       data-bs-toggle="tooltip">
                                        <i class="fas fa-edit"></i>
                                        <span class="d-none d-md-inline ms-1">Editar</span>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm" 
                                            title="Excluir modalidade <?php echo htmlspecialchars($row['nome']); ?>"
                                            data-bs-toggle="tooltip"
                                            onclick="confirmarExclusao(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nome']); ?>', <?php echo $row['total_equipes']; ?>)">
                                        <i class="fas fa-trash"></i>
                                        <span class="d-none d-md-inline ms-1">Excluir</span>
                                    </button>
                                </div>
                                
                                <!-- Form oculto para exclusão -->
                                <form id="formExcluir<?php echo $row['id']; ?>" method="post" style="display: none;">
                                    <input type="hidden" name="id_to_delete" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="delete_modalidade" value="1">
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <br>
                            <span class="text-muted">Nenhuma modalidade cadastrada ainda.</span>
                            <br>
                            <small class="text-muted">Adicione a primeira modalidade usando o formulário acima.</small>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Função para confirmar exclusão com validação de equipes
function confirmarExclusao(id, nome, totalEquipes) {
    if (totalEquipes > 0) {
        // Se há equipes, mostra aviso e não permite exclusão
        const mensagem = `❌ Não é possível excluir a modalidade '${nome}'.\n\n` +
                        `🔗 Esta modalidade possui ${totalEquipes} equipe(s) associada(s).\n` +
                        `📋 Para excluir esta modalidade, primeiro remova todas as equipes associadas.`;
        
        alert(mensagem);
        return false;
    } else {
        // Se não há equipes, pede confirmação
        const mensagem = `⚠️ Confirmar exclusão da modalidade '${nome}'?\n\n` +
                        `🗑️ Esta ação não poderá ser desfeita.\n` +
                        `✅ A modalidade será removida permanentemente do sistema.`;
        
        if (confirm(mensagem)) {
            // Mostra mensagem de processamento
            const button = event.target.closest('button');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Excluindo...';
            
            // Submete o formulário
            document.getElementById('formExcluir' + id).submit();
        }
    }
}

// Inicializar tooltips do Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-focus no campo de nome da modalidade
    const nomeInput = document.getElementById('nome');
    if (nomeInput) {
        nomeInput.focus();
    }
});
</script>

<?php include '../templates/footer.php'; ?>