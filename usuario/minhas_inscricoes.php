<?php
require_once '../config.php';
is_aluno();

$aluno_id = $_SESSION['id'];

// Buscar inscrições e equipes do usuario
$sql = "SELECT 
            m.nome as modalidade_nome, 
            i.status,
            eq.nome as equipe_nome
        FROM inscricoes_modalidade i
        JOIN modalidades m ON i.modalidade_id = m.id
        LEFT JOIN equipe_membros em ON i.aluno_id = em.aluno_id
        LEFT JOIN equipes eq ON em.equipe_id = eq.id AND eq.modalidade_id = m.id
        WHERE i.aluno_id = ?
        ORDER BY m.nome";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $aluno_id);
$stmt->execute();
$inscricoes = $stmt->get_result();
?>

<?php include '../templates/header.php'; ?>

    <h1>Minhas Inscrições e Equipes</h1>
    <p>Acompanhe o status de suas candidaturas e veja em quais equipes você foi alocado.</p>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Modalidade</th>
                        <th>Status da Inscrição</th>
                        <th>Equipe</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($inscricoes->num_rows > 0): ?>
                        <?php while($inscricao = $inscricoes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($inscricao['modalidade_nome']); ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                    switch($inscricao['status']) {
                                        case 'aprovado': echo 'success'; break;
                                        case 'recusado': echo 'danger'; break;
                                        default: echo 'warning text-dark'; break;
                                    }
                                    ?>"><?php echo ucfirst($inscricao['status']); ?></span>
                                </td>
                                <td>
                                    <?php if ($inscricao['status'] == 'aprovado'): ?>
                                        <?php echo htmlspecialchars($inscricao['equipe_nome'] ?? '<em>Aguardando alocação</em>'); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">Você ainda não se inscreveu em nenhuma modalidade.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>