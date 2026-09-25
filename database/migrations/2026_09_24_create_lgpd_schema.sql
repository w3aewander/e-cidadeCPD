-- ==============================================================================
-- MIGRAÇÃO IDEMPOTENTE: MÓDULO E MENU LGPD (GOVERNANÇA & CONFORMIDADE)
-- Compatível com PostgreSQL 9.x / 11.x / 14.x / 15.x e-Cidade
-- Seguro: Pode ser executado múltiplas vezes sem duplicar nem quebrar nada.
-- ==============================================================================

BEGIN;

DO $$
DECLARE
    v_mod_id INTEGER := 3000300;
    v_item_id INTEGER := 3000301;
    v_area_id INTEGER := 11; -- Área Configurações
    v_seq_area INTEGER;
BEGIN
    -- 1. Criação do Módulo LGPD em db_modulos
    IF NOT EXISTS (SELECT 1 FROM db_modulos WHERE id_item = v_mod_id) THEN
        INSERT INTO db_modulos (id_item, nomemod, descr_mod, ativo)
        VALUES (v_mod_id, 'LGPD - Governança', 'Painel de Governança, ROPA e Conformidade LGPD', 1);
        RAISE NOTICE 'Módulo LGPD (id_item %) inserido com sucesso em db_modulos.', v_mod_id;
    ELSE
        UPDATE db_modulos 
        SET nomemod = 'LGPD - Governança', 
            descr_mod = 'Painel de Governança, ROPA e Conformidade LGPD', 
            ativo = 1 
        WHERE id_item = v_mod_id;
        RAISE NOTICE 'Módulo LGPD (id_item %) já existia e foi atualizado.', v_mod_id;
    END IF;

    -- 2. Vinculação do Módulo à Área 11 (Configuração) em atendcadareamod
    IF NOT EXISTS (SELECT 1 FROM atendcadareamod WHERE at26_codarea = v_area_id AND at26_id_item = v_mod_id) THEN
        SELECT COALESCE(MAX(at26_sequencia), 0) + 1 INTO v_seq_area FROM atendcadareamod;
        INSERT INTO atendcadareamod (at26_sequencia, at26_codarea, at26_id_item)
        VALUES (v_seq_area, v_area_id, v_mod_id);
        RAISE NOTICE 'Vínculo do módulo % à área % inserido com sequência %.', v_mod_id, v_area_id, v_seq_area;
    END IF;

    -- 3. Criação do Item de Menu em db_itensmenu
    IF NOT EXISTS (SELECT 1 FROM db_itensmenu WHERE id_item = v_item_id) THEN
        INSERT INTO db_itensmenu (id_item, descricao, help, funcao, itemativo, manutencao, desctec, libcliente)
        VALUES (v_item_id, 'Painel de Governança LGPD', 'Acesso ao Painel de Governança, ROPA e Conformidade LGPD', 'con4_lgpd_painel001.php', '1', '1', 'Painel de Governança LGPD', true);
        RAISE NOTICE 'Item de menu % inserido em db_itensmenu.', v_item_id;
    ELSE
        UPDATE db_itensmenu
        SET descricao = 'Painel de Governança LGPD',
            funcao = 'con4_lgpd_painel001.php',
            itemativo = '1',
            manutencao = '1',
            libcliente = true
        WHERE id_item = v_item_id;
        RAISE NOTICE 'Item de menu % atualizado em db_itensmenu.', v_item_id;
    END IF;

    -- 4. Associação Hierárquica em db_menu
    IF NOT EXISTS (SELECT 1 FROM db_menu WHERE id_item = v_mod_id AND id_item_filho = v_item_id) THEN
        INSERT INTO db_menu (id_item, id_item_filho, modulo)
        VALUES (v_mod_id, v_item_id, v_mod_id);
        RAISE NOTICE 'Associação do menu % -> % inserida em db_menu.', v_mod_id, v_item_id;
    END IF;

    -- 5. Garantia de Permissões de Acesso para Usuário Administrador (id_usuario = 1)
    IF NOT EXISTS (SELECT 1 FROM db_permissao WHERE id_item = v_item_id AND id_usuario = 1) THEN
        INSERT INTO db_permissao (id_item, id_usuario, permissao, anousu, id_instit)
        SELECT v_item_id, 1, '1', anousu, codigo 
        FROM db_config
        LIMIT 1;
        RAISE NOTICE 'Permissão concedida ao usuário administrador (id 1).';
    END IF;

END $$;

COMMIT;

