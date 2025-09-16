-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16/09/2025 às 08:03
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

-- esvaziando as tabelas para garantir que não haja conflitos
DELETE FROM `professores_cursos`;
DELETE FROM `presencas`;
DELETE FROM `inscricoes_modalidade`;
DELETE FROM `inscricoes_eventos`;
DELETE FROM `equipe_membros`;
DELETE FROM `equipes`;
DELETE FROM `eventos`;
DELETE FROM `agendamentos`;
DELETE FROM `usuarios` WHERE `role` != 'superadmin'; -- Mantém o superadmin original
DELETE FROM `cursos`;
DELETE FROM `atleticas`;


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sga_db`
--

--
-- Populando a tabela `atleticas`
--
INSERT INTO `atleticas` (`id`, `nome`, `descricao`, `logo_url`) VALUES
                                                                    (1, 'Atlética de Engenharia Elétrica - Faísca', 'A atlética mais energizada do campus.', 'https://example.com/logo_eletrica.png'),
                                                                    (2, 'Atlética de Ciência da Computação - A.T.I', 'Conectando mentes e promovendo o esporte.', 'https://example.com/logo_comp.png'),
                                                                    (3, 'Atlética de Direito - Lex', 'Pela honra e pela glória do esporte e da justiça.', 'https://example.com/logo_direito.png'),
                                                                    (4, 'Atlética de Medicina - Med', 'Saúde em primeiro lugar, dentro e fora das quadras.', 'https://example.com/logo_medicina.png'),
                                                                    (5, 'Atlética de Arquitetura e Urbanismo - Traço', 'Construindo vitórias e grandes amizades.', 'https://example.com/logo_arq.png'),
                                                                    (6, 'Atlética de Psicologia - Psique', 'Mente sã, corpo são e muita garra no esporte.', 'https://example.com/logo_psico.png'),
                                                                    (7, 'Atlética de Educação Física - Movimento', 'O corpo alcança o que a mente acredita.', 'https://example.com/logo_edfisica.png'),
                                                                    (8, 'Atlética de Relações Internacionais - Diplomacia', 'Unindo nações através do esporte.', 'https://example.com/logo_ri.png'),
                                                                    (9, 'Atlética de Engenharia Civil - Concreta', 'Fortes como concreto, unidos pela vitória.', 'https://example.com/logo_civil.png'),
                                                                    (10, 'Atlética de Administração - Gestores', 'Planejando o sucesso, executando a vitória.', 'https://example.com/logo_adm.png');

--
-- Populando a tabela `cursos`
--
INSERT INTO `cursos` (`id`, `nome`, `atletica_id`, `coordenador_id`) VALUES
                                                                         (1, 'Engenharia Elétrica', 1, NULL),
                                                                         (2, 'Ciência da Computação', 2, NULL),
                                                                         (3, 'Direito', 3, NULL),
                                                                         (4, 'Medicina', 4, NULL),
                                                                         (5, 'Arquitetura e Urbanismo', 5, NULL),
                                                                         (6, 'Psicologia', 6, NULL),
                                                                         (7, 'Educação Física', 7, NULL),
                                                                         (8, 'Relações Internacionais', 8, NULL),
                                                                         (9, 'Engenharia Civil', 9, NULL),
                                                                         (10, 'Administração', 10, NULL);

--
-- Populando a tabela `usuarios`
--
-- IDs de 2 a 12 serão para alunos, 13-14 para professores, 15 para comunidade externa, 16 para admin
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `ra`, `data_nascimento`, `curso_id`, `role`, `atletica_id`, `tipo_usuario_detalhado`, `is_coordenador`, `atletica_join_status`) VALUES
                                                                                                                                                                                            (2, 'Admin Geral', 'admin@sga.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', NULL, '1990-01-01', NULL, 'admin', NULL, NULL, 0, 'none'),
                                                                                                                                                                                            (3, 'João Silva', 'joao.silva@aluno.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', '123456', '2002-05-10', 2, 'usuario', 2, 'Aluno', 0, 'aprovado'),
                                                                                                                                                                                            (4, 'Maria Oliveira', 'maria.oliveira@aluno.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', '234567', '2001-08-22', 1, 'usuario', 1, 'Aluno', 0, 'aprovado'),
                                                                                                                                                                                            (5, 'Pedro Martins', 'pedro.martins@aluno.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', '345678', '2003-02-28', 3, 'usuario', 3, 'Aluno', 0, 'aprovado'),
                                                                                                                                                                                            (6, 'Juliana Santos', 'juliana.santos@aluno.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', '456789', '2000-07-12', 4, 'usuario', 4, 'Membro das Atléticas', 0, 'aprovado'),
                                                                                                                                                                                            (7, 'Fernanda Almeida', 'fernanda.almeida@aluno.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', '567890', '2002-12-01', 5, 'usuario', 5, 'Aluno', 0, 'pendente'),
                                                                                                                                                                                            (8, 'Ricardo Souza', 'ricardo.souza@aluno.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', '678901', '2001-04-18', 6, 'usuario', 6, 'Membro das Atléticas', 0, 'aprovado'),
                                                                                                                                                                                            (9, 'Lucas Ferreira', 'lucas.ferreira@aluno.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', '789012', '2003-01-20', 7, 'usuario', 7, 'Aluno', 0, 'aprovado'),
                                                                                                                                                                                            (10, 'Beatriz Gonçalves', 'beatriz.goncalves@aluno.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', '890123', '2002-06-14', 8, 'usuario', 8, 'Membro das Atléticas', 0, 'aprovado'),
                                                                                                                                                                                            (11, 'Gabriel Ribeiro', 'gabriel.ribeiro@aluno.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', '901234', '2001-10-09', 9, 'usuario', 9, 'Aluno', 0, 'aprovado'),
                                                                                                                                                                                            (12, 'Laura Azevedo', 'laura.azevedo@aluno.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', '012345', '2000-03-25', 10, 'usuario', 10, 'Membro das Atléticas', 0, 'aprovado'),
                                                                                                                                                                                            (13, 'Carlos Pereira (Professor)', 'carlos.pereira@professor.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', NULL, '1985-03-15', NULL, 'usuario', NULL, 'Professor', 0, 'none'),
                                                                                                                                                                                            (14, 'Marcos Lima (Professor Coordenador)', 'marcos.lima@professor.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', NULL, '1978-09-05', NULL, 'usuario', NULL, 'Professor', 1, 'none'),
                                                                                                                                                                                            (15, 'Ana Costa (Comunidade Externa)', 'ana.costa@externo.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', NULL, '1995-11-30', NULL, 'usuario', NULL, 'Comunidade Externa', 0, 'none');

--
-- Atualizando a tabela `cursos` com os coordenadores
--
UPDATE `cursos` SET `coordenador_id` = 14 WHERE `id` = 2; -- Marcos Lima é coordenador de Ciência da Computação
UPDATE `cursos` SET `coordenador_id` = 13 WHERE `id` = 1; -- Carlos Pereira é coordenador de Engenharia Elétrica

--
-- Populando a tabela `agendamentos` (que também servirá para `inscricoes_eventos`)
--
INSERT INTO `agendamentos` (`id`, `usuario_id`, `titulo`, `tipo_agendamento`, `esporte_tipo`, `data_agendamento`, `periodo`, `descricao`, `status`, `atletica_confirmada`, `atletica_id_confirmada`, `quantidade_atletica`, `quantidade_pessoas`) VALUES
                                                                                                                                                                                                                                (1, 3, 'Treino de Futsal - A.T.I', 'esportivo', 'Futsal', '2025-09-20', 'Noturno', 'Treino preparatório para o campeonato intercursos.', 'aprovado', 1, 2, 1, 15),
                                                                                                                                                                                                                                (2, 4, 'Treino de Vôlei - Faísca', 'esportivo', 'Vôlei', '2025-09-22', 'Vespertino', 'Treino focado em saque e recepção.', 'aprovado', 1, 1, 1, 12),
                                                                                                                                                                                                                                (3, 13, 'Reunião do Centro Acadêmico', 'nao_esportivo', NULL, '2025-09-25', 'Matutino', 'Discussão sobre as próximas atividades do CA.', 'pendente', 0, NULL, 0, 20),
                                                                                                                                                                                                                                (4, 5, 'Amistoso de Basquete: Direito x Adm', 'esportivo', 'Basquete', '2025-10-01', 'Noturno', 'Amistoso entre a Atlética de Direito e a de Administração.', 'aprovado', 1, 3, 2, 25),
                                                                                                                                                                                                                                (5, 6, 'Treino de Handebol - Med', 'esportivo', 'Handebol', '2025-09-28', 'Vespertino', 'Treino de ataque e defesa.', 'aprovado', 1, 4, 1, 18),
                                                                                                                                                                                                                                (6, 8, 'Encontro de Tênis de Mesa', 'esportivo', 'Tênis de Mesa', '2025-09-30', 'Noturno', 'Evento aberto para todos os amantes de tênis de mesa.', 'aprovado', 0, NULL, 0, 10),
                                                                                                                                                                                                                                (7, 3, 'Campeonato Interno de Futebol A.T.I', 'esportivo', 'Futebol', '2025-10-10', 'Integral', 'Início do campeonato interno da A.T.I.', 'aprovado', 1, 2, 1, 30),
                                                                                                                                                                                                                                (8, 15, 'Palestra sobre Nutrição Esportiva', 'nao_esportivo', NULL, '2025-10-05', 'Noturno', 'Palestra com nutricionista convidado.', 'aprovado', 0, NULL, 0, 50),
                                                                                                                                                                                                                                (9, 4, 'Seletiva de Atletismo - Faísca', 'esportivo', 'Atletismo', '2025-10-12', 'Matutino', 'Seletiva para novos membros da equipe de atletismo.', 'pendente', 1, 1, 1, 8),
                                                                                                                                                                                                                                (10, 5, 'Festa de Integração das Atléticas', 'nao_esportivo', NULL, '2025-10-18', 'Noturno', 'Festa para celebrar o início dos jogos universitários.', 'rejeitado', 0, NULL, 0, 100),
                                                                                                                                                                                                                                (11, 12, 'Jogos Universitários de Outono 2025', 'esportivo', NULL, '2025-10-20', 'Integral', 'Evento principal com várias modalidades.', 'aprovado', 1, 10, 10, 200);

--
-- Populando a tabela `eventos` (tabela separada para eventos maiores)
--
INSERT INTO `eventos` (`id`, `nome`, `data_inicio`, `data_fim`, `ativo`) VALUES
                                                                             (1, 'Jogos Universitários de Outono 2025', '2025-10-20', '2025-10-28', 1),
                                                                             (2, 'Copa de Futsal Interatléticas', '2025-11-05', '2025-11-15', 1),
                                                                             (3, 'Festival de Verão de Esportes de Praia', '2026-01-15', '2026-01-20', 1),
                                                                             (4, 'Semana da Saúde e Bem-estar', '2025-09-22', '2025-09-26', 1),
                                                                             (5, 'Campeonato de E-Sports', '2025-11-20', '2025-11-22', 1),
                                                                             (6, 'Olimpíadas Internas', '2026-03-10', '2026-03-20', 1),
                                                                             (7, 'Torneio de Tênis de Mesa', '2025-10-05', '2025-10-05', 1),
                                                                             (8, 'Corrida Rústica Universitária', '2025-12-07', '2025-12-07', 1),
                                                                             (9, 'Festival Cultural e Esportivo', '2026-04-18', '2026-04-21', 1),
                                                                             (10, 'Jogos de Inverno', '2026-07-10', '2026-07-18', 0);

--
-- Populando a tabela `inscricoes_eventos` (usando IDs de `agendamentos` como chave estrangeira)
--
INSERT INTO `inscricoes_eventos` (`aluno_id`, `evento_id`, `atletica_id`, `status`, `observacoes`) VALUES
                                                                                                       (3, 11, 2, 'aprovado', 'Inscrição para Futsal e Atletismo nos Jogos.'),
                                                                                                       (4, 11, 1, 'aprovado', 'Inscrição para Vôlei.'),
                                                                                                       (5, 11, 3, 'aprovado', 'Inscrição para Basquete.'),
                                                                                                       (6, 11, 4, 'pendente', 'Aguardando confirmação da equipe de Handebol.'),
                                                                                                       (8, 11, 6, 'aprovado', 'Inscrição para Tênis de Mesa.'),
                                                                                                       (3, 7, 2, 'aprovado', 'Capitão da equipe de Futsal da A.T.I.'),
                                                                                                       (5, 4, 3, 'recusado', 'Inscrição duplicada.'),
                                                                                                       (4, 8, 1, 'aprovado', 'Participação na palestra de abertura.'),
                                                                                                       (6, 8, 4, 'aprovado', NULL),
                                                                                                       (9, 11, 7, 'aprovado', 'Inscrição para Natação.'),
                                                                                                       (10, 11, 8, 'pendente', 'Aguardando aprovação na atlética para confirmar inscrição no evento.');

--
-- Populando a tabela `inscricoes_modalidade`
--
INSERT INTO `inscricoes_modalidade` (`aluno_id`, `modalidade_id`, `atletica_id`, `status`) VALUES
                                                                                               (3, 1, 2, 'aprovado'), -- João, Futsal, A.T.I
                                                                                               (4, 2, 1, 'aprovado'), -- Maria, Vôlei, Faísca
                                                                                               (5, 3, 3, 'aprovado'), -- Pedro, Basquete, Lex
                                                                                               (6, 4, 4, 'aprovado'), -- Juliana, Handebol, Med
                                                                                               (7, 5, 5, 'pendente'), -- Fernanda, Futebol, Traço
                                                                                               (8, 6, 6, 'aprovado'), -- Ricardo, Tênis de Mesa, Psique
                                                                                               (3, 8, 2, 'aprovado'), -- João, Atletismo, A.T.I
                                                                                               (4, 7, 1, 'rejeitado'), -- Maria, Natação, Faísca
                                                                                               (9, 7, 7, 'aprovado'), -- Lucas, Natação, Movimento
                                                                                               (10, 2, 8, 'pendente'), -- Beatriz, Vôlei, Diplomacia
                                                                                               (11, 1, 9, 'aprovado'); -- Gabriel, Futsal, Concreta

--
-- Populando a tabela `equipes`
--
INSERT INTO `equipes` (`nome`, `modalidade_id`, `atletica_id`) VALUES
                                                                   ('A.T.I Futsal Masculino', 1, 2),
                                                                   ('Faísca Vôlei Feminino', 2, 1),
                                                                   ('Lex Basquete', 3, 3),
                                                                   ('Med Handebol', 4, 4),
                                                                   ('Psique Tênis de Mesa', 6, 6),
                                                                   ('A.T.I Atletismo', 8, 2),
                                                                   ('Gestores Futebol de Campo', 5, 10),
                                                                   ('Movimento Vôlei Masculino', 2, 7),
                                                                   ('Concreta Futsal Feminino', 1, 9),
                                                                   ('Traço Basquete', 3, 5);

--
-- Populando a tabela `equipe_membros`
--
INSERT INTO `equipe_membros` (`equipe_id`, `aluno_id`) VALUES
                                                           (1, 3), -- João no Futsal da A.T.I
                                                           (2, 4), -- Maria no Vôlei da Faísca
                                                           (3, 5), -- Pedro no Basquete da Lex
                                                           (4, 6), -- Juliana no Handebol da Med
                                                           (5, 8), -- Ricardo no Tênis de Mesa da Psique
                                                           (1, 11),-- Gabriel (Eng. Civil) no time de futsal da A.T.I
                                                           (2, 10),-- Beatriz (RI) no vôlei da Faísca
                                                           (3, 12),-- Laura (Adm) no basquete da Lex
                                                           (6, 3), -- João no Atletismo da A.T.I
                                                           (7, 12);-- Laura no Futebol da Gestores

--
-- Populando a tabela `presencas`
--
INSERT INTO `presencas` (`usuario_id`, `agendamento_id`) VALUES
                                                             (3, 1),
                                                             (4, 2),
                                                             (5, 4),
                                                             (12, 4),
                                                             (6, 5),
                                                             (8, 6),
                                                             (3, 7),
                                                             (15, 8),
                                                             (4, 1),
                                                             (5, 1);

--
-- Populando a tabela `professores_cursos`
--
INSERT INTO `professores_cursos` (`professor_id`, `curso_id`) VALUES
                                                                  (13, 1),
                                                                  (13, 9),
                                                                  (14, 2),
                                                                  (14, 3),
                                                                  (13, 4),
                                                                  (14, 5),
                                                                  (13, 6),
                                                                  (14, 7),
                                                                  (13, 8),
                                                                  (14, 10);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;