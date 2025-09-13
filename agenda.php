<?php
require_once 'config.php';
check_login();

// Buscar eventos esportivos (aprovados)
$sql_esportivos = "SELECT a.titulo, a.data_agendamento, a.periodo, u.nome as responsavel 
                   FROM agendamentos a
                   JOIN usuarios u ON a.usuario_id = u.id
                   WHERE a.tipo_agendamento = 'esportivo' AND a.status = 'aprovado'
                   ORDER BY a.data_agendamento ASC";
$eventos_esportivos = $conexao->query($sql_esportivos);

// Buscar eventos não esportivos (aprovados)
$sql_nao_esportivos = "SELECT a.titulo, a.data_agendamento, a.periodo, u.nome as responsavel 
                       FROM agendamentos a
                       JOIN usuarios u ON a.usuario_id = u.id
                       WHERE a.tipo_agendamento = 'nao_esportivo' AND a.status = 'aprovado'
                       ORDER BY a.data_agendamento ASC";
$eventos_nao_esportivos = $conexao->query($sql_nao_esportivos);
?>

<?php include 'templates/header.php'; ?>
    <h1>Agenda da Quadra</h1>
    <p>Confira os próximos eventos aprovados para a quadra poliesportiva.</p>

    <div class="row">
        <!-- Coluna de Eventos Esportivos -->
        <div class="col-md-6">
            <h3><i class="bi bi-trophy-fill text-primary"></i> Eventos Esportivos</h3>
            <div class="list-group">
                <?php if ($eventos_esportivos->num_rows > 0): ?>
                    <?php while($evento = $eventos_esportivos->fetch_assoc()): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?php echo htmlspecialchars($evento['titulo']); ?></h5>
                                <small><?php echo date('d/m/Y', strtotime($evento['data_agendamento'])); ?></small>
                            </div>
                            <p class="mb-1">Período: <?php echo htmlspecialchars($evento['periodo']); ?></p>
                            <small>Responsável: <?php echo htmlspecialchars($evento['responsavel']); ?></small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">Nenhum evento esportivo agendado.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Coluna de Eventos Não Esportivos -->
        <div class="col-md-6">
            <h3><i class="bi bi-calendar-event-fill text-success"></i> Eventos Não Esportivos</h3>
            <div class="list-group">
                <?php if ($eventos_nao_esportivos->num_rows > 0): ?>
                    <?php while($evento = $eventos_nao_esportivos->fetch_assoc()): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?php echo htmlspecialchars($evento['titulo']); ?></h5>
                                <small><?php echo date('d/m/Y', strtotime($evento['data_agendamento'])); ?></small>
                            </div>
                            <p class="mb-1">Período: <?php echo htmlspecialchars($evento['periodo']); ?></p>
                            <small>Responsável: <?php echo htmlspecialchars($evento['responsavel']); ?></small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">Nenhum evento não esportivo agendado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php include 'templates/footer.php'; ?>