<?php
require_once '../../src/config/config.php';
is_superadmin();

// Configurar fuso horário de Brasília
date_default_timezone_set('America/Sao_Paulo');

$mensagem = '';
$relatorio_tipo = '';
$dados_relatorio = [];
$form_data = []; // Para preservar os dados do formulário

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $relatorio_tipo = $_POST['tipo_relatorio'];

    // Preservar dados do formulário
    $form_data = $_POST;

    if ($relatorio_tipo == 'evento_especifico') {
        $evento_id = $_POST['evento_id'];

        // Buscar dados do evento específico
        $sql_evento = "SELECT a.*, u.nome as responsavel, at.nome as atletica_confirmada_nome
                       FROM agendamentos a
                       JOIN usuarios u ON a.usuario_id = u.id
                       LEFT JOIN atleticas at ON a.atletica_id_confirmada = at.id
                       WHERE a.id = ?";
        $stmt_evento = $conexao->prepare($sql_evento);
        $stmt_evento->bind_param("i", $evento_id);
        $stmt_evento->execute();
        $evento_dados = $stmt_evento->get_result()->fetch_assoc();

        if ($evento_dados) {
            // Buscar presenças confirmadas
            $sql_presencas = "SELECT u.nome, u.email, u.tipo_usuario_detalhado, c.nome as curso_nome
                             FROM presencas p
                             JOIN usuarios u ON p.usuario_id = u.id
                             LEFT JOIN cursos c ON u.curso_id = c.id
                             WHERE p.agendamento_id = ?
                             ORDER BY u.nome";
            $stmt_presencas = $conexao->prepare($sql_presencas);
            $stmt_presencas->bind_param("i", $evento_id);
            $stmt_presencas->execute();
            $presencas = $stmt_presencas->get_result();

            $dados_relatorio = [
                'evento' => $evento_dados,
                'presencas' => $presencas,
                'total_presencas' => $presencas->num_rows
            ];
        } else {
            $mensagem = "<div class='alert alert-danger'>Evento não encontrado.</div>";
        }

    } elseif ($relatorio_tipo == 'geral') {
        $data_inicio = $_POST['data_inicio'];
        $data_fim = $_POST['data_fim'];

        // Validar datas
        if (strtotime($data_inicio) > strtotime($data_fim)) {
            $mensagem = "<div class='alert alert-danger'>A data início não pode ser posterior à data fim.</div>";
        } else {
            // Relatório geral de eventos
            $sql_geral = "SELECT 
                            COUNT(*) as total_eventos,
                            COUNT(CASE WHEN status = 'aprovado' THEN 1 END) as eventos_aprovados,
                            COUNT(CASE WHEN status = 'pendente' THEN 1 END) as eventos_pendentes,
                            COUNT(CASE WHEN status = 'rejeitado' THEN 1 END) as eventos_rejeitados,
                            COUNT(CASE WHEN tipo_agendamento = 'esportivo' THEN 1 END) as eventos_esportivos,
                            COUNT(CASE WHEN tipo_agendamento = 'nao_esportivo' THEN 1 END) as eventos_nao_esportivos,
                            COUNT(CASE WHEN atletica_confirmada = 1 THEN 1 END) as eventos_com_atletica,
                            SUM(quantidade_pessoas) as total_pessoas_estimadas,
                            SUM(quantidade_atletica) as total_pessoas_atleticas
                          FROM agendamentos 
                          WHERE data_agendamento BETWEEN ? AND ?";
            $stmt_geral = $conexao->prepare($sql_geral);
            $stmt_geral->bind_param("ss", $data_inicio, $data_fim);
            $stmt_geral->execute();
            $estatisticas_gerais = $stmt_geral->get_result()->fetch_assoc();

            // Eventos por modalidade
            $sql_modalidades = "SELECT esporte_tipo, COUNT(*) as total
                               FROM agendamentos 
                               WHERE data_agendamento BETWEEN ? AND ? 
                               AND tipo_agendamento = 'esportivo' 
                               AND esporte_tipo IS NOT NULL
                               GROUP BY esporte_tipo
                               ORDER BY total DESC";
            $stmt_modalidades = $conexao->prepare($sql_modalidades);
            $stmt_modalidades->bind_param("ss", $data_inicio, $data_fim);
            $stmt_modalidades->execute();
            $modalidades_stats = $stmt_modalidades->get_result();

            // Eventos por atlética
            $sql_atleticas = "SELECT at.nome as atletica_nome, COUNT(*) as total
                             FROM agendamentos a
                             JOIN atleticas at ON a.atletica_id_confirmada = at.id
                             WHERE a.data_agendamento BETWEEN ? AND ? 
                             AND a.atletica_confirmada = 1
                             GROUP BY at.id, at.nome
                             ORDER BY total DESC";
            $stmt_atleticas = $conexao->prepare($sql_atleticas);
            $stmt_atleticas->bind_param("ss", $data_inicio, $data_fim);
            $stmt_atleticas->execute();
            $atleticas_stats = $stmt_atleticas->get_result();

            // Lista de eventos no período
            $sql_eventos = "SELECT a.*, u.nome as responsavel
                           FROM agendamentos a
                           JOIN usuarios u ON a.usuario_id = u.id
                           WHERE a.data_agendamento BETWEEN ? AND ?
                           ORDER BY a.data_agendamento DESC, a.periodo";
            $stmt_eventos = $conexao->prepare($sql_eventos);
            $stmt_eventos->bind_param("ss", $data_inicio, $data_fim);
            $stmt_eventos->execute();
            $eventos_lista = $stmt_eventos->get_result();

            $dados_relatorio = [
                'estatisticas' => $estatisticas_gerais,
                'modalidades' => $modalidades_stats,
                'atleticas' => $atleticas_stats,
                'eventos' => $eventos_lista,
                'periodo' => ['inicio' => $data_inicio, 'fim' => $data_fim]
            ];
        }
    }
}

// Buscar eventos para o select
$sql_eventos_select = "SELECT id, titulo, data_agendamento, periodo, status FROM agendamentos ORDER BY data_agendamento DESC LIMIT 50";
$eventos_select = $conexao->query($sql_eventos_select);
?>

<?php include '../../templates/header.php'; ?>

<div class="report-page">
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">📊 Relatórios de Eventos</h1>
                <p class="text-muted mb-0">Gere relatórios detalhados sobre os eventos da quadra</p>
            </div>
            <div class="text-end">
                <small class="text-muted">Sistema de Gestão de Agendamentos - UNIFIO</small>
            </div>
        </div>
    </div>

    <?php echo $mensagem; ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Relatório de Evento Específico</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="tipo_relatorio" value="evento_especifico">
                        <div class="mb-3">
                            <label for="evento_id" class="form-label">Selecione o Evento</label>
                            <select name="evento_id" id="evento_id" class="form-select" required>
                                <option value="">-- Selecione um evento --</option>
                                <?php while($evento = $eventos_select->fetch_assoc()): ?>
                                    <option value="<?php echo $evento['id']; ?>"
                                            <?php echo (isset($form_data['evento_id']) && $form_data['evento_id'] == $evento['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($evento['titulo']); ?> -
                                        <?php echo date('d/m/Y', strtotime($evento['data_agendamento'])); ?>
                                        (<?php echo $evento['periodo']; ?>)
                                        <span class="badge bg-<?php echo $evento['status'] == 'aprovado' ? 'success' : ($evento['status'] == 'pendente' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($evento['status']); ?>
                                        </span>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Gerar Relatório
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Relatório Geral por Período</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="tipo_relatorio" value="geral">
                        <div class="mb-3">
                            <label for="data_inicio" class="form-label">Data Início</label>
                            <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                                   value="<?php echo $form_data['data_inicio'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="data_fim" class="form-label">Data Fim</label>
                            <input type="date" name="data_fim" id="data_fim" class="form-control"
                                   value="<?php echo $form_data['data_fim'] ?? ''; ?>" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-graph-up"></i> Gerar Relatório
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($dados_relatorio) && $relatorio_tipo == 'evento_especifico'): ?>
    <div class="report-content">
        <div class="card shadow">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📋 Relatório do Evento: <?php echo htmlspecialchars($dados_relatorio['evento']['titulo']); ?></h5>
                    <div class="no-print">
                        <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="bg-light p-4 rounded h-100">
                            <h6 class="text-primary border-bottom border-primary pb-2 mb-3">ℹ️ Informações do Evento</h6>
                            <div class="d-grid gap-2">
                                <div class="p-3 bg-white rounded border-start border-primary border-3">
                                    <strong>Título:</strong> <?php echo htmlspecialchars($dados_relatorio['evento']['titulo']); ?>
                                </div>
                                <div class="p-3 bg-white rounded border-start border-primary border-3">
                                    <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($dados_relatorio['evento']['data_agendamento'])); ?>
                                </div>
                                <div class="p-3 bg-white rounded border-start border-primary border-3">
                                    <strong>Período:</strong> <?php echo htmlspecialchars($dados_relatorio['evento']['periodo']); ?>
                                </div>
                                <div class="p-3 bg-white rounded border-start border-primary border-3">
                                    <strong>Tipo:</strong> <?php echo ucfirst(str_replace('_', ' ', $dados_relatorio['evento']['tipo_agendamento'])); ?>
                                </div>
                                <?php if ($dados_relatorio['evento']['esporte_tipo']): ?>
                                <div class="p-3 bg-white rounded border-start border-primary border-3">
                                    <strong>Modalidade:</strong> <?php echo htmlspecialchars($dados_relatorio['evento']['esporte_tipo']); ?>
                                </div>
                                <?php endif; ?>
                                <div class="p-3 bg-white rounded border-start border-primary border-3">
                                    <strong>Responsável:</strong> <?php echo htmlspecialchars($dados_relatorio['evento']['responsavel']); ?>
                                </div>
                                <div class="p-3 bg-white rounded border-start border-primary border-3">
                                    <strong>Status:</strong>
                                    <span class="badge bg-<?php echo $dados_relatorio['evento']['status'] == 'aprovado' ? 'success' : ($dados_relatorio['evento']['status'] == 'pendente' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($dados_relatorio['evento']['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-4 rounded h-100">
                            <h6 class="text-primary border-bottom border-primary pb-2 mb-3">📊 Estatísticas de Participação</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="bg-white p-3 rounded text-center border border-2">
                                        <div class="stat-number"><?php echo $dados_relatorio['evento']['quantidade_pessoas'] ?: '0'; ?></div>
                                        <div class="text-muted small mt-1">Pessoas Estimadas</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="bg-white p-3 rounded text-center border border-2">
                                        <div class="stat-number"><?php echo $dados_relatorio['total_presencas']; ?></div>
                                        <div class="text-muted small mt-1">Presenças Confirmadas</div>
                                    </div>
                                </div>
                                <?php if ($dados_relatorio['evento']['atletica_confirmada']): ?>
                                <div class="col-12">
                                    <div class="stat-card atletica-confirmed p-3 rounded text-center border border-2">
                                        <div class="stat-number"><?php echo $dados_relatorio['evento']['quantidade_atletica']; ?></div>
                                        <div class="text-muted small mt-1">Pessoas da Atlética</div>
                                        <div class="text-success fw-bold small mt-1"><?php echo htmlspecialchars($dados_relatorio['evento']['atletica_confirmada_nome']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($dados_relatorio['presencas']->num_rows > 0): ?>
                <div class="bg-light p-4 rounded mt-4">
                    <h6 class="text-primary border-bottom border-primary pb-2 mb-3">👥 Lista de Presenças Confirmadas (<?php echo $dados_relatorio['total_presencas']; ?>)</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Tipo de Usuário</th>
                                    <th>Curso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; ?>
                                <?php while($presenca = $dados_relatorio['presencas']->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo htmlspecialchars($presenca['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($presenca['email']); ?></td>
                                        <td><?php echo htmlspecialchars($presenca['tipo_usuario_detalhado']); ?></td>
                                        <td><?php echo htmlspecialchars($presenca['curso_nome'] ?: 'N/A'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-light p-4 rounded mt-4">
                    <div class="alert alert-info text-center mb-0">
                        <i class="bi bi-info-circle"></i>
                        <strong>Nenhuma presença confirmada</strong><br>
                        Este evento ainda não possui presenças confirmadas.
                    </div>
                </div>
                <?php endif; ?>

                <div class="bg-light p-3 rounded mt-4 border-top border-2">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Relatório gerado em:</strong> <?php echo date('d/m/Y H:i:s'); ?>
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <strong>Sistema:</strong> SGA - UNIFIO
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($dados_relatorio) && $relatorio_tipo == 'geral'): ?>
    <div class="report-content">
        <div class="card shadow">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📈 Relatório Geral -
                        <?php echo date('d/m/Y', strtotime($dados_relatorio['periodo']['inicio'])); ?> a
                        <?php echo date('d/m/Y', strtotime($dados_relatorio['periodo']['fim'])); ?>
                    </h5>
                    <div class="no-print">
                        <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Resumo Executivo -->
                <div class="bg-gradient-primary-subtle p-4 rounded border mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="bg-white p-3 rounded border-start border-primary border-4 h-100">
                                <h6 class="text-primary mb-3">📅 Estatísticas Gerais</h6>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span>Total de Eventos:</span>
                                    <strong><?php echo $dados_relatorio['estatisticas']['total_eventos']; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span>Eventos Aprovados:</span>
                                    <strong class="text-success"><?php echo $dados_relatorio['estatisticas']['eventos_aprovados']; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span>Eventos Pendentes:</span>
                                    <strong class="text-warning"><?php echo $dados_relatorio['estatisticas']['eventos_pendentes']; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span>Eventos Rejeitados:</span>
                                    <strong class="text-danger"><?php echo $dados_relatorio['estatisticas']['eventos_rejeitados']; ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-white p-3 rounded border-start border-success border-4 h-100">
                                <h6 class="text-success mb-3">🏃‍♂️ Por Tipo de Evento</h6>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span>Eventos Esportivos:</span>
                                    <strong><?php echo $dados_relatorio['estatisticas']['eventos_esportivos']; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span>Eventos Não Esportivos:</span>
                                    <strong><?php echo $dados_relatorio['estatisticas']['eventos_nao_esportivos']; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span>Com Atlética Confirmada:</span>
                                    <strong class="text-info"><?php echo $dados_relatorio['estatisticas']['eventos_com_atletica']; ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-white p-3 rounded border-start border-info border-4 h-100">
                                <h6 class="text-info mb-3">👥 Participação Total</h6>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span>Pessoas Estimadas:</span>
                                    <strong><?php echo $dados_relatorio['estatisticas']['total_pessoas_estimadas'] ?: '0'; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span>Pessoas das Atléticas:</span>
                                    <strong><?php echo $dados_relatorio['estatisticas']['total_pessoas_atleticas'] ?: '0'; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span>Período Analisado:</span>
                                    <strong><?php echo floor((strtotime($dados_relatorio['periodo']['fim']) - strtotime($dados_relatorio['periodo']['inicio'])) / 86400) + 1; ?> dias</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <?php if ($dados_relatorio['modalidades']->num_rows > 0): ?>
                    <div class="col-md-6">
                        <div class="bg-light p-4 rounded">
                            <h6 class="text-primary border-bottom border-primary pb-2 mb-3">🏆 Eventos por Modalidade</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Modalidade</th>
                                            <th class="text-center">Quantidade</th>
                                            <th class="text-center">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total_modalidades = $dados_relatorio['estatisticas']['eventos_esportivos'];
                                        while($modalidade = $dados_relatorio['modalidades']->fetch_assoc()):
                                            $percentual = $total_modalidades > 0 ? round(($modalidade['total'] / $total_modalidades) * 100, 1) : 0;
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($modalidade['esporte_tipo']); ?></td>
                                                <td class="text-center"><strong><?php echo $modalidade['total']; ?></strong></td>
                                                <td class="text-center"><?php echo $percentual; ?>%</td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($dados_relatorio['atleticas']->num_rows > 0): ?>
                    <div class="col-md-6">
                        <div class="bg-light p-4 rounded">
                            <h6 class="text-primary border-bottom border-primary pb-2 mb-3">🏫 Participação por Atlética</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Atlética</th>
                                            <th class="text-center">Eventos Confirmados</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($atletica = $dados_relatorio['atleticas']->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($atletica['atletica_nome']); ?></td>
                                                <td class="text-center"><strong><?php echo $atletica['total']; ?></strong></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="bg-light p-4 rounded mt-4">
                    <h6 class="text-primary border-bottom border-primary pb-2 mb-3">📋 Lista Completa de Eventos</h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-nowrap">Data</th>
                                    <th>Título</th>
                                    <th class="text-center d-none d-md-table-cell">Tipo</th>
                                    <th class="text-center">Status</th>
                                    <th class="d-none d-lg-table-cell">Responsável</th>
                                    <th class="text-center d-none d-sm-table-cell">Pessoas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($evento = $dados_relatorio['eventos']->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-nowrap small"><?php echo date('d/m/Y', strtotime($evento['data_agendamento'])); ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($evento['titulo']); ?></div>
                                            <div class="d-md-none">
                                                <small class="text-muted">
                                                    <span class="badge bg-<?php echo $evento['tipo_agendamento'] == 'esportivo' ? 'primary' : 'secondary'; ?> me-1">
                                                        <?php echo ucfirst(str_replace('_', ' ', $evento['tipo_agendamento'])); ?>
                                                    </span>
                                                    <span class="d-lg-none"><?php echo htmlspecialchars($evento['responsavel']); ?></span>
                                                </small>
                                            </div>
                                        </td>
                                        <td class="text-center d-none d-md-table-cell">
                                            <span class="badge bg-<?php echo $evento['tipo_agendamento'] == 'esportivo' ? 'primary' : 'secondary'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $evento['tipo_agendamento'])); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo $evento['status'] == 'aprovado' ? 'success' : ($evento['status'] == 'pendente' ? 'warning text-dark' : 'danger'); ?>">
                                                <?php echo ucfirst($evento['status']); ?>
                                            </span>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <small><?php echo htmlspecialchars($evento['responsavel']); ?></small>
                                        </td>
                                        <td class="text-center d-none d-sm-table-cell">
                                            <span class="badge bg-info"><?php echo $evento['quantidade_pessoas'] ?: '0'; ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Nota informativa para dispositivos móveis -->
                    <div class="d-md-none mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Em dispositivos móveis, informações adicionais são exibidas abaixo do título.
                        </small>
                    </div>
                </div>

                <div class="bg-light p-3 rounded mt-4 border-top border-2">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">
                                <strong>Período:</strong> <?php echo date('d/m/Y', strtotime($dados_relatorio['periodo']['inicio'])); ?> a <?php echo date('d/m/Y', strtotime($dados_relatorio['periodo']['fim'])); ?>
                            </small>
                        </div>
                        <div class="col-md-4 text-center">
                            <small class="text-muted">
                                <strong>Relatório gerado em:</strong> <?php echo date('d/m/Y H:i:s'); ?>
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted">
                                <strong>Sistema:</strong> SGA - UNIFIO
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Apenas estilos essenciais que o Bootstrap não cobre */
.report-page {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.stat-card.atletica-confirmed {
    border-color: #28a745;
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
}

/* Estilos para impressão */
@media print {
    .no-print, .btn, .card-header .btn {
        display: none !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }

    .page-header {
        background: #f8f9fa !important;
        color: #333 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .summary-card, .table {
        page-break-inside: avoid;
    }

    .report-content {
        page-break-before: always;
    }

    body {
        font-size: 12px;
    }
}
</style>

<?php include '../../templates/footer.php'; ?>
