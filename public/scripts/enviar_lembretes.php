<?php
require_once '../../src/config/config.php';

// Este script deve ser executado via cron job ou chamado periodicamente
// para enviar lembretes de eventos próximos

function enviarLembrete($evento, $usuarios) {
    // Aqui você implementaria o envio real de email/notificação
    // Por enquanto, vamos apenas registrar no log ou banco de dados

    foreach ($usuarios as $usuario) {
        echo "Lembrete enviado para {$usuario['nome']} ({$usuario['email']}) sobre o evento '{$evento['titulo']}' em " . date('d/m/Y', strtotime($evento['data_agendamento'])) . "\n";

        // Opcional: Inserir notificação no banco de dados se você tiver uma tabela de notificações
        // insertNotification($usuario['id'], $evento);
    }
}

function insertNotification($usuario_id, $evento) {
    global $conexao;

    // Verifica se existe tabela de notificações (caso você tenha uma)
    $table_check = $conexao->query("SHOW TABLES LIKE 'notificacoes'");
    if ($table_check->num_rows > 0) {
        $titulo = "Lembrete: " . $evento['titulo'];
        $mensagem = "Não se esqueça do evento '{$evento['titulo']}' agendado para " .
                   date('d/m/Y', strtotime($evento['data_agendamento'])) .
                   " no período " . $evento['periodo'];

        $sql = "INSERT INTO notificacoes (usuario_id, titulo, mensagem, tipo, agendamento_id) VALUES (?, ?, ?, 'lembrete', ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("issi", $usuario_id, $titulo, $mensagem, $evento['id']);
        $stmt->execute();
    }
}

try {
    // Buscar eventos que acontecerão amanhã
    $data_amanha = date('Y-m-d', strtotime('+1 day'));

    $sql = "SELECT a.id, a.titulo, a.tipo_agendamento, a.esporte_tipo, a.data_agendamento, a.periodo
            FROM agendamentos a
            WHERE a.status = 'aprovado' 
            AND a.data_agendamento = ?
            ORDER BY a.periodo ASC";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $data_amanha);
    $stmt->execute();
    $eventos = $stmt->get_result();

    if ($eventos->num_rows > 0) {
        echo "Encontrados " . $eventos->num_rows . " eventos para amanhã ($data_amanha)\n";

        while ($evento = $eventos->fetch_assoc()) {
            echo "\nProcessando evento: " . $evento['titulo'] . "\n";

            // Buscar usuários que marcaram presença neste evento
            $sql_presencas = "SELECT DISTINCT u.id, u.nome, u.email
                             FROM presencas p
                             JOIN usuarios u ON p.usuario_id = u.id
                             WHERE p.agendamento_id = ?";

            $stmt_presencas = $conexao->prepare($sql_presencas);
            $stmt_presencas->bind_param("i", $evento['id']);
            $stmt_presencas->execute();
            $usuarios_com_presenca = $stmt_presencas->get_result();

            if ($usuarios_com_presenca->num_rows > 0) {
                $usuarios = [];
                while ($usuario = $usuarios_com_presenca->fetch_assoc()) {
                    $usuarios[] = $usuario;
                }

                enviarLembrete($evento, $usuarios);

                // Inserir notificações no banco
                foreach ($usuarios as $usuario) {
                    insertNotification($usuario['id'], $evento);
                }
            } else {
                echo "Nenhum usuário marcou presença neste evento.\n";
            }
        }
    } else {
        echo "Nenhum evento encontrado para amanhã ($data_amanha)\n";
    }

    echo "\nScript de lembretes executado com sucesso!\n";

} catch (Exception $e) {
    echo "Erro ao executar script de lembretes: " . $e->getMessage() . "\n";
}
?>
