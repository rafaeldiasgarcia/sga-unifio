-- Atualiza o enum atletica_join_status para incluir o status 'aprovado'
ALTER TABLE usuarios MODIFY COLUMN atletica_join_status ENUM('none', 'pendente', 'aprovado') DEFAULT 'none';