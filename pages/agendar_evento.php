<?php
require_once '../config.php';
check_login();

// Redireciona se não for professor
if (!isset($_SESSION['tipo_usuario_detalhado']) || $_SESSION['tipo_usuario_detalhado'] !== 'Professor') {
    header("location: ../index.php");
    exit;
}

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coleta de dados do formulário
    $titulo = trim($_POST['titulo']);
    $tipo_agendamento = trim($_POST['tipo_agendamento']);
    $data_agendamento = trim($_POST['data_agendamento']);
    $periodo = trim($_POST['periodo']);
    $descricao = trim($_POST['descricao']);
    $esporte_tipo = $_POST['esporte_tipo'] ?? null;
    $usuario_id = $_SESSION['id'];

    // Define esporte_tipo como null se não for do tipo esportivo
    if ($tipo_agendamento !== 'esportivo') {
        $esporte_tipo = null;
    }

    // Validação dos campos obrigatórios
    if (empty($titulo) || empty($tipo_agendamento) || empty($data_agendamento) || empty($periodo)) {
        $mensagem = "<div class='alert alert-danger'>Preencha todos os campos obrigatórios.</div>";
    } else {
        // Prepara e executa a query
        $sql = "INSERT INTO agendamentos (usuario_id, titulo, tipo_agendamento, esporte_tipo, data_agendamento, periodo, descricao) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conexao->prepare($sql)) {
            $stmt->bind_param("issssss", $usuario_id, $titulo, $tipo_agendamento, $esporte_tipo, $data_agendamento, $periodo, $descricao);
            if ($stmt->execute()) {
                $mensagem = "<div class='alert alert-success'>Solicitação de agendamento enviada com sucesso! Aguarde aprovação.</div>";
            } else {
                $mensagem = "<div class='alert alert-danger'>Erro ao enviar solicitação.</div>";
            }
            $stmt->close();
        }
    }
}
?>

<?php include '../templates/header.php'; ?>

<h2>Agendamento de Horário na Quadra</h2>
<p>Preencha o formulário para solicitar o uso da quadra para um evento.</p>

<div class="card">
    <div class="card-body">
        <?php echo $mensagem; ?>
        <form action="agendar_evento.php" method="post">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título do Evento</label>
                <input type="text" name="titulo" id="titulo" class="form-control" required>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="tipo_agendamento" class="form-label">Tipo de Evento</label>
                    <select name="tipo_agendamento" id="tipo_agendamento" class="form-select" required>
                        <option value="esportivo">Esportivo</option>
                        <option value="nao_esportivo">Não Esportivo</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3" id="campo_esporte">
                    <label for="esporte_tipo" class="form-label">Qual Esporte?</label>
                    <select name="esporte_tipo" id="esporte_tipo" class="form-select">
                        <option value="Futsal">Futsal</option>
                        <option value="Vôlei">Vôlei</option>
                        <option value="Basquete">Basquete</option>
                        <option value="Handebol">Handebol</option>
                    </select>
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

<?php include '../templates/footer.php'; ?>
