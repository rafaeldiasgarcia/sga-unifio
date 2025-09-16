<?php
require_once '../../src/config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    try {
        // Verificar se a tabela de notificações existe
        $table_check = $conexao->query("SHOW TABLES LIKE 'notificacoes'");

        if ($table_check->num_rows > 0) {
            if (isset($input['notification_id'])) {
                // Marcar uma notificação específica como lida
                $notification_id = (int)$input['notification_id'];

                $sql = "UPDATE notificacoes SET lida = 1 WHERE id = ? AND usuario_id = ?";
                $stmt = $conexao->prepare($sql);
                $stmt->bind_param("ii", $notification_id, $usuario_id);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Notificação marcada como lida']);
                } else {
                    echo json_encode(['error' => 'Erro ao marcar notificação como lida']);
                }

            } else {
                // Marcar todas as notificações como lidas
                $sql = "UPDATE notificacoes SET lida = 1 WHERE usuario_id = ? AND lida = 0";
                $stmt = $conexao->prepare($sql);
                $stmt->bind_param("i", $usuario_id);

                if ($stmt->execute()) {
                    $affected_rows = $stmt->affected_rows;
                    echo json_encode([
                        'success' => true,
                        'message' => "$affected_rows notificações marcadas como lidas"
                    ]);
                } else {
                    echo json_encode(['error' => 'Erro ao marcar notificações como lidas']);
                }
            }
        } else {
            // Tabela não existe
            echo json_encode([
                'success' => true,
                'message' => 'Tabela de notificações não encontrada. Execute o script create_notificacoes_table.sql no phpMyAdmin.'
            ]);
        }

    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Método não permitido']);
}
?>
