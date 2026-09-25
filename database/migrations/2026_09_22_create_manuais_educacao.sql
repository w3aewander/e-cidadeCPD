BEGIN;

-- 1. Tabela de Manuais da Educacao
CREATE TABLE IF NOT EXISTS escola.manuais_educacao (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    caminho_arquivo VARCHAR(500) NOT NULL,
    nome_arquivo_original VARCHAR(255) NOT NULL,
    tamanho_bytes INTEGER,
    mime_type VARCHAR(50) DEFAULT 'application/pdf',
    id_usuario INTEGER,
    criado_em TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    ativo BOOLEAN DEFAULT TRUE
);

CREATE INDEX IF NOT EXISTS idx_manuais_educacao_ativo ON escola.manuais_educacao(ativo);

-- 2. Itens de Menu e Modulo
-- Modulo Manuais (id_item: 4001840)
DELETE FROM configuracoes.atendcadareamod WHERE at26_id_item = 4001840;
DELETE FROM configuracoes.db_menu WHERE id_item = 4001840 OR modulo = 4001840 OR id_item_filho = 4001841;
DELETE FROM configuracoes.db_modulos WHERE id_item = 4001840;
DELETE FROM configuracoes.db_itensmenu WHERE id_item IN (4001840, 4001841);

INSERT INTO configuracoes.db_itensmenu (id_item, descricao, help, funcao, itemativo, manutencao, desctec, libcliente)
VALUES (4001840, 'Manuais', 'Módulo de Manuais da Educação', '', 1, 1, 'Módulo de Manuais da Educação', true);

INSERT INTO configuracoes.db_modulos (id_item, nome_modulo, descr_modulo, imagem, temexerc, nome_manual)
VALUES (4001840, 'Manuais', 'Manuais', '', false, 'manuais');

INSERT INTO configuracoes.atendcadareamod (at26_sequencia, at26_codarea, at26_id_item)
VALUES ((SELECT COALESCE(MAX(at26_sequencia), 0) + 1 FROM configuracoes.atendcadareamod), 8, 4001840);

-- Item de Menu Filho (id_item: 4001841)
INSERT INTO configuracoes.db_itensmenu (id_item, descricao, help, funcao, itemativo, manutencao, desctec, libcliente)
VALUES (4001841, 'Manuais', 'Manuais da Educação', 'edu4_manuais001.php', 1, 1, 'Manuais da Educação', true);

INSERT INTO configuracoes.db_menu (id_item, id_item_filho, menusequencia, modulo)
VALUES (4001840, 4001841, 1, 4001840);

-- 3. Liberar Modulo e Menu para todos os usuarios em todas as instituicoes ativas
-- db_usermod
INSERT INTO configuracoes.db_usermod (id_instit, id_usuario, id_modulo)
SELECT DISTINCT db_userinst.id_instit, db_usuarios.id_usuario, 4001840
FROM configuracoes.db_usuarios
JOIN configuracoes.db_userinst ON db_userinst.id_usuario = db_usuarios.id_usuario
WHERE NOT EXISTS (
    SELECT 1 FROM configuracoes.db_usermod m 
    WHERE m.id_instit = db_userinst.id_instit 
      AND m.id_usuario = db_usuarios.id_usuario 
      AND m.id_modulo = 4001840
);

-- db_usumod (ano corrente e proximo)
INSERT INTO configuracoes.db_usumod (id_item, anousu, id_usuario, datausu)
SELECT DISTINCT 4001840, u.anousu, u.id_usuario, CURRENT_DATE
FROM (
    SELECT DISTINCT anousu, id_usuario FROM configuracoes.db_usumod WHERE anousu >= EXTRACT(YEAR FROM CURRENT_DATE)::integer - 1
    UNION
    SELECT EXTRACT(YEAR FROM CURRENT_DATE)::integer as anousu, id_usuario FROM configuracoes.db_usuarios
) u
WHERE NOT EXISTS (
    SELECT 1 FROM configuracoes.db_usumod um
    WHERE um.id_item = 4001840 AND um.anousu = u.anousu AND um.id_usuario = u.id_usuario
);

-- db_permissao
INSERT INTO configuracoes.db_permissao (id_usuario, id_item, permissaoativa, anousu, id_instit, id_modulo)
SELECT DISTINCT db_userinst.id_usuario, 4001841, '1', ano.ano, db_userinst.id_instit, 4001840
FROM configuracoes.db_userinst
CROSS JOIN (
    SELECT EXTRACT(YEAR FROM CURRENT_DATE)::integer as ano
    UNION
    SELECT EXTRACT(YEAR FROM CURRENT_DATE)::integer + 1 as ano
) ano
WHERE NOT EXISTS (
    SELECT 1 FROM configuracoes.db_permissao p
    WHERE p.id_usuario = db_userinst.id_usuario
      AND p.id_item = 4001841
      AND p.anousu = ano.ano
      AND p.id_instit = db_userinst.id_instit
      AND p.id_modulo = 4001840
);

COMMIT;
