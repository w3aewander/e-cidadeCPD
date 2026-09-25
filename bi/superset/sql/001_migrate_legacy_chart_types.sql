-- Migra o gráfico categórico legado para o plugin ECharts disponível no
-- Superset 6. Seguro para reaplicação: atua apenas no gráfico ainda legado.
UPDATE slices
SET viz_type = 'echarts_timeseries_bar',
    params = (
        params::jsonb
        - 'groupby'
        - 'order_by_cols'
        || jsonb_build_object(
            'viz_type', 'echarts_timeseries_bar',
            'x_axis', 'fonte_recurso',
            'adhoc_filters', jsonb_build_array(),
            'time_range', 'No filter',
            'sort_series_type', 'sum'
        )
    )::text
WHERE id = 2
  AND viz_type = 'dist_bar';

-- Os gráficos iniciais referenciavam um alias que não existe na view aprovada.
-- Usa a coluna real do dataset para que o Superset consiga montar as consultas.
UPDATE slices
SET params = replace(params, 'fonte_recurso', 'descricao_recurso')
WHERE id IN (1, 2)
  AND params LIKE '%fonte_recurso%';

-- Normaliza o layout criado antes do Superset 6. O frontend procura a chave
-- canônica GRID_ID e percorre as relações parents para montar a árvore.
UPDATE dashboards
SET position_json = $layout$
{
  "DASHBOARD_VERSION_KEY": "v2",
  "ROOT_ID": {
    "children": ["GRID_ID"],
    "id": "ROOT_ID",
    "type": "ROOT"
  },
  "GRID_ID": {
    "children": ["ROW-1", "ROW-2"],
    "id": "GRID_ID",
    "parents": ["ROOT_ID"],
    "type": "GRID"
  },
  "HEADER_ID": {
    "id": "HEADER_ID",
    "meta": {"text": "Receita por Fonte de Recursos — Prefeitura Municipal (2025)"},
    "type": "HEADER"
  },
  "ROW-1": {
    "children": ["CHART-2"],
    "id": "ROW-1",
    "meta": {"background": "BACKGROUND_TRANSPARENT"},
    "parents": ["ROOT_ID", "GRID_ID"],
    "type": "ROW"
  },
  "ROW-2": {
    "children": ["CHART-1"],
    "id": "ROW-2",
    "meta": {"background": "BACKGROUND_TRANSPARENT"},
    "parents": ["ROOT_ID", "GRID_ID"],
    "type": "ROW"
  },
  "CHART-1": {
    "children": [],
    "id": "CHART-1",
    "meta": {
      "chartId": 1,
      "height": 50,
      "uuid": "93df90d2-9268-4d8c-a433-ea32883cc965",
      "width": 12
    },
    "parents": ["ROOT_ID", "GRID_ID", "ROW-2"],
    "type": "CHART"
  },
  "CHART-2": {
    "children": [],
    "id": "CHART-2",
    "meta": {
      "chartId": 2,
      "height": 50,
      "uuid": "a77e7bc5-00c8-48c4-bce9-a9cdd6864606",
      "width": 12
    },
    "parents": ["ROOT_ID", "GRID_ID", "ROW-1"],
    "type": "CHART"
  }
}
$layout$
WHERE id = 1;
