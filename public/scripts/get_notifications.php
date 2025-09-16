<?php
require_once '../../src/config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['id'];

try {
    // Verificar se a tabela de notificações existe
    $table_check = $conexao->query("SHOW TABLES LIKE 'notificacoes'");

    if ($table_check->num_rows > 0) {
        // Buscar notificações do usuário
        $sql = "SELECT n.id, n.titulo, n.mensagem, n.tipo, n.data_criacao, n.lida,
                       a.titulo as agendamento_titulo, a.data_agendamento
                FROM notificacoes n
                LEFT JOIN agendamentos a ON n.agendamento_id = a.id
                WHERE n.usuario_id = ? 
                ORDER BY n.data_criacao DESC
                LIMIT 10";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $notificacoes = [];
        $nao_lidas = 0;

        while ($row = $result->fetch_assoc()) {
            if (!$row['lida']) {
                $nao_lidas++;
            }

            // Formatar a data
            $row['data_formatada'] = date('d/m/Y H:i', strtotime($row['data_criacao']));

            $notificacoes[] = $row;
        }

        echo json_encode([
            'success' => true,
            'notificacoes' => $notificacoes,
            'nao_lidas' => $nao_lidas
        ]);
    } else {
        // Tabela não existe, retornar vazio mas sem erro
        echo json_encode([
            'success' => true,
            'notificacoes' => [],
            'nao_lidas' => 0,
            'message' => 'Tabela de notificações não encontrada. Execute o script create_notificacoes_table.sql no phpMyAdmin.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Erro ao buscar notificações: ' . $e->getMessage()
    ]);
}
?>
