<?php
require_once '../../src/config/config.php';
check_login();
$usuario_id = $_SESSION['id'];
$role = $_SESSION['role'] ?? '';
$atletica_id = $_SESSION['atletica_id'] ?? null;

// Lógica para Marcar/Desmarcar Presença e Confirmar Presença da Atlética
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
    } elseif (isset($_POST['confirmar_atletica'])) {
        $agendamento_id = $_POST['agendamento_id'];
        $quantidade_atletica = (int)$_POST['quantidade_atletica'];

        // Atualizar agendamento com confirmação da atlética
        $stmt = $conexao->prepare("UPDATE agendamentos SET atletica_confirmada = 1, atletica_id_confirmada = ?, quantidade_atletica = ? WHERE id = ?");
        $stmt->bind_param("iii", $atletica_id, $quantidade_atletica, $agendamento_id);
        $stmt->execute();
    } elseif (isset($_POST['desconfirmar_atletica'])) {
        $agendamento_id = $_POST['agendamento_id'];

        // Remover confirmação da atlética
        $stmt = $conexao->prepare("UPDATE agendamentos SET atletica_confirmada = 0, atletica_id_confirmada = NULL, quantidade_atletica = 0 WHERE id = ?");
        $stmt->bind_param("i", $agendamento_id);
        $stmt->execute();
    }
    header("Location: agenda.php");
    exit;
}

// OTIMIZADO: Busca todos os eventos aprovados de uma só vez
$sql = "SELECT a.id, a.titulo, a.tipo_agendamento, a.esporte_tipo, a.data_agendamento, a.periodo, 
               u.nome as responsavel, p.id as presenca_id, a.atletica_confirmada, 
               a.atletica_id_confirmada, a.quantidade_atletica, at.nome as atletica_nome
        FROM agendamentos a
        JOIN usuarios u ON a.usuario_id = u.id
        LEFT JOIN presencas p ON a.id = p.agendamento_id AND p.usuario_id = ?
        LEFT JOIN atleticas at ON a.atletica_id_confirmada = at.id
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

<?php include '../../templates/header.php'; ?>
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

                            <?php if ($evento['atletica_confirmada']): ?>
                                <div class="mt-2">
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($evento['atletica_nome']); ?> confirmada
                                        (<?php echo $evento['quantidade_atletica']; ?> pessoas)
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="mt-2">
                                <!-- Botão de presença individual -->
                                <form method="post" class="d-inline">
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

                                <!-- Botão de confirmação da atlética (apenas para admins de atlética) -->
                                <?php if ($role === 'admin' && $atletica_id): ?>
                                    <?php if ($evento['atletica_confirmada'] && $evento['atletica_id_confirmada'] == $atletica_id): ?>
                                        <form method="post" class="d-inline ms-2">
                                            <input type="hidden" name="agendamento_id" value="<?php echo $evento['id']; ?>">
                                            <button type="submit" name="desconfirmar_atletica" class="btn btn-sm btn-warning">
                                                <i class="bi bi-x-circle"></i> Desconfirmar Atlética
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-info ms-2" data-bs-toggle="modal" data-bs-target="#modalAtletica<?php echo $evento['id']; ?>">
                                            <i class="bi bi-people-fill"></i> Confirmar Atlética
                                        </button>

                                        <!-- Modal para confirmar atlética -->
                                        <div class="modal fade" id="modalAtletica<?php echo $evento['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmar Presença da Atlética</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="agendamento_id" value="<?php echo $evento['id']; ?>">
                                                            <div class="mb-3">
                                                                <label for="quantidade_atletica<?php echo $evento['id']; ?>" class="form-label">
                                                                    Quantas pessoas da atlética irão participar?
                                                                </label>
                                                                <input type="number" name="quantidade_atletica"
                                                                       id="quantidade_atletica<?php echo $evento['id']; ?>"
                                                                       class="form-control" min="1" max="50" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" name="confirmar_atletica" class="btn btn-primary">Confirmar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
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

<?php include '../../templates/footer.php'; ?>
