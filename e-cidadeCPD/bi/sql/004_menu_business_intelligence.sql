-- Cadastra o ponto de entrada do BI no Desktop do e-Cidade.
-- Escopo: Financeiro > Contabilidade > Relatórios > Business Intelligence.
-- O script não concede permissões a usuários comuns. Conceda o item final pela
-- administração de permissões do e-Cidade, para a instituição e exercício alvo.

BEGIN;

WITH novo_pai AS (
    INSERT INTO configuracoes.db_itensmenu
        (descricao, help, funcao, itemativo, manutencao, desctec, libcliente, api)
    SELECT
        'Business Intelligence',
        'Painéis analíticos aprovados do e-Cidade',
        '',
        1,
        '1',
        'Ponto de entrada para painéis BI integrados ao e-Cidade.',
        true,
        false
    WHERE NOT EXISTS (
        SELECT 1
          FROM configuracoes.db_itensmenu item
          JOIN configuracoes.db_menu menu ON menu.id_item_filho = item.id_item
         WHERE menu.id_item = 3331
           AND menu.modulo = 209
           AND item.descricao = 'Business Intelligence'
    )
    RETURNING id_item
), pai AS (
    SELECT id_item FROM novo_pai
    UNION ALL
    SELECT item.id_item
      FROM configuracoes.db_itensmenu item
      JOIN configuracoes.db_menu menu ON menu.id_item_filho = item.id_item
     WHERE menu.id_item = 3331
       AND menu.modulo = 209
       AND item.descricao = 'Business Intelligence'
    LIMIT 1
)
INSERT INTO configuracoes.db_menu (id_item, id_item_filho, menusequencia, modulo)
SELECT 3331, pai.id_item, 60, 209
  FROM pai
 WHERE NOT EXISTS (
    SELECT 1
      FROM configuracoes.db_menu menu
     WHERE menu.id_item = 3331
       AND menu.id_item_filho = pai.id_item
       AND menu.modulo = 209
 );

WITH pai AS (
    SELECT item.id_item
      FROM configuracoes.db_itensmenu item
      JOIN configuracoes.db_menu menu ON menu.id_item_filho = item.id_item
     WHERE menu.id_item = 3331
       AND menu.modulo = 209
       AND item.descricao = 'Business Intelligence'
     LIMIT 1
), novo_filho AS (
    INSERT INTO configuracoes.db_itensmenu
        (descricao, help, funcao, itemativo, manutencao, desctec, libcliente, api)
    SELECT
        'Receita por Fonte de Recursos',
        'Balancete dinâmico da Receita por Recurso',
        'web/bi/balancete-receita-recurso',
        1,
        '1',
        'Dashboard BI do balancete da receita por fonte de recursos.',
        true,
        false
    WHERE NOT EXISTS (
        SELECT 1
          FROM configuracoes.db_itensmenu
         WHERE funcao = 'web/bi/balancete-receita-recurso'
    )
    RETURNING id_item
), filho AS (
    SELECT id_item FROM novo_filho
    UNION ALL
    SELECT id_item
      FROM configuracoes.db_itensmenu
     WHERE funcao = 'web/bi/balancete-receita-recurso'
    LIMIT 1
)
INSERT INTO configuracoes.db_menu (id_item, id_item_filho, menusequencia, modulo)
SELECT pai.id_item, filho.id_item, 1, 209
  FROM pai
 CROSS JOIN filho
 WHERE NOT EXISTS (
    SELECT 1
      FROM configuracoes.db_menu menu
     WHERE menu.id_item = pai.id_item
       AND menu.id_item_filho = filho.id_item
       AND menu.modulo = 209
 );

COMMIT;
