CREATE DATABASE acc_db;

USE acc_db;

CREATE TABLE alunos (
    matricula INT PRIMARY KEY,
    nome VARCHAR(100),
    senha VARCHAR(100)
);

CREATE TABLE administradores (
    matricula INT PRIMARY KEY,
    nome VARCHAR(100),
    senha VARCHAR(100)
);

CREATE TABLE atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula_aluno INT,
    descricao VARCHAR(255),
    horas INT,
    certificado LONGBLOB, -- Campo para armazenar a imagem do certificado
    certificado_tipo VARCHAR(100), -- Campo para armazenar o tipo MIME do arquivo
    FOREIGN KEY (matricula_aluno) REFERENCES alunos(matricula)
);

ALTER TABLE atividades 
MODIFY COLUMN certificado VARCHAR(255);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    matricula VARCHAR(20) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('aluno', 'admin', 'gerente') NOT NULL
);

ALTER TABLE atividades MODIFY COLUMN certificado VARCHAR(255);
ALTER TABLE atividades ADD COLUMN validado BOOLEAN DEFAULT FALSE;
ALTER TABLE atividades ADD COLUMN status ENUM('pendente', 'validada', 'negada') DEFAULT 'pendente';

CREATE TABLE mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula_aluno VARCHAR(20),
    id_atividade INT,
    mensagem TEXT,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_atividade) REFERENCES atividades(id) ON DELETE CASCADE
);

ALTER TABLE atividades ADD COLUMN motivo_negacao TEXT NULL;
ALTER TABLE atividades ADD COLUMN pendente_atualizacao BOOLEAN DEFAULT FALSE;
ALTER TABLE atividades ADD COLUMN data_envio DATETIME DEFAULT NULL;

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    horas_ad INT NOT NULL,
    horas_ac INT NOT NULL,
    horas_max INT NOT NULL
);







INSERT INTO categorias (nome, horas_ad, horas_ac, horas_max) VALUES
('Atividades de iniciação científica, iniciação à docência, residência pedagógica ou equivalentes', 2, 1, 100),
('Atividades de monitorias de ensino, extensão e de eventos', 2, 1, 100),
('Aperfeiçoamento em cursos de extensão, minicursos e oficinas', 1, 1, 100),
('Participação como ouvinte em seminários, congressos e eventos de natureza acadêmica e profissional organizadas pela UNEB', 2, 1, 100),
('Participação como ouvinte em seminários, congressos e eventos promovidos por órgãos públicos', 3, 1, 100),
('Disciplinas ou componentes curriculares não aproveitados na análise de equivalência do curso', 1, 1, 100),
('Disciplinas e/ou componentes curriculares cursados em outros cursos do mesmo departamento', 1, 1, 100),
('Disciplinas e/ou componentes curriculares na modalidade de Educação à Distância', 1, 1, 100),
('Participação como membro de comissão organizadora de eventos', 1, 1, 60),
('Participação como membro de comissão organizadora de monitor de seminários', 2, 1, 60),
('Participação em projetos de extensão ou outros projetos de alcance social', 2, 1, 60),
('Elaboração ou execução em projetos de extensão', 1, 1, 60),
('Participação em atividade de mobilidade/intercâmbio', 2, 1, 60),
('Participação em Empresa Júnior', 1, 10, 60),
('Participações em estágios não obrigatórios', 1, 1, 60),
('Participação em cursos de Educação a Distância', 2, 1, 60),
('Visitas temáticas ou estudo de campo', 1, 8, 40),
('Representação estudantil nos Conselhos superiores', 1, 10, 40),
('Participação na direção de Diretório Central e acadêmico', 1, 10, 40),
('Participação em Grupo de Pesquisa Certificado ou Centro de Pesquisa da UNEB', 1, 20, 40),
('Participação como mesário voluntário em eleições', 1, 20, 40),
('Participação em comissões eleitorais da UNEB', 1, 20, 45),
('Produção/elaboração de material técnico, multimídia, didático', 1, 20, 20),
('Comunicação oral em eventos', 1, 10, 60),
('Relato de Experiências em eventos', 1, 10, 60),
('Apresentação de Poster em eventos', 1, 10, 60),
('Expositor de Mesa em eventos', 1, 15, 60),
('Mediador de Mesa em eventos', 1, 10, 60),
('Palestrante/Conferencista em eventos', 1, 20, 60),
('Entrevista em eventos', 1, 15, 60),
('Publicação de livro impresso ou digital com conselho editorial', 1, 40, 100),
('Publicação em revista indexada, impressa ou eletrônica', 1, 40, 100),
('Publicação de capítulo de livro impresso ou digital com conselho editorial', 1, 20, 100),
('Publicação de trabalho completo em anais de evento', 1, 20, 100),
('Publicação de resumo expandido em anais de evento', 1, 10, 100),
('Publicação de artigo em revista especializada, não indexada', 1, 10, 100),
('Publicação de artigo, resenha, crônicas, poemas, contos em jornais, livros ou revistas não especializadas', 1, 5, 100);

ALTER TABLE categorias ADD UNIQUE (nome);
ALTER TABLE atividades ADD CONSTRAINT fk_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id);

CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT
);

ALTER TABLE usuarios 
ADD COLUMN curso_id INT,
ADD FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE SET NULL;

CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    tipo ENUM('presencial', 'a_distancia') NOT NULL
);

ALTER TABLE alunos ADD COLUMN curso_id INT;
ALTER TABLE alunos ADD FOREIGN KEY (curso_id) REFERENCES cursos(id);

ALTER TABLE administradores ADD COLUMN curso_id INT;
ALTER TABLE administradores ADD FOREIGN KEY (curso_id) REFERENCES cursos(id);

ALTER TABLE alunos ADD COLUMN tipo_curso ENUM('bacharelado', 'licenciatura') NOT NULL;
ALTER TABLE alunos ADD COLUMN tipo_modalidade ENUM('presencial', 'a_distancia') NOT NULL;

ALTER TABLE administradores ADD COLUMN tipo_curso ENUM('bacharelado', 'licenciatura') NOT NULL;
ALTER TABLE administradores ADD COLUMN tipo_modalidade ENUM('presencial', 'a_distancia') NOT NULL;

ALTER TABLE cursos ADD COLUMN tipo_curso ENUM('bacharelado', 'licenciatura') NOT NULL;
ALTER TABLE cursos MODIFY COLUMN tipo ENUM('presencial', 'a_distancia') NOT NULL;

RENAME TABLE categorias TO baremabacharelado;


CREATE TABLE baremalicenciatura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    horas_ad INT NOT NULL,
    horas_ac INT NOT NULL,
    horas_max INT NOT NULL
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('bacharelado', 'licenciatura') NOT NULL,  -- Identificação do tipo de curso
    horas_ad INT NOT NULL,
    horas_ac INT NOT NULL,
    horas_max INT NOT NULL
);



ALTER TABLE atividades ADD COLUMN tipo_categoria ENUM('bacharelado', 'licenciatura') NOT NULL;






