-- ============================================================================
-- Dados iniciais de acesso
-- ============================================================================

INSERT IGNORE INTO grupo (id, nome) VALUES
    (1, 'admin'),
    (2, 'advogado'),
    (3, 'assistente'),
    (4, 'financeiro'),
    (5, 'estagiario');

INSERT IGNORE INTO permissao (id, nome, descricao) VALUES
    (1, 'usuario.gerenciar', 'Criar, editar e remover usuarios'),
    (2, 'cidades.visualizar', 'Visualizar cadastro de cidades'),
    (3, 'especialidade.visualizar', 'Visualizar cadastro de especialidades'),
    (4, 'cliente.visualizar', 'Visualizar clientes'),
    (5, 'cliente.cadastrar', 'Cadastrar clientes'),
    (6, 'cliente.editar', 'Editar clientes'),
    (7, 'processo.visualizar', 'Visualizar processos'),
    (8, 'processo.cadastrar', 'Cadastrar processos'),
    (9, 'processo.editar', 'Editar processos'),
    (10, 'processo.excluir', 'Excluir processos'),
    (11, 'andamento.registrar', 'Registrar andamento processual'),
    (12, 'documento.visualizar', 'Visualizar documentos'),
    (13, 'documento.anexar', 'Anexar documentos'),
    (14, 'audiencia.visualizar', 'Visualizar audiencias'),
    (15, 'audiencia.agendar', 'Agendar audiencias'),
    (16, 'tarefa.visualizar', 'Visualizar tarefas'),
    (17, 'tarefa.cadastrar', 'Cadastrar tarefas'),
    (18, 'tarefa.editar', 'Editar tarefas'),
    (19, 'movimentacao.visualizar', 'Visualizar movimentacoes'),
    (20, 'movimentacao.cadastrar', 'Cadastrar movimentacoes'),
    (21, 'movimentacao.editar', 'Editar movimentacoes');

INSERT IGNORE INTO grupo_permissao (grupo_id, permissao_id) VALUES
    (1, 1),(1, 2),(1, 3),(1, 4),(1, 5),(1, 6),(1, 7),(1, 8),(1, 9),(1, 10),
    (1, 11),(1, 12),(1, 13),(1, 14),(1, 15),(1, 16),(1, 17),(1, 18),(1, 19),(1, 20),(1, 21),
    (2, 4),(2, 7),(2, 8),(2, 9),(2, 11),(2, 12),(2, 13),(2, 14),(2, 15),(2, 16),(2, 17),(2, 18),
    (3, 4),(3, 5),(3, 6),(3, 7),(3, 12),(3, 13),(3, 14),(3, 16),(3, 17),(3, 18),
    (4, 19),(4, 20),(4, 21),
    (5, 4),(5, 7),(5, 12),(5, 14),(5, 16);

-- =====================================================
-- Test data (from docker/mysql/init/02-seed-test.sql)
-- =====================================================

SET NAMES utf8mb4;
USE advocacia;

START TRANSACTION;

-- ---------------------------------------------------------------------------
-- Base geografica
-- ---------------------------------------------------------------------------

INSERT IGNORE INTO estado (nome, sigla) VALUES
    ('Sao Paulo', 'SP'),
    ('Rio de Janeiro', 'RJ');

INSERT IGNORE INTO cidade (nome, estado_id)
SELECT 'Sao Paulo', e.id FROM estado e WHERE e.sigla = 'SP';

INSERT IGNORE INTO cidade (nome, estado_id)
SELECT 'Campinas', e.id FROM estado e WHERE e.sigla = 'SP';

INSERT IGNORE INTO cidade (nome, estado_id)
SELECT 'Rio de Janeiro', e.id FROM estado e WHERE e.sigla = 'RJ';

INSERT IGNORE INTO cidade (nome, estado_id)
SELECT 'Niteroi', e.id FROM estado e WHERE e.sigla = 'RJ';

-- ---------------------------------------------------------------------------
-- Grupos e permissoes
-- ---------------------------------------------------------------------------

INSERT IGNORE INTO grupo (nome) VALUES
    ('admin'),
    ('advogado'),
    ('assistente'),
    ('financeiro'),
    ('estagiario');

INSERT IGNORE INTO permissao (nome, descricao) VALUES
    ('usuario.gerenciar', 'Criar, editar e remover usuarios'),
    ('cidades.visualizar', 'Visualizar cadastro de cidades'),
    ('especialidade.visualizar', 'Visualizar cadastro de especialidades'),
    ('cliente.visualizar', 'Visualizar clientes'),
    ('cliente.cadastrar', 'Cadastrar clientes'),
    ('cliente.editar', 'Editar clientes'),
    ('processo.visualizar', 'Visualizar processos'),
    ('processo.cadastrar', 'Cadastrar processos'),
    ('processo.editar', 'Editar processos'),
    ('processo.excluir', 'Excluir processos'),
    ('andamento.registrar', 'Registrar andamento processual'),
    ('documento.visualizar', 'Visualizar documentos'),
    ('documento.anexar', 'Anexar documentos'),
    ('audiencia.visualizar', 'Visualizar audiencias'),
    ('audiencia.agendar', 'Agendar audiencias'),
    ('tarefa.visualizar', 'Visualizar tarefas'),
    ('tarefa.cadastrar', 'Cadastrar tarefas'),
    ('tarefa.editar', 'Editar tarefas'),
    ('movimentacao.visualizar', 'Visualizar movimentacoes'),
    ('movimentacao.cadastrar', 'Cadastrar movimentacoes'),
    ('movimentacao.editar', 'Editar movimentacoes');

-- Admin recebe tudo
INSERT IGNORE INTO grupo_permissao (grupo_id, permissao_id)
SELECT g.id, p.id
FROM grupo g
CROSS JOIN permissao p
WHERE g.nome = 'admin';

-- Advogado
INSERT IGNORE INTO grupo_permissao (grupo_id, permissao_id)
SELECT g.id, p.id
FROM grupo g
JOIN permissao p ON p.nome IN (
    'cliente.visualizar',
    'processo.visualizar',
    'processo.cadastrar',
    'processo.editar',
    'andamento.registrar',
    'documento.visualizar',
    'documento.anexar',
    'audiencia.visualizar',
    'audiencia.agendar',
    'tarefa.visualizar',
    'tarefa.cadastrar',
    'tarefa.editar'
)
WHERE g.nome = 'advogado';

-- Assistente
INSERT IGNORE INTO grupo_permissao (grupo_id, permissao_id)
SELECT g.id, p.id
FROM grupo g
JOIN permissao p ON p.nome IN (
    'cliente.visualizar',
    'cliente.cadastrar',
    'cliente.editar',
    'processo.visualizar',
    'documento.visualizar',
    'documento.anexar',
    'audiencia.visualizar',
    'tarefa.visualizar',
    'tarefa.cadastrar',
    'tarefa.editar'
)
WHERE g.nome = 'assistente';

-- Financeiro
INSERT IGNORE INTO grupo_permissao (grupo_id, permissao_id)
SELECT g.id, p.id
FROM grupo g
JOIN permissao p ON p.nome IN (
    'movimentacao.visualizar',
    'movimentacao.cadastrar',
    'movimentacao.editar'
)
WHERE g.nome = 'financeiro';

-- Estagiario
INSERT IGNORE INTO grupo_permissao (grupo_id, permissao_id)
SELECT g.id, p.id
FROM grupo g
JOIN permissao p ON p.nome IN (
    'cliente.visualizar',
    'processo.visualizar',
    'documento.visualizar',
    'audiencia.visualizar',
    'tarefa.visualizar'
)
WHERE g.nome = 'estagiario';

-- ---------------------------------------------------------------------------
-- Especialidades e catalogos juridicos
-- ---------------------------------------------------------------------------

INSERT IGNORE INTO especialidade (nome) VALUES
    ('Civil'),
    ('Trabalhista'),
    ('Familia'),
    ('Empresarial');

INSERT IGNORE INTO tipo_acao (nome) VALUES
    ('Cobranca'),
    ('Trabalhista'),
    ('Familia'),
    ('Empresarial');

INSERT IGNORE INTO tipo_documento (nome) VALUES
    ('Peticao'),
    ('Contrato'),
    ('Procuracao'),
    ('Comprovante'),
    ('Sentenca');

INSERT IGNORE INTO acao (nome, tipo_acao_id)
SELECT 'Acao de Cobranca', ta.id
FROM tipo_acao ta
WHERE ta.nome = 'Cobranca';

INSERT IGNORE INTO acao (nome, tipo_acao_id)
SELECT 'Reclamacao Trabalhista', ta.id
FROM tipo_acao ta
WHERE ta.nome = 'Trabalhista';

-- ---------------------------------------------------------------------------
-- Pessoas
-- ---------------------------------------------------------------------------

INSERT IGNORE INTO pessoa
    (nome, endereco, bairro, telefone, email, senha, foto, tipo, ativo, observacoes, cidade_id, cpf, rg, dt_nascimento, sexo, cnpj, nome_fantasia, razao_social, inscricao_estadual, cargo, oab, oab_uf, contratado_em, demitido_em, salario)
VALUES
    ('Admin Sistema', 'Rua Central, 100', 'Centro', '(11) 90000-0001', 'admin@advocacia.local', SHA2('123456', 256), NULL, 'F', 'S', 'Usuario administrador do sistema', (SELECT id FROM cidade WHERE nome = 'Sao Paulo' LIMIT 1), '11111111111', 'RGADMIN', '1985-01-15 00:00:00', 'M', NULL, NULL, NULL, NULL, 'Socio', NULL, NULL, '2024-01-02 00:00:00', NULL, 15000.00),
    ('Dr. Paulo Nogueira', 'Rua dos Advogados, 50', 'Jardim Paulista', '(11) 90000-0002', 'paulo@advocacia.local', SHA2('123456', 256), NULL, 'F', 'S', 'Advogado responsavel', (SELECT id FROM cidade WHERE nome = 'Sao Paulo' LIMIT 1), '22222222222', 'RGPAULO', '1988-04-10 00:00:00', 'M', NULL, NULL, NULL, NULL, 'Advogado Senior', '12345', 'SP', '2024-02-01 00:00:00', NULL, 18000.00),
    ('Mariana Souza', 'Av. Comercial, 200', 'Centro', '(11) 90000-0003', 'mariana@advocacia.local', SHA2('123456', 256), NULL, 'F', 'S', 'Assistente juridica', (SELECT id FROM cidade WHERE nome = 'Campinas' LIMIT 1), '33333333333', 'RGMAR', '1991-09-22 00:00:00', 'F', NULL, NULL, NULL, NULL, 'Assistente Juridico', NULL, NULL, '2024-03-05 00:00:00', NULL, 5200.00),
    ('Carlos Lima', 'Rua Financeira, 77', 'Centro', '(21) 90000-0004', 'carlos@advocacia.local', SHA2('123456', 256), NULL, 'F', 'S', 'Gestor financeiro', (SELECT id FROM cidade WHERE nome = 'Rio de Janeiro' LIMIT 1), '44444444444', 'RGCARLOS', '1987-06-30 00:00:00', 'M', NULL, NULL, NULL, NULL, 'Gestor Financeiro', NULL, NULL, '2024-04-01 00:00:00', NULL, 8000.00),
    ('Lucas Ferreira', 'Rua do Estagio, 15', 'Centro', '(21) 90000-0005', 'lucas@advocacia.local', SHA2('123456', 256), NULL, 'F', 'S', 'Estagiario de direito', (SELECT id FROM cidade WHERE nome = 'Niteroi' LIMIT 1), '55555555555', 'RGLUCAS', '2001-11-18 00:00:00', 'M', NULL, NULL, NULL, NULL, 'Estagiario de Direito', NULL, NULL, '2025-01-10 00:00:00', NULL, 1800.00),
    ('Joao Silva', 'Rua do Cliente, 10', 'Centro', '(11) 98888-0001', 'joao.silva@email.com', SHA2('123456', 256), NULL, 'F', 'S', 'Cliente pessoa fisica', (SELECT id FROM cidade WHERE nome = 'Sao Paulo' LIMIT 1), '66666666666', 'RGJOAO', '1990-02-12 00:00:00', 'M', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
    ('Empresa Alfa LTDA', 'Av. Industrial, 500', 'Distrito', '(21) 98888-0002', 'contato@empresaalfa.com', SHA2('123456', 256), NULL, 'J', 'S', 'Cliente pessoa juridica', (SELECT id FROM cidade WHERE nome = 'Rio de Janeiro' LIMIT 1), NULL, NULL, NULL, NULL, '12345678000199', 'Empresa Alfa', 'Empresa Alfa LTDA', '123456789', NULL, NULL, NULL, NULL, NULL, NULL),
    ('Empresa Beta LTDA', 'Av. Empresarial, 800', 'Centro', '(21) 98888-0003', 'juridico@empresabeta.com', NULL, NULL, 'J', 'S', 'Parte contraria', (SELECT id FROM cidade WHERE nome = 'Campinas' LIMIT 1), NULL, NULL, NULL, NULL, '98765432000188', 'Empresa Beta', 'Empresa Beta LTDA', '987654321', NULL, NULL, NULL, NULL, NULL, NULL);

INSERT IGNORE INTO pessoa_grupo (pessoa_id, grupo_id)
SELECT p.id, g.id
FROM pessoa p
JOIN grupo g ON g.nome = 'admin'
WHERE p.email = 'admin@advocacia.local';

INSERT IGNORE INTO pessoa_grupo (pessoa_id, grupo_id)
SELECT p.id, g.id
FROM pessoa p
JOIN grupo g ON g.nome = 'advogado'
WHERE p.email = 'paulo@advocacia.local';

INSERT IGNORE INTO pessoa_grupo (pessoa_id, grupo_id)
SELECT p.id, g.id
FROM pessoa p
JOIN grupo g ON g.nome = 'assistente'
WHERE p.email = 'mariana@advocacia.local';

INSERT IGNORE INTO pessoa_grupo (pessoa_id, grupo_id)
SELECT p.id, g.id
FROM pessoa p
JOIN grupo g ON g.nome = 'financeiro'
WHERE p.email = 'carlos@advocacia.local';

INSERT IGNORE INTO pessoa_grupo (pessoa_id, grupo_id)
SELECT p.id, g.id
FROM pessoa p
JOIN grupo g ON g.nome = 'estagiario'
WHERE p.email = 'lucas@advocacia.local';

INSERT IGNORE INTO pessoa_especialidade (pessoa_id, especialidade_id)
SELECT p.id, e.id
FROM pessoa p
JOIN especialidade e ON e.nome = 'Civil'
WHERE p.email = 'paulo@advocacia.local';

INSERT IGNORE INTO pessoa_especialidade (pessoa_id, especialidade_id)
SELECT p.id, e.id
FROM pessoa p
JOIN especialidade e ON e.nome = 'Familia'
WHERE p.email = 'paulo@advocacia.local';

-- ---------------------------------------------------------------------------
-- Estrutura juridica
-- ---------------------------------------------------------------------------

INSERT IGNORE INTO vara (nome, cidade_id)
SELECT '1a Vara Civel', c.id FROM cidade c WHERE c.nome = 'Sao Paulo' LIMIT 1;

INSERT IGNORE INTO vara (nome, cidade_id)
SELECT '2a Vara Trabalhista', c.id FROM cidade c WHERE c.nome = 'Campinas' LIMIT 1;

INSERT IGNORE INTO processo
    (numero_processo, titulo, valor, status, resumo, registrado_por, vara_id, acao_id)
VALUES
    ('0001234-56.2026.8.26.0100', 'Acao de cobranca - Joao Silva', 25000.00, 'Ativo', 'Processo de cobranca para teste da aplicacao.', (SELECT id FROM pessoa WHERE email = 'admin@advocacia.local' LIMIT 1), (SELECT id FROM vara WHERE nome = '1a Vara Civel' LIMIT 1), (SELECT id FROM acao WHERE nome = 'Acao de Cobranca' LIMIT 1)),
    ('0009876-54.2026.8.26.0100', 'Reclamacao trabalhista - Empresa Alfa', 42000.00, 'Suspenso', 'Processo trabalhista para teste da aplicacao.', (SELECT id FROM pessoa WHERE email = 'paulo@advocacia.local' LIMIT 1), (SELECT id FROM vara WHERE nome = '2a Vara Trabalhista' LIMIT 1), (SELECT id FROM acao WHERE nome = 'Reclamacao Trabalhista' LIMIT 1));

INSERT IGNORE INTO pessoa_processo (pessoa_id, processo_id, figura_como)
SELECT p.id, pr.id, 'Autor'
FROM pessoa p
JOIN processo pr ON pr.numero_processo = '0001234-56.2026.8.26.0100'
WHERE p.email = 'joao.silva@email.com';

INSERT IGNORE INTO pessoa_processo (pessoa_id, processo_id, figura_como)
SELECT p.id, pr.id, 'Advogado do Autor'
FROM pessoa p
JOIN processo pr ON pr.numero_processo = '0001234-56.2026.8.26.0100'
WHERE p.email = 'paulo@advocacia.local';

INSERT IGNORE INTO pessoa_processo (pessoa_id, processo_id, figura_como)
SELECT p.id, pr.id, 'Reu'
FROM pessoa p
JOIN processo pr ON pr.numero_processo = '0001234-56.2026.8.26.0100'
WHERE p.email = 'juridico@empresabeta.com';

INSERT IGNORE INTO pessoa_processo (pessoa_id, processo_id, figura_como)
SELECT p.id, pr.id, 'Reclamante'
FROM pessoa p
JOIN processo pr ON pr.numero_processo = '0009876-54.2026.8.26.0100'
WHERE p.email = 'mariana@advocacia.local';

INSERT IGNORE INTO pessoa_processo (pessoa_id, processo_id, figura_como)
SELECT p.id, pr.id, 'Advogado do Reclamante'
FROM pessoa p
JOIN processo pr ON pr.numero_processo = '0009876-54.2026.8.26.0100'
WHERE p.email = 'paulo@advocacia.local';

INSERT IGNORE INTO documento
    (processo_id, tipo_documento_id, nome_arquivo, caminho, descricao, mime, tamanho, enviado_por)
VALUES
    ((SELECT id FROM processo WHERE numero_processo = '0001234-56.2026.8.26.0100' LIMIT 1), (SELECT id FROM tipo_documento WHERE nome = 'Peticao' LIMIT 1), 'peticao_inicial.pdf', 'App/Uploads/processos/0001234-56.2026.8.26.0100/peticao_inicial.pdf', 'Peticao inicial do processo de cobranca', 'application/pdf', 128, (SELECT id FROM pessoa WHERE email = 'paulo@advocacia.local' LIMIT 1)),
    ((SELECT id FROM processo WHERE numero_processo = '0001234-56.2026.8.26.0100' LIMIT 1), (SELECT id FROM tipo_documento WHERE nome = 'Procuracao' LIMIT 1), 'procuracao.pdf', 'App/Uploads/processos/0001234-56.2026.8.26.0100/procuracao.pdf', 'Procuracao assinada', 'application/pdf', 64, (SELECT id FROM pessoa WHERE email = 'mariana@advocacia.local' LIMIT 1)),
    ((SELECT id FROM processo WHERE numero_processo = '0009876-54.2026.8.26.0100' LIMIT 1), (SELECT id FROM tipo_documento WHERE nome = 'Contrato' LIMIT 1), 'contrato_social.pdf', 'App/Uploads/processos/0009876-54.2026.8.26.0100/contrato_social.pdf', 'Contrato social da parte contraria', 'application/pdf', 96, (SELECT id FROM pessoa WHERE email = 'carlos@advocacia.local' LIMIT 1));

INSERT IGNORE INTO andamento_processual
    (processo_id, descricao, dt_registro, registrado_por)
VALUES
    ((SELECT id FROM processo WHERE numero_processo = '0001234-56.2026.8.26.0100' LIMIT 1), 'Distribuicao inicial', '2026-05-02 09:00:00', (SELECT id FROM pessoa WHERE email = 'admin@advocacia.local' LIMIT 1)),
    ((SELECT id FROM processo WHERE numero_processo = '0001234-56.2026.8.26.0100' LIMIT 1), 'Citacao expedida', '2026-05-10 14:30:00', (SELECT id FROM pessoa WHERE email = 'paulo@advocacia.local' LIMIT 1)),
    ((SELECT id FROM processo WHERE numero_processo = '0009876-54.2026.8.26.0100' LIMIT 1), 'Audiencia de conciliacao marcada', '2026-06-01 11:15:00', (SELECT id FROM pessoa WHERE email = 'paulo@advocacia.local' LIMIT 1));

INSERT IGNORE INTO audiencia
    (processo_id, advogado_id, dt_audiencia, local, juiz, situacao, observacoes, registrado_por)
VALUES
    ((SELECT id FROM processo WHERE numero_processo = '0001234-56.2026.8.26.0100' LIMIT 1), (SELECT id FROM pessoa WHERE email = 'paulo@advocacia.local' LIMIT 1), '2026-07-10 10:00:00', 'Forum Central - Sala 2', 'Juiz de Direito', 'Agendada', 'Audiencia inicial de teste', (SELECT id FROM pessoa WHERE email = 'mariana@advocacia.local' LIMIT 1)),
    ((SELECT id FROM processo WHERE numero_processo = '0009876-54.2026.8.26.0100' LIMIT 1), (SELECT id FROM pessoa WHERE email = 'paulo@advocacia.local' LIMIT 1), '2026-07-18 15:00:00', 'VT de Campinas - Sala 1', 'Juiza do Trabalho', 'Agendada', 'Audiencia trabalhista de teste', (SELECT id FROM pessoa WHERE email = 'mariana@advocacia.local' LIMIT 1));

INSERT IGNORE INTO audiencia_participante
    (audiencia_id, pessoa_id, processo_id, confirmou_presenca)
VALUES
    ((SELECT id FROM audiencia WHERE local = 'Forum Central - Sala 2' LIMIT 1), (SELECT id FROM pessoa WHERE email = 'joao.silva@email.com' LIMIT 1), (SELECT id FROM processo WHERE numero_processo = '0001234-56.2026.8.26.0100' LIMIT 1), 1),
    ((SELECT id FROM audiencia WHERE local = 'Forum Central - Sala 2' LIMIT 1), (SELECT id FROM pessoa WHERE email = 'juridico@empresabeta.com' LIMIT 1), (SELECT id FROM processo WHERE numero_processo = '0001234-56.2026.8.26.0100' LIMIT 1), 0),
    ((SELECT id FROM audiencia WHERE local = 'VT de Campinas - Sala 1' LIMIT 1), (SELECT id FROM pessoa WHERE email = 'mariana@advocacia.local' LIMIT 1), (SELECT id FROM processo WHERE numero_processo = '0009876-54.2026.8.26.0100' LIMIT 1), 1);

INSERT IGNORE INTO tarefa
    (titulo, descricao, prioridade, dt_vencimento, dt_conclusao, registrado_por)
VALUES
    ('Protocolar peticao inicial', 'Protocolar peticao no processo de cobranca', 'Urgente', '2026-06-25 18:00:00', NULL, (SELECT id FROM pessoa WHERE email = 'mariana@advocacia.local' LIMIT 1)),
    ('Revisar documentos da audiencia', 'Separar documentos para a audiencia agendada', 'Alta', '2026-07-08 12:00:00', NULL, (SELECT id FROM pessoa WHERE email = 'paulo@advocacia.local' LIMIT 1)),
    ('Acompanhar retorno financeiro', 'Verificar pagamento pendente do cliente', 'Baixa', '2026-06-30 12:00:00', NULL, (SELECT id FROM pessoa WHERE email = 'carlos@advocacia.local' LIMIT 1));

INSERT IGNORE INTO pessoa_tarefa (pessoa_id, tarefa_id, anotacoes, status, atualizado_em)
VALUES
    ((SELECT id FROM pessoa WHERE email = 'paulo@advocacia.local' LIMIT 1), (SELECT id FROM tarefa WHERE titulo = 'Protocolar peticao inicial' LIMIT 1), 'Responsavel pelo protocolo', 'Em_Andamento', '2026-06-22 09:00:00'),
    ((SELECT id FROM pessoa WHERE email = 'mariana@advocacia.local' LIMIT 1), (SELECT id FROM tarefa WHERE titulo = 'Revisar documentos da audiencia' LIMIT 1), 'Separar e conferir provas', 'Aberta', '2026-06-22 09:15:00'),
    ((SELECT id FROM pessoa WHERE email = 'carlos@advocacia.local' LIMIT 1), (SELECT id FROM tarefa WHERE titulo = 'Acompanhar retorno financeiro' LIMIT 1), 'Aguardar comprovante do cliente', 'Aberta', '2026-06-22 09:20:00');

INSERT IGNORE INTO movimentacao
    (pessoa_id, dt_pagamento, tipo_mov, direcao, descricao, valor, dt_vencimento, dt_emissao, paga_em, metodo, status, observacoes, registrado_por)
VALUES
    ((SELECT id FROM pessoa WHERE email = 'joao.silva@email.com' LIMIT 1), NULL, 'A_Receber', 'Entrada', 'Honorarios contratuais do processo de cobranca', 5000.00, '2026-07-05 18:00:00', '2026-06-22 10:00:00', NULL, 'Pix', 'Pendente', 'Parcelamento em 2 vezes', (SELECT id FROM pessoa WHERE email = 'carlos@advocacia.local' LIMIT 1)),
    ((SELECT id FROM pessoa WHERE email = 'admin@advocacia.local' LIMIT 1), NULL, 'A_Pagar', 'Saida', 'Pagamento de software e hospedagem', 850.00, '2026-06-30 18:00:00', '2026-06-22 11:00:00', NULL, 'Boleto', 'Pendente', 'Despesa operacional do escritorio', (SELECT id FROM pessoa WHERE email = 'carlos@advocacia.local' LIMIT 1));

COMMIT;
