<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class M29903Valetransportediasafastavr extends Migration
{
    public function up()
 {
    DB::connection()->getPdo()->exec(<<<SQL
     DROP TYPE public.tp_dias_afastamento_valetransporte_vr CASCADE;
     DROP FUNCTION IF EXISTS public.fc_valetransporte_dias_afasta_vr(integer, integer, integer);

     CREATE TYPE tp_dias_afastamento_valetransporte_vr AS 
                            ( riDiasMes           INTEGER,
                              riDiasMesAnt        INTEGER,
                              riDiasFerias        INTEGER,
                              riDiasAfasta        INTEGER, 
                              riDtReto            DATE,
                              riDtAfas            DATE,
                              riDiasMesInteiro    INTEGER,
                              riDiasFeriasInteiro INTEGER,
                              riDiasValidos       INTEGER);

CREATE OR REPLACE FUNCTION pessoal.fc_valetransporte_dias_afasta_vr(integer, integer, integer)
 RETURNS SETOF tp_dias_afastamento_valetransporte_vr AS 
 $$
declare
    _registro           alias for $1 ;
    _ano                alias for $2 ;
    _mes                alias for $3 ;

    ano_              integer default 0;
    mes_              integer default 0;
    dias_mes_         integer default 0;
    dias_mes_inteiro_ integer default 0;
    ano_ant_          integer default 0;
    mes_ant_          integer default 0;
    mes_prox_         integer default 0;
    ano_prox_         integer default 0;
    dias_mes_ant_     integer default 0;
    dias_afa_         integer default 0;
    dias_fer_         integer default 0;
    dias_fer_inteiro_ integer default 0;
    retorno_          integer default 0;
    dias_validos_     integer default 0;
    r30_per1i_        date;
    r30_per1f_        date;

    rTpDiasAfasta     tp_dias_afastamento_valetransporte_vr%ROWTYPE;

begin

retorno_ = 0;

rTpDiasAfasta.riDiasMes           := 0;
rTpDiasAfasta.riDiasMesInteiro    := 0;
rTpDiasAfasta.riDiasMesAnt        := 0;
rTpDiasAfasta.riDiasAfasta        := 0;
rTpDiasAfasta.riDiasFerias        := 0;
rTpDiasAfasta.riDiasFeriasInteiro := 0;
rTpDiasAfasta.riDtReto            := null;
rTpDiasAfasta.riDtAfas            := null;

---- verifica os dias de afastamento dos assentamentos referentes ao mes do processamento
select count(*) into dias_mes_
from
(
select distinct
       h16_regist,
       date_part('dow', dtconc+generate_series(0, dtterm - dtconc)) as dia_semana,
       dtconc+generate_series(0, dtterm - dtconc) as data_dia
from
  (select h16_regist,
          h16_dtconc,
          h16_dtterm,
          case when h16_dtconc < to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd')
               then to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd')
               else h16_dtconc
          end as dtconc,
          case when h16_dtterm > to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd') or h16_dtterm is null
               then to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd')
               else h16_dtterm
          end as dtterm
      from assenta
           inner join tipoasse on h12_codigo = h16_assent
      where h12_assent in ('001', '029', '041', '067', '052', '062', '100', '110', '158') and h16_regist = _registro
        and (h16_dtconc <= to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd')
        and (h16_dtterm >= to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd') or h16_dtterm is null ))
     ) as x
) as y
  inner join rhpessoalmov on rh02_anousu = _ano
                         and rh02_mesusu = _mes
                         and rh02_regist = _registro
  left join rhlotacalend  on rh02_lota   = rh64_lota
  left outer join calendf on r62_calend::bigint  = rh64_calend
                         and r62_data    = data_dia
where dia_semana between 1 and 5
      and r62_data is null;

---- verifica os dias de afastamento dos assentamentos referentes ao mes do processamento sem descontar finais de semana e feriados para o desconto do vt
select count(*) into dias_mes_inteiro_
from
(
select distinct
       h16_regist,
       date_part('dow', dtconc+generate_series(0, dtterm - dtconc)) as dia_semana,
       dtconc+generate_series(0, dtterm - dtconc) as data_dia
from
  (select h16_regist,
          h16_dtconc,
          h16_dtterm,
          case when h16_dtconc < to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd')
               then to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd')
               else h16_dtconc
          end as dtconc,
          case when h16_dtterm > to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd') or h16_dtterm is null
               then to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd')
               else h16_dtterm
          end as dtterm
      from assenta
           inner join tipoasse on h12_codigo = h16_assent
      where h12_assent in ('001', '029', '041', '067', '052', '062', '100', '110', '158') and h16_regist = _registro
        and (h16_dtconc <= to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd')
        and (h16_dtterm >= to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd') or h16_dtterm is null ))
     ) as x
) as y
  inner join rhpessoalmov on rh02_anousu = _ano
                         and rh02_mesusu = _mes
                         and rh02_regist = _registro
  left join rhlotacalend  on rh02_lota   = rh64_lota
  left outer join calendf on r62_calend::bigint  = rh64_calend
                         and r62_data    = data_dia
where dia_semana between 0 and 6;

---- verifica os dias de afastamento dos assentamentos referentes ao mes anterior ao do processamento
if _mes = 1 then
   mes_ant_ := 12;
   ano_ant_ := _ano - 1;
else
  mes_ant_ := _mes - 1;
  ano_ant_ := _ano;
end if;

select count(*) into dias_mes_ant_
from
(
select distinct
       h16_regist,
       date_part('dow', dtconc+generate_series(0, dtterm - dtconc)) as dia_semana,
       dtconc+generate_series(0, dtterm - dtconc) as data_dia
from
  (select h16_regist,
          h16_dtconc,
          h16_dtterm,
          case when h16_dtconc < to_date(ano_ant_||'-'||mes_ant_||'-01','YYYY-mm-dd')
               then to_date(ano_ant_||'-'||mes_ant_||'-01','YYYY-mm-dd')
               else h16_dtconc
          end as dtconc,
          case when h16_dtterm > to_date(ano_ant_||'-'||mes_ant_||'-'||ndias(ano_ant_,mes_ant_)::char(2),'YYYY-mm-dd') or h16_dtterm is null
               then to_date(ano_ant_||'-'||mes_ant_||'-'||ndias(ano_ant_,mes_ant_)::char(2),'YYYY-mm-dd')
               else h16_dtterm
          end as dtterm
      from assenta
           inner join tipoasse on h12_codigo = h16_assent
      where h12_assent in ('025') and h16_regist = _registro
        and (h16_dtconc <= to_date(ano_ant_||'-'||mes_ant_||'-'||ndias(ano_ant_,mes_ant_)::char(2),'YYYY-mm-dd')
        and (h16_dtterm >= to_date(ano_ant_||'-'||mes_ant_||'-01','YYYY-mm-dd') or h16_dtterm is null ))
     ) as x
) as y
  inner join rhpessoalmov on rh02_anousu = ano_ant_
                         and rh02_mesusu = mes_ant_
                         and rh02_regist = _registro
  left join rhlotacalend  on rh02_lota   = rh64_lota
  left outer join calendf on r62_calend::bigint  = rh64_calend
                         and r62_data    = data_dia
where dia_semana between 1 and 5
  and r62_data is null;

---- verifica os dias de afastamento da folha referentes ao mes do processamento
select count(*) into dias_afa_
from
(
select distinct
       r45_regist,
       r45_dtafas,
       r45_dtreto,
       date_part('dow', dtafas+generate_series(0, dtreto - dtafas)) as dia_semana,
       dtafas+generate_series(0, dtreto - dtafas) as data_dia
from
  (select r45_regist,
          r45_dtafas,
          r45_dtreto,
          case when r45_dtafas < to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd')
               then to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd')
               else r45_dtafas
          end as dtafas,
          case when r45_dtreto > to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd') or r45_dtreto is null
               then to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd')
               else r45_dtreto
          end as dtreto
      from afasta
      where r45_regist = _registro
        and r45_anousu = _ano
        and r45_mesusu = _mes
        and ( (  r45_dtafas <= to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd')
        and r45_dtreto >= to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd') ) or r45_dtreto is null
            )
     ) as x
) as y
  inner join rhpessoalmov on rh02_anousu = _ano
                         and rh02_mesusu = _mes
                         and rh02_regist = _registro
  left join rhlotacalend  on rh02_lota   = rh64_lota
  left outer join calendf on r62_calend::bigint  = rh64_calend
                         and r62_data    = data_dia
where dia_semana between 1 and 5
  and r62_data is null;

---- verifica os dias de ferias referentes ao mes seguinte do processamento
if _mes = 12 then
   mes_prox_ := 1;  -- Janeiro
   ano_prox_ := _ano + 1;  -- Incrementa o ano
else
   mes_prox_ := _mes + 1;  -- Incrementa o mês
   ano_prox_ := _ano;
end if;

select count(*),  max(r30_per1i), max(r30_per1f) into dias_fer_, r30_per1i_, r30_per1f_
from
(
select distinct
       r30_regist,
       r30_per1i,
       r30_per1f,
       date_part('dow', dtafas+generate_series(0, dtreto - dtafas +1 )) as dia_semana,
       dtafas+generate_series(0, dtreto - dtafas) as data_dia
from
  (select r30_regist,
          r30_per1i,
          r30_per1f,
          case when r30_per1i < to_date(ano_prox_||'-'||mes_prox_ ||'-01','YYYY-mm-dd')
               then to_date(ano_prox_||'-'||mes_prox_ ||'-01','YYYY-mm-dd')
               else r30_per1i
          end as dtafas,
          case when r30_per1f > to_date(ano_prox_||'-'||mes_prox_ ||'-'||ndias(ano_prox_,mes_prox_ )::char(2),'YYYY-mm-dd')
               then to_date(ano_prox_||'-'||mes_prox_||'-'||ndias(ano_prox_,mes_prox_)::char(2),'YYYY-mm-dd')
               else r30_per1f
          end as dtreto
      from cadferia
      where r30_regist = _registro
        and (r30_per1i <= to_date(ano_prox_||'-'||mes_prox_ ||'-'||ndias(ano_prox_,mes_prox_ )::char(2),'YYYY-mm-dd')
        and r30_per1f >= to_date(ano_prox_||'-'||mes_prox_ ||'-01','YYYY-mm-dd'))

   union all

   select r30_regist,
          r30_per2i,
          r30_per2f,
          case when r30_per2i < to_date(ano_prox_||'-'||mes_prox_ ||'-01','YYYY-mm-dd')
               then to_date(ano_prox_||'-'||mes_prox_ ||'-01','YYYY-mm-dd')
               else r30_per2i
          end as dtafas,
          case when r30_per2f > to_date(ano_prox_||'-'||mes_prox_ ||'-'||ndias(ano_prox_,mes_prox_ )::char(2),'YYYY-mm-dd')
               then to_date(ano_prox_||'-'||mes_prox_ ||'-'||ndias(ano_prox_,mes_prox_ )::char(2),'YYYY-mm-dd')
               else r30_per2f
          end as dtreto
      from cadferia
      where r30_regist = _registro
        and (r30_per2i <= to_date(ano_prox_||'-'||mes_prox_ ||'-'||ndias(ano_prox_,mes_prox_ )::char(2),'YYYY-mm-dd')
        and r30_per2f >= to_date(ano_prox_||'-'||mes_prox_ ||'-01','YYYY-mm-dd'))
     ) as x
) as y
  inner join rhpessoalmov on rh02_anousu = _ano
                         and rh02_mesusu = _mes
                         and rh02_regist = _registro
  left join rhlotacalend  on rh02_lota   = rh64_lota
  left outer join calendf on r62_calend::bigint  = rh64_calend
                         and r62_data    = data_dia
where --dia_semana between 1 and 5
  --and 
  r62_data is null ;

---- verifica os dias de ferias referentes ao mes do processamento sem descontar as finais de semana e feriados para o desconto do vt
select count(*),  max(r30_per1i), max(r30_per1f) into dias_fer_inteiro_, r30_per1i_, r30_per1f_
from
(
select distinct
       r30_regist,
       r30_per1i,
       r30_per1f,
       date_part('dow', dtafas+generate_series(0, dtreto - dtafas +1 )) as dia_semana,
       dtafas+generate_series(0, dtreto - dtafas) as data_dia
from
  (select r30_regist,
          r30_per1i,
          r30_per1f,
          case when r30_per1i < to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd')
               then to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd')
               else r30_per1i
          end as dtafas,
          case when r30_per1f > to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd')
               then to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd')
               else r30_per1f
          end as dtreto
      from cadferia
      where r30_regist = _registro
        and (r30_per1i <= to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd')
        and r30_per1f >= to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd'))

   union all

   select r30_regist,
          r30_per2i,
          r30_per2f,
          case when r30_per2i < to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd')
               then to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd')
               else r30_per2i
          end as dtafas,
          case when r30_per2f > to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd')
               then to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd')
               else r30_per2f
          end as dtreto
      from cadferia
      where r30_regist = _registro
        and (r30_per2i <= to_date(_ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2),'YYYY-mm-dd')
        and r30_per2f >= to_date(_ano||'-'||_mes||'-01','YYYY-mm-dd'))
     ) as x
) as y
  inner join rhpessoalmov on rh02_anousu = _ano
                         and rh02_mesusu = _mes
                         and rh02_regist = _registro
  left join rhlotacalend  on rh02_lota   = rh64_lota
  left outer join calendf on r62_calend::bigint  = rh64_calend
                         and r62_data    = data_dia
where dia_semana between 0 and 6
      and r62_data is null;

-- Calcula os dias válidos
dias_validos_ := 30 - (dias_mes_inteiro_ + dias_fer_inteiro_);

raise notice ' 1 ano     %', _ano;
raise notice ' 2 mes     %', _mes;
raise notice ' 3 ano_ant %', ano_ant_;
raise notice ' 4 mes_ant %', mes_ant_;
raise notice ' 5 dias_mes %', dias_mes_;
raise notice ' 6 dias_mes_inteiro %', dias_mes_inteiro_;
raise notice ' 7 dias_mes_ant_ %', dias_mes_ant_;
raise notice ' 8 dias_afa_ %', dias_afa_;
raise notice ' 9 dias_fer_ %', dias_fer_;
raise notice ' 10 dias_fer_inteiro %', dias_fer_inteiro_;
raise notice ' 11 data_ini %', _ano||'-'||_mes||'-'||ndias(_ano,_mes)::char(2);
raise notice ' 12 data_fin %',_ano||'-'||_mes||'-01';
raise notice ' 13 dias_validos %', dias_validos_;

retorno_ = dias_mes_ + dias_mes_ant_;

rTpDiasAfasta.riDiasMes           := dias_mes_;
rTpDiasAfasta.riDiasMesInteiro    := dias_mes_inteiro_;
rTpDiasAfasta.riDiasMesAnt        := dias_mes_ant_;
--rTpDiasAfasta.riDiasFerias      := dias_fer_;
rTpDiasAfasta.riDiasFeriasInteiro := dias_fer_inteiro_;
--rTpDiasAfasta.riDiasAfasta      := dias_afa_;
rTpDiasAfasta.riDiasValidos       := dias_validos_; -- Novo campo com os dias válidos

rTpDiasAfasta.riDtAfas := r30_per1i_;
rTpDiasAfasta.riDtReto := r30_per1f_;

return next rTpDiasAfasta;

-- return next retorno_;

return ;

end;

$$ LANGUAGE plpgsql;


SQL
     );
 }

 public function down()
 {
    DB::connection()->getPdo()->exec(<<<SQL
      drop function fc_valetransporte_dias_afasta_vr(integer,integer,integer);
SQL
     );

 }
}
