-- Configuração dos Menus de Desktop para Manuais e LGPD

SET session_replication_role = 'replica';

-- =========================================================================
-- 1. MÓDULO MANUAIS (DENTRO DE DB:EDUCAÇÃO)
-- =========================================================================
-- Item de Nível Módulo (Manuais)
INSERT INTO db_itensmenu (id_item, descricao, help, funcao, itemativo, manutencao, desctec)
VALUES (3000200, 'Manuais', 'Módulo de Manuais da Educação', '', '1', '1', 'Módulo Manuais')
ON CONFLICT (id_item) DO UPDATE SET descricao = 'Manuais', itemativo = '1';

-- Item de Nível Tela/Ação (Manuais)
INSERT INTO db_itensmenu (id_item, descricao, help, funcao, itemativo, manutencao, desctec)
VALUES (3000201, 'Manuais', 'Manuais e Guias da Educação', 'edu4_manuaiseducacao001.php', '1', '1', 'Tela de Manuais')
ON CONFLICT (id_item) DO UPDATE SET funcao = 'edu4_manuaiseducacao001.php', itemativo = '1';

-- Vínculo 1: DB:EDUCAÇÃO (3000092) -> Módulo Manuais (3000200)
DELETE FROM db_menu WHERE id_item_filho IN (3000200, 3000201);
INSERT INTO db_menu (id_item, id_item_filho, modulo, menusequencia)
VALUES (3000092, 3000200, 1, 999);

-- Vínculo 2: Módulo Manuais (3000200) -> Tela Manuais (3000201)
INSERT INTO db_menu (id_item, id_item_filho, modulo, menusequencia)
VALUES (3000200, 3000201, 1, 1);

-- =========================================================================
-- 2. MÓDULO LGPD - GOVERNANÇA (DENTRO DE DB:CONFIGURAÇÃO)
-- =========================================================================
-- Item de Nível Módulo (LGPD - Governança)
INSERT INTO db_itensmenu (id_item, descricao, help, funcao, itemativo, manutencao, desctec)
VALUES (3000300, 'LGPD - Governança', 'Governança e Conformidade LGPD', '', '1', '1', 'Módulo LGPD')
ON CONFLICT (id_item) DO UPDATE SET descricao = 'LGPD - Governança', itemativo = '1';

-- Item de Nível Tela (Painel de Governança LGPD)
INSERT INTO db_itensmenu (id_item, descricao, help, funcao, itemativo, manutencao, desctec)
VALUES (3000301, 'Painel de Governança LGPD', 'Painel ROPA e Dossiê do Titular', 'con4_lgpd_painel001.php', '1', '1', 'Painel LGPD')
ON CONFLICT (id_item) DO UPDATE SET funcao = 'con4_lgpd_painel001.php', itemativo = '1';

-- Vínculo na área de Configuração (3000098)
DELETE FROM db_menu WHERE id_item_filho IN (3000300, 3000301);
INSERT INTO db_menu (id_item, id_item_filho, modulo, menusequencia)
VALUES (3000098, 3000300, 1, 999);

INSERT INTO db_menu (id_item, id_item_filho, modulo, menusequencia)
VALUES (3000300, 3000301, 1, 1);

-- =========================================================================
-- 3. PERMISSÕES DE ACESSO PARA TODOS OS USUÁRIOS
-- =========================================================================
INSERT INTO db_permissao (id_usuario, id_item, permissaoativa, anousu, id_instit)
SELECT u.id_usuario, m.id_item, '1', 2026, 1
FROM db_usuarios u
CROSS JOIN (VALUES (3000200), (3000201), (3000300), (3000301)) AS m(id_item)
WHERE NOT EXISTS (
    SELECT 1 FROM db_permissao p 
    WHERE p.id_usuario = u.id_usuario AND p.id_item = m.id_item AND p.anousu = 2026
);

SET session_replication_role = 'origin';

