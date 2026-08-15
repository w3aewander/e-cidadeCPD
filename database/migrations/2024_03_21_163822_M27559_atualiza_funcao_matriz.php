<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M27559AtualizaFuncaoMatriz extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()->getPdo()->exec(<<<SQL
drop function if exists contabilidade.matriz(integer,integer,date,date,boolean,int[],boolean);
drop function if exists contabilidade.matriz(integer,integer,date,date,boolean,int[]);

create or replace function contabilidade.matriz(
    f_ano integer,
    f_instituicao integer,
    f_dataInicio date,
    f_dataFim date,
    f_encerramento boolean,
    f_reduzidos int[],
    f_utilizaPlanoUniao boolean default true
) returns table (
    estrutural varchar(13),
    estrutural_padrao varchar(13),
    reduzido integer,
    exercicio integer,
    instituicao integer,
    poder_ordao varchar(6),
    siconfi varchar(4),
    complemento integer,
    indicador_superavit char(1),
    divida_consolidada char(1),
    nr varchar(8),
    nd varchar(8),
    ai integer,
    funcao varchar(2),
    subfuncao varchar(3),
    informacoescomplementares varchar(20),
    saldo_anterior numeric(17,2),
    saldo_debito numeric(17,2),
    saldo_credito numeric(17,2),
    saldo_final numeric(17,2)
) language plpgsql
as $$
declare
    primeiroDiaAno date;
    recordContas record;
    recordDados record;
begin

    primeiroDiaAno = (f_ano || '-01-01')::date;

    for recordContas in (
        SELECT c60_estrut,
               c60_codigo,
               c60_consistemaconta,
               c60_identificadorfinanceiro,
               c61_instit,
               c61_reduz,
               c61_anousu,
               c61_codigo,
               db21_codigosiconfi,
               pcasp.conta,
               pcasp.informacoescomplementares
        from contabilidade.conplano
        join contabilidade.conplanoreduz ON (c61_codcon,c61_anousu) = (c60_codcon, c60_anousu)
        join contabilidade.pcaspconplano ON conplano_codigo = c60_codigo
        join contabilidade.pcasp ON pcasp.id = pcasp_id
        join configuracoes.db_config ON codigo = c61_instit
        join configuracoes.db_tipoinstit ON db21_tipoinstit = db21_codtipo
        WHERE c61_anousu = f_ano
          and c61_instit = f_instituicao
          and uniao = f_utilizaPlanoUniao
          and (case
                   when (array_length(f_reduzidos, 1) is null) then true
                   when array_length(f_reduzidos, 1) > 0 and array[c61_reduz] <@ f_reduzidos then true
                   else false
            end)
    ) loop
        for recordDados in (
            select at.reduzido,
                   at.saldo_final_anterior,
                   at.saldo_debito,
                   at.saldo_credito,
                   at.saldo_final,
                   null as siconfi,
                   null as complemento,
                   null as nr,
                   null as nd,
                   null as funcao,
                   null as subfuncao,
                   null::integer as ano_empenho
            from contabilidade.valores_atributo_dc(recordContas.c61_reduz,recordContas.c61_anousu,f_dataInicio,f_dataFim, f_encerramento) as at
            where recordContas.informacoescomplementares in ('PO', 'PO,FP', 'PO,FP,DC')

            union all

            select at.reduzido,
                   at.saldo_final_anterior,
                   at.saldo_debito,
                   at.saldo_credito,
                   at.saldo_final,
                   at.siconfi,
                   at.complemento,
                   null as nr,
                   null as nd,
                   null as funcao,
                   null as subfuncao,
                   null::integer as ano_empenho
            from contabilidade.valores_atributo_recurso(recordContas.c61_reduz,recordContas.c61_anousu,f_dataInicio,f_dataFim, f_encerramento) as at
            where recordContas.informacoescomplementares in ('PO,FP,DC,FR', 'PO,FP,FR,CO', 'PO,FR,CO')

            union all

            select at.reduzido,
                   at.saldo_final_anterior,
                   at.saldo_debito,
                   at.saldo_credito,
                   at.saldo_final,
                   at.siconfi,
                   at.complemento,
                   at.nr,
                   null as nd,
                   null as funcao,
                   null as subfuncao,
                   null::integer as ano_empenho
            from contabilidade.valores_atributo_receita (recordContas.c61_reduz,recordContas.c61_anousu,f_dataInicio,f_dataFim, f_encerramento) as at
            where recordContas.informacoescomplementares in ('PO,FR,CO,NR')

            union all

            select at.reduzido,
                   at.saldo_final_anterior,
                   at.saldo_debito,
                   at.saldo_credito,
                   at.saldo_final,
                   at.siconfi,
                   at.complemento,
                   null as nr,
                   at.nd,
                   at.funcao,
                   at.subfuncao,
                   null::integer as ano_empenho
            from contabilidade.valores_atributo_empenho_exercicio(recordContas.c61_reduz,recordContas.c61_anousu,f_dataInicio,f_dataFim, f_encerramento) as at
            where recordContas.informacoescomplementares in ('PO,FS,FR,CO,ND')

            union all

            select at.reduzido,
                   at.saldo_final_anterior,
                   at.saldo_debito,
                   at.saldo_credito,
                   at.saldo_final,
                   at.siconfi,
                   at.complemento,
                   null as nr,
                   at.nd,
                   at.funcao,
                   at.subfuncao,
                   at.ano_empenho::integer
            from contabilidade.valores_atributo_empenho_rp(recordContas.c61_reduz, recordContas.c61_anousu, f_dataInicio, f_dataFim, f_encerramento) as at
            where recordContas.informacoescomplementares in ('PO,FS,FR,CO,ND,AI')
        ) loop
            estrutural = recordContas.c60_estrut;
            estrutural_padrao = recordContas.conta;
            reduzido = recordContas.c61_reduz;
            exercicio = recordContas.c61_anousu;
            instituicao = recordContas.c61_instit;
            poder_ordao = recordContas.db21_codigosiconfi;

            -- Atributo do Superavit Financeiro (Financeiro/Permanente)
            if strpos(recordContas.informacoescomplementares, 'FP') = 0 or
               (strpos(recordContas.informacoescomplementares, 'FP') != 0 and recordContas.c60_identificadorfinanceiro = 'N') then
                indicador_superavit = null;
            elsif recordContas.c60_identificadorfinanceiro = 'F' then
                indicador_superavit = 1;
            else
                indicador_superavit = 2;
            end if;

            -- Divida Consolidada: 1 - não compoem a DC
            divida_consolidada = case
                 when recordContas.c60_consistemaconta != 9 and strpos(recordContas.informacoescomplementares, 'DC') != 0
                     then 1
                 else null
            end;

            informacoescomplementares = recordContas.informacoescomplementares;
            siconfi = recordDados.siconfi;
            complemento = recordDados.complemento;
            nr = recordDados.nr;
            nd = recordDados.nd;
            ai = recordDados.ano_empenho;
            funcao = recordDados.funcao;
            subfuncao = recordDados.subfuncao;

            saldo_anterior = recordDados.saldo_final_anterior;
            saldo_debito = recordDados.saldo_debito;
            saldo_credito = recordDados.saldo_credito;
            saldo_final = recordDados.saldo_final;

            return next;
        end loop;
    end loop;
end;
$$;
SQL
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection()->getPdo()->exec(<<<SQL
drop function if exists contabilidade.matriz;

create or replace function contabilidade.matriz(
    f_ano integer,
    f_instituicao integer,
    f_dataInicio date,
    f_dataFim date,
    f_encerramento boolean,
    f_reduzidos int[]
) returns table (
    estrutural varchar(13),
    estrutural_padrao varchar(13),
    reduzido integer,
    exercicio integer,
    instituicao integer,
    poder_ordao varchar(6),
    siconfi varchar(4),
    complemento integer,
    indicador_superavit char(1),
    divida_consolidada char(1),
    nr varchar(8),
    nd varchar(8),
    ai integer,
    funcao varchar(2),
    subfuncao varchar(3),
    informacoescomplementares varchar(20),
    saldo_anterior numeric(17,2),
    saldo_debito numeric(17,2),
    saldo_credito numeric(17,2),
    saldo_final numeric(17,2)
) language plpgsql
as $$
declare
    primeiroDiaAno date;
    recordContas record;
    recordDados record;
begin

    primeiroDiaAno = (f_ano || '-01-01')::date;

    for recordContas in (
        SELECT c60_estrut,
               c60_codigo,
               c60_consistemaconta,
               c60_identificadorfinanceiro,
               c61_instit,
               c61_reduz,
               c61_anousu,
               c61_codigo,
               db21_codigosiconfi,
               pcasp.conta,
               pcasp.informacoescomplementares
        from contabilidade.conplano
        join contabilidade.conplanoreduz ON (c61_codcon,c61_anousu) = (c60_codcon, c60_anousu)
        join contabilidade.pcaspconplano ON conplano_codigo = c60_codigo
        join contabilidade.pcasp ON pcasp.id = pcasp_id
        join configuracoes.db_config ON codigo = c61_instit
        join configuracoes.db_tipoinstit ON db21_tipoinstit = db21_codtipo
        WHERE c61_anousu = f_ano
          and c61_instit = f_instituicao
          and uniao = 't'
          and (case
                   when (array_length(f_reduzidos, 1) is null) then true
                   when array_length(f_reduzidos, 1) > 0 and array[c61_reduz] <@ f_reduzidos then true
                   else false
            end)
    ) loop
        for recordDados in (
            select at.reduzido,
                   at.saldo_final_anterior,
                   at.saldo_debito,
                   at.saldo_credito,
                   at.saldo_final,
                   null as siconfi,
                   null as complemento,
                   null as nr,
                   null as nd,
                   null as funcao,
                   null as subfuncao,
                   null::integer as ano_empenho
            from contabilidade.valores_atributo_dc(recordContas.c61_reduz,recordContas.c61_anousu,f_dataInicio,f_dataFim, f_encerramento) as at
            where recordContas.informacoescomplementares in ('PO', 'PO,FP', 'PO,FP,DC')

            union all

            select at.reduzido,
                   at.saldo_final_anterior,
                   at.saldo_debito,
                   at.saldo_credito,
                   at.saldo_final,
                   at.siconfi,
                   at.complemento,
                   null as nr,
                   null as nd,
                   null as funcao,
                   null as subfuncao,
                   null::integer as ano_empenho
            from contabilidade.valores_atributo_recurso(recordContas.c61_reduz,recordContas.c61_anousu,f_dataInicio,f_dataFim, f_encerramento) as at
            where recordContas.informacoescomplementares in ('PO,FP,DC,FR', 'PO,FP,FR,CO', 'PO,FR,CO')

            union all

            select at.reduzido,
                   at.saldo_final_anterior,
                   at.saldo_debito,
                   at.saldo_credito,
                   at.saldo_final,
                   at.siconfi,
                   at.complemento,
                   at.nr,
                   null as nd,
                   null as funcao,
                   null as subfuncao,
                   null::integer as ano_empenho
            from contabilidade.valores_atributo_receita (recordContas.c61_reduz,recordContas.c61_anousu,f_dataInicio,f_dataFim, f_encerramento) as at
            where recordContas.informacoescomplementares in ('PO,FR,CO,NR')

            union all

            select at.reduzido,
                   at.saldo_final_anterior,
                   at.saldo_debito,
                   at.saldo_credito,
                   at.saldo_final,
                   at.siconfi,
                   at.complemento,
                   null as nr,
                   at.nd,
                   at.funcao,
                   at.subfuncao,
                   null::integer as ano_empenho
            from contabilidade.valores_atributo_empenho_exercicio(recordContas.c61_reduz,recordContas.c61_anousu,f_dataInicio,f_dataFim, f_encerramento) as at
            where recordContas.informacoescomplementares in ('PO,FS,FR,CO,ND')

            union all

            select at.reduzido,
                   at.saldo_final_anterior,
                   at.saldo_debito,
                   at.saldo_credito,
                   at.saldo_final,
                   at.siconfi,
                   at.complemento,
                   null as nr,
                   at.nd,
                   at.funcao,
                   at.subfuncao,
                   at.ano_empenho::integer
            from contabilidade.valores_atributo_empenho_rp(recordContas.c61_reduz, recordContas.c61_anousu, f_dataInicio, f_dataFim, f_encerramento) as at
            where recordContas.informacoescomplementares in ('PO,FS,FR,CO,ND,AI')
        ) loop
            estrutural = recordContas.c60_estrut;
            estrutural_padrao = recordContas.conta;
            reduzido = recordContas.c61_reduz;
            exercicio = recordContas.c61_anousu;
            instituicao = recordContas.c61_instit;
            poder_ordao = recordContas.db21_codigosiconfi;

            -- Atributo do Superavit Financeiro (Financeiro/Permanente)
            if strpos(recordContas.informacoescomplementares, 'FP') = 0 or
               (strpos(recordContas.informacoescomplementares, 'FP') != 0 and recordContas.c60_identificadorfinanceiro = 'N') then
                indicador_superavit = null;
            elsif recordContas.c60_identificadorfinanceiro = 'F' then
                indicador_superavit = 1;
            else
                indicador_superavit = 2;
            end if;

            -- Divida Consolidada: 1 - não compoem a DC
            divida_consolidada = case
                 when recordContas.c60_consistemaconta != 9 and strpos(recordContas.informacoescomplementares, 'DC') != 0
                     then 1
                 else null
            end;

            informacoescomplementares = recordContas.informacoescomplementares;
            siconfi = recordDados.siconfi;
            complemento = recordDados.complemento;
            nr = recordDados.nr;
            nd = recordDados.nd;
            ai = recordDados.ano_empenho;
            funcao = recordDados.funcao;
            subfuncao = recordDados.subfuncao;

            saldo_anterior = recordDados.saldo_final_anterior;
            saldo_debito = recordDados.saldo_debito;
            saldo_credito = recordDados.saldo_credito;
            saldo_final = recordDados.saldo_final;

            return next;
        end loop;
    end loop;
end;
$$;
SQL
        );
    }
}
