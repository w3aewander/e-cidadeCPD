<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M29941FuncTxLixo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(<<<SQL

            -- DROP FUNCTION public.fc_iptu_taxa_de_coleta_e_remocao_lixo_valenca(int4, numeric, int4, numeric, numeric);

CREATE OR REPLACE FUNCTION public.fc_iptu_taxa_de_coleta_e_remocao_lixo_valenca(integer, numeric, integer, numeric, numeric)
 RETURNS boolean
 LANGUAGE plpgsql
AS \$function$
declare

	iReceita alias for $1;

	nAliquota alias for $2;

	iHistcalc alias for $3;

	nPercIsen alias for $4;

	nValpar alias for $5;

	iMatric int;

	iValidaMatric int;

	lRaise bool;

	iAnoUsu int;

	iPercentualMaximo int;

	nValorMaximoTaxa numeric;

	nValorIptu numeric;

	nAreaTotalLote numeric;

	nAreaFracaoLote numeric;

	nAreaConstruidaUnidade numeric;

	vTipoMatricula varchar;

	nValorTaxa numeric;

	dDataBaixaMatric date;

	nPercentualIsencao numeric;

begin

	lRaise := (case when fc_getsession('DB_debugon') is null then false else true end);

	select matric, anousu
    into iMatric, iAnoUsu
    from tmpdadostaxa;

    if not found then
       perform fc_debug( 'Matricula não encontrada.',lRaise);
       return false;
    end if;

	select j01_baixa
	into dDataBaixaMatric
	from cadastro.iptubase
	where j01_matric = iMatric;

	if dDataBaixaMatric is not null then
		perform fc_debug( 'Matricula baixada.',lRaise);
       	return false;
	end if;

	if nValpar = 0 then
    	perform fc_debug( 'Valor para multiplicacao zerado.',lRaise);
    	return false;
    end if;

	select	valiptu 
    into	nValorIptu
    from	tmpdadostaxa
    where	anousu = iAnoUsu
    and 	matric = iMatric;

    if not found then
   		perform fc_debug( 'Valor do IPTU Zerado.',lRaise);
        return false;
    end if;

	perform fc_debug( 'CALCULANDO TAXA DE COLETA E REMOCAO DE LIXO ...',lRaise);

	-- Valor da taxa da taxa deve ser no maximo 20% sobre o valor do IPTU

	iPercentualMaximo = 20;

	nValorMaximoTaxa = round((nValorIptu * iPercentualMaximo) / 100, 2);
	
	perform fc_debug( 	'iAnoUsu: ' || iAnoUsu || 
						', iMatric: ' || iMatric || 
					  	', iHistcalc: '|| iHistcalc || 
					  	', iReceita: '|| iReceita || 
						', nValpar: '|| nValpar || 
						', iPercentualMaximo: ' || iPercentualMaximo ||
						', nValorIptu: '|| nValorIptu || 
						', nValorMaximoTaxa: '|| nValorMaximoTaxa 
						,lRaise);

	-- Área total do lote:
	select j34_area
	into nAreaTotalLote
	from cadastro.lote l
	inner join cadastro.iptubase i 
	on i.j01_idbql = l.j34_idbql 
	where i.j01_matric = iMatric;

	-- Área da Fração do Lote:
	select round(areat, 2) as areat
	into nAreaFracaoLote
	from TMPDADOSIPTU
	where matric = iMatric;

	-- Total Construido na Unidade (Irregular e/ou Regular):
	select coalesce(sum(j39_area),0) as area_construida
	into nAreaConstruidaUnidade
	from iptuconstr
	inner join iptubase 
	on j01_matric = j39_matric
	where j39_matric = iMatric
	and j39_dtdemo is null
	and j01_baixa is null;

	-- Tipo da matricula:
	select 	distinct 
			case
				when c.j48_caract is null then 'matric_predial'
				when c.j48_caract is not null then 'matric_predial_irregular_independente'
			end as tipo_matric
	into vTipoMatricula
	from cadastro.iptuconstr i
	left join cadastro.carconstr c
	on c.j48_matric = i.j39_matric 
	and c.j48_idcons = i.j39_idcons 
	and c.j48_caract = 7203
	where i.j39_dtdemo is null
	and i.j39_matric = iMatric;

	if not found then
		vTipoMatricula = 'matric_territorial';
	end if;	

	perform fc_debug(	'nAreaTotalLote: ' || nAreaTotalLote || 
						', nAreaFracaoLote: '|| nAreaFracaoLote|| 
						', nAreaConstruidaUnidade: '|| nAreaConstruidaUnidade|| 
						', vTipoMatricula: '|| vTipoMatricula, 
						lRaise);

	
	if vTipoMatricula = 'matric_predial' then
	-- Matrículas Prediais: Área do lote ou área de fração acrescido da área da construção ativa * R$5,00

		if nAreaFracaoLote > 0 then
			nValorTaxa = (nAreaFracaoLote + nAreaConstruidaUnidade) * nValpar;
		else
			nValorTaxa = nAreaConstruidaUnidade * nValpar;
		end if;

	elseif vTipoMatricula = 'matric_predial_irregular_independente' then
	-- Matrículas Prediais Irregular independente: Área da construção ativa (regular e/ou irregular) * R$5,00

		nValorTaxa = nAreaConstruidaUnidade * nValpar;

	elseif vTipoMatricula = 'matric_territorial' then
	-- Matrículas Territoriais: Área do lote ou área de fração * R$5,00

		if nAreaFracaoLote > 0 then
			nValorTaxa = nAreaFracaoLote * nValpar;
		else
			nValorTaxa = nAreaTotalLote * nValpar;
		end if;

	else
	    perform fc_debug( 'Tipo de matricula nao encontrado.',lRaise);
    	return false;
	end if;

	if nValorTaxa > nValorMaximoTaxa then

		perform fc_debug( 'Calculo da taxa excede 20% do valor do IPTU. VALOR CALCULO:' || nValorTaxa, lRaise);
		nValorTaxa = nValorMaximoTaxa;

	end if;

		perform fc_debug(	'nValorIptu: '|| nValorIptu || 
							', nValorMaximoTaxa: '|| nValorMaximoTaxa || 
							', nValorTaxa: '|| nValorTaxa ,lRaise);
	
	select * from cadastro.iptuisen
	into nPercentualIsencao
	where j46_tipo = 7706
	and j46_matric = iMatric
	and j46_dtinc <= now()
	and j46_dtfim >= now(); 

	insert into tmptaxapercisen values (iReceita, nPercentualIsencao, 0, nValorTaxa);

	if nPercentualIsencao > 0 then
		nValorTaxa := nValorTaxa * (100 - nPercentualIsencao) / 100;
	end if;

	insert into tmprecval values (iReceita, nValorTaxa, iHistCalc, true);

	return true;
end;

\$function$
;



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
        DB::unprepared(<<<SQL

            drop function if exists public.fc_iptu_taxa_de_coleta_e_remocao_lixo_valenca;

SQL

        );
    }
}
