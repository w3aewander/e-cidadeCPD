# Dashboard: Receita por Fonte de Recursos — consulta dinâmica

## Origem e escopo inicial homologado

- Dataset: `bi.balancete_receita_por_recurso_prefeitura_2025`
- Instituição: `1 — Prefeitura Municipal`
- Período: `01/01/2025 a 31/12/2025`
- Relatório de conferência no e-Cidade: `Financeiro > Contabilidade > Relatórios
  > Balancetes > Balancete Receita Por Recurso`.

Antes de publicar, compare os totais da tabela com a emissão oficial do relatório.
Enquanto a base local estiver indisponível, o dashboard não deve ser apresentado
como homologado.

Em operação, o painel usa `bi.balancete_receita_por_recurso`: exercício e período
são escolhidos na barra integrada, enquanto a instituição é obrigatoriamente a
instituição ativa da sessão. O e-Cidade gera o recorte agregado e o guest token
aplica RLS pela chave opaca desse recorte; parâmetros enviados pelo navegador
não conseguem ampliar o escopo institucional.

## Configuração do dataset no Superset

Cadastre a view materializada como dataset físico. Defina `recurso` como dimensão principal e
formate os campos `numeric` como moeda BRL; `percentual_arrecadado` como percentual.

Métricas recomendadas:

- `Previsão atualizada`: `SUM(previsao_atualizada)`
- `Arrecadado no ano`: `SUM(arrecadado_ano)`
- `Diferença a arrecadar`: `SUM(diferenca)`
- `% realizado`: `SUM(arrecadado_ano) / NULLIF(SUM(previsao_atualizada), 0)`

## Layout proposto

1. Quatro cartões: Previsão atualizada, Arrecadado no ano, Diferença a arrecadar
   e % realizado.
2. Barras horizontais — `Arrecadado no ano por recurso`; ordenar descrescente e
   exibir os 12 maiores recursos. No Superset 6, use o plugin
   `echarts_timeseries_bar` com `descricao_recurso` no eixo categórico; o tipo legado
   `dist_bar` não está disponível. Agrupar o restante apenas se o volume exigir.
3. Barras divergentes — `Diferença a arrecadar por recurso`; valores negativos
   representam arrecadação acima da previsão e devem aparecer em cor distinta.
4. Tabela analítica — recurso, descrição, previsto, previsão adicional,
   arrecadado no período, arrecadado no ano, diferença e % realizado.
5. Rosca — participação das oito principais fontes na arrecadação.
6. Barras comparativas — previsão atualizada versus arrecadação nas dez fontes
   de maior expressão.

O painel incorporado inicia em tema claro e oferece alternância local para tema
escuro dentro da própria janela do e-Cidade.

Os títulos dos componentes são apenas informativos. O CSS versionado do
dashboard desabilita os links automáticos para `/explore/`, evitando qualquer
sugestão de saída da interface incorporada.

Não use gráfico de pizza para todas as fontes: o número de categorias prejudica a
leitura. Um filtro por `codigo_fonte` pode ser adicionado quando houver mais de um
exercício ou instituição no dataset.

## Atualização

O recorte é calculado sob demanda pela função controlada
`bi.gerar_balancete_receita_por_recurso`, executada somente pelo e-Cidade. Dados
temporários expiram em duas horas. O usuário do Superset recebe apenas `SELECT`
sobre a view dinâmica e nunca executa a função nem lê diretamente a tabela de
cache.
