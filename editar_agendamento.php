<?php
require_once 'config.php';
check_login();

if (!isset($_SESSION['tipo_usuario_detalhado']) || $_SESSION['tipo_usuario_detalhado'] !== 'Professor') {
    header("location: index.php");
    exit;
}

$agendamento_id = $_GET['id'] ?? 0;
$usuario_id = $_SESSION['id'];
$mensagem = '';

// Lógica para Atualizar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_agendamento'])) {
    $titulo = trim($_POST['titulo']);
    $data_agendamento = $_POST['data_agendamento'];
    $periodo = $_POST['periodo'];
    $descricao = trim($_POST['descricao']);

    // Ao editar, o status volta para pendente para nova aprovação do Super Admin
    $stmt = $conexao->prepare("UPDATE agendamentos SET titulo = ?, data_agendamento = ?, periodo = ?, descricao = ?, status = 'pendente' WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ssssii", $titulo, $data_agendamento, $periodo, $descricao, $agendamento_id, $usuario_id);
    if ($stmt->execute()) {
        header("location: meus_agendamentos.php");
        exit;
    } else {
        $mensagem = "<div class='alert alert-danger'>Erro ao atualizar o agendamento.</div>";
    }
}

// Busca dados do agendamento para preencher o formulário, garantindo que o professor seja o dono
$stmt = $conexao->prepare("SELECT * FROM agendamentos WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $agendamento_id, $usuario_id);
$stmt->execute();
$agendamento = $stmt->get_result()->fetch_assoc();

if (!$agendamento) {
    // Se não encontrou o agendamento ou o usuário não é o dono, redireciona
    header("location: meus_agendamentos.php");
    exit;
}
?>
<?php include 'templates/header.php'; ?>
<h2>Editando Agendamento</h2>
<p>Ajuste as informações da sua solicitação. Após salvar, ela voltará para o status "Pendente" e precisará de nova aprovação.</p>
<?php echo $mensagem; ?>
<div class="card"><div class="card-body">
        <form method="post">
            <div class="mb-3"><label for="titulo" class="form-label">Título do Evento</label><input type="text" name="titulo" id="titulo" class="form-control" value="<?php echo htmlspecialchars($agendamento['titulo']); ?>" required></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="data_agendamento" class="form-label">Data</label><input type="date" name="data_agendamento" id="data_agendamento" class="form-control" value="<?php echo $agendamento['data_agendamento']; ?>" required></div>
                <div class="col-md-6 mb-3"><label for="periodo" class="form-label">Período</label>
                    <select name="periodo" id="periodo" class="form-select" required>
                        <option value="19:15 - 20:55" <?php if($agendamento['periodo'] == '19:15 - 20:55') echo 'selected'; ?>>1º Período (19:15 - 20:55)</option>
                        <option value="21:10 - 22:50" <?php if($agendamento['periodo'] == '21:10 - 22:50') echo 'selected'; ?>>2º Período (21:10 - 22:50)</option>
                    </select>
                </div>
            </div>
            <div class="mb-3"><label for="descricao" class="form-label">Breve Descrição (Opcional)</label><textarea name="descricao" id="descricao" class="form-control" rows="3"><?php echo htmlspecialchars($agendamento['descricao']); ?></textarea></div>
            <button type="submit" name="update_agendamento" class="btn btn-success">Salvar e Reenviar para Aprovação</button>
            <a href="meus_agendamentos.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div></div>
<?php include 'templates/footer.php'; ?>```