-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13/09/2025 às 11:49
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

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

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `tipo_agendamento` enum('esportivo','nao_esportivo') NOT NULL,
  `data_agendamento` date NOT NULL,
  `periodo` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `status` enum('aprovado','pendente','rejeitado') NOT NULL DEFAULT 'pendente',
  `motivo_rejeicao` text DEFAULT NULL,
  `data_solicitacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `usuario_id`, `titulo`, `tipo_agendamento`, `data_agendamento`, `periodo`, `descricao`, `status`, `motivo_rejeicao`, `data_solicitacao`) VALUES
(1, 14, 'TESTE 1', 'nao_esportivo', '2312-03-12', '21:10 - 22:50', '', 'aprovado', NULL, '2025-09-13 09:43:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `atleticas`
--

CREATE TABLE `atleticas` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `atleticas`
--

INSERT INTO `atleticas` (`id`, `nome`, `descricao`, `logo_url`) VALUES
(1, 'Atlética de Direito', 'A gloriosa atlética do curso de Direito.', NULL),
(2, 'Atlética de Engenharia de Software', NULL, NULL),
(3, 'Atlética de Psicologia', NULL, NULL),
(4, 'Atlética de Administração', NULL, NULL),
(5, 'Atlética de Biomedicina', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `atletica_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cursos`
--

INSERT INTO `cursos` (`id`, `nome`, `atletica_id`) VALUES
(1, 'Direito', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipes`
--

CREATE TABLE `equipes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `modalidade_id` int(11) NOT NULL,
  `atletica_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `equipes`
--

INSERT INTO `equipes` (`id`, `nome`, `modalidade_id`, `atletica_id`) VALUES
(3, '123', 1, 1),
(4, '322', 1, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipe_membros`
--

CREATE TABLE `equipe_membros` (
  `id` int(11) NOT NULL,
  `equipe_id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `eventos`
--

INSERT INTO `eventos` (`id`, `nome`, `data_inicio`, `data_fim`, `ativo`) VALUES
(1, 'Teste', '2025-09-30', '2025-10-01', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `inscricoes_modalidade`
--

CREATE TABLE `inscricoes_modalidade` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `modalidade_id` int(11) NOT NULL,
  `atletica_id` int(11) NOT NULL,
  `status` enum('pendente','aprovado','recusado') NOT NULL DEFAULT 'pendente',
  `data_inscricao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `inscricoes_modalidade`
--

INSERT INTO `inscricoes_modalidade` (`id`, `aluno_id`, `modalidade_id`, `atletica_id`, `status`, `data_inscricao`) VALUES
(1, 12, 1, 1, 'aprovado', '2025-09-13 08:52:05'),
(2, 13, 1, 1, 'recusado', '2025-09-13 09:15:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `modalidades`
--

CREATE TABLE `modalidades` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `modalidades`
--

INSERT INTO `modalidades` (`id`, `evento_id`, `nome`) VALUES
(1, 1, 'Futsal');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `matricula` varchar(50) DEFAULT NULL,
  `ra` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `role` enum('aluno','admin','superadmin') NOT NULL DEFAULT 'aluno',
  `atletica_id` int(11) DEFAULT NULL,
  `tipo_usuario_detalhado` enum('Membro das Atléticas','Professor','Aluno','Comunidade Externa') DEFAULT NULL,
  `materia_professor` varchar(255) DEFAULT NULL,
  `atletica_join_status` enum('none','pendente') NOT NULL DEFAULT 'none',
  `login_code` varchar(6) DEFAULT NULL,
  `login_code_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `matricula`, `ra`, `data_nascimento`, `curso_id`, `role`, `atletica_id`, `tipo_usuario_detalhado`, `materia_professor`, `atletica_join_status`, `login_code`, `login_code_expires`) VALUES
(1, 'Super Admin', 'super@unifio.edu.br', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', NULL, NULL, NULL, NULL, 'superadmin', NULL, NULL, NULL, 'none', NULL, NULL),
(9, 'Teste Comunidade Externa 1', 'testecomunidadeexterna1@gmail.com', '$2y$10$TIYn3T9vwia325SsjJb7KuNMh5vZI2xd29N5NEHbRwhIw5DK8JeCC', NULL, '000001', '0001-01-01', NULL, 'admin', NULL, 'Comunidade Externa', '', 'none', NULL, NULL),
(11, 'teste teste', 'teste@hotmail.com', '$2y$10$W4QedD5Bdn3VS6.Imw0YVeXYUkjiQqAFreeiLvyuDwgHiiTx9S7be', NULL, '123123', '2001-09-11', NULL, 'aluno', NULL, 'Comunidade Externa', NULL, 'none', NULL, NULL),
(12, 'teste aluno 1', '00005@unifio.edu.br', '$2y$10$FkgpgsGvmqS8GWFJlhf/QuM4EgdaEK49jxBK0KbUHejDptpUzaxUa', NULL, '000005', '0123-03-12', 1, 'admin', 1, 'Membro das Atléticas', NULL, 'none', NULL, NULL),
(13, 'teste321', 'teste321@unifio.edu.br', '$2y$10$fqUMlTLAWQPn3q9ICP/egOJwezqdcM5xDdf4iqEm/nvFlzO3TCZO6', NULL, '1231212', '0123-03-12', 1, 'aluno', NULL, 'Membro das Atléticas', NULL, 'none', NULL, NULL),
(14, 'Teste Professor 2', '000006@unifio.edu.br', '$2y$10$/3DVmLFFBXilk.l0Xyfwi.YsMspUfeOQjhiwiBQmJFb4G7T5HMs0.', NULL, '000006', '2132-03-12', NULL, 'aluno', NULL, 'Professor', 'Engenharia de Software', 'none', NULL, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `atleticas`
--
ALTER TABLE `atleticas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `atletica_id` (`atletica_id`);

--
-- Índices de tabela `equipes`
--
ALTER TABLE `equipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modalidade_id` (`modalidade_id`),
  ADD KEY `atletica_id` (`atletica_id`);

--
-- Índices de tabela `equipe_membros`
--
ALTER TABLE `equipe_membros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipe_id` (`equipe_id`,`aluno_id`),
  ADD KEY `aluno_id` (`aluno_id`);

--
-- Índices de tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `inscricoes_modalidade`
--
ALTER TABLE `inscricoes_modalidade`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aluno_id` (`aluno_id`,`modalidade_id`),
  ADD KEY `modalidade_id` (`modalidade_id`),
  ADD KEY `atletica_id` (`atletica_id`);

--
-- Índices de tabela `modalidades`
--
ALTER TABLE `modalidades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `matricula` (`matricula`),
  ADD UNIQUE KEY `ra` (`ra`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `atletica_id` (`atletica_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `atleticas`
--
ALTER TABLE `atleticas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `equipes`
--
ALTER TABLE `equipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `equipe_membros`
--
ALTER TABLE `equipe_membros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `inscricoes_modalidade`
--
ALTER TABLE `inscricoes_modalidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `modalidades`
--
ALTER TABLE `modalidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`atletica_id`) REFERENCES `atleticas` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `equipes`
--
ALTER TABLE `equipes`
  ADD CONSTRAINT `equipes_ibfk_1` FOREIGN KEY (`modalidade_id`) REFERENCES `modalidades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipes_ibfk_2` FOREIGN KEY (`atletica_id`) REFERENCES `atleticas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `equipe_membros`
--
ALTER TABLE `equipe_membros`
  ADD CONSTRAINT `equipe_membros_ibfk_1` FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipe_membros_ibfk_2` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `inscricoes_modalidade`
--
ALTER TABLE `inscricoes_modalidade`
  ADD CONSTRAINT `inscricoes_modalidade_ibfk_1` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscricoes_modalidade_ibfk_2` FOREIGN KEY (`modalidade_id`) REFERENCES `modalidades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscricoes_modalidade_ibfk_3` FOREIGN KEY (`atletica_id`) REFERENCES `atleticas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `modalidades`
--
ALTER TABLE `modalidades`
  ADD CONSTRAINT `modalidades_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`atletica_id`) REFERENCES `atleticas` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
