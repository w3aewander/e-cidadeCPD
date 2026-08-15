<?php

if($opcao_geral == 1) {

    $sqlMargemCartao = <<<SQL
with atualizacao_margem_consignado_cartao as (
    select
        rh02_regist as matricula,
        r14_rubric as rubrica_margem,
        rh02_anousu as ano_base,
        rh02_mesusu as mes_base,
        rh02_instit as instituicao_base,
        coalesce(
            (
                SELECT
                    sum(r14_valor)
                FROM
                    gerfsal sal
                WHERE
                    sal.r14_regist = rh02_regist
                    AND sal.r14_anousu = rh02_anousu
                    AND sal.r14_mesusu = rh02_mesusu
                    AND sal.r14_rubric IN (
                        SELECT
                            r09_rubric
                        FROM
                            basesr
                        WHERE
                            r09_anousu = sal.r14_anousu
                            AND r09_mesusu = sal.r14_mesusu
                            AND r09_instit = sal.r14_instit
                            AND r09_base = 'B051'
                    )
            ),
            0
        ) as base_051,
        coalesce(
            (
                SELECT
                    sum(r14_valor)
                FROM
                    gerfsal sal
                WHERE
                    sal.r14_regist = rh02_regist
                    AND sal.r14_anousu = rh02_anousu
                    AND sal.r14_mesusu = rh02_mesusu
                    AND sal.r14_rubric IN (
                        SELECT
                            r09_rubric
                        FROM
                            basesr
                        WHERE
                            r09_anousu = sal.r14_anousu
                            AND r09_mesusu = sal.r14_mesusu
                            AND r09_instit = sal.r14_instit
                            AND r09_base = 'B052'
                    )
            ),
            0
        ) as base_052,
        coalesce(
            (
                SELECT
                    sum(r14_valor)
                FROM
                    gerfsal sal
                WHERE
                    sal.r14_regist = rh02_regist
                    AND sal.r14_anousu = rh02_anousu
                    AND sal.r14_mesusu = rh02_mesusu
                    AND sal.r14_rubric IN (
                        SELECT
                            r09_rubric
                        FROM
                            basesr
                        WHERE
                            r09_anousu = sal.r14_anousu
                            AND r09_mesusu = sal.r14_mesusu
                            AND r09_instit = sal.r14_instit
                            AND r09_base = 'B053'
                    )
            ),
            0
        ) as base_053
    from
        rhpessoalmov
        join gerfsal on rh02_regist = r14_regist
        AND rh02_anousu = r14_anousu
        AND rh02_mesusu = r14_mesusu
    where
        gerfsal.r14_anousu = $anousu
        AND gerfsal.r14_mesusu = $mesusu
        AND gerfsal.r14_rubric = 'R804'
        AND gerfsal.r14_instit = $DB_instit
        $where_regist_fim
)
update
    gerfsal
set
    r14_valor = round(((base_051 - base_052) * 0.05) - base_053, 2)
from
    atualizacao_margem_consignado_cartao
where
    matricula = r14_regist
    and ano_base = r14_anousu
    and mes_base = r14_mesusu
    and instituicao_base = r14_instit
    and rubrica_margem = r14_rubric;
SQL;

    $rsMargemCartao = db_query($sqlMargemCartao);
   
    if (!$rsMargemCartao) {
        die('Erro ao atualizar margem cartão');
    }

    $sqlMargemConsignavel = <<<SQL
with atualizacao_margem_consignado as (
    select
        rh02_regist as matricula,
        r14_rubric as rubrica_margem,
        rh02_anousu as ano_base,
        rh02_mesusu as mes_base,
        rh02_instit as instituicao_base,
        coalesce(
            (
                SELECT
                    sum(r14_valor)
                FROM
                    gerfsal sal
                WHERE
                    sal.r14_regist = rh02_regist
                    AND sal.r14_anousu = rh02_anousu
                    AND sal.r14_mesusu = rh02_mesusu
                    AND sal.r14_rubric IN (
                        SELECT
                            r09_rubric
                        FROM
                            basesr
                        WHERE
                            r09_anousu = sal.r14_anousu
                            AND r09_mesusu = sal.r14_mesusu
                            AND r09_instit = sal.r14_instit
                            AND r09_base = 'B051'
                    )
            ),
            0
        ) as base_051,
        coalesce(
            (
                SELECT
                    sum(r14_valor)
                FROM
                    gerfsal sal
                WHERE
                    sal.r14_regist = rh02_regist
                    AND sal.r14_anousu = rh02_anousu
                    AND sal.r14_mesusu = rh02_mesusu
                    AND sal.r14_rubric IN (
                        SELECT
                            r09_rubric
                        FROM
                            basesr
                        WHERE
                            r09_anousu = sal.r14_anousu
                            AND r09_mesusu = sal.r14_mesusu
                            AND r09_instit = sal.r14_instit
                            AND r09_base = 'B052'
                    )
            ),
            0
        ) as base_052,
        coalesce(
            (
                SELECT
                    sum(r14_valor)
                FROM
                    gerfsal sal
                WHERE
                    sal.r14_regist = rh02_regist
                    AND sal.r14_anousu = rh02_anousu
                    AND sal.r14_mesusu = rh02_mesusu
                    AND sal.r14_rubric IN (
                        SELECT
                            r09_rubric
                        FROM
                            basesr
                        WHERE
                            r09_anousu = sal.r14_anousu
                            AND r09_mesusu = sal.r14_mesusu
                            AND r09_instit = sal.r14_instit
                            AND r09_base = 'B053'
                    )
            ),
            0
        ) as base_053
    from
        rhpessoalmov
        join gerfsal on rh02_regist = r14_regist
        AND rh02_anousu = r14_anousu
        AND rh02_mesusu = r14_mesusu
    where
        gerfsal.r14_anousu = $anousu
        AND gerfsal.r14_mesusu = $mesusu
        AND gerfsal.r14_rubric = 'R803'
        AND gerfsal.r14_instit = $DB_instit
        $where_regist_fim
)
update
    gerfsal
set
    r14_valor = round(((base_051 - base_052) * 0.35) - base_053, 2)
from
    atualizacao_margem_consignado
where
    matricula = r14_regist
    and ano_base = r14_anousu
    and mes_base = r14_mesusu
    and instituicao_base = r14_instit
    and rubrica_margem = r14_rubric;

SQL;

    $rsMargemConsignavel = db_query($sqlMargemConsignavel);

    if (!$rsMargemConsignavel) {
        die('Erro ao atualizar margem consignavel');
    }
}