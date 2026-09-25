-- Remove referências ao recorte inicial de 2025 dos títulos agora dinâmicos.
UPDATE dashboards
SET dashboard_title = 'Receita por Fonte de Recursos',
    slug = 'receita-por-fonte-de-recursos',
    changed_on = now()
WHERE id = 1;

UPDATE slices
SET slice_name = CASE id
        WHEN 1 THEN 'Arrecadação por fonte de recursos'
        WHEN 2 THEN 'Top 10 fontes por arrecadação'
    END,
    changed_on = now()
WHERE id IN (1, 2);
