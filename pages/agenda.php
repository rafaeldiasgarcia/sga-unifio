<?php
require_once '../config.php';
check_login();
$usuario_id = $_SESSION['id'];

// Lógica para Marcar/Desmarcar Presença (permanece a mesma)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['marcar_presenca'])) {
        $agendamento_id = $_POST['agendamento_id'];
        $stmt = $conexao->prepare("INSERT INTO presencas (usuario_id, agendamento_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $usuario_id, $agendamento_id);
        $stmt->execute();
    } elseif (isset($_POST['desmarcar_presenca'])) {
        $agendamento_id = $_POST['agendamento_id'];
        $stmt = $conexao->prepare("DELETE FROM presencas WHERE usuario_id = ? AND agendamento_id = ?");
        $stmt->bind_param("ii", $usuario_id, $agendamento_id);
        $stmt->execute();
    }
    header("Location: agenda.php");
    exit;
}

// OTIMIZADO: Busca todos os eventos aprovados de uma só vez
$sql = "SELECT a.id, a.titulo, a.tipo_agendamento, a.esporte_tipo, a.data_agendamento, a.periodo, u.nome as responsavel, p.id as presenca_id
        FROM agendamentos a
        JOIN usuarios u ON a.usuario_id = u.id
        LEFT JOIN presencas p ON a.id = p.agendamento_id AND p.usuario_id = ?
        WHERE a.status = 'aprovado' AND a.data_agendamento >= CURDATE()
        ORDER BY a.data_agendamento ASC, a.periodo ASC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$eventos = $stmt->get_result();

// Variáveis para contar se existem eventos de cada tipo para exibição
$eventos_esportivos_count = 0;
$eventos_nao_esportivos_count = 0;
?>

<?php include '../templates/header.php'; ?>
    <h1>Agenda da Quadra</h1>
    <p>Confira os próximos eventos aprovados e marque sua presença.</p>

    <div class="row">
        <div class="col-md-6">
            <h3><i class="bi bi-trophy-fill text-primary"></i> Eventos Esportivos</h3>
            <div class="list-group">
                <?php if ($eventos->num_rows > 0): ?>
                    <?php $eventos->data_seek(0); // Reinicia o ponteiro do resultado ?>
                    <?php while($evento = $eventos->fetch_assoc()): ?>
                        <?php if($evento['tipo_agendamento'] != 'esportivo') continue; // Pula se não for esportivo ?>
                        <?php $eventos_esportivos_count++; ?>
                        <div class="list-group-item list-group-item-action flex-column align-items-start mb-2">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?php echo htmlspecialchars($evento['titulo']); ?></h5>
                                <small><?php echo date('d/m/Y', strtotime($evento['data_agendamento'])); ?></small>
                            </div>
                            <p class="mb-1">Período: <?php echo htmlspecialchars($evento['periodo']); ?> | Esporte: <strong><?php echo htmlspecialchars($evento['esporte_tipo']); ?></strong></p>
                            <small class="text-muted">Responsável: <?php echo htmlspecialchars($evento['responsavel']); ?></small>

                            <form method="post" class="mt-2">
                                <input type="hidden" name="agendamento_id" value="<?php echo $evento['id']; ?>">
                                <?php if ($evento['presenca_id']): ?>
                                    <button type="submit" name="desmarcar_presenca" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-circle-fill"></i> Desmarcar Presença
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="marcar_presenca" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-check-circle"></i> Marcar Presença
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>

                <?php if ($eventos_esportivos_count == 0): ?>
                    <p class="text-muted">Nenhum evento esportivo agendado no momento.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <h3><i class="bi bi-calendar-event-fill text-success"></i> Eventos Não Esportivos</h3>
            <div class="list-group">
                <?php if ($eventos->num_rows > 0): ?>
                    <?php $eventos->data_seek(0); // Reinicia o ponteiro do resultado ?>
                    <?php while($evento = $eventos->fetch_assoc()): ?>
                        <?php if($evento['tipo_agendamento'] != 'nao_esportivo') continue; // Pula se não for não esportivo ?>
                        <?php $eventos_nao_esportivos_count++; ?>
                        <div class="list-group-item list-group-item-action flex-column align-items-start mb-2">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?php echo htmlspecialchars($evento['titulo']); ?></h5>
                                <small><?php echo date('d/m/Y', strtotime($evento['data_agendamento'])); ?></small>
                            </div>
                            <p class="mb-1">Período: <?php echo htmlspecialchars($evento['periodo']); ?></p>
                            <small class="text-muted">Responsável: <?php echo htmlspecialchars($evento['responsavel']); ?></small>

                            <form method="post" class="mt-2">
                                <input type="hidden" name="agendamento_id" value="<?php echo $evento['id']; ?>">
                                <?php if ($evento['presenca_id']): ?>
                                    <button type="submit" name="desmarcar_presenca" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-circle-fill"></i> Desmarcar Presença
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="marcar_presenca" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-check-circle"></i> Marcar Presença
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>

                <?php if ($eventos_nao_esportivos_count == 0): ?>
                    <p class="text-muted">Nenhum evento não esportivo agendado no momento.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>