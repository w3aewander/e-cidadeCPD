-- Os títulos dos gráficos são links para /explore/ por padrão. No dashboard
-- incorporado eles não devem sugerir nem permitir saída da interface e-Cidade.
UPDATE dashboards
SET css = COALESCE(css, '') || $css$

/* e-Cidade: títulos informativos, sem navegação para o editor Superset */
.dashboard a[href^="/explore/"],
.dashboard a[href*="/explore/"],
.dashboard-component-chart-holder a[href^="/explore/"],
.dashboard-component-chart-holder a[href*="/explore/"] {
  pointer-events: none !important;
  cursor: default !important;
  color: inherit !important;
  text-decoration: none !important;
}
$css$,
    changed_on = now()
WHERE id = 1
  AND COALESCE(css, '') NOT LIKE '%e-Cidade: títulos informativos%';
