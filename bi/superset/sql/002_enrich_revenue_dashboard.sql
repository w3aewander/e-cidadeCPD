-- Enriquece o dashboard gerencial sem ampliar o dataset nem o escopo de acesso.
-- IDs e UUIDs fixos tornam a aplicação repetível nesta instância controlada.
INSERT INTO slices (
    id, slice_name, datasource_id, datasource_type, datasource_name, viz_type,
    params, created_on, changed_on, uuid
) VALUES
(3, 'Previsão atualizada', 1, 'table', 'bi.balancete_receita_por_recurso_prefeitura_2025', 'big_number_total',
 '{"datasource":"1__table","viz_type":"big_number_total","metric":{"expressionType":"SIMPLE","column":{"column_name":"previsao_atualizada"},"aggregate":"SUM","label":"Previsão atualizada"},"adhoc_filters":[],"time_range":"No filter","y_axis_format":"$,.2f"}', now(), now(), '4c7ca7c1-c145-4afc-9670-6c94e5eb2bd1'),
(4, 'Arrecadado no ano', 1, 'table', 'bi.balancete_receita_por_recurso_prefeitura_2025', 'big_number_total',
 '{"datasource":"1__table","viz_type":"big_number_total","metric":{"expressionType":"SIMPLE","column":{"column_name":"arrecadado_ano"},"aggregate":"SUM","label":"Arrecadado no ano"},"adhoc_filters":[],"time_range":"No filter","y_axis_format":"$,.2f"}', now(), now(), '701ec0c7-91c8-4d0a-998f-671535cdf177'),
(5, 'Percentual realizado', 1, 'table', 'bi.balancete_receita_por_recurso_prefeitura_2025', 'big_number_total',
 '{"datasource":"1__table","viz_type":"big_number_total","metric":{"expressionType":"SQL","sqlExpression":"SUM(arrecadado_ano) / NULLIF(SUM(previsao_atualizada), 0)","label":"Percentual realizado"},"adhoc_filters":[],"time_range":"No filter","y_axis_format":".1%"}', now(), now(), 'f49f4dad-f5ca-403b-afba-b4c080cb633a'),
(9, 'Diferença a arrecadar', 1, 'table', 'bi.balancete_receita_por_recurso_prefeitura_2025', 'big_number_total',
 '{"datasource":"1__table","viz_type":"big_number_total","metric":{"expressionType":"SIMPLE","column":{"column_name":"diferenca"},"aggregate":"SUM","label":"Diferença a arrecadar"},"adhoc_filters":[],"time_range":"No filter","y_axis_format":"$,.2f"}', now(), now(), 'ed6dbed9-97a3-4192-b7c6-7a6b74af2f29'),
(6, 'Participação das principais fontes', 1, 'table', 'bi.balancete_receita_por_recurso_prefeitura_2025', 'pie',
 '{"datasource":"1__table","viz_type":"pie","groupby":["descricao_recurso"],"metric":{"expressionType":"SIMPLE","column":{"column_name":"arrecadado_ano"},"aggregate":"SUM","label":"Arrecadado no ano"},"adhoc_filters":[],"time_range":"No filter","row_limit":8,"sort_by_metric":true,"donut":true,"show_labels":true,"label_type":"key_percent"}', now(), now(), '5d929977-ad00-40df-aeb8-311251411498'),
(7, 'Previsão versus arrecadação', 1, 'table', 'bi.balancete_receita_por_recurso_prefeitura_2025', 'echarts_timeseries_bar',
 '{"datasource":"1__table","viz_type":"echarts_timeseries_bar","x_axis":"descricao_recurso","metrics":[{"expressionType":"SIMPLE","column":{"column_name":"previsao_atualizada"},"aggregate":"SUM","label":"Previsão atualizada"},{"expressionType":"SIMPLE","column":{"column_name":"arrecadado_ano"},"aggregate":"SUM","label":"Arrecadado no ano"}],"adhoc_filters":[],"time_range":"No filter","row_limit":10,"sort_series_type":"sum","orientation":"horizontal","show_legend":true,"show_value":true}', now(), now(), '98996947-bf93-48af-9a0c-1b5679006341'),
(8, 'Diferença a arrecadar por fonte', 1, 'table', 'bi.balancete_receita_por_recurso_prefeitura_2025', 'echarts_timeseries_bar',
 '{"datasource":"1__table","viz_type":"echarts_timeseries_bar","x_axis":"descricao_recurso","metrics":[{"expressionType":"SIMPLE","column":{"column_name":"diferenca"},"aggregate":"SUM","label":"Diferença a arrecadar"}],"adhoc_filters":[],"time_range":"No filter","row_limit":10,"sort_series_type":"sum","orientation":"horizontal","show_legend":false,"show_value":true}', now(), now(), 'b3c0a0a5-cce6-40af-b604-70149bed185a')
ON CONFLICT (uuid) DO UPDATE SET
    slice_name = EXCLUDED.slice_name,
    datasource_id = EXCLUDED.datasource_id,
    table_id = NULL,
    datasource_type = EXCLUDED.datasource_type,
    datasource_name = EXCLUDED.datasource_name,
    viz_type = EXCLUDED.viz_type,
    params = EXCLUDED.params,
    changed_on = now();

SELECT setval('slices_id_seq', GREATEST((SELECT max(id) FROM slices), 1));

INSERT INTO dashboard_slices (dashboard_id, slice_id)
SELECT 1, id FROM slices WHERE id BETWEEN 3 AND 9
ON CONFLICT DO NOTHING;

UPDATE dashboards
SET position_json = $layout$
{
  "DASHBOARD_VERSION_KEY": "v2",
  "ROOT_ID": {"children": ["GRID_ID"], "id": "ROOT_ID", "type": "ROOT"},
  "GRID_ID": {"children": ["ROW-KPIS", "ROW-OVERVIEW", "ROW-ANALYSIS", "ROW-DETAIL"], "id": "GRID_ID", "parents": ["ROOT_ID"], "type": "GRID"},
  "ROW-KPIS": {"children": ["CHART-3", "CHART-4", "CHART-9", "CHART-5"], "id": "ROW-KPIS", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["ROOT_ID", "GRID_ID"], "type": "ROW"},
  "ROW-OVERVIEW": {"children": ["CHART-6", "CHART-7"], "id": "ROW-OVERVIEW", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["ROOT_ID", "GRID_ID"], "type": "ROW"},
  "ROW-ANALYSIS": {"children": ["CHART-2", "CHART-8"], "id": "ROW-ANALYSIS", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["ROOT_ID", "GRID_ID"], "type": "ROW"},
  "ROW-DETAIL": {"children": ["CHART-1"], "id": "ROW-DETAIL", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["ROOT_ID", "GRID_ID"], "type": "ROW"},
  "CHART-1": {"children": [], "id": "CHART-1", "meta": {"chartId": 1, "height": 52, "uuid": "93df90d2-9268-4d8c-a433-ea32883cc965", "width": 12}, "parents": ["ROOT_ID", "GRID_ID", "ROW-DETAIL"], "type": "CHART"},
  "CHART-2": {"children": [], "id": "CHART-2", "meta": {"chartId": 2, "height": 42, "uuid": "a77e7bc5-00c8-48c4-bce9-a9cdd6864606", "width": 6}, "parents": ["ROOT_ID", "GRID_ID", "ROW-ANALYSIS"], "type": "CHART"},
  "CHART-3": {"children": [], "id": "CHART-3", "meta": {"chartId": 3, "height": 16, "uuid": "4c7ca7c1-c145-4afc-9670-6c94e5eb2bd1", "width": 3}, "parents": ["ROOT_ID", "GRID_ID", "ROW-KPIS"], "type": "CHART"},
  "CHART-4": {"children": [], "id": "CHART-4", "meta": {"chartId": 4, "height": 16, "uuid": "701ec0c7-91c8-4d0a-998f-671535cdf177", "width": 3}, "parents": ["ROOT_ID", "GRID_ID", "ROW-KPIS"], "type": "CHART"},
  "CHART-5": {"children": [], "id": "CHART-5", "meta": {"chartId": 5, "height": 16, "uuid": "f49f4dad-f5ca-403b-afba-b4c080cb633a", "width": 3}, "parents": ["ROOT_ID", "GRID_ID", "ROW-KPIS"], "type": "CHART"},
  "CHART-6": {"children": [], "id": "CHART-6", "meta": {"chartId": 6, "height": 42, "uuid": "5d929977-ad00-40df-aeb8-311251411498", "width": 5}, "parents": ["ROOT_ID", "GRID_ID", "ROW-OVERVIEW"], "type": "CHART"},
  "CHART-7": {"children": [], "id": "CHART-7", "meta": {"chartId": 7, "height": 42, "uuid": "98996947-bf93-48af-9a0c-1b5679006341", "width": 7}, "parents": ["ROOT_ID", "GRID_ID", "ROW-OVERVIEW"], "type": "CHART"},
  "CHART-8": {"children": [], "id": "CHART-8", "meta": {"chartId": 8, "height": 42, "uuid": "b3c0a0a5-cce6-40af-b604-70149bed185a", "width": 6}, "parents": ["ROOT_ID", "GRID_ID", "ROW-ANALYSIS"], "type": "CHART"},
  "CHART-9": {"children": [], "id": "CHART-9", "meta": {"chartId": 9, "height": 16, "uuid": "ed6dbed9-97a3-4192-b7c6-7a6b74af2f29", "width": 3}, "parents": ["ROOT_ID", "GRID_ID", "ROW-KPIS"], "type": "CHART"}
}
$layout$,
    changed_on = now()
WHERE id = 1;
