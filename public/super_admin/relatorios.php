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
                        <div class="info-section">
                            <h6 class="section-title">ℹ️ Informações do Evento</h6>
                            <div class="info-grid">
                                <div class="info-item">
                                    <strong>Título:</strong> <?php echo htmlspecialchars($dados_relatorio['evento']['titulo']); ?>
                                </div>
                                <div class="info-item">
                                    <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($dados_relatorio['evento']['data_agendamento'])); ?>
                                </div>
                                <div class="info-item">
                                    <strong>Período:</strong> <?php echo htmlspecialchars($dados_relatorio['evento']['periodo']); ?>
                                </div>
                                <div class="info-item">
                                    <strong>Tipo:</strong> <?php echo ucfirst(str_replace('_', ' ', $dados_relatorio['evento']['tipo_agendamento'])); ?>
                                </div>
                                <?php if ($dados_relatorio['evento']['esporte_tipo']): ?>
                                <div class="info-item">
                                    <strong>Modalidade:</strong> <?php echo htmlspecialchars($dados_relatorio['evento']['esporte_tipo']); ?>
                                </div>
                                <?php endif; ?>
                                <div class="info-item">
                                    <strong>Responsável:</strong> <?php echo htmlspecialchars($dados_relatorio['evento']['responsavel']); ?>
                                </div>
                                <div class="info-item">
                                    <strong>Status:</strong>
                                    <span class="badge bg-<?php echo $dados_relatorio['evento']['status'] == 'aprovado' ? 'success' : ($dados_relatorio['evento']['status'] == 'pendente' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($dados_relatorio['evento']['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stats-section">
                            <h6 class="section-title">📊 Estatísticas de Participação</h6>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo $dados_relatorio['evento']['quantidade_pessoas'] ?: '0'; ?></div>
                                    <div class="stat-label">Pessoas Estimadas</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo $dados_relatorio['total_presencas']; ?></div>
                                    <div class="stat-label">Presenças Confirmadas</div>
                                </div>
                                <?php if ($dados_relatorio['evento']['atletica_confirmada']): ?>
                                <div class="stat-card atletica-confirmed">
                                    <div class="stat-number"><?php echo $dados_relatorio['evento']['quantidade_atletica']; ?></div>
                                    <div class="stat-label">Pessoas da Atlética</div>
                                    <div class="stat-sublabel"><?php echo htmlspecialchars($dados_relatorio['evento']['atletica_confirmada_nome']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($dados_relatorio['presencas']->num_rows > 0): ?>
                <div class="participants-section mt-4">
                    <h6 class="section-title">👥 Lista de Presenças Confirmadas (<?php echo $dados_relatorio['total_presencas']; ?>)</h6>
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
                <div class="no-data-section mt-4">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i>
                        <strong>Nenhuma presença confirmada</strong><br>
                        Este evento ainda não possui presenças confirmadas.
                    </div>
                </div>
                <?php endif; ?>

                <div class="report-footer mt-4 pt-3 border-top">
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
                <div class="executive-summary mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="summary-card bg-primary">
                                <h6>📅 Estatísticas Gerais</h6>
                                <div class="summary-stats">
                                    <div class="stat-row">
                                        <span>Total de Eventos:</span>
                                        <strong><?php echo $dados_relatorio['estatisticas']['total_eventos']; ?></strong>
                                    </div>
                                    <div class="stat-row">
                                        <span>Eventos Aprovados:</span>
                                        <strong class="text-success"><?php echo $dados_relatorio['estatisticas']['eventos_aprovados']; ?></strong>
                                    </div>
                                    <div class="stat-row">
                                        <span>Eventos Pendentes:</span>
                                        <strong class="text-warning"><?php echo $dados_relatorio['estatisticas']['eventos_pendentes']; ?></strong>
                                    </div>
                                    <div class="stat-row">
                                        <span>Eventos Rejeitados:</span>
                                        <strong class="text-danger"><?php echo $dados_relatorio['estatisticas']['eventos_rejeitados']; ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card bg-success">
                                <h6>🏃‍♂️ Por Tipo de Evento</h6>
                                <div class="summary-stats">
                                    <div class="stat-row">
                                        <span>Eventos Esportivos:</span>
                                        <strong><?php echo $dados_relatorio['estatisticas']['eventos_esportivos']; ?></strong>
                                    </div>
                                    <div class="stat-row">
                                        <span>Eventos Não Esportivos:</span>
                                        <strong><?php echo $dados_relatorio['estatisticas']['eventos_nao_esportivos']; ?></strong>
                                    </div>
                                    <div class="stat-row">
                                        <span>Com Atlética Confirmada:</span>
                                        <strong class="text-info"><?php echo $dados_relatorio['estatisticas']['eventos_com_atletica']; ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card bg-info">
                                <h6>👥 Participação Total</h6>
                                <div class="summary-stats">
                                    <div class="stat-row">
                                        <span>Pessoas Estimadas:</span>
                                        <strong><?php echo $dados_relatorio['estatisticas']['total_pessoas_estimadas'] ?: '0'; ?></strong>
                                    </div>
                                    <div class="stat-row">
                                        <span>Pessoas das Atléticas:</span>
                                        <strong><?php echo $dados_relatorio['estatisticas']['total_pessoas_atleticas'] ?: '0'; ?></strong>
                                    </div>
                                    <div class="stat-row">
                                        <span>Período Analisado:</span>
                                        <strong><?php echo floor((strtotime($dados_relatorio['periodo']['fim']) - strtotime($dados_relatorio['periodo']['inicio'])) / 86400) + 1; ?> dias</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php if ($dados_relatorio['modalidades']->num_rows > 0): ?>
                    <div class="col-md-6">
                        <div class="data-section">
                            <h6 class="section-title">🏆 Eventos por Modalidade</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
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
                        <div class="data-section">
                            <h6 class="section-title">🏫 Participação por Atlética</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
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

                <div class="events-list-section mt-4">
                    <h6 class="section-title">📋 Lista Completa de Eventos</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data</th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Responsável</th>
                                    <th class="text-center">Pessoas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($evento = $dados_relatorio['eventos']->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($evento['data_agendamento'])); ?></td>
                                        <td><?php echo htmlspecialchars($evento['titulo']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $evento['tipo_agendamento'] == 'esportivo' ? 'primary' : 'secondary'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $evento['tipo_agendamento'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $evento['status'] == 'aprovado' ? 'success' : ($evento['status'] == 'pendente' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($evento['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($evento['responsavel']); ?></td>
                                        <td class="text-center"><?php echo $evento['quantidade_pessoas'] ?: '-'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="report-footer mt-4 pt-3 border-top">
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
.report-page {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 2rem;
    border-radius: 0.5rem;
    margin-bottom: 2rem;
}

.info-section, .stats-section, .data-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.5rem;
    height: 100%;
}

.section-title {
    color: #495057;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #007bff;
}

.info-grid .info-item {
    margin-bottom: 0.75rem;
    padding: 0.5rem;
    background: white;
    border-radius: 0.25rem;
    border-left: 3px solid #007bff;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.stat-card {
    background: white;
    padding: 1rem;
    border-radius: 0.5rem;
    text-align: center;
    border: 2px solid #e9ecef;
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.stat-card.atletica-confirmed {
    border-color: #28a745;
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #007bff;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.stat-sublabel {
    font-size: 0.75rem;
    color: #28a745;
    font-weight: bold;
    margin-top: 0.25rem;
}

.executive-summary {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 2rem;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
}

.summary-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    height: 100%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid;
}

.summary-card.bg-primary {
    border-left-color: #007bff;
}

.summary-card.bg-success {
    border-left-color: #28a745;
}

.summary-card.bg-info {
    border-left-color: #17a2b8;
}

.summary-card h6 {
    color: #495057;
    margin-bottom: 1rem;
    font-weight: bold;
}

.summary-stats .stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.summary-stats .stat-row:last-child {
    border-bottom: none;
}

.participants-section, .events-list-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.5rem;
}

.no-data-section {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 0.5rem;
}

.report-footer {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-top: 2rem;
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

    .summary-card {
        page-break-inside: avoid;
    }

    .table {
        page-break-inside: avoid;
    }

    .report-content {
        page-break-before: always;
    }

    body {
        font-size: 12px;
    }

    .section-title {
        color: #333 !important;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .executive-summary .row {
        flex-direction: column;
    }

    .summary-card {
        margin-bottom: 1rem;
    }
}
</style>

<?php include '../../templates/footer.php'; ?>
