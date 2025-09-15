<?php
// Função para gerar o calendário de eventos
function gerarCalendario($conexao, $mes = null, $ano = null) {
    // Se não especificado, usar mês e ano atual
    if ($mes === null) $mes = date('n'); // 'n' retorna mês sem zero à esquerda
    if ($ano === null) $ano = date('Y');

    // Converter para inteiro para evitar problemas com strings
    $mes = (int)$mes;
    $ano = (int)$ano;

    // Buscar agendamentos aprovados do mês
    $sql = "SELECT data_agendamento, periodo FROM agendamentos 
            WHERE status = 'aprovado' 
            AND MONTH(data_agendamento) = ? 
            AND YEAR(data_agendamento) = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $mes, $ano);
    $stmt->execute();
    $result = $stmt->get_result();

    // Organizar agendamentos por data
    $agendamentos = [];
    while ($row = $result->fetch_assoc()) {
        $data = $row['data_agendamento'];
        if (!isset($agendamentos[$data])) {
            $agendamentos[$data] = [];
        }
        $agendamentos[$data][] = $row['periodo'];
    }

    // Calcular informações do calendário
    $primeiro_dia = mktime(0, 0, 0, $mes, 1, $ano);
    $dias_no_mes = date('t', $primeiro_dia);
    $dia_semana_inicio = date('w', $primeiro_dia); // 0 = domingo
    $nomes_mes = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
    ];

    $mes_anterior = $mes - 1;
    $ano_anterior = $ano;
    if ($mes_anterior < 1) {
        $mes_anterior = 12;
        $ano_anterior--;
    }

    $mes_proximo = $mes + 1;
    $ano_proximo = $ano;
    if ($mes_proximo > 12) {
        $mes_proximo = 1;
        $ano_proximo++;
    }
// Renderizar o calendário stilizado com Bootstrap
    echo '<div class="calendario-container border rounded p-3" style="width: auto; background-color: #f8f9fa;">';
    echo '<h6 class="text-muted mb-3">Calendário</h6>';

    // Cabeçalho com navegação
    echo '<div class="d-flex justify-content-between align-items-center mb-3">';
    echo '<button class="btn btn-outline-secondary btn-sm" onclick="navegarCalendario(' . $mes_anterior . ', ' . $ano_anterior . ')">&lt;</button>';
    echo '<h6 class="mb-0 text-muted">' . $nomes_mes[$mes] . ' ' . $ano . '</h6>';
    echo '<button class="btn btn-outline-secondary btn-sm" onclick="navegarCalendario(' . $mes_proximo . ', ' . $ano_proximo . ')">&gt;</button>';
    echo '</div>';

    // Cabeçalho dos dias da semana
    echo '<div class="row g-1 mb-2">';
    $dias_semana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
    foreach ($dias_semana as $dia) {
        echo '<div class="col text-center"><small class="fw-bold text-muted">' . $dia . '</small></div>';
    }
    echo '</div>';

    // Dias do calendário
    $dia_atual = 1;
    $linha = 0;

    while ($dia_atual <= $dias_no_mes) {
        echo '<div class="row g-1 mb-1">';

        // Preencher células vazias no início (primeira linha)
        if ($linha == 0) {
            for ($i = 0; $i < $dia_semana_inicio; $i++) {
                echo '<div class="col"><div style="height: 40px;"></div></div>';
            }
        }

        // Preencher os dias da semana
        for ($col = ($linha == 0 ? $dia_semana_inicio : 0); $col < 7 && $dia_atual <= $dias_no_mes; $col++) {
            // Determinar cor do dia baseado nos agendamentos
            $data_completa = sprintf('%04d-%02d-%02d', $ano, $mes, $dia_atual);
            $classe_bg = 'bg-light';
            $classe_texto = 'text-dark';
            $title = '';

            if (isset($agendamentos[$data_completa])) {
                $periodos_agendados = count($agendamentos[$data_completa]);
                if ($periodos_agendados == 1) {
                    $classe_bg = 'bg-warning'; // Laranja
                    $classe_texto = 'text-white';
                    $title = 'Um período agendado';
                } else if ($periodos_agendados >= 2) {
                    $classe_bg = 'bg-danger'; // Vermelho
                    $classe_texto = 'text-white';
                    $title = 'Ambos períodos agendados';
                }
            }

            echo '<div class="col">';
            echo '<div class="d-flex justify-content-center align-items-center ' . $classe_bg . ' ' . $classe_texto . ' rounded" style="height: 40px; min-width: 40px; cursor: pointer;" title="' . $title . '">';
            echo '<small>' . $dia_atual . '</small>';
            echo '</div>';
            echo '</div>';

            $dia_atual++;
        }

        // Preencher células vazias no final (última linha)
        if ($dia_atual > $dias_no_mes) {
            for ($i = $col; $i < 7; $i++) {
                echo '<div class="col"><div style="height: 40px;"></div></div>';
            }
        }

        echo '</div>';
        $linha++;
    }

    // Legenda
    echo '<div class="mt-3">';
    echo '<div class="row g-2">';
    echo '<div class="col-12">';
    echo '<div class="d-flex align-items-center mb-1">';
    echo '<div class="border rounded me-2" style="width: 15px; height: 15px; background-color: transparent;"></div>';
    echo '<small class="text-muted">DIA LIVRE</small>';
    echo '</div>';
    echo '<div class="d-flex align-items-center mb-1">';
    echo '<div class="bg-warning rounded me-2" style="width: 15px; height: 15px;"></div>';
    echo '<small class="text-muted">UM PERÍODO LIVRE</small>';
    echo '</div>';
    echo '<div class="d-flex align-items-center">';
    echo '<div class="bg-danger rounded me-2" style="width: 15px; height: 15px;"></div>';
    echo '<small class="text-muted">DIA NÃO DISPONÍVEL</small>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    echo '</div>';
}
?>

<script>
function navegarCalendario(mes, ano) {
    // Fazer requisição AJAX para atualizar apenas o calendário
    const calendarioElement = document.querySelector('.calendario-container').parentElement;

    // Mostrar loading
    calendarioElement.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Carregando...</span></div></div>';

    // Fazer a requisição
    fetch(`?mes=${mes}&ano=${ano}`, {
        method: 'GET',
        headers: {
            'X-Calendar-Update': 'true'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Extrair apenas o conteúdo do calendário da resposta
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const novoCalendario = doc.querySelector('.calendario-container').parentElement;

        if (novoCalendario) {
            calendarioElement.innerHTML = novoCalendario.innerHTML;
        }
    })
    .catch(error => {
        console.error('Erro ao carregar calendário:', error);
        // Fallback para recarregamento da página
        const url = new URL(window.location);
        url.searchParams.set('mes', mes);
        url.searchParams.set('ano', ano);
        window.location.href = url.toString();
    });
}
</script>

