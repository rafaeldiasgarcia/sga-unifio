-- Data Base de exemplo, cole no phpMyAdmin para criar o banco de dados e as tabelas
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13/09/2025 às 22:55
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
  `esporte_tipo` varchar(100) DEFAULT NULL,
  `data_agendamento` date NOT NULL,
  `periodo` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `status` enum('aprovado','pendente','rejeitado') NOT NULL DEFAULT 'pendente',
  `motivo_rejeicao` text DEFAULT NULL,
  `data_solicitacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- Sem dados iniciais para atleticas

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `atletica_id` int(11) DEFAULT NULL,
  `coordenador_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cursos`
--

-- Sem dados iniciais para cursos

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

-- --------------------------------------------------------

--
-- Estrutura para tabela `modalidades`
--

CREATE TABLE `modalidades` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `presencas`
--

CREATE TABLE `presencas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `agendamento_id` int(11) NOT NULL,
  `data_presenca` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `professores_cursos`
--

CREATE TABLE `professores_cursos` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `ra` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `role` enum('usuario','admin','superadmin') NOT NULL DEFAULT 'usuario',
  `atletica_id` int(11) DEFAULT NULL,
  `tipo_usuario_detalhado` enum('Membro das Atléticas','Professor','Aluno','Comunidade Externa') DEFAULT NULL,
  `is_coordenador` tinyint(1) NOT NULL DEFAULT 0,
  `atletica_join_status` enum('none','pendente','aprovado') NOT NULL DEFAULT 'none',
  `login_code` varchar(6) DEFAULT NULL,
  `login_code_expires` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `ra`, `data_nascimento`, `curso_id`, `role`, `atletica_id`, `tipo_usuario_detalhado`, `is_coordenador`, `atletica_join_status`, `login_code`, `login_code_expires`, `reset_token`, `reset_token_expires`) VALUES
(1, 'Super Admin', 'super@admin.com', '$2y$10$d8SoY8sdOkYci2Q/de.uye4c6j7Cu.CUYVxEm55Lk43l4Am7KBbqi', NULL, NULL, NULL, 'superadmin', NULL, NULL, 0, 'none', NULL, NULL, NULL, NULL);

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
  ADD KEY `atletica_id` (`atletica_id`),
  ADD KEY `coordenador_id` (`coordenador_id`);

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
-- Índices de tabela `presencas`
--
ALTER TABLE `presencas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`,`agendamento_id`),
  ADD KEY `agendamento_id` (`agendamento_id`);

--
-- Índices de tabela `professores_cursos`
--
ALTER TABLE `professores_cursos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `professor_id` (`professor_id`,`curso_id`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `atleticas`
--
ALTER TABLE `atleticas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `equipes`
--
ALTER TABLE `equipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `equipe_membros`
--
ALTER TABLE `equipe_membros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `inscricoes_modalidade`
--
ALTER TABLE `inscricoes_modalidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `modalidades`
--
ALTER TABLE `modalidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `presencas`
--
ALTER TABLE `presencas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `professores_cursos`
--
ALTER TABLE `professores_cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
  ADD CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`atletica_id`) REFERENCES `atleticas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cursos_ibfk_2` FOREIGN KEY (`coordenador_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

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
-- Restrições para tabelas `presencas`
--
ALTER TABLE `presencas`
  ADD CONSTRAINT `presencas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `presencas_ibfk_2` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `professores_cursos`
--
ALTER TABLE `professores_cursos`
  ADD CONSTRAINT `professores_cursos_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `professores_cursos_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;

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
