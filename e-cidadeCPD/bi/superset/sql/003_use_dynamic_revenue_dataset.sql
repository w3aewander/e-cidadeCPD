-- Aponta o dataset existente para a view parametrizada e registra a coluna
-- técnica usada exclusivamente pela cláusula RLS do guest token.
UPDATE tables
SET table_name = 'balancete_receita_por_recurso',
    changed_on = now()
WHERE id = 1;

INSERT INTO table_columns (
    table_id, column_name, is_dttm, is_active, type, groupby, filterable,
    created_on, changed_on, uuid, description
) VALUES (
    1, 'scope_key', false, true, 'TEXT', false, false,
    now(), now(), '06c44b4c-948e-4d37-8f57-85bacdb44027',
    'Chave opaca do recorte gerado pelo e-Cidade; uso exclusivo em RLS.'
)
ON CONFLICT (column_name, table_id) DO UPDATE SET
    is_active = true,
    type = 'TEXT',
    groupby = false,
    filterable = false,
    changed_on = now();
