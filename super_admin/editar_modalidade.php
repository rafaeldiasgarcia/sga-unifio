<?php
require_once '../config.php';
is_superadmin();

$id = $_GET['id'] ?? 0;
if (!$id) { 
    header("location: gerenciar_modalidades.php"); 
    exit; 
}

$mensagem = '';

// Lógica para Atualizar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_modalidade'])) {
    $nome = trim($_POST['nome']);

    if (!empty($nome)) {
        // Verificar se já existe outra modalidade com o mesmo nome
        $check_stmt = $conexao->prepare("SELECT id FROM modalidades WHERE nome = ? AND id != ?");
        $check_stmt->bind_param("si", $nome, $id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            $mensagem = "<div class='alert alert-warning'>Já existe uma modalidade com este nome!</div>";
        } else {
            $stmt = $conexao->prepare("UPDATE modalidades SET nome = ? WHERE id = ?");
            $stmt->bind_param("si", $nome, $id);
            if ($stmt->execute()) {
                $_SESSION['mensagem_sucesso'] = "Modalidade '<strong>" . htmlspecialchars($nome) . "</strong>' atualizada com sucesso!";
                header("location: gerenciar_modalidades.php");
                exit;
            } else {
                $mensagem = "<div class='alert alert-danger'>Erro ao atualizar a modalidade.</div>";
            }
        }
    } else {
        $mensagem = "<div class='alert alert-danger'>O nome da modalidade é obrigatório.</div>";
    }
}

// Busca dados da modalidade para preencher o formulário
$stmt = $conexao->prepare("SELECT * FROM modalidades WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$modalidade = $stmt->get_result()->fetch_assoc();

if (!$modalidade) {
    // Se o ID não for válido, volta para a lista
    header("location: gerenciar_modalidades.php");
    exit;
}

// Buscar total de equipes associadas
$equipes_stmt = $conexao->prepare("SELECT COUNT(*) as total FROM equipes WHERE modalidade_id = ?");
$equipes_stmt->bind_param("i", $id);
$equipes_stmt->execute();
$total_equipes = $equipes_stmt->get_result()->fetch_assoc()['total'];
?>

<?php include '../templates/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit"></i> Editando Modalidade</h2>
    <a href="gerenciar_modalidades.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<?php echo $mensagem; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Dados da Modalidade</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Modalidade</label>
                        <input type="text" name="nome" id="nome" class="form-control" 
                               value="<?php echo htmlspecialchars($modalidade['nome']); ?>" required>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="gerenciar_modalidades.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" name="update_modalidade" class="btn btn-success">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informações</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>ID:</strong> 
                   <span class="badge bg-secondary"><?php echo $modalidade['id']; ?></span>
                </p>
                <p class="mb-2"><strong>Equipes Associadas:</strong> 
                   <?php if ($total_equipes > 0): ?>
                       <span class="badge bg-primary"><?php echo $total_equipes; ?> equipe(s)</span>
                   <?php else: ?>
                       <span class="badge bg-secondary">0 equipes</span>
                   <?php endif; ?>
                </p>
                
                <?php if ($total_equipes > 0): ?>
                    <div class="alert alert-info small">
                        <i class="fas fa-exclamation-triangle"></i>
                        Esta modalidade possui equipes associadas. Altere com cuidado.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include '../templates/footer.php'; ?>