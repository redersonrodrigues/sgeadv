/*
===============================================================================
Sistema de Gestao de Escritorio de Advocacia
===============================================================================
*/

CREATE DATABASE IF NOT EXISTS advocacia
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

USE advocacia;

-- ============================================================================
-- Apoio geografico e seguranca
-- ============================================================================

CREATE TABLE IF NOT EXISTS estado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sigla CHAR(2) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_estado_nome (nome),
    UNIQUE KEY uk_estado_sigla (sigla)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS cidade (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL,
 estado_id INT NOT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 KEY idx_cidade_estado_id (estado_id),
 CONSTRAINT fk_cidade_estado
   FOREIGN KEY (estado_id) REFERENCES estado (id) 
   ON DELETE CASCADE ON UPDATE CASCADE -- Apagar estado remove suas cidades
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS grupo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_grupo_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS especialidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_especialidade_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS permissao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_permissao_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS grupo_permissao (
 grupo_id INT NOT NULL,
 permissao_id INT NOT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (grupo_id, permissao_id),
 KEY idx_grupo_permissao_permissao_id (permissao_id),
 CONSTRAINT fk_grupo_permissao_grupo
   FOREIGN KEY (grupo_id) REFERENCES grupo (id) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT fk_grupo_permissao_permissao
   FOREIGN KEY (permissao_id) REFERENCES permissao (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================================================
-- Pessoa e relacionamentos
-- ============================================================================

CREATE TABLE IF NOT EXISTS pessoa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL COMMENT 'Nome completo ou Razao Social',
    endereco VARCHAR(255) NULL,
    bairro VARCHAR(100) NULL,
    telefone VARCHAR(20) NULL,
    email VARCHAR(150) NOT NULL,
    senha VARCHAR(255) NULL COMMENT 'Hash da senha de acesso',
    foto MEDIUMBLOB NULL,
    tipo ENUM('F', 'J') NOT NULL DEFAULT 'F',
    ativo CHAR(1) NOT NULL DEFAULT 'S',
    observacoes TEXT NULL,
    cidade_id INT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    recovery_token VARCHAR(100) NULL,
    token_expiration DATETIME NULL,
    cpf VARCHAR(14) NULL,
    rg VARCHAR(20) NULL,
    dt_nascimento DATETIME NULL,
    sexo CHAR(1) NULL,
    cnpj VARCHAR(18) NULL,
    nome_fantasia VARCHAR(255) NULL,
    razao_social VARCHAR(255) NULL,
    inscricao_estadual VARCHAR(50) NULL,
    cargo VARCHAR(100) NULL,
    oab VARCHAR(20) NULL,
    oab_uf CHAR(2) NULL,
    contratado_em DATETIME NULL,
    demitido_em DATETIME NULL,
    salario DECIMAL(10,2) NULL,
    UNIQUE KEY uk_pessoa_email (email),
    UNIQUE KEY uk_pessoa_cpf (cpf),
    UNIQUE KEY uk_pessoa_cnpj (cnpj),
    KEY idx_pessoa_cidade_id (cidade_id),
    CONSTRAINT fk_pessoa_cidade
        FOREIGN KEY (cidade_id) REFERENCES cidade (id)
        ON DELETE SET NULL ON UPDATE CASCADE -- Preserva o cliente se a cidade sumir
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS pessoa_grupo (
 pessoa_id INT NOT NULL,
 grupo_id INT NOT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (pessoa_id, grupo_id),
 KEY idx_pessoa_grupo_grupo_id (grupo_id),
 CONSTRAINT fk_pessoa_grupo_pessoa
   FOREIGN KEY (pessoa_id) REFERENCES pessoa (id) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT fk_pessoa_grupo_grupo
   FOREIGN KEY (grupo_id) REFERENCES grupo (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS pessoa_especialidade (
 pessoa_id INT NOT NULL,
 especialidade_id INT NOT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (pessoa_id, especialidade_id),
 KEY idx_pessoa_especialidade_especialidade_id (especialidade_id),
 CONSTRAINT fk_pessoa_especialidade_pessoa
   FOREIGN KEY (pessoa_id) REFERENCES pessoa (id) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT fk_pessoa_especialidade_especialidade
   FOREIGN KEY (especialidade_id) REFERENCES especialidade (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================================================
-- Estrutura juridica
-- ============================================================================

CREATE TABLE IF NOT EXISTS tipo_acao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tipo_acao_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS acao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    tipo_acao_id INT NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_acao_tipo_acao_id (tipo_acao_id),
    CONSTRAINT fk_acao_tipo_acao
        FOREIGN KEY (tipo_acao_id) REFERENCES tipo_acao (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS vara (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL,
 cidade_id INT NOT NULL COMMENT 'Comarca da vara judicial',
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 KEY idx_vara_cidade_id (cidade_id),
 CONSTRAINT fk_vara_cidade
   FOREIGN KEY (cidade_id) REFERENCES cidade (id) 
   ON DELETE CASCADE ON UPDATE CASCADE -- Apagar cidade limpa suas varas judiciais
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS processo (
 id INT AUTO_INCREMENT PRIMARY KEY,
 numero_processo VARCHAR(100) NOT NULL,
 titulo VARCHAR(255) NULL,
 valor DECIMAL(15,2) NULL,
 status ENUM('Ativo', 'Suspenso', 'Arquivado', 'Encerrado') NOT NULL DEFAULT 'Ativo',
 resumo TEXT NULL,
 registrado_por INT NULL,
 vara_id INT NULL,
 acao_id INT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uk_processo_numero_processo (numero_processo),
 KEY idx_processo_registrado_por (registrado_por),
 KEY idx_processo_vara_id (vara_id),
 KEY idx_processo_acao_id (acao_id),
 CONSTRAINT fk_processo_registrado_por
   FOREIGN KEY (registrado_por) REFERENCES pessoa (id) ON DELETE SET NULL ON UPDATE CASCADE,
 CONSTRAINT fk_processo_vara
   FOREIGN KEY (vara_id) REFERENCES vara (id) ON DELETE SET NULL ON UPDATE CASCADE, -- Se a vara sumir, mantém o processo
 CONSTRAINT fk_processo_acao
   FOREIGN KEY (acao_id) REFERENCES acao (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS pessoa_processo (
 pessoa_id INT NOT NULL,
 processo_id INT NOT NULL,
 figura_como VARCHAR(150) NOT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (pessoa_id, processo_id),
 KEY idx_pessoa_processo_processo_id (processo_id),
 CONSTRAINT fk_pessoa_processo_pessoa
   FOREIGN KEY (pessoa_id) REFERENCES pessoa (id) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT fk_pessoa_processo_processo
   FOREIGN KEY (processo_id) REFERENCES processo (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS tipo_documento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tipo_documento_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS documento (
id INT AUTO_INCREMENT PRIMARY KEY,
processo_id INT NOT NULL,
tipo_documento_id INT NOT NULL,
nome_arquivo VARCHAR(255) NOT NULL,
caminho VARCHAR(500) NOT NULL,
descricao TEXT NULL,
mime VARCHAR(100) NULL,
tamanho INT NULL,
enviado_por INT NULL, -- Alterado para NULL para suportar SET NULL caso o usuário seja deletado
criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
KEY idx_documento_processo_id (processo_id),
KEY idx_documento_tipo_documento_id (tipo_documento_id),
KEY idx_documento_enviado_por (enviado_por),
CONSTRAINT fk_documento_processo
FOREIGN KEY (processo_id) REFERENCES processo (id) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT fk_documento_tipo_documento
FOREIGN KEY (tipo_documento_id) REFERENCES tipo_documento (id) ON DELETE RESTRICT ON UPDATE CASCADE,
CONSTRAINT fk_documento_enviado_por
FOREIGN KEY (enviado_por) REFERENCES pessoa (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS andamento_processual (
id INT AUTO_INCREMENT PRIMARY KEY,
processo_id INT NOT NULL,
descricao TEXT NOT NULL,
dt_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
registrado_por INT NULL, -- Alterado para NULL para suportar SET NULL caso o usuário seja deletado
criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
KEY idx_andamento_processual_processo_id (processo_id),
KEY idx_andamento_processual_registrado_por (registrado_por),
CONSTRAINT fk_andamento_processual_processo
FOREIGN KEY (processo_id) REFERENCES processo (id) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT fk_andamento_processual_registrado_por
FOREIGN KEY (registrado_por) REFERENCES pessoa (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS audiencia (
id INT AUTO_INCREMENT PRIMARY KEY,
processo_id INT NOT NULL,
advogado_id INT NULL, -- Alterado para NULL para não perder a audiência caso o advogado saia do escritório
dt_audiencia DATETIME NOT NULL,
local VARCHAR(255) NULL,
juiz VARCHAR(255) NULL,
situacao ENUM('Agendada', 'Concluida', 'Cancelada', 'Remarcada') NOT NULL DEFAULT 'Agendada',
observacoes TEXT NULL,
registrado_por INT NULL, -- Alterado para NULL para suportar SET NULL
criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
KEY idx_audiencia_processo_id (processo_id),
KEY idx_audiencia_advogado_id (advogado_id),
KEY idx_audiencia_registrado_por (registrado_por),
CONSTRAINT fk_audiencia_processo
FOREIGN KEY (processo_id) REFERENCES processo (id) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT fk_audiencia_advogado
FOREIGN KEY (advogado_id) REFERENCES pessoa (id) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT fk_audiencia_registrado_por
FOREIGN KEY (registrado_por) REFERENCES pessoa (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS audiencia_participante (
audiencia_id INT NOT NULL,
pessoa_id INT NOT NULL,
processo_id INT NOT NULL,
confirmou_presenca TINYINT(1) NOT NULL DEFAULT 0,
criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (audiencia_id, pessoa_id, processo_id),
KEY idx_audiencia_participante_pessoa_processo (pessoa_id, processo_id),
CONSTRAINT fk_audiencia_participante_audiencia
FOREIGN KEY (audiencia_id) REFERENCES audiencia (id) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT fk_audiencia_participante_pessoa_processo
FOREIGN KEY (pessoa_id, processo_id) REFERENCES pessoa_processo (pessoa_id, processo_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================================================
-- Tarefas e movimentacoes
-- ============================================================================

CREATE TABLE IF NOT EXISTS tarefa (
id INT AUTO_INCREMENT PRIMARY KEY,
titulo VARCHAR(255) NOT NULL,
descricao TEXT NULL,
prioridade ENUM('Alta', 'Baixa', 'Emergencia', 'Urgente') NOT NULL,
dt_vencimento DATETIME NOT NULL,
dt_conclusao DATETIME NULL,
registrado_por INT NULL, -- Alterado para NULL para suportar SET NULL
criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
KEY idx_tarefa_registrado_por (registrado_por),
CONSTRAINT fk_tarefa_registrado_por
FOREIGN KEY (registrado_por) REFERENCES pessoa (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS pessoa_tarefa (
    pessoa_id INT NOT NULL,
    tarefa_id INT NOT NULL,
    anotacoes TEXT NULL,
    status ENUM('Aberta', 'Em_Andamento', 'Concluida', 'Cancelada') NOT NULL,
    atualizado_em DATETIME NULL,
    PRIMARY KEY (pessoa_id, tarefa_id),
    KEY idx_pessoa_tarefa_tarefa_id (tarefa_id),
    CONSTRAINT fk_pessoa_tarefa_pessoa
    FOREIGN KEY (pessoa_id) REFERENCES pessoa (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pessoa_tarefa_tarefa
    FOREIGN KEY (tarefa_id) REFERENCES tarefa (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS movimentacao (
id INT AUTO_INCREMENT PRIMARY KEY,
pessoa_id INT NULL, -- Alterado para NULL para que o histórico financeiro continue existindo mesmo se o cliente for apagado
dt_pagamento DATETIME NULL,
tipo_mov ENUM('A_Pagar', 'A_Receber') NOT NULL,
direcao ENUM('Saida', 'Entrada') NOT NULL,
descricao VARCHAR(255) NOT NULL,
valor DECIMAL(15,2) NOT NULL,
dt_vencimento DATETIME NOT NULL,
dt_emissao DATETIME NULL,
paga_em DATETIME NULL,
metodo ENUM('Dinheiro', 'Transferencia', 'Cartao', 'Cheque', 'Boleto', 'Pix', 'Outro') NULL,
status ENUM('Pendente', 'Paga', 'Parcialmente_Paga', 'Vencida', 'Cancelada') NULL,
observacoes TEXT NULL,
registrado_por INT NULL, -- Alterado para NULL para suportar SET NULL
criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
KEY idx_movimentacao_pessoa_id (pessoa_id),
KEY idx_movimentacao_registrado_por (registrado_por),
CONSTRAINT fk_movimentacao_pessoa
FOREIGN KEY (pessoa_id) REFERENCES pessoa (id) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT fk_movimentacao_registrado_por
FOREIGN KEY (registrado_por) REFERENCES pessoa (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
