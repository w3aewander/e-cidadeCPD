<?php
/**
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao 2 da
 *  Licenca como (a seu criterio) qualquer versao mais nova.
 *
 *  Este programa e distribuido na expectativa de ser util, mas SEM
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 *  detalhes.
 *
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 *  junto com este programa; se nao, escreva para a Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *  02111-1307, USA.
 *
 *  Copia da licenca no diretorio licenca/licenca_en.txt
 *                                licenca/licenca_pt.txt
 */

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db"."_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("libs/exceptions/BusinessException.php"));
require_once(modification("libs/exceptions/DBException.php"));
require_once(modification("libs/exceptions/ParameterException.php"));
require_once(modification("dbforms/db_funcoes.php"));


$oJson                = new services_json();
$oParam               = $oJson->decode(str_replace("\\","",$_POST["json"]));
$oRetorno             = new stdClass();
$oRetorno->lErro      = false;
$oRetorno->sMessage   = '';

$iInstituicaoSessao = db_getsession('DB_instit');
$iAnoSessao         = db_getsession('DB_anousu');

try {

  db_inicio_transacao();

  switch ($oParam->exec) {
  	
  	
  	case "recriaConplanoExeSaldo":
  	
  	
  		/**
  		 *  deleta conplanoexesaldo
  		 */
  		
  		$sSqlDeletaConplanoExeSaldo = "delete from conplanoexesaldo";
  		
  		/**
  		 * cria lancamentos de debitos
  		 */
  		
  		$sSqlLancDeb  = " create table landeb as select c69_anousu,
                                                  c69_debito,to_char(c69_data,'MM')::integer,
                                                  sum(round(c69_valor,2)),0::float8
                                                  from conlancamval
                                                  group by c69_anousu,c69_debito,to_char(c69_data,'MM')::integer";
  		
  		/**
  		 * cria lancamentos de creditos
  		 */
  		$sSqlLancCre = "create table lancre as select c69_anousu,
                              c69_credito,to_char(c69_data,'MM')::integer,
                              0::float8,
                              sum(round(c69_valor,2))
                         from conlancamval
                        group by c69_anousu,c69_credito,to_char(c69_data,'MM')::integer";
  		
  		/**
  		 * insere conplanoexesaldo a partir dos lanc deb
  		 */
  		$sSqlInsereLancSeb = "insert into conplanoexesaldo select * from landeb";
  		
  		/**
  		 * atualiza conplanoexesaldo
  		 */
  		 
  		$sSqlAtualizaConplanoExeSaldo = "update conplanoexesaldo set c68_credito = lancre.sum
                                       from lancre
                                      where c68_anousu = lancre.c69_anousu
                                        and c68_reduz  = lancre.c69_credito
                                        and c68_mes    = lancre.to_char ";
  		 
  		 
  		 
  		/*
  		 * remove lancre dos registros atualizados
  		*
  		*/
  		 
  		$sSqlRemoveAtualizadosLancCre = "delete from lancre
                                           using conplanoexesaldo
                                           where lancre.c69_anousu          = conplanoexesaldo.c68_anousu
                                             and conplanoexesaldo.c68_reduz = lancre.c69_credito
                                             and conplanoexesaldo.c68_mes   = lancre.to_char ";
  		 
  		/**
  		 * insere os restantes
  		 */
  		 
  		$sSqlInsereLancCre = " insert into conplanoexesaldo select * from lancre ";
  		 
  		/**
  		 * dropa as tabela auxiliar
  		 */
  		 
  		$sSqlDropTabelaAuxDeb = " drop table landeb ";
  		$sSqlDropTabelaAuxCre = " drop table lancre ";
  		
  		
  		 
  		if ( !db_query($sSqlDeletaConplanoExeSaldo) ) {
  			throw new Exception("Erro ao Processar ConplanoExeSaldo.\n" . pg_last_error());
  		} 
  		
  		if ( !db_query($sSqlLancDeb) ) {
  			throw new Exception("Erro ao Processar ConplanoExeSaldo.\n" . pg_last_error());
  		}
  		if ( !db_query($sSqlLancCre) ) {
  			throw new Exception("Erro ao Processar ConplanoExeSaldo.\n" . pg_last_error());
  		}
  		if ( !db_query($sSqlInsereLancSeb) ) {
  			throw new Exception("Erro ao Processar ConplanoExeSaldo.\n" . pg_last_error());
  		}
  		if ( !db_query($sSqlAtualizaConplanoExeSaldo) ){
  			throw new Exception("Erro ao Processar ConplanoExeSaldo.\n" . pg_last_error());
  		}
  		if ( !db_query($sSqlRemoveAtualizadosLancCre) ){
  			throw new Exception("Erro ao Processar ConplanoExeSaldo.\n" . pg_last_error());
  		}
  		if ( !db_query($sSqlInsereLancCre) ){
  			throw new Exception("Erro ao Processar ConplanoExeSaldo.\n" . pg_last_error());
  		}
  		if ( !db_query($sSqlDropTabelaAuxDeb) ) {
  			throw new Exception("Erro ao Processar ConplanoExeSaldo.\n" . pg_last_error());
  		}
  		if ( !db_query($sSqlDropTabelaAuxCre) ){
  			throw new Exception("Erro ao Processar ConplanoExeSaldo.\n" . pg_last_error());
  		}
  		
  		db_fim_transacao(false);
  		$oRetorno->sMessage = urlencode("Processo Efetuado Com Sucesso.");  	
  	
  	
  	break;
  	
  	

    case "processaDisponibilidade":

    	$sEstrutural       = $oParam->sEstrutural     ;  
    	$iRecurso          = $oParam->iRecurso        ; 
    	$sCaracteristica   = $oParam->sCaracteristica ; 
    	$dDataInicial      = $oParam->dDataInicial    ; 
    	$dDataFinal        = $oParam->dDataFinal      ; 
    	$iInstituicao      = db_getsession('DB_instit');
    	$iAnoUsu           = db_getsession("DB_anousu");
    	
    	$sSqlProcessaDisponibilidade = "
    			
    			
        drop table if exists w_reduz_contacorrente ;
        create temp table w_reduz_contacorrente as
        select c61_reduz
        from conplano inner join conplanoreduz on c60_anousu = c61_anousu and c60_codcon = c61_codcon
        where c60_anousu = {$iAnoUsu} and c60_estrut like '{$sEstrutural}%' and c61_instit = {$iInstituicao} ;
        
        select distinct c70_codlan,
              c70_data,
              c70_valor,
              c69_debito,
              c69_credito,
              c71_coddoc,
              c53_coddoc,
              c53_descr
        from conlancam
              inner join conlancamval on c69_codlan = c70_codlan
              inner join conlancamdoc on c71_codlan = c70_codlan
              inner join conhistdoc on c53_coddoc = c71_coddoc
              left  join contacorrentedetalheconlancamval on c28_conlancamval = c69_sequen
        where c70_data between '{$dDataInicial}' and '{$dDataFinal}'
          and (c69_debito = (select c61_reduz from w_reduz_contacorrente) or c69_credito = (select c61_reduz from w_reduz_contacorrente))
          and c28_conlancamval is null;
        
        drop table if exists w_reduz_contacorrente ;
        
        create temp table w_reduz_contacorrente as
        select c61_reduz
        from conplano inner join conplanoreduz on c60_anousu = c61_anousu and c60_codcon = c61_codcon
        where c60_anousu = {$iAnoUsu} and c60_estrut like '{$sEstrutural}%' and c61_instit = {$iInstituicao} ; 
        
        insert into contacorrentedetalheconlancamval
             select nextval('contacorrentedetalheconlancamval_c28_sequencial_seq')
                    ,(select c19_sequencial
                      from contacorrentedetalhe
                      where c19_conplanoreduzanousu = {$iAnoUsu} and c19_reduz = (select c61_reduz from w_reduz_contacorrente)
                        and c19_orctiporec     =  {$iRecurso}         
                        and c19_concarpeculiar = '{$sCaracteristica}'
                        and c19_instit         =  {$iInstituicao}     
                        limit 1)
                    ,c69_sequen
                    ,case when c69_debito = (select c61_reduz from w_reduz_contacorrente) then 'D' else 'C' end
               from conlancam
                    inner join conlancamval on c69_codlan = c70_codlan
                    inner join conlancamdoc on c71_codlan = c70_codlan
                    inner join conhistdoc on c53_coddoc = c71_coddoc
                    left  join contacorrentedetalheconlancamval on c28_conlancamval = c69_sequen
              where c70_data between  '{$dDataInicial}' and '{$dDataFinal}'
                and (c69_debito = (select c61_reduz from w_reduz_contacorrente) or c69_credito = (select c61_reduz from w_reduz_contacorrente))
                and c28_conlancamval is null;
      ";

    	if ( !db_query($sSqlProcessaDisponibilidade)  ) {
    		
    		throw new Exception("Erro ao Processar Disponibilidade.\n" . pg_last_error());
    	}
    	
    	db_fim_transacao(false);
    	
    	
    	$oRetorno->sMessage = urlencode("Processo Efetuado Com Sucesso.");
    	

    break;


  }

} catch (Exception $eErro) {

  db_fim_transacao(true);
  $oRetorno->lErro    = true;
  $oRetorno->sMessage = urlencode($eErro->getMessage());
}

echo $oJson->encode($oRetorno);
