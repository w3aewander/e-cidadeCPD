<?php

use Classes\PostgresMigration;

class M5916TaxaLimpezaSapiranga extends PostgresMigration
{
    public function up()
    {
        $this->execute("insert into db_sysfuncoes values(183, 'fc_iptu_taxalimpeza_sap_2017', 'Cálculo de taxa de limpeza de Sapiranga para 2017', 'a', '0');");
        $this->execute("update db_sysfuncoes set codfuncao = 183 , nomefuncao = 'fc_iptu_taxalimpeza_sap_2017' , nomearquivo = 'iptu_taxalimpeza_sap_2017.sql' , obsfuncao = 'Cálculo de taxa de limpeza de Sapiranga para 2017' , corpofuncao = '0' , triggerfuncao = '0' where codfuncao = 181;");
        $this->execute("insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 965 ,183 ,1 ,'iReceita' ,'int4' ,0 ,0 ,'0' ,'RECEITA' );");
        $this->execute("insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 966 ,183 ,2 ,'iAliquota' ,'numeric' ,0 ,0 ,'0' ,'ALIQUOTA' );");
        $this->execute("insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 967 ,183 ,3 ,'iHistCalc' ,'int4' ,0 ,0 ,'0' ,'HISTORICO DE CALCULO' );");
        $this->execute("insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 968 ,183 ,4 ,'iPercIsen' ,'numeric' ,0 ,0 ,'0' ,'PERCENTUAL DE ISENCAO' );");
        $this->execute("insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 969 ,183 ,5 ,'nValpar' ,'numeric' ,0 ,0 ,'0' ,'VALOR POR PARAMETRO' );");
        $this->execute("insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 970 ,183 ,6 ,'lRaise' ,'bool' ,0 ,0 ,'FALSE' ,'DEBUG' );");

        $this->atualizarFuncao();
    }

    public function down()
    {
        $this->execute("delete from db_sysfuncoesparam where db42_funcao = 183;");
        $this->execute("delete from db_sysfuncoes where codfuncao = 183;");

        $this->deletarFuncao();
    }

    private function atualizarFuncao()
    {
        $sFuncao = <<<EOL
create or replace function fc_iptu_taxalimpeza_sap_2017 (integer, numeric, integer, numeric, numeric, boolean) returns boolean as
$$
declare

    iReceita       alias for $1;
    iAliquota      alias for $2;
    iHistCalc      alias for $3;
    iPercIsen      alias for $4;
    nValpar        alias for $5;
    lRaise         alias for $6;

    nValTaxa       numeric(15,2) default 0;
    nValorBase     numeric(15,2) default 0;
    nTotAreaConstr numeric       default 0;

    iZona          integer       default 0;
    iIdbql         integer       default 0;
    iAnousu        integer       default 0;
    iMatric        integer       default 0;

    lValorBaseDuplicado  boolean       default false;

    tSql           text          default '';

begin

    lRaise := true;

    perform fc_debug(' <iptu_taxalimpeza> Calculando taxa de limpeza', lRaise);
    perform fc_debug(' <iptu_taxalimpeza> receita: '   || iReceita, lRaise);
    perform fc_debug(' <iptu_taxalimpeza> aliq: '      || iAliquota, lRaise);
    perform fc_debug(' <iptu_taxalimpeza> historico: ' || iHistCalc, lRaise);

    select matric
      into iMatric
      from tmpdadosiptu limit 1;

    perform *
      from carconstr
           inner join iptuconstr on j39_matric = j48_matric
                                and j39_idcons = j48_idcons
           inner join caracter   on j48_caract = j31_codigo
           inner join cargrup    on j31_grupo  = j32_grupo
     where j48_matric = iMatric
       and j39_dtdemo is null
       and j48_caract in (21, 20, 212);
    if found then
      lValorBaseDuplicado := true;
    end if;

    perform fc_debug(' <iptu_taxalimpeza> lValorBaseDuplicado: ' || lValorBaseDuplicado, lRaise);

    select idbql, anousu
          into iIdbql, iAnousu
            from tmpdadostaxa limit 1;

        select j34_zona
          into iZona
          from lote
       where j34_idbql = iIdbql;

    select j57_valor
          into nValorBase
      from zonastaxa
         where j57_zona   = iZona
           and j57_receit = iReceita
             and j57_anousu = iAnousu;

    perform fc_debug(' <iptu_taxalimpeza> nValorBase: ' || nValorBase, lRaise);

    select sum(j39_area)
      into nTotAreaConstr
      from iptuconstr
     where j39_matric = iMatric
       and j39_dtdemo is null;

    perform fc_debug(' <iptu_taxalimpeza> nTotAreaConstr: ' || nTotAreaConstr, lRaise);

    -- se comercio e area construida > 100 dobra o valor da taxa de limpeza
        if nTotAreaConstr > 100::numeric and lValorBaseDuplicado  is true then
          nValorBase := (nValorBase*2)::numeric;
        end if;

        nValTaxa := nValorBase;

    if nValTaxa is null then
           return false;
        end if;

    insert into tmptaxapercisen values (iReceita,iPercIsen,0,nValTaxa);

        if iPercIsen > 0 then
      nValTaxa := nValTaxa * (100 - iPercIsen) / 100;
    end if;

    perform fc_debug(' <iptu_taxalimpeza> Percentual Isencao: ' || iPercIsen, lRaise);
    perform fc_debug(' <iptu_taxalimpeza> Valor final da taxa: ' || nValTaxa, lRaise);

    tSql := 'insert into tmprecval values ('||iReceita||','||nValTaxa||','||iHistCalc||',true)';
    execute tSql;

    return true;
end;

$$ language 'plpgsql';
EOL;

        $this->execute($sFuncao);
    }

    private function deletarFuncao()
    {
        $this->execute("drop function if exists fc_iptu_taxalimpeza_sap_2017 (integer, numeric, integer, numeric, numeric, boolean);");
    }
}
