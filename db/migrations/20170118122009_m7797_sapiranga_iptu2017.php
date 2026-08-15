<?php

use Classes\PostgresMigration;

class M7797SapirangaIptu2017 extends PostgresMigration
{
    public function up()
    {
        /**
         * Dicionário de dados
         * Nova função de IPTU para 2017
         */
        $sDicionario  = "insert into db_sysfuncoes( codfuncao ,nomefuncao ,nomearquivo ,obsfuncao ,corpofuncao ,triggerfuncao ) values ( 184 ,'fc_calculoiptu_sapiranga_2017' ,'calculoiptu_sapiranga_2017.sql' ,'Função principal para o cálculo de IPTU de Sapiranga no exercício de 2017' ,'.' ,'0' );";
        $sDicionario .= "insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 983 ,184 ,1 ,'iMatricula' ,'int4' ,0 ,0 ,'0' ,'MATRICULA' );";
        $sDicionario .= "insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 984 ,184 ,2 ,'iAnousu' ,'int4' ,0 ,0 ,'0' ,'EXERCÍCIO' );";
        $sDicionario .= "insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 985 ,184 ,3 ,'lGerafinanc' ,'bool' ,0 ,0 ,'0' ,'SE GERA FINANCEIRO' );";
        $sDicionario .= "insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 986 ,184 ,4 ,'lAtualizap' ,'bool' ,0 ,0 ,'0' ,'ATUALIZA PARCELAS' );";
        $sDicionario .= "insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 987 ,184 ,5 ,'lNovonumpre' ,'bool' ,0 ,0 ,'0' ,'SE GERA UM NOVO NUMPRE' );";
        $sDicionario .= "insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 988 ,184 ,6 ,'lCalculogeral' ,'bool' ,0 ,0 ,'0' ,'SE E CALCULO GERAL' );";
        $sDicionario .= "insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 989 ,184 ,7 ,'lDemo' ,'bool' ,0 ,0 ,'0' ,'SE GERA DEMONSTRATIVO' );";
        $sDicionario .= "insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 990 ,184 ,8 ,'iParcelaini' ,'int4' ,0 ,0 ,'0' ,'PARCELA INICIAL' );";
        $sDicionario .= "insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 991 ,184 ,9 ,'iParcelafim' ,'int4' ,0 ,0 ,'0' ,'PARCELA FINAL' );";

        $this->execute($sDicionario);

        $this->atualizarValorVenalTerreno();
        $this->atualizarValorVenalConstrucao();
        $this->atualizarCalculoIptu();
    }

    public function down()
    {
        /**
         * Dicionário de dados
         * Deletemos a nova função de IPTU para 2017
         */
        $sDicionario  = "delete from db_sysfuncoesparam where db42_funcao = 184;";
        $sDicionario .= "delete from db_sysfuncoes where codfuncao = 184;";

        $this->execute($sDicionario);

        $this->deletarValoVenalTerreno();
        $this->deletarValoVenalConstrucao();
        $this->deletarCalculoIptu();
    }

    /**
     * Função responsável por atualizar as funçõe de IPTU no banco
     */
    private function atualizarValorVenalTerreno()
    {
        $sValorVenalTerreno = <<<EOL
create or replace function fc_iptu_calculavvt_sap_2017(integer,integer,numeric,numeric,boolean,boolean) returns tp_iptu_calculavvt as
$$

declare

  iIdbql            alias for $1;
  iAnousu           alias for $2;
  nFracao           alias for $3;
  nAreaIsenta       alias for $4;
  lMostrademo       alias for $5;
  lRaise            alias for $6;

  iMatricula        integer;
  iZona             integer;
  iQface            integer;
  iCarAmbas         integer;
  iCarEsq           integer;
  iPavimentacao     integer default 0;
  iIluminacao       integer default 0;
  iCondicaoEdif     integer default 0;

  lAlagado          boolean default false;
  lEncravado        boolean default false;
  lGleba            boolean default false;

  nDescontot        numeric default 0;
  nValDesconto      numeric default 0;
  nProfund          numeric default 0;
  nAreaCorrig       numeric default 0;
  nAreaCalcLote     numeric default 0;

  nTestada          numeric default 0;
  nVm2t             numeric default 0;
  rnAreaTotLote     numeric default 0;
  rnArealote        numeric default 0;
  nValor            numeric default 0;

  rCarlote          record;
  rCarface          record;
  rCfiptu           record;

  rtp_iptu_calculavvt tp_iptu_calculavvt%ROWTYPE;

begin

    rtp_iptu_calculavvt.rnVvt        := 0;
    rtp_iptu_calculavvt.rnAreaTotalC := 0;
    rtp_iptu_calculavvt.rnArea       := 0;
    rtp_iptu_calculavvt.rnTestada    := 0;
    rtp_iptu_calculavvt.rtDemo       := '';
    rtp_iptu_calculavvt.rtMsgerro    := '';
    rtp_iptu_calculavvt.rbErro       := 'f';
    rtp_iptu_calculavvt.riCoderro    := 0;
    rtp_iptu_calculavvt.rtErro       := '';

    perform fc_debug('INICIANDO CALCULO DO VALOR VENAL TERRITORIAL...', lRaise);

    select j34_zona,
           case when j34_areal = 0
             then j34_area
             else j34_areal
           end as nAreal
      into iZona,
           rnArealote
      from lote
     where j34_idbql = iIdbql;

    select matric
      into iMatricula
      from tmpdadosiptu
      limit 1;

    select j37_face,
           j37_valor,
           case
             when j36_testle = 0
               then j36_testad
             else j36_testle
           end as j36_testle
      into iQface,
           nVm2t,
           nTestada
      from iptuconstr
           inner join testada  on j36_face   = j39_codigo
                              and j36_idbql  = iIdbql
           inner join face     on j37_face   = j36_face
           inner join iptubase on j01_matric = j39_matric
     where j39_matric = iMatricula
       and j39_dtdemo is null
       and j01_baixa is null limit 1;


    if iQface is null then

      select j49_face,
             case
               when j36_testle = 0
                 then j36_testad
                 else j36_testle
             end as j36_testle
        into iQface,
             nTestada
        from testpri
             inner join face    on j49_face = j37_face
             inner join testada on j49_face = j36_face
                               and j49_idbql = j36_idbql
      where j49_idbql = iIdbql;

    end if;

    if iQface is null then

      rtp_iptu_calculavvt.rbErro    := 't';
      rtp_iptu_calculavvt.riCoderro := 6;
      return rtp_iptu_calculavvt;
    end if;


    select j51_valorm2t
      into nVm2t
      from zonasvalor
     where j51_zona   = iZona
       and j51_anousu = iAnousu;

    perform fc_debug('nVm2t    '||nVm2t, lRaise);
    perform fc_debug('iZona    '||iZona, lRaise);
    perform fc_debug('iAnousu  '||iAnousu, lRaise);

    if nVm2t is null then

      rtp_iptu_calculavvt.rbErro    := 't';
      rtp_iptu_calculavvt.riCoderro := 7;
      rtp_iptu_calculavvt.rtErro    := iZona::text;

      return rtp_iptu_calculavvt;
    end if;

-- testar ambas testadas
    select j35_caract
      into iCarAmbas
      from carlote
           inner join caracter on j31_codigo = j35_caract
     where j31_grupo = 47
           and j35_idbql = iIdbql;

    if iCarAmbas is not null then
      if iCarAmbas = 143 then -- tributar ambas as testadas
        select sum(j36_testle)
          into nTestada
                  from ( select case
                                    when j36_testle = 0
                                                      then j36_testad
                                                        else j36_testle
                                                end as j36_testle
                   from testada
                  where j36_idbql = iIdbql
                             ) as x;
      end if;
    end if;

    if nAreaIsenta > 0 then
      rnArealote := (rnArealote-nAreaIsenta);
    end if;

    nProfund := rnArealote / nTestada;

    nAreaCorrig := sqrt(nProfund);

    perform fc_debug( 'profundidade - '||nProfund||' area corrigida - '||nAreaCorrig||' testada - '||nTestada ,lRaise);

    nAreaCalcLote := nTestada * nAreaCorrig * 5::numeric;
    nAreaCorrig   := nTestada * nAreaCorrig * 5::numeric * nVm2t;

-- verifica caracteristica de esquina

    select j35_caract
      into iCarEsq
      from carlote
           inner join caracter on j31_codigo = j35_caract
     where j31_grupo = 31
           and j35_idbql = iIdbql;

    if iCarEsq is null then
      rtp_iptu_calculavvt.rbErro    := 't';
      rtp_iptu_calculavvt.riCoderro := 18;

      return rtp_iptu_calculavvt;
    end if;

    if iCarEsq = 100 then -- esquina
      nAreaCorrig    := nAreaCorrig   + ( nAreaCorrig / 3::numeric );
      nAreaCalcLote  := nAreaCalcLote + ( nAreaCalcLote / 3::numeric);
    else
      if iCarEsq = 108 then -- mais de uma esquina
        nAreaCorrig    := nAreaCorrig   + ( nAreaCorrig / 2::numeric );
        nAreaCalcLote  := nAreaCalcLote + ( nAreaCalcLote / 2::numeric);
      end if;
    end if;

        /* REGRAS PARA DESCONTO TERRITORIAL */

--============================================================================================================================================================================
-- COMECO CALCULO DESCONTO


     /* PROCURA AS CARACTERISTICAS DO LOTE */
    for rCarlote in select j35_caract, j31_grupo, j31_descr, j32_descr
                      from carlote
                           inner join caracter on j31_codigo = j35_caract
                           inner join cargrup  on j32_grupo = j31_grupo
                     where j35_idbql = iIdbql
        loop

-- se alagado
      if rCarlote.j35_caract = 96 then
        lAlagado = true;
      end if;

-- se encravado
      if rCarlote.j35_caract = 98 then
        lEncravado = true;
      end if;

-- se gleba
      if rCarlote.j35_caract = 94 then
        lGleba = true;
      end if;

-- se tipo de pavimentacao
      if rCarlote.j31_grupo = 2 then
        iPavimentacao = rCarlote.j35_caract;
      end if;

-- se tipo de iluminacao
      if rCarlote.j31_grupo = 1 then
        iIluminacao = rCarlote.j35_caract;
      end if;


-- se cond. edificação
      if rCarlote.j31_grupo = 59 then
        iCondicaoEdif = rCarlote.j35_caract;
      end if;


    end loop;

--=======================================================================================

-- verifica caracteristicas da face de quadra
    for rCarface in select j38_caract, j31_grupo
                      from carface
                           inner join caracter on j31_codigo = j38_caract
                      where j38_face = iQface
        loop

-- se tipo de pavimentacao
      if rCarface.j31_grupo = 37 then
        iPavimentacao = rCarface.j38_caract;
      end if;

-- se tipo de iluminacao
      if rCarface.j31_grupo = 36 and iIluminacao = 0 then
        iIluminacao = rCarface.j38_caract;
      end if;
    end loop;

--=======================================================================================

    perform fc_debug('iPavimentacao '||iPavimentacao, lRaise);
    perform fc_debug('iIluminacao   '||iIluminacao, lRaise);

    if iIluminacao = 119 then
      iIluminacao = 1;
    end if;

    if iPavimentacao = 120 then
      iPavimentacao = 7;
    end if;

    if iPavimentacao = 121 then
      iPavimentacao = 8;
    end if;

-- desconto se rua projetada
    if iPavimentacao = 7 then
      nDescontot = 20::float8;
    end if;

-- desconto se rua sem calcamento
    if iPavimentacao = 8 then
      nDescontot = 10::float8;
    end if;

-- desconto se sem rede eletrica
    if iIluminacao = 1 then
      nDescontot = nDescontot::float8 + 10::float8;
    end if;

-- desconto se alagado
    if lAlagado then
      nDescontot = nDescontot::float8 + 40::float8;
    end if;

-- desconto se encravado
    if lEncravado then
      nDescontot = 50::float8;
    end if;

-- desconto se gleba
    if lGleba then
      nDescontot = 50::float8;
    end if;

-- desconto de imóvel sem condição de edificação
    if iCondicaoEdif = 210  then
      nDescontot = nDescontot::float8  + 40::float8;
    end if;

-- Comentando este select a pedido do cliente para que as matrículas destes loteamentos
-- calcule limpeza de acordo com as características.

        /*============================================================================================*/

    nValor       := ( nAreaCorrig * ( nFracao / 100::numeric ))::numeric;

      nValDesconto := ((nValor * nDescontot)/100)::numeric;
        nValor       := round( (nValor - nValDesconto)::numeric, 2);


        select * from into rCfiptu cfiptu where j18_anousu = iAnousu;

    rnAreaTotLote                 := nAreaCalcLote * (nFracao / 100);
    rtp_iptu_calculavvt.rnArea    := rnAreaTotLote;
    rtp_iptu_calculavvt.rnVvt     := nValor;
    rtp_iptu_calculavvt.rtDemo    := '';
    rtp_iptu_calculavvt.rtMsgerro := '';
    rtp_iptu_calculavvt.rbErro    := 'f';

    update tmpdadosiptu set vvt = rtp_iptu_calculavvt.rnVvt, vm2t=nVm2t, areat=rnAreaTotLote;

    return rtp_iptu_calculavvt;

end;
$$  language 'plpgsql';
EOL;

        $this->execute($sValorVenalTerreno);
    }

    /**
     * Função responsável por deletar as novas funçõe de IPTU no banco
     */
    private function deletarValoVenalTerreno()
    {
        $this->execute("drop function fc_iptu_calculavvt_sap_2017(integer,integer,numeric,numeric,boolean,boolean)");
    }

    private function atualizarValorVenalConstrucao()
    {
        $sValorVenalConstrucao = <<<EOL
create or replace function fc_iptu_calculavvc_sapiranga_2017(integer,integer,boolean,boolean) returns tp_iptu_calculavvc as
$$

declare

    iMatricula       alias for $1;
    iAnousu          alias for $2;
    lMostrademo      alias for $3;
    lRaise           alias for $4;

    nDescontop       numeric default 0;
    nAreatc          numeric default 0;
    nVm2c            numeric default 0;
    nVvcp            numeric default 0;
    nVvc             numeric default 0;

    iPontos          integer default 0;
    iAnoconstrucao   integer default 0;
    iUso             integer default 0;
    iTipoconstrucao  integer default 0;
    iCondominio      integer default 0;
    iNumerocontr     integer default 0;
    lAtualiza        boolean default true;

    rConstr          record;
    rIdconstr        record;

    rtp_iptu_calculavvc tp_iptu_calculavvc%ROWTYPE;

begin
    perform fc_debug('INICIANDO CALCULO VVC ...', lRaise);

    rtp_iptu_calculavvc.rnVvc       := 0;
    rtp_iptu_calculavvc.rnTotarea   := 0;
    rtp_iptu_calculavvc.riNumconstr := 0;
    rtp_iptu_calculavvc.rtDemo      := '';
    rtp_iptu_calculavvc.rtMsgerro   := 'Retorno ok' ;
    rtp_iptu_calculavvc.rbErro      := 'f';
    rtp_iptu_calculavvc.riCodErro   := 0;
    rtp_iptu_calculavvc.rtErro      := '';

    -- Valor do metro quadrado padrão
    nVm2c := 461.44;
    iNumerocontr := 0;


    select j35_caract
      into iCondominio
      from carlote
           inner join caracter on j31_codigo = j35_caract
      where j31_grupo = 34
        and j35_idbql = (select j01_idbql from iptubase where j01_matric = iMatricula limit 1);

      if iCondominio = 106 then -- condominio
        nVm2c := 692.16;
      end if;

    for rConstr in select * from iptuconstr
                    where j39_matric = iMatricula
                      and j39_dtdemo is null
    loop

      iPontos        := 0;
      iAnoconstrucao := iAnousu - rConstr.j39_ano;
      iNumerocontr   := iNumerocontr + 1;

      for rIdconstr in select j48_caract,j31_pontos::numeric, j31_grupo, j31_descr, j32_descr
        from carconstr
             inner join caracter on j48_caract = j31_codigo
             inner join cargrup  on j31_grupo  = j32_grupo
        where j48_matric = rConstr.j39_matric
          and j48_idcons = rConstr.j39_idcons
      loop

        nDescontop := 0;
        iPontos    := iPontos + rIdconstr.j31_pontos;

        -- uso do solo (se industria por exemplo)
        if rIdconstr.j31_grupo = 5 then
          iUso := rIdconstr.j48_caract;
        end if;

        -- tipo de construcao (alvenaria ou mista)
        if rIdconstr.j31_grupo = 11 then
          iTipoconstrucao := rIdconstr.j48_caract;
        end if;

      end loop; --primeiro

      -- desconto se diferente de industria
      if iUso <> 20 then

        if iAnoconstrucao >= 6 and iAnoconstrucao <= 10 then
          if iTipoconstrucao = 53 or iTipoconstrucao = 54 then
            nDescontop := 5::numeric;
          end if;
          if iTipoconstrucao = 55 then
            nDescontop := 10::numeric;
          end if;
        end if;

        if iAnoconstrucao >= 11 and iAnoconstrucao <= 20 then
          if iTipoconstrucao = 53 or iTipoconstrucao = 54 then
            nDescontop := 10::numeric;
          end if;
          if iTipoconstrucao = 55 then
            nDescontop := 20::numeric;
          end if;
        end if;

        if iAnoconstrucao >= 21 and iAnoconstrucao <= 30 then
          if iTipoconstrucao = 53 or iTipoconstrucao = 54 then
            nDescontop := 20::numeric;
          end if;
          if iTipoconstrucao = 55 then
            nDescontop := 30::numeric;
          end if;
        end if;

        if iAnoconstrucao >= 31 and iAnoconstrucao <= 40 then
          if iTipoconstrucao = 53 or iTipoconstrucao = 54 then
            nDescontop := 30::numeric;
          end if;
          if iTipoconstrucao = 55 then
            nDescontop := 40::numeric;
          end if;
        end if;

        if iAnoconstrucao >= 40 then
          nDescontop := 50::numeric;
        end if;

      else
        nDescontop := 50::numeric;
        nVm2c      := 230.72::numeric;
      end if;

      nAreatc := sum(nAreatc::numeric + rConstr.j39_area::numeric)::numeric;

      if iPontos between 0 and 33 then
        iPontos := 33;
      elsif iPontos between 34 and 56 then
        iPontos := 56;
      elsif iPontos between 57 and 85 then
        iPontos := 85;
      elsif iPontos between 86 and 95 then
        iPontos := 95;
      elsif iPontos > 95 then
        iPontos := 100;
      end if;

      perform fc_debug('iPontos:     '||iPontos, lRaise);

      nVvcp   := ( iPontos::numeric * nVm2c::numeric * (rConstr.j39_area::numeric/100::numeric)::numeric)::numeric;

      perform fc_debug('nVvcp - '||nVvcp||' descontop - '||nDescontop||' nVvc - '||nVvc, lRaise);

      nVvcp   := round(nVvcp - (nVvcp * nDescontop / 100::numeric),2)::numeric;
      nVvc    := round(nVvc + nVvcp,2)::numeric;

      perform fc_debug('total - '||nVvc||' nVm2c - '||nVm2c||' nVvcp - '||nVvcp, lRaise);

      insert into tmpiptucale (anousu, matric,idcons,areaed,vm2,pontos,valor)
                       values (iAnousu,iMatricula,rConstr.j39_idcons,rConstr.j39_area,nVm2c,iPontos,nVvcp);
      if lAtualiza then
        update tmpdadosiptu set predial = true;
        lAtualiza := false;
      end if;

    end loop;

    rtp_iptu_calculavvc.rnVvc       := nVvc::numeric;
    rtp_iptu_calculavvc.rnTotarea   := nAreatc::numeric;
    rtp_iptu_calculavvc.riNumconstr := iNumerocontr;
    rtp_iptu_calculavvc.rtDemo      := '';
    rtp_iptu_calculavvc.rbErro      := 'f';

    update tmpdadosiptu set vvc = rtp_iptu_calculavvc.rnVvc;

    return rtp_iptu_calculavvc;

end;

$$  language 'plpgsql';
EOL;

        $this->execute($sValorVenalConstrucao);
    }

    private function deletarValoVenalConstrucao()
    {
        $this->execute("drop function fc_iptu_calculavvc_sapiranga_2017(integer,integer,boolean,boolean);");
    }

    private function atualizarCalculoIptu()
    {
        $sCalculoGeral = <<<EOL
create or replace function fc_calculoiptu_sapiranga_2017(integer,integer,boolean,boolean,boolean,boolean,boolean,integer,integer) returns varchar(100) as
$$
declare

   iMatricula        alias   for $1;
   iAnousu           alias   for $2;
   lGerafinanc       alias   for $3;
   lAtualizaParcela  alias   for $4;
   lNovonumpre       alias   for $5;
   lCalculogeral     alias   for $6;
   lDemonstrativo    alias   for $7;
   iParcelaini       alias   for $8;
   iParcelafim       alias   for $9;

   iIdbql               integer default 0;
   iNumcgm              integer default 0;
   iCodcli              integer default 0;
   iCodisen             integer default 0;
   iTipois              integer default 0;
   iParcelas            integer default 0;
   iNumconstr           integer default 0;
   iReceitaBomPagador   integer default 0;
   iCodErro             integer default 0;

   dDatabaixa           date;

   nAreal               numeric default 0;
   nAreac               numeric default 0;
   nTotarea             numeric default 0;
   nFracao              numeric default 0;
   nFracaolote          numeric default 0;
   nAliquota            numeric default 0;
   nIsenaliq            numeric default 0;
   nArealo              numeric default 0;
   nOneracao            numeric default 0;
   nVvc                 numeric(15,2) default 0;
   nVvt                 numeric(15,2) default 0;
   nVv                  numeric(15,2) default 0;
   nViptu               numeric(15,2) default 0;
   nDescontoBomPagador  numeric(15,2) default 0;

   tRetorno         text default '';
   tDemo            text default '';
   tErro            text default '';

   lFinanceiro      boolean;
   lDadosIptu       boolean;
   lErro            boolean;
   lIsentaxas       boolean;
   lTempagamento    boolean;
   lEmpagamento     boolean;
   lTaxasCalculadas boolean;
   lRaise           boolean default false; -- true para abilitar raise na funcao principal
   lSubRaise        boolean default false; -- true para abilitar raise nas sub-funcoes

   rCfiptu          record;
   rIptucalv        record;

begin
  lRaise    := ( case when fc_getsession('DB_debugon') is null then false else true end );

  perform fc_debug('INICIANDO CALCULO',lRaise,true,false);

  /**
   * Executa PRE CALCULO
   */
  select r_iIdbql, r_nAreal, r_nFracao, r_iNumcgm, r_dDatabaixa, r_nFracaolote,
         r_tDemo, r_lTempagamento, r_lEmpagamento, r_iCodisen, r_iTipois, r_nIsenaliq,
         r_lIsentaxas, r_nArealote, r_iCodCli, r_tRetorno

    into iIdbql, nAreal, nFracao, iNumcgm, dDatabaixa, nFracaolote, tDemo, lTempagamento,
         lEmpagamento, iCodisen, iTipois, nIsenaliq, lIsentaxas, nArealo, iCodCli, tRetorno

    from fc_iptu_precalculo( iMatricula, iAnousu, lCalculogeral, lAtualizaParcela, lDemonstrativo, lRaise );

  perform fc_debug(' RETORNO DA PRE CALCULO: ',            lRaise);
  perform fc_debug('  iIdbql        -> ' || iIdbql,        lRaise);
  perform fc_debug('  nAreal        -> ' || nAreal,        lRaise);
  perform fc_debug('  nFracao       -> ' || nFracao,       lRaise);
  perform fc_debug('  iNumcgm       -> ' || iNumcgm,       lRaise);
  perform fc_debug('  dDatabaixa    -> ' || dDatabaixa,    lRaise);
  perform fc_debug('  nFracaolote   -> ' || nFracaolote,   lRaise);
  perform fc_debug('  tDemo         -> ' || tDemo,         lRaise);
  perform fc_debug('  lTempagamento -> ' || lTempagamento, lRaise);
  perform fc_debug('  lEmpagamento  -> ' || lEmpagamento,  lRaise);
  perform fc_debug('  iCodisen      -> ' || iCodisen,      lRaise);
  perform fc_debug('  iTipois       -> ' || iTipois,       lRaise);
  perform fc_debug('  nIsenaliq     -> ' || nIsenaliq,     lRaise);
  perform fc_debug('  lIsentaxas    -> ' || lIsentaxas,    lRaise);
  perform fc_debug('  nArealote     -> ' || nArealo,       lRaise);
  perform fc_debug('  iCodCli       -> ' || iCodCli,       lRaise);
  perform fc_debug('  tRetorno      -> ' || tRetorno,      lRaise);

  /**
   * Variavel de retorno contem a msg
   * de erro retornada do pre calculo
   */
  if trim(tRetorno) <> '' then
    return tRetorno;
  end if;

  /**
   * Guarda os parametros do calculo
   */
  select * from into rCfiptu cfiptu where j18_anousu = iAnousu;

  /**
   * Calcula valor do terreno
   */
  perform fc_debug('PARAMETROS fc_iptu_calculavvt_sapiranga_2017 Anousu: '||iAnousu||' - IDBQL: '||iIdbql||' - FRACAO DO LOTE: '||nFracaolote||' DEMO: '||lDemonstrativo||'- j18_vlrref: '||rCfiptu.j18_vlrref::numeric, lRaise);

  select rnvvt, rnarea, rtdemo, rtmsgerro, rberro, riCodErro, rtErro
    into nVvt, nAreac, tDemo, tRetorno, lErro, iCodErro, tErro
    from fc_iptu_calculavvt_sap_2017( iIdbql, iAnousu, nFracaolote, nArealo, lDemonstrativo, lRaise );

  perform fc_debug('RETORNO fc_iptu_calculavvt_sapiranga_2017 -> VVT: '||nVvt||' - AREA CONSTRUIDA: '||nAreac||' - RETORNO: '||tRetorno||' - ERRO: '||lErro, lRaise);
  perform fc_debug('', lRaise);

  if lErro is true then

    select fc_iptu_geterro( iCodErro, tErro ) into tRetorno;
    return tRetorno;
  end if;

  /**
   * Calcula valor da construcao
   */
  perform fc_debug('PARAMETROS fc_iptu_calculavvc_sapiranga_2017 MATRICULA: '||iMatricula||' - ANOUSU:'||iAnousu, lRaise);

  select rnvvc, rntotarea, rinumconstr, rtdemo, rtmsgerro, rberro, riCodErro, rtErro
    into nVvc, nTotarea, iNumconstr, tDemo, tRetorno, lErro, iCodErro, tErro
    from fc_iptu_calculavvc_sapiranga_2017( iMatricula, iAnousu, lDemonstrativo,lRaise );

  perform fc_debug('RETORNO fc_iptu_calculavvc_sapiranga_2017 -> VVC: '||nVvc||' - AREA TOTAL: '||nTotarea||' - NUMERO DE CONSTRUÇÕES: '||iNumconstr||' - RETORNO: '||tRetorno||' - ERRO: '||lErro, lRaise);
  perform fc_debug('', lRaise);

  if lErro is true then

    select fc_iptu_geterro(iCodErro, tErro) into tRetorno;
    return tRetorno;
  end if;

  /* BUSCA A ALIQUOTA  */
  -- so executar se nao for isento
  if iNumconstr is not null and iNumconstr > 0 then
    select fc_iptu_getaliquota_sap_2008(iMatricula,iIdbql,iNumcgm,true,lSubRaise) into nAliquota;
  else
    select fc_iptu_getaliquota_sap_2008(iMatricula,iIdbql,iNumcgm,false,lSubRaise) into nAliquota;
  end if;

  if not found or nAliquota = 0 then
    select fc_iptu_geterro(13,'') into tRetorno;
    return tRetorno;
  end if;

  /*--------- CALCULA O VALOR VENAL -----------*/
  perform fc_debug('nVvc - '||nVvc||' nVvt - '||nVvt, lRaise);

  nVv    := nVvc + nVvt;

  perform fc_debug('valor sem aliquota - '||nVv, lRaise);

  nViptu := nVv * ( nAliquota / 100 );

  perform fc_debug('valor com aliquota - '||nViptu, lRaise);

  /*-------------------------------------------*/

  select count(*)
    into iParcelas
    from cadvencdesc
         inner join cadvenc on q92_codigo = q82_codigo
   where q92_codigo = rCfiptu.j18_vencim ;

  if not found or iParcelas = 0 then
    select fc_iptu_geterro(14,'') into tRetorno;
    return tRetorno;
  end if;


  perform predial from tmpdadosiptu where predial is true;
  if found then
    insert into tmprecval values (rCfiptu.j18_rpredi, nViptu, 1, false);
  else
    insert into tmprecval values (rCfiptu.j18_rterri, nViptu, 1, false);
  end if;

  update tmpdadosiptu set viptu = nViptu, codvenc = rCfiptu.j18_vencim;

  update tmpdadostaxa set anousu = iAnousu, matric = iMatricula, idbql = iIdbql, valiptu = nViptu, valref = rCfiptu.j18_vlrref, vvt = nVvt, nparc = iParcelas;

  /* CALCULA AS TAXAS */

  perform fc_debug('PARAMETROS fc_iptu_calculataxas  ANOUSU '||iAnousu||' -- CODCLI '||iCodcli, lRaise);

  select fc_iptu_calculataxas(iMatricula,iAnousu,iCodcli,lSubRaise)
    into lTaxasCalculadas;

  perform fc_debug('RETORNO fc_iptu_calculataxas --->>> TAXASCALCULADAS - '||lTaxasCalculadas, lRaise);

/* MONTA O DEMONSTRATIVO */
  select fc_iptu_demonstrativo(iMatricula,iAnousu,iIdbql,lSubRaise )
    into tDemo;

/* GERA FINANCEIRO */
  if lDemonstrativo is false then -- Se nao for demonstrativo gera o financeiro, caso contrario retorna o demonstrativo
    select fc_iptu_geradadosiptu(iMatricula,iIdbql,iAnousu,nIsenaliq,lDemonstrativo,lSubRaise)
      into lDadosIptu;

      if lGerafinanc then
        select fc_iptu_gerafinanceiro(iMatricula,iAnousu,iParcelaini,iParcelafim,lCalculogeral,lTempagamento,lNovonumpre,lDemonstrativo,lSubRaise)
          into lFinanceiro;
      end if;
  else
    return tDemo;
  end if;

  --
  -- se tem receita para bom pagador guarda o valor
  --


  if exists( select *
               from iptucalcconfrec
              where j23_matric = iMatricula
                and j23_anousu = iAnousu ) then

    for rIptucalv in

       select arrecad.k00_numpre,
              arrecad.k00_receit,
              ( select j21_codhis
                 from iptucalv
                where j21_matric = iMatricula
                  and j21_anousu = iAnousu
                  and j21_receit = arrecad.k00_receit
                  and j21_codhis <> rCfiptu.j18_iptuhistisen ) as hist,
              ( sum( coalesce( substr( fc_calcula( arrecad.k00_numpre,
                                       arrecad.k00_numpar,
                                       arrecad.k00_receit,
                                       current_date,
                                       arrecad.k00_dtvenc,
                                       iAnousu ) ,54,13 )::numeric ,0 ) ) +
              coalesce(
              ( ( select sum(coalesce(k00_valor,0))
                  from arrecant
                 where arrecant.k00_numpre = arrecad.k00_numpre
                   and arrecant.k00_receit = arrecad.k00_receit ) -
                ( select sum(coalesce(k00_valor,0))
                    from arrepaga
                   where arrepaga.k00_numpre = arrecad.k00_numpre
                     and arrepaga.k00_receit = arrecad.k00_receit ) ) ,0)
              ) as valor_desconto
         from iptucalcconfrec
              inner join iptunump on iptunump.j20_matric = iptucalcconfrec.j23_matric
                                 and iptunump.j20_anousu = iptucalcconfrec.j23_anousu
              inner join arrecad  on arrecad.k00_numpre  = iptunump.j20_numpre
                                 and arrecad.k00_receit  = iptucalcconfrec.j23_recdst
        where j23_matric = iMatricula
          and j23_anousu = iAnousu
        group by arrecad.k00_numpre,
                 arrecad.k00_receit

    loop

      if rIptucalv.hist = 7 then
        insert into iptucalv (j21_anousu,j21_matric,j21_codhis,j21_receit,j21_valor,j21_quant)
                      values (iAnousu, iMatricula, 12, rIptucalv.k00_receit, ( abs(rIptucalv.valor_desconto) * -1 ), 0);
      elsif rIptucalv.hist = 1 then
        insert into iptucalv (j21_anousu,j21_matric,j21_codhis,j21_receit,j21_valor,j21_quant)
                      values (iAnousu, iMatricula, 11, rIptucalv.k00_receit, ( abs(rIptucalv.valor_desconto) * -1 ), 0);
      end if;

    end loop;

  end if;


  if lDemonstrativo is false then
    update iptucalc set j23_manual = tDemo where j23_matric = iMatricula and j23_anousu = iAnousu;
  end if;

  select fc_iptu_geterro(1,'') into tRetorno;
  return tRetorno;

end;
$$  language 'plpgsql';
EOL;

        $this->execute($sCalculoGeral);
    }

    private function deletarCalculoIptu()
    {
        $this->execute("drop function fc_calculoiptu_sapiranga_2017(integer,integer,boolean,boolean,boolean,boolean,boolean,integer,integer)");
    }
}
