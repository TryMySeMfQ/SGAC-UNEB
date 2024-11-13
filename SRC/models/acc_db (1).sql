-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 12/11/2024 às 00:15
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
-- Banco de dados: `acc_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `administradores`
--

CREATE TABLE `administradores` (
  `matricula` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `senha` varchar(100) DEFAULT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `tipo_curso` enum('bacharelado','licenciatura') NOT NULL,
  `tipo_modalidade` enum('presencial','a_distancia') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `administradores`
--

INSERT INTO `administradores` (`matricula`, `nome`, `senha`, `curso_id`, `tipo_curso`, `tipo_modalidade`) VALUES
(2222, 'Julia', '$2y$10$DlrKLaiNxw9vQ/TWjC1lfeRTZmplrrAP4w8im/fNWTRK2z/ARn5he', 1, 'bacharelado', 'presencial');

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `matricula` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `senha` varchar(100) DEFAULT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `tipo_curso` enum('bacharelado','licenciatura') NOT NULL,
  `tipo_modalidade` enum('presencial','a_distancia') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `alunos`
--

INSERT INTO `alunos` (`matricula`, `nome`, `senha`, `curso_id`, `tipo_curso`, `tipo_modalidade`) VALUES
(1111, 'Jorge', '$2y$10$C3vIx71bJwWCsFzk3fqsg.Jy5STCJ73zursjO5Pe5NK9BD8uRNxmy', 1, 'bacharelado', 'presencial'),
(3333, 'Ana Julia', '$2y$10$G7Y/f.xrZlqxK2F/2BR0iehgIbuZ6wNuRK14zq6aJ0kRBikxQEqrG', 2, 'licenciatura', 'presencial');

-- --------------------------------------------------------

--
-- Estrutura para tabela `atividades`
--

CREATE TABLE `atividades` (
  `id` int(11) NOT NULL,
  `matricula_aluno` int(11) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `horas` int(11) DEFAULT NULL,
  `certificado` varchar(255) DEFAULT NULL,
  `certificado_tipo` varchar(100) DEFAULT NULL,
  `validado` tinyint(1) DEFAULT 0,
  `status` enum('pendente','validada','negada') DEFAULT 'pendente',
  `motivo_negacao` text DEFAULT NULL,
  `pendente_atualizacao` tinyint(1) DEFAULT 0,
  `data_envio` datetime DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `tipo_categoria` enum('bacharelado','licenciatura') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `atividades`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `baremabacharelado`
--

CREATE TABLE `baremabacharelado` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `horas_ad` int(11) NOT NULL,
  `horas_ac` int(11) NOT NULL,
  `horas_max` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `baremabacharelado`
--

INSERT INTO `baremabacharelado` (`id`, `nome`, `horas_ad`, `horas_ac`, `horas_max`) VALUES
(1, 'Atividades de iniciação científica, iniciação à docência, residência pedagógica ou equivalentes', 2, 1, 100),
(2, 'Atividades de monitorias de ensino, extensão e de eventos', 2, 1, 100),
(3, 'Aperfeiçoamento em cursos de extensão, minicursos e oficinas', 1, 1, 100),
(4, 'Participação como ouvinte em seminários, congressos e eventos de natureza acadêmica e profissional o', 2, 1, 100),
(5, 'Participação como ouvinte em seminários, congressos e eventos promovidos por órgãos públicos', 3, 1, 100),
(6, 'Disciplinas ou componentes curriculares não aproveitados na análise de equivalência do curso', 1, 1, 100),
(7, 'Disciplinas e/ou componentes curriculares cursados em outros cursos do mesmo departamento', 1, 1, 100),
(8, 'Disciplinas e/ou componentes curriculares na modalidade de Educação à Distância', 1, 1, 100),
(9, 'Participação como membro de comissão organizadora de eventos', 1, 1, 60),
(10, 'Participação como membro de comissão organizadora de monitor de seminários', 2, 1, 60),
(11, 'Participação em projetos de extensão ou outros projetos de alcance social', 2, 1, 60),
(12, 'Elaboração ou execução em projetos de extensão', 1, 1, 60),
(13, 'Participação em atividade de mobilidade/intercâmbio', 2, 1, 60),
(14, 'Participação em Empresa Júnior', 1, 10, 60),
(15, 'Participações em estágios não obrigatórios', 1, 1, 60),
(16, 'Participação em cursos de Educação a Distância', 2, 1, 60),
(17, 'Visitas temáticas ou estudo de campo', 1, 8, 40),
(18, 'Representação estudantil nos Conselhos superiores', 1, 10, 40),
(19, 'Participação na direção de Diretório Central e acadêmico', 1, 10, 40),
(20, 'Participação em Grupo de Pesquisa Certificado ou Centro de Pesquisa da UNEB', 1, 20, 40),
(21, 'Participação como mesário voluntário em eleições', 1, 20, 40),
(22, 'Participação em comissões eleitorais da UNEB', 1, 20, 45),
(23, 'Produção/elaboração de material técnico, multimídia, didático', 1, 20, 20),
(24, 'Comunicação oral em eventos', 1, 10, 60),
(25, 'Relato de Experiências em eventos', 1, 10, 60),
(26, 'Apresentação de Poster em eventos', 1, 10, 60),
(27, 'Expositor de Mesa em eventos', 1, 15, 60),
(28, 'Mediador de Mesa em eventos', 1, 10, 60),
(29, 'Palestrante/Conferencista em eventos', 1, 20, 60),
(30, 'Entrevista em eventos', 1, 15, 60),
(31, 'Publicação de livro impresso ou digital com conselho editorial', 1, 40, 100),
(32, 'Publicação em revista indexada, impressa ou eletrônica', 1, 40, 100),
(33, 'Publicação de capítulo de livro impresso ou digital com conselho editorial', 1, 20, 100),
(34, 'Publicação de trabalho completo em anais de evento', 1, 20, 100),
(35, 'Publicação de resumo expandido em anais de evento', 1, 10, 100),
(36, 'Publicação de artigo em revista especializada, não indexada', 1, 10, 100),
(37, 'Publicação de artigo, resenha, crônicas, poemas, contos em jornais, livros ou revistas não especiali', 1, 5, 100);

-- --------------------------------------------------------

--
-- Estrutura para tabela `baremalicenciatura`
--

CREATE TABLE `baremalicenciatura` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `horas_ad` int(11) NOT NULL,
  `horas_ac` int(11) NOT NULL,
  `horas_max` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `baremalicenciatura`
--

INSERT INTO `baremalicenciatura` (`id`, `nome`, `horas_ad`, `horas_ac`, `horas_max`) VALUES
(1, 'Atividades de iniciação científica, iniciação à docência, residência pedagógica ou equivalentes', 2, 1, 100);

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `tipo` enum('bacharelado','licenciatura') NOT NULL,
  `horas_ad` int(11) NOT NULL,
  `horas_ac` int(11) NOT NULL,
  `horas_max` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` enum('presencial','a_distancia') NOT NULL,
  `tipo_curso` enum('bacharelado','licenciatura') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cursos`
--

INSERT INTO `cursos` (`id`, `nome`, `descricao`, `tipo`, `tipo_curso`) VALUES
(1, 'Sistema de Informação', 'tecnologia', 'presencial', 'bacharelado'),
(2, 'Biologia', 'BIO', 'presencial', 'licenciatura');

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensagens`
--

CREATE TABLE `mensagens` (
  `id` int(11) NOT NULL,
  `matricula_aluno` varchar(20) DEFAULT NULL,
  `id_atividade` int(11) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `mensagens`
--


-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `matricula` varchar(20) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('aluno','admin','gerente') NOT NULL,
  `curso_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--


--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`matricula`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`matricula`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Índices de tabela `atividades`
--
ALTER TABLE `atividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `matricula_aluno` (`matricula_aluno`);

--
-- Índices de tabela `baremabacharelado`
--
ALTER TABLE `baremabacharelado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `baremalicenciatura`
--
ALTER TABLE `baremalicenciatura`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `mensagens`
--
ALTER TABLE `mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_atividade` (`id_atividade`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matricula` (`matricula`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `atividades`
--
ALTER TABLE `atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `baremabacharelado`
--
ALTER TABLE `baremabacharelado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `baremalicenciatura`
--
ALTER TABLE `baremalicenciatura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `mensagens`
--
ALTER TABLE `mensagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `administradores`
--
ALTER TABLE `administradores`
  ADD CONSTRAINT `administradores_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`);

--
-- Restrições para tabelas `alunos`
--
ALTER TABLE `alunos`
  ADD CONSTRAINT `alunos_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`);

--
-- Restrições para tabelas `atividades`
--
ALTER TABLE `atividades`
  ADD CONSTRAINT `atividades_ibfk_1` FOREIGN KEY (`matricula_aluno`) REFERENCES `alunos` (`matricula`);

--
-- Restrições para tabelas `mensagens`
--
ALTER TABLE `mensagens`
  ADD CONSTRAINT `mensagens_ibfk_1` FOREIGN KEY (`id_atividade`) REFERENCES `atividades` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
