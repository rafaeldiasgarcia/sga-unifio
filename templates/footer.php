</main>

<footer class="bg-dark text-white mt-5">
    <div class="container p-4">
        <div class="row">
            <div class="col-lg-5 col-md-12 mb-4 mb-md-0">
                <h5 class="text-uppercase mb-4">SGA UNIFIO</h5>
                <p>
                    Rodovia BR 153, Km 338+420m,<br>
                    Bairro Água do Cateto, Ourinhos-SP.<br>
                    CEP 19909-100
                </p>
            </div>
            <div class="col-lg-7 col-md-12 mb-4 mb-md-0">
                <h5 class="text-uppercase mb-4">Atendimento - Fale Conosco</h5>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Pró-Reitoria UNIFIO</strong><br>
                        <a href="mailto:email@unifio.edu.br" class="text-white">email@unifio.edu.br</a>
                    </div>
                    <div class="col-md-4">
                        <strong>Secretaria UNIFIO</strong><br>
                        (14) 3302-6400
                    </div>
                    <div class="col-md-4">
                        <strong>Coordenação Ed. Física</strong><br>
                        <a href="mailto:email@unifio.edu.br" class="text-white">email@unifio.edu.br</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
        © <?php echo date("Y"); ?> SGA - Sistema de Gerenciamento de Atléticas
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Sistema de Notificações -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationsList = document.getElementById('notificationsList');
    const markAllReadBtn = document.getElementById('markAllRead');

    // Função para carregar notificações
    function loadNotifications() {
        fetch('/sga/public/scripts/get_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationDisplay(data.notificacoes, data.nao_lidas);
                } else {
                    console.error('Erro ao carregar notificações:', data.error);
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
            });
    }

    // Função para atualizar a exibição das notificações
    function updateNotificationDisplay(notificacoes, naoLidas) {
        // Atualizar badge
        if (naoLidas > 0) {
            notificationBadge.textContent = naoLidas > 99 ? '99+' : naoLidas;
            notificationBadge.style.display = 'block';
        } else {
            notificationBadge.style.display = 'none';
        }

        // Atualizar lista de notificações
        if (notificacoes.length === 0) {
            notificationsList.innerHTML = `
                <div class="dropdown-item text-muted text-center">
                    <i class="bi bi-bell-slash"></i> Nenhuma notificação
                </div>
            `;
        } else {
            notificationsList.innerHTML = notificacoes.map(notificacao => {
                const isUnread = !notificacao.lida;
                const bgClass = isUnread ? 'bg-light' : '';
                const boldClass = isUnread ? 'fw-bold' : '';

                return `
                    <div class="dropdown-item ${bgClass}" data-notification-id="${notificacao.id}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 ${boldClass}">${notificacao.titulo}</h6>
                                <p class="mb-1 small text-muted">${notificacao.mensagem}</p>
                                <small class="text-muted">${notificacao.data_formatada}</small>
                            </div>
                            ${isUnread ? '<span class="badge bg-primary rounded-pill ms-2">Nova</span>' : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }
    }

    // Função para marcar notificação como lida
    function markNotificationAsRead(notificationId) {
        fetch('/sga/public/scripts/mark_notifications_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications(); // Recarregar notificações
            }
        })
        .catch(error => {
            console.error('Erro ao marcar notificação como lida:', error);
        });
    }

    // Função para marcar todas as notificações como lidas
    function markAllNotificationsAsRead() {
        fetch('/sga/public/scripts/mark_notifications_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications(); // Recarregar notificações
            }
        })
        .catch(error => {
            console.error('Erro ao marcar todas as notificações como lidas:', error);
        });
    }

    // Event listeners
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', markAllNotificationsAsRead);
    }

    // Marcar notificação individual como lida ao clicar
    if (notificationsList) {
        notificationsList.addEventListener('click', function(e) {
            const notificationItem = e.target.closest('[data-notification-id]');
            if (notificationItem) {
                const notificationId = notificationItem.getAttribute('data-notification-id');
                markNotificationAsRead(notificationId);
            }
        });
    }

    // Carregar notificações inicialmente
    loadNotifications();

    // Atualizar notificações a cada 30 segundos
    setInterval(loadNotifications, 30000);
});
</script>
</body>
</html>