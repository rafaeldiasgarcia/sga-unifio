<?php
require_once '../../src/config/config.php';
check_login();

// Redireciona se não for professor, super admin ou admin das atléticas
$tipo_usuario = $_SESSION['tipo_usuario_detalhado'] ?? '';
$role = $_SESSION['role'] ?? '';

$can_schedule = ($tipo_usuario === 'Professor') || 
                ($role === 'superadmin') || 
                ($role === 'admin' && $tipo_usuario === 'Membro das Atléticas');

if (!$can_schedule) {
    header("location: ../index.php");
    exit;
}

// Buscar modalidades ativas do banco de dados
$modalidades_query = "SELECT id, nome FROM modalidades ORDER BY nome";
$modalidades_result = $conexao->query($modalidades_query);

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coleta de dados do formulário
    $titulo = trim($_POST['titulo']);
    $tipo_agendamento = trim($_POST['tipo_agendamento']);
    $data_agendamento = trim($_POST['data_agendamento']);
    $periodo = trim($_POST['periodo']);
    $descricao = trim($_POST['descricao']);
    $esporte_tipo = $_POST['esporte_tipo'] ?? null;
    $quantidade_pessoas = (int)$_POST['quantidade_pessoas'] ?? 0;
    $usuario_id = $_SESSION['id'];

    // Define esporte_tipo como null se não for do tipo esportivo
    if ($tipo_agendamento !== 'esportivo') {
        $esporte_tipo = null;
    }

    // Validação dos campos obrigatórios
    if (empty($titulo) || empty($tipo_agendamento) || empty($data_agendamento) || empty($periodo) || $quantidade_pessoas <= 0) {
        $mensagem = "<div class='alert alert-danger'>Preencha todos os campos obrigatórios, incluindo a quantidade de pessoas.</div>";
    } else {
        // Verificar se o horário já está ocupado
        $check_sql = "SELECT COUNT(*) as total FROM agendamentos 
                      WHERE data_agendamento = ? AND periodo = ? AND status = 'aprovado'";
        $check_stmt = $conexao->prepare($check_sql);
        $check_stmt->bind_param("ss", $data_agendamento, $periodo);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_row = $check_result->fetch_assoc();

        if ($check_row['total'] > 0) {
            $mensagem = "<div class='alert alert-danger'><strong>Erro:</strong> Este horário já está reservado! Por favor, escolha outra data ou período.</div>";
        } else {
            // Prepara e executa a query
            $sql = "INSERT INTO agendamentos (usuario_id, titulo, tipo_agendamento, esporte_tipo, data_agendamento, periodo, descricao, quantidade_pessoas) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = $conexao->prepare($sql)) {
                $stmt->bind_param("issssssi", $usuario_id, $titulo, $tipo_agendamento, $esporte_tipo, $data_agendamento, $periodo, $descricao, $quantidade_pessoas);
                if ($stmt->execute()) {
                    $mensagem = "<div class='alert alert-success'>Solicitação de agendamento enviada com sucesso! Aguarde aprovação.</div>";
                } else {
                    $mensagem = "<div class='alert alert-danger'>Erro ao enviar solicitação.</div>";
                }
                $stmt->close();
            }
        }
        $check_stmt->close();
    }
}

// Verificar se é uma requisição de atualização do calendário
if (isset($_SERVER['HTTP_X_CALENDAR_UPDATE'])) {
    require_once '../templates/calendar.php';

    $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : null;
    $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : null;

    echo '<div>';
    gerarCalendario($conexao, $mes, $ano);
    echo '</div>';
    exit;
}
?>

<?php include '../../templates/header.php'; ?>

<h2>Agendamento de Horário na Quadra</h2>
<p>Preencha o formulário para solicitar o uso da quadra para um evento.</p>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <?php echo $mensagem; ?>
                <form action="agendar_evento.php" method="post">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título do Evento</label>
                        <input type="text" name="titulo" id="titulo" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_agendamento" class="form-label">Tipo de Evento</label>
                            <select name="tipo_agendamento" id="tipo_agendamento" class="form-select" required>
                                <option value="esportivo">Esportivo</option>
                                <option value="nao_esportivo">Não Esportivo</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3" id="campo_esporte">
                            <label for="esporte_tipo" class="form-label">Qual Modalidade?</label>
                            <select name="esporte_tipo" id="esporte_tipo" class="form-select">
                                <option value="">-- Selecione uma modalidade --</option>
                                <?php if ($modalidades_result && $modalidades_result->num_rows > 0): ?>
                                    <?php while($modalidade = $modalidades_result->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($modalidade['nome']); ?>">
                                            <?php echo htmlspecialchars($modalidade['nome']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option value="" disabled>Nenhuma modalidade disponível</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="quantidade_pessoas" class="form-label">Qtd. de Pessoas Aproximadas</label>
                            <input type="number" name="quantidade_pessoas" id="quantidade_pessoas" class="form-control" min="1" max="100" required placeholder="Ex: 15">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="data_agendamento" class="form-label">Data</label>
                            <input type="date" name="data_agendamento" id="data_agendamento" class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="periodo" class="form-label">Período</label>
                            <select name="periodo" id="periodo" class="form-select" required>
                                <option value="19:15 - 20:55">1º Período (19:15 - 20:55)</option>
                                <option value="21:10 - 22:50">2º Período (21:10 - 22:50)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Breve Descrição (Opcional)</label>
                        <textarea name="descricao" id="descricao" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Enviar Solicitação</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <?php
        require_once '../../templates/calendar.php';

        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : null;
        $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : null;

        echo '<div>';
        gerarCalendario($conexao, $mes, $ano);
        echo '</div>';
        ?>
    </div>
</div>

<script>
    document.getElementById('tipo_agendamento').addEventListener('change', function () {
        const campoEsporte = document.getElementById('campo_esporte');
        campoEsporte.style.display = (this.value === 'esportivo') ? 'block' : 'none';
    });

    // Garante o comportamento correto ao carregar a página
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('tipo_agendamento').dispatchEvent(new Event('change'));
    });
</script>

<?php include '../../templates/footer.php'; ?>
