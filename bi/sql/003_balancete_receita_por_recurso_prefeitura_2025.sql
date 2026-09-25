-- MVP BI: Balancete da Receita por Recurso
-- Escopo aprovado: Instituição 1 (Prefeitura Municipal), exercício 2025,
-- período de 01/01/2025 a 31/12/2025.
--
-- Regra reproduzida de con2_balancrecu002.php e db_receitasaldo(): a função
-- fc_receitasaldo é a fonte de verdade para os cinco saldos do balancete.
-- Execute depois de 001_bi_role_and_schema.sql, usando o proprietário do schema
-- bi. A consulta não inclui dados pessoais.

-- Esta é uma view materializada: fc_receitasaldo consulta tabelas operacionais
-- com as permissões de quem executa a função. Materializar evita conceder essas
-- permissões à conta BI; apenas o resultado agregado é disponibilizado.
CREATE MATERIALIZED VIEW bi.balancete_receita_por_recurso_prefeitura_2025 AS
WITH receita_por_lancamento AS (
    SELECT
        receita.o70_codigo AS recurso_id,
        CAST(COALESCE(NULLIF(SUBSTRING(saldo.saldos FROM 3 FOR 12), ''), '0') AS numeric) AS previsto,
        CAST(COALESCE(NULLIF(SUBSTRING(saldo.saldos FROM 16 FOR 12), ''), '0') AS numeric) AS previsto_adicional,
        CAST(COALESCE(NULLIF(SUBSTRING(saldo.saldos FROM 55 FOR 12), ''), '0') AS numeric) AS arrecadado_periodo,
        CAST(COALESCE(NULLIF(SUBSTRING(saldo.saldos FROM 68 FOR 12), ''), '0') AS numeric) AS diferenca,
        CAST(COALESCE(NULLIF(SUBSTRING(saldo.saldos FROM 81 FOR 12), ''), '0') AS numeric) AS arrecadado_ano
    FROM orcreceita AS receita
    INNER JOIN orcfontes AS fonte_orcamentaria
        ON fonte_orcamentaria.o57_codfon = receita.o70_codfon
       AND fonte_orcamentaria.o57_anousu = receita.o70_anousu
    CROSS JOIN LATERAL (
        SELECT fc_receitasaldo(
            2025,
            receita.o70_codrec,
            2,
            DATE '2025-01-01',
            DATE '2025-12-31'
        ) AS saldos
    ) AS saldo
    WHERE receita.o70_anousu = 2025
      AND receita.o70_instit = 1
      AND receita.o70_codigo > 0
), receita_agrupada AS (
    SELECT
        recurso_id,
        SUM(previsto) AS previsto,
        SUM(previsto_adicional) AS previsto_adicional,
        SUM(arrecadado_periodo) AS arrecadado_periodo,
        SUM(arrecadado_ano) AS arrecadado_ano,
        SUM(diferenca) AS diferenca
    FROM receita_por_lancamento
    GROUP BY recurso_id
)
SELECT
    1::integer AS instituicao_id,
    'PREFEITURA MUNICIPAL'::text AS instituicao,
    2025::integer AS exercicio,
    DATE '2025-01-01' AS periodo_inicial,
    DATE '2025-12-31' AS periodo_final,
    fonte.gestao::text || ' - ' || LPAD(tipo_recurso.o15_complemento::text, 4, '0') AS recurso,
    fonte.gestao::text AS codigo_fonte,
    LPAD(tipo_recurso.o15_complemento::text, 4, '0') AS complemento_fonte,
    fonte.descricao AS descricao_recurso,
    receita_agrupada.previsto,
    receita_agrupada.previsto_adicional,
    receita_agrupada.arrecadado_periodo,
    receita_agrupada.arrecadado_ano,
    receita_agrupada.diferenca,
    receita_agrupada.previsto + receita_agrupada.previsto_adicional AS previsao_atualizada,
    CASE
        WHEN receita_agrupada.previsto + receita_agrupada.previsto_adicional = 0 THEN NULL
        ELSE receita_agrupada.arrecadado_ano
             / (receita_agrupada.previsto + receita_agrupada.previsto_adicional)
    END AS percentual_arrecadado
FROM receita_agrupada
INNER JOIN orctiporec AS tipo_recurso
    ON tipo_recurso.o15_codigo = receita_agrupada.recurso_id
INNER JOIN fonterecurso AS fonte
    ON fonte.orctiporec_id = tipo_recurso.o15_codigo
   AND fonte.exercicio = 2025
ORDER BY fonte.gestao, tipo_recurso.o15_complemento, fonte.descricao;

GRANT SELECT ON bi.balancete_receita_por_recurso_prefeitura_2025
    TO ecidade_bi_readonly;

-- Recalcule com o proprietário do schema após ajustes contábeis de 2025:
-- REFRESH MATERIALIZED VIEW bi.balancete_receita_por_recurso_prefeitura_2025;

-- Validação após a publicação:
-- SELECT recurso, previsto, previsto_adicional, arrecadado_periodo,
--        arrecadado_ano, diferenca
-- FROM bi.balancete_receita_por_recurso_prefeitura_2025;
-- Os totais devem coincidir com o relatório e-Cidade emitido em:
-- Financeiro > Contabilidade > Relatórios > Balancetes >
-- Balancete Receita Por Recurso, Instituição 1, 01/01/2025 a 31/12/2025.
