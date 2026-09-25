-- Cache parametrizado do Balancete da Receita por Recurso.
-- O e-Cidade gera cada recorte; o Superset possui somente SELECT e recebe uma
-- cláusula RLS com a chave opaca do recorte correspondente à sessão.
CREATE TABLE IF NOT EXISTS bi.balancete_receita_por_recurso_cache (
    scope_key text NOT NULL,
    criado_em timestamp without time zone NOT NULL DEFAULT now(),
    instituicao_id integer NOT NULL,
    instituicao text NOT NULL,
    exercicio integer NOT NULL,
    periodo_inicial date NOT NULL,
    periodo_final date NOT NULL,
    recurso text NOT NULL,
    codigo_fonte text,
    complemento_fonte text,
    descricao_recurso text,
    previsto numeric,
    previsto_adicional numeric,
    arrecadado_periodo numeric,
    arrecadado_ano numeric,
    diferenca numeric,
    previsao_atualizada numeric,
    percentual_arrecadado numeric
);

CREATE INDEX IF NOT EXISTS balancete_receita_recurso_cache_scope_idx
    ON bi.balancete_receita_por_recurso_cache (scope_key);

CREATE OR REPLACE VIEW bi.balancete_receita_por_recurso AS
SELECT scope_key, instituicao_id, instituicao, exercicio, periodo_inicial,
       periodo_final, recurso, codigo_fonte, complemento_fonte,
       descricao_recurso, previsto, previsto_adicional, arrecadado_periodo,
       arrecadado_ano, diferenca, previsao_atualizada, percentual_arrecadado
FROM bi.balancete_receita_por_recurso_cache;

CREATE OR REPLACE FUNCTION bi.gerar_balancete_receita_por_recurso(
    p_scope_key text,
    p_instituicao integer,
    p_exercicio integer,
    p_periodo_inicial date,
    p_periodo_final date
) RETURNS integer
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = orcamento, contabilidade, configuracoes, public, pg_catalog, bi
AS $function$
DECLARE
    linhas integer;
BEGIN
    IF p_scope_key !~ '^[a-f0-9]{32}$'
       OR p_instituicao <= 0
       OR p_exercicio < 2000
       OR EXTRACT(YEAR FROM p_periodo_inicial) <> p_exercicio
       OR EXTRACT(YEAR FROM p_periodo_final) <> p_exercicio
       OR p_periodo_inicial > p_periodo_final THEN
        RAISE EXCEPTION 'Parâmetros inválidos para o balancete dinâmico';
    END IF;

    -- Renova no máximo a cada cinco minutos para que as renovações do guest
    -- token não repitam o cálculo contábil do mesmo usuário e período.
    SELECT count(*) INTO linhas
      FROM bi.balancete_receita_por_recurso_cache
     WHERE scope_key = p_scope_key
       AND criado_em >= now() - interval '5 minutes';
    IF linhas > 0 THEN
        RETURN linhas;
    END IF;

    DELETE FROM bi.balancete_receita_por_recurso_cache
     WHERE criado_em < now() - interval '2 hours'
        OR scope_key = p_scope_key;

    INSERT INTO bi.balancete_receita_por_recurso_cache (
        scope_key, instituicao_id, instituicao, exercicio, periodo_inicial,
        periodo_final, recurso, codigo_fonte, complemento_fonte,
        descricao_recurso, previsto, previsto_adicional, arrecadado_periodo,
        arrecadado_ano, diferenca, previsao_atualizada, percentual_arrecadado
    )
    WITH receita_por_lancamento AS (
        SELECT receita.o70_codigo AS recurso_id,
               CAST(COALESCE(NULLIF(SUBSTRING(saldo.saldos FROM 3 FOR 12), ''), '0') AS numeric) AS previsto,
               CAST(COALESCE(NULLIF(SUBSTRING(saldo.saldos FROM 16 FOR 12), ''), '0') AS numeric) AS previsto_adicional,
               CAST(COALESCE(NULLIF(SUBSTRING(saldo.saldos FROM 55 FOR 12), ''), '0') AS numeric) AS arrecadado_periodo,
               CAST(COALESCE(NULLIF(SUBSTRING(saldo.saldos FROM 68 FOR 12), ''), '0') AS numeric) AS diferenca,
               CAST(COALESCE(NULLIF(SUBSTRING(saldo.saldos FROM 81 FOR 12), ''), '0') AS numeric) AS arrecadado_ano
          FROM orcamento.orcreceita AS receita
         CROSS JOIN LATERAL (
             SELECT public.fc_receitasaldo(
                 p_exercicio, receita.o70_codrec, 3,
                 p_periodo_inicial, p_periodo_final
             ) AS saldos
         ) AS saldo
         WHERE receita.o70_anousu = p_exercicio
           AND receita.o70_instit = p_instituicao
           AND receita.o70_codigo > 0
    ), receita_agrupada AS (
        SELECT recurso_id, SUM(previsto) AS previsto,
               SUM(previsto_adicional) AS previsto_adicional,
               SUM(arrecadado_periodo) AS arrecadado_periodo,
               SUM(arrecadado_ano) AS arrecadado_ano,
               SUM(diferenca) AS diferenca
          FROM receita_por_lancamento
         GROUP BY recurso_id
    )
    SELECT p_scope_key, p_instituicao, config.nomeinst, p_exercicio,
           p_periodo_inicial, p_periodo_final,
           fonte.gestao::text || ' - ' || LPAD(tipo.o15_complemento::text, 4, '0'),
           fonte.gestao::text, LPAD(tipo.o15_complemento::text, 4, '0'),
           fonte.descricao, agrupada.previsto, agrupada.previsto_adicional,
           agrupada.arrecadado_periodo, agrupada.arrecadado_ano,
           agrupada.diferenca,
           agrupada.previsto + agrupada.previsto_adicional,
           CASE WHEN agrupada.previsto + agrupada.previsto_adicional = 0 THEN NULL
                ELSE agrupada.arrecadado_ano /
                     (agrupada.previsto + agrupada.previsto_adicional) END
      FROM receita_agrupada AS agrupada
      JOIN orcamento.orctiporec AS tipo ON tipo.o15_codigo = agrupada.recurso_id
      JOIN orcamento.fonterecurso AS fonte
        ON fonte.orctiporec_id = tipo.o15_codigo
       AND fonte.exercicio = p_exercicio
      JOIN configuracoes.db_config AS config ON config.codigo = p_instituicao;

    GET DIAGNOSTICS linhas = ROW_COUNT;
    RETURN linhas;
END;
$function$;

REVOKE ALL ON bi.balancete_receita_por_recurso_cache FROM PUBLIC;
REVOKE ALL ON bi.balancete_receita_por_recurso FROM PUBLIC;
REVOKE ALL ON FUNCTION bi.gerar_balancete_receita_por_recurso(text, integer, integer, date, date) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION bi.gerar_balancete_receita_por_recurso(text, integer, integer, date, date) TO ecidade;
GRANT SELECT ON bi.balancete_receita_por_recurso TO ecidade_bi_readonly;
