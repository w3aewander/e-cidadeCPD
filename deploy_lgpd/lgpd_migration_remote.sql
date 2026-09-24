-- ==============================================================================
-- MIGRAÇÃO DEFINITIVA E SEGURA: MÓDULO E MENU LGPD NO SERVIDOR REMOTO
-- ==============================================================================

BEGIN;

DO $$
DECLARE
    v_mod_id   INTEGER := 3000300;
    v_item_id  INTEGER := 3000301;
    v_area_id  INTEGER := 11; -- Área DB:CONFIGURAÇÃO
    v_seq_area INTEGER;
BEGIN
    -- 1. Criação do Módulo LGPD em db_modulos
    IF NOT EXISTS (SELECT 1 FROM db_modulos WHERE id_item = v_mod_id) THEN
        INSERT INTO db_modulos (id_item, nome_modulo, descr_modulo, temexerc, nome_manual)
        VALUES (v_mod_id, 'LGPD - Governança', 'Painel de Governança, ROPA e Conformidade LGPD', false, 'LGPD');
        RAISE NOTICE 'Módulo LGPD (id_item %) inserido em db_modulos.', v_mod_id;
    ELSE
        UPDATE db_modulos 
        SET nome_modulo = 'LGPD - Governança', 
            descr_modulo = 'Painel de Governança, ROPA e Conformidade LGPD'
        WHERE id_item = v_mod_id;
        RAISE NOTICE 'Módulo LGPD (id_item %) atualizado em db_modulos.', v_mod_id;
    END IF;

    -- 2. Vinculação do Módulo à Área 11 (Configuração) em atendcadareamod
    IF NOT EXISTS (SELECT 1 FROM atendcadareamod WHERE at26_id_item = v_mod_id) THEN
        SELECT COALESCE(MAX(at26_sequencia), 0) + 1 INTO v_seq_area FROM atendcadareamod;
        INSERT INTO atendcadareamod (at26_sequencia, at26_codarea, at26_id_item)
        VALUES (v_seq_area, v_area_id, v_mod_id);
        RAISE NOTICE 'Vínculo do módulo % à área % inserido com sequência %.', v_mod_id, v_area_id, v_seq_area;
    END IF;

    -- 3. Inserção do Item Cabeçalho do Módulo em db_itensmenu
    IF NOT EXISTS (SELECT 1 FROM db_itensmenu WHERE id_item = v_mod_id) THEN
        INSERT INTO db_itensmenu (id_item, descricao, help, funcao, itemativo, manutencao, desctec, libcliente, api)
        VALUES (v_mod_id, 'LGPD - Governança', 'Módulo de Governança LGPD', '', 1, '1', 'LGPD', true, false);
        RAISE NOTICE 'Item cabeçalho % inserido em db_itensmenu.', v_mod_id;
    ELSE
        UPDATE db_itensmenu 
        SET descricao = 'LGPD - Governança',
            libcliente = true,
            itemativo = 1
        WHERE id_item = v_mod_id;
    END IF;

    -- 4. Inserção do Item de Menu Filho em db_itensmenu
    IF NOT EXISTS (SELECT 1 FROM db_itensmenu WHERE id_item = v_item_id) THEN
        INSERT INTO db_itensmenu (id_item, descricao, help, funcao, itemativo, manutencao, desctec, libcliente, api)
        VALUES (v_item_id, 'Painel de Governança LGPD', 'Acesso ao Painel de Governança, ROPA e Conformidade LGPD', 'con4_lgpd_painel001.php', 1, '1', 'Painel de Governança LGPD', true, false);
        RAISE NOTICE 'Item de menu % inserido em db_itensmenu.', v_item_id;
    ELSE
        UPDATE db_itensmenu
        SET descricao = 'Painel de Governança LGPD',
            funcao = 'con4_lgpd_painel001.php',
            itemativo = 1,
            manutencao = '1',
            libcliente = true
        WHERE id_item = v_item_id;
        RAISE NOTICE 'Item de menu % atualizado em db_itensmenu.', v_item_id;
    END IF;

    -- 5. Associação Hierárquica em db_menu
    IF NOT EXISTS (SELECT 1 FROM db_menu WHERE id_item = v_mod_id AND id_item_filho = v_item_id AND modulo = v_mod_id) THEN
        INSERT INTO db_menu (id_item, id_item_filho, menusequencia, modulo)
        VALUES (v_mod_id, v_item_id, 1, v_mod_id);
        RAISE NOTICE 'Associação do menu % -> % inserida em db_menu.', v_mod_id, v_item_id;
    END IF;

    -- 6. Garantia de Permissões de Acesso para Usuários Ativos
    INSERT INTO db_permissao (id_usuario, id_item, permissaoativa, anousu, id_instit, id_modulo)
    SELECT DISTINCT p.id_usuario, v_item_id, '1', p.anousu, p.id_instit, v_mod_id
    FROM db_permissao p
    WHERE p.id_modulo = 1 AND p.permissaoativa = '1'
      AND NOT EXISTS (
          SELECT 1 FROM db_permissao p2
          WHERE p2.id_usuario = p.id_usuario 
            AND p2.id_item = v_item_id 
            AND p2.anousu = p.anousu 
            AND p2.id_instit = p.id_instit 
            AND p2.id_modulo = v_mod_id
      );
    RAISE NOTICE 'Permissões concedidas aos operadores e administradores.';

END $$;

COMMIT;

