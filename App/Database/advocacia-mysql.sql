/*
================================================================================
ARQUIVO: advocacia_mysql.sql
PROJETO: Sistema de Gestão de Escritório de Advocacia
DESCRIÇÃO: Este script cria a estrutura de banco de dados relacional para gerenciar
           pessoas, processos, tarefas, audiências e movimentações financeiras.
================================================================================

PASSO A PASSO E LOGA DE USO:
1. EXECUÇÃO: Este script deve ser executado em um servidor MySQL (via MySQL Workbench,
   DBeaver ou linha de comando).
2. ORDEM DE CRIAÇÃO: As tabelas seguem uma ordem lógica de dependência (FKs). Primeiro
   as tabelas de base (Estado, Cidade), depois as entidades principais (pessoa, processo)
   e por fim as tabelas de ligação (Muitos-para-Muitos).
3. INTEGRAÇÃO: Utilize este esquema como o "Back-end" da sua aplicação. As chaves
   estrangeiras (FOREIGN KEYs) garantem que um processo não fique sem ação vinculada
   e que tarefas/audiências estejam sempre presas a um processo real.
4. MANUTENÇÃO: Campos de 'criado_em' e 'atualizado_em' permitem auditoria de dados.
*/

SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS advocacia CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
USE advocacia;

-- ---------------------------------------------------------
-- TABELA: estado
-- Propósito: Armazenar as unidades federativas para localização de cidades e pessoas.
-- Campos:
--   id: Identificador único e incremental.
--   nome: Campo reservado para o nome completo do Estado.
--   sigla: Campo reservado para a sigla de 2 caracteres do Estado.
-- ---------------------------------------------------------
CREATE TABLE estado (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    nome VARCHAR(100) NOT NULL,
    sigla CHAR(2) NOT NULL
);

-- ---------------------------------------------------------
-- TABELA: cidade
-- Propósito: Armazenar os municípios vinculados a um Estado.
-- Campos:
--   id: Identificador único da cidade.
--   nome: Campo reservado para o nome da cidade.
--   estado_id: Chave que liga a cidade ao seu respectivo Estado.
-- ---------------------------------------------------------
CREATE TABLE cidade (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    nome VARCHAR(100) NOT NULL,
    estado_id INT,
    FOREIGN KEY (estado_id) REFERENCES estado(id)
);

-- ---------------------------------------------------------
-- TABELA: grupo
-- Propósito: Definir papéis (Administrador, Advogado, Cliente, etc.) que uma pessoa pode assumir.
-- Campos:
--   id: Identificador único do grupo.
--   nome: Campo reservado para o nome do grupo funcional.
-- ---------------------------------------------------------
CREATE TABLE grupo (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    nome VARCHAR(50) NOT NULL
);

-- ---------------------------------------------------------
-- TABELA: especialidade
-- Propósito: Listar as áreas de atuação jurídica (Direito Civil, Penal, etc.).
-- Campos:
--   id: Identificador único da especialidade.
--   nome: Campo reservado para a descrição da área do Direito.
-- ---------------------------------------------------------
CREATE TABLE especialidade (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    nome VARCHAR(100) NOT NULL
);

-- ---------------------------------------------------------
-- TABELA: pessoa (Usando Single Table Inheritance para Simplificar)
-- Propósito: Entidade central que armazena dados de clientes, advogados e colaboradores.
-- Campos:
--   id: Identificador único da pessoa.
--   nome: Campo reservado para o nome completo ou razão social.
--   tipo: Define se é pessoa Física (F) ou Jurídica (J).
--   oab: Registro profissional (se for advogado).
--   ativo: Indica se o cadastro está ativo no sistema.
--   (Demais campos como email, telefone, cpf/cnpj conforme diagrama).
-- ---------------------------------------------------------
CREATE TABLE pessoa (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    cidade_id INT,
    nome VARCHAR(255) NOT NULL COMMENT 'Nome completo ou Razão Social',
    endereco VARCHAR(255),
    bairro VARCHAR(100),
    telefone VARCHAR(20),
    email VARCHAR(150) UNIQUE COMMENT 'E-mail usado como login',
    senha VARCHAR(255) COMMENT 'hash da senha de acesso.',
    -- Foto em BLOB grande porque o arquivo padrao e fotos reais podem ultrapassar 64 KB.
    foto MEDIUMBLOB,
    ativo BOOLEAN DEFAULT TRUE,
    observacoes TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME ON UPDATE CURRENT_TIMESTAMP,
    -- Campos de Colaborador
    cargo VARCHAR(100),
    oab VARCHAR(20),
    oab_uf CHAR(2),
    contratado_em DATETIME,
    demitido_em DATETIME,
    salario DECIMAL(10,2),
    -- Campos de PessoaFisica
    cpf VARCHAR(14) UNIQUE COMMENT 'CPF usado como login (Apenas Números)',
    rg VARCHAR(20),
    dt_nascimento DATETIME,
    sexo CHAR(1) DEFAULT 'M' COMMENT 'M - masculino, F - feminino',
    -- Campos de PessoaJuridica
    cnpj VARCHAR(18),
    nome_fantasia VARCHAR(255),
    razao_social VARCHAR(255),
    inscricao_estadual VARCHAR(50),
    FOREIGN KEY (cidade_id) REFERENCES cidade(id)
);

-- ---------------------------------------------------------
-- TABELA: tipo_acao
-- Propósito: Classificar a natureza da ação (Trabalhista, Cível, etc.).
-- Campos:
--   id: Identificador do tipo.
--   nome: Campo reservado para a categoria da ação judicial.
-- ---------------------------------------------------------
CREATE TABLE tipo_acao (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    nome VARCHAR(100) NOT NULL
);

-- ---------------------------------------------------------
-- TABELA: acao
-- Propósito: Registrar os nomes específicos das petições/ações judiciais.
-- Campos:
--   id: Identificador da ação.
--   nome: Nome da ação (ex: Reclamação Trabalhista).
--   tipo_acao_id: Vínculo obrigatório com um tipo_acao.
-- ---------------------------------------------------------
CREATE TABLE acao (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    nome VARCHAR(255) NOT NULL,
    tipo_acao_id INT NOT NULL,
    FOREIGN KEY (tipo_acao_id) REFERENCES tipo_acao(id)
);

-- ---------------------------------------------------------
-- TABELA: vara
-- Propósito: Identificar a vara judicial e comarca onde tramita o processo.
-- Campos:
--   id: Identificador da vara.
--   nome: Campo reservado para o nome da vara (ex: 1ª Vara Cível).
--   cidade_id: Localidade da vara.
-- ---------------------------------------------------------
CREATE TABLE vara (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cidade_id INT,
    FOREIGN KEY (cidade_id) REFERENCES cidade(id)
);

-- ---------------------------------------------------------
-- TABELA: processo
-- Propósito: Classe pivô que centraliza os dados jurídicos de um caso.
-- Campos:
--   nr_processo: Número oficial do processo (Chave Primária).
--   titulo: Breve descrição ou nome do caso.
--   status: Situação atual (Ativo, Suspenso, Arquivado, Encerrado).
--   registrado_por: ID da pessoa que cadastrou o processo.
-- ---------------------------------------------------------
CREATE TABLE processo (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    numero_processo VARCHAR(100) UNIQUE NOT NULL,
    titulo VARCHAR(255),
    valor DECIMAL(15,2),
    status ENUM('Ativo', 'Suspenso', 'Arquivado', 'Encerrado'),
    resumo TEXT,
    registrado_por INT COMMENT 'FK que Recebe os dados de Pessoa',
    vara_id INT,
    acao_id INT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vara_id) REFERENCES vara(id),
    FOREIGN KEY (acao_id) REFERENCES acao(id),
    FOREIGN KEY (registrado_por) REFERENCES pessoa(id)
);

-- Tabela de Domínio: Armazena as opções (ex: "Autor", "Réu", "Advogado do Autor", "Testemunha").
CREATE TABLE papel_processo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    figura_como VARCHAR(150) NOT NULL -- Ex: 'Autor', 'Réu'
);

-- ---------------------------------------------------------
-- TABELA: tarefa
-- Propósito: Agendar diligências, prazos e lembretes vinculados ou não a processos.
-- Campos:
--   id: Identificador da tarefa.
--   prioridade: Nível de urgência (Alta, Baixa, Emergência, Urgente).
--   status: Situação (Aberta, Em Andamento, Concluída, Cancelada).
-- ---------------------------------------------------------
CREATE TABLE tarefa (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    titulo VARCHAR(255),
    descricao TEXT,
    prioridade ENUM('Alta', 'Baixa', 'Emergencia', 'Urgente'),
    dt_vencimento DATETIME,
    dt_conclusao  DATETIME,
    registrado_por INT COMMENT 'FK que Recebe os dados de Pessoa',
    criado_em DATETIME,
    FOREIGN KEY (registrado_por) REFERENCES pessoa(id)
);

-- ---------------------------------------------------------
-- TABELA: audiencia
-- Propósito: Registrar os agendamentos judiciais de cada processo.
-- Campos:
--   agendada_para: Data e hora do evento.
--   situacao: Estado da audiência (Concluída, Cancelada, Remarcada).
-- ---------------------------------------------------------
CREATE TABLE audiencia (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    processo_id INT,
    dt_audiencia DATETIME,
    local VARCHAR(255),
    juiz VARCHAR(255),
    advogado_id INT,
    situacao ENUM('Concluida', 'Cancelada', 'Remarcada'),
    observacoes TEXT,
    registrado_por INT COMMENT 'FK que Recebe os dados de Pessoa',
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (processo_id) REFERENCES processo(id),
    FOREIGN KEY (advogado_id) REFERENCES pessoa(id)
);

-- ---------------------------------------------------------
-- TABELA: tipo_doc
-- Propósito: Classificar documentos (RG, CPF, Contrato, etc).
-- ---------------------------------------------------------
CREATE TABLE tipo_doc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

-- ---------------------------------------------------------
-- TABELA: documento
-- Propósito: Armazenar metadados e caminhos de arquivos vinculados a processos.
-- Campos:
--   caminho: Localização física do arquivo no servidor.
--   mime: Tipo de arquivo (PDF, JPEG, etc).
-- ---------------------------------------------------------
CREATE TABLE documento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_doc_id INT,
    processo_id INT,
    nome_arquivo VARCHAR(255),
    caminho VARCHAR(500),
    descricao TEXT,
    mime VARCHAR(50),
    tamanho INT,
    enviado_por INT,
    criado_em DATETIME,
    atualizado_em DATETIME,
    FOREIGN KEY (tipo_doc_id) REFERENCES tipo_doc(id),
    FOREIGN KEY (processo_id) REFERENCES processo(id),
    FOREIGN KEY (enviado_por) REFERENCES pessoa(id)
);

-- ---------------------------------------------------------
-- TABELA: movimentacao
-- Propósito: Gestão financeira (contas a pagar e receber) do escritório.
-- Campos:
--   tipo_mov: Define se é 'A Pagar' ou 'A Receber'.
--   direcao: Define fluxo de caixa 'Entrada' ou 'Saida'.
--   metodo: Dinheiro, Transferência, Cartão, Cheque, Boleto, Pix, Outro
--   status: Pendente, Paga, Parcialmente Paga, Vencida ou Cancelada.
-- ---------------------------------------------------------
CREATE TABLE movimentacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pessoa_id INT,
    dt_pagamento DATETIME,
    tipo_mov ENUM('A_Pagar', 'A_Receber'),
    direcao ENUM('Saida', 'Entrada'),
    descricao VARCHAR(255),
    valor DECIMAL(15,2),
    dt_vencimento DATETIME,
    dt_emissao DATETIME,
    paga_em DATETIME,
    metodo ENUM('Dinheiro', 'Transferencia', 'Cartão', 'Cheque', 'Boleto', 'Pix', 'Outro'),
    status ENUM('Pendente', 'Paga', 'Parcialmente_Paga', 'Vencida', 'Cancelada'),
    observacoes TEXT,
    registrado_por INT,
    criado_em DATETIME,
    atualizado_em DATETIME,
    FOREIGN KEY (pessoa_id) REFERENCES pessoa(id),
    FOREIGN KEY (registrado_por) REFERENCES pessoa(id)
);

-- ---------------------------------------------------------
-- TABELA: andamento_processual
-- Propósito: Registrar o histórico de eventos e evoluções de cada processo.
-- ---------------------------------------------------------
CREATE TABLE andamento_processual (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    processo_id INT NOT NULL,
    dt_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    descricao TEXT NOT NULL COMMENT 'O fato ocorrido (ex: Despacho Proferido) - Detalhes adicionais ou notas internas',
    registrado_por INT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME ON UPDATE CURRENT_TIMESTAMP,
    -- Chave Estrangeira: Garante que o andamento pertença a um processo real
    CONSTRAINT fk_andamento_processo
        FOREIGN KEY (processo_id)
        REFERENCES processo(id)
        ON DELETE CASCADE -- Se o processo for deletado, os andamentos também serão
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- TABELAS DE LIGAÇÃO (RELACIONAMENTOS MUITOS-PARA-MUITOS)
-- =========================================================

-- Ligação pessoa e grupo (Uma pessoa em vários grupos)
CREATE TABLE pessoa_grupo (
    pessoa_id INT,
    grupo_id INT,
    PRIMARY KEY (pessoa_id, grupo_id),
    FOREIGN KEY (pessoa_id) REFERENCES pessoa(id),
    FOREIGN KEY (grupo_id) REFERENCES grupo(id)
);

-- Ligação pessoa e Especialidade (Um advogado com várias especialidades)
CREATE TABLE pessoa_especialidade (
    pessoa_id INT,
    especialidade_id INT,
    PRIMARY KEY (pessoa_id, especialidade_id),
    FOREIGN KEY (pessoa_id) REFERENCES pessoa(id),
    FOREIGN KEY (especialidade_id) REFERENCES especialidade(id)
);

-- Ligação pessoa e tarefa (Uma tarefa para várias pessoas e vice-versa)
CREATE TABLE pessoa_tarefa (
    pessoa_id INT,
    tarefa_id INT,
    anotacoes TEXT,
    status ENUM('Aberta', 'Em_Andamento', 'Concluida', 'Cancelada'),
    atualizado_em DATETIME,
    PRIMARY KEY (pessoa_id, tarefa_id),
    FOREIGN KEY (pessoa_id) REFERENCES pessoa(id),
    FOREIGN KEY (tarefa_id) REFERENCES tarefa(id)
);

-- Ligação pessoa e processo (Várias partes/advogados em um processo)
CREATE TABLE pessoa_processo (
    pessoa_id INT,
    processo_id INT,
    papel_processo_id INT, -- Ex: Cliente, Advogado, Parte Contraria
    PRIMARY KEY (pessoa_id, processo_id, papel_processo_id),
    FOREIGN KEY (pessoa_id) REFERENCES pessoa(id),
    FOREIGN KEY (processo_id) REFERENCES processo(id),
    FOREIGN KEY (papel_processo_id) REFERENCES papel_processo(id)
);

-- Tabela de Ligação para pessoa_processo e audiencia
CREATE TABLE audiencia_participante (
    audiencia_id INT NOT NULL,
    pessoa_id INT NOT NULL,
    processo_id INT NOT NULL,
    confirmou_presenca CHAR(1) DEFAULT 'N' COMMENT 'S - SIM ou N - NÃO',
    PRIMARY KEY (audiencia_id, pessoa_id, processo_id),
    FOREIGN KEY (audiencia_id) REFERENCES audiencia(id),
    FOREIGN KEY (pessoa_id, processo_id) REFERENCES pessoa_processo(pessoa_id, processo_id)
);

-- Adicionado campos de segurança na tabela pessoa
ALTER TABLE Pessoa ADD COLUMN recovery_token VARCHAR(100), ADD COLUMN token_expiration DATETIME;

-- TABELA: permissao (Armazena as ações do sistema)
CREATE TABLE IF NOT EXISTS permissao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao VARCHAR(255)
    ) ENGINE=InnoDB;

-- TABELA: grupo_permissao (Liga grupos às ações)
CREATE TABLE IF NOT EXISTS grupo_permissao (
    grupo_id INT NOT NULL,
    permissao_id INT NOT NULL,
    PRIMARY KEY (grupo_id, permissao_id),
    FOREIGN KEY (grupo_id) REFERENCES grupo(id) ON DELETE CASCADE,
    FOREIGN KEY (permissao_id) REFERENCES permissao(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

-- CARGA INICIAL DE TESTE
INSERT INTO permissao (nome, descricao) VALUES ('financeiro', 'Acesso ao módulo financeiro');
INSERT INTO grupo_permissao (grupo_id, permissao_id) VALUES (1, 1); -- Grupo 1 pode ver financeiro
INSERT INTO pessoa_grupo (pessoa_id, grupo_id) VALUES (1, 1); -- Usuário 1 pertence ao grupo 1
