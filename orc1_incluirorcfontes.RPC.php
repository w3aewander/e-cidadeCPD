<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBselller Servicos de Informatica
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
require_once(modification("std/db_stdClass.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_"."conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("dbforms/db_funcoes.php"));

$oJson               = new services_json();
$oParam              = $oJson->decode(str_replace("\\","",$_POST["json"]));
$oRetorno            = new stdClass();
$oRetorno->iStatus   = 1;
$oRetorno->sMensagem = '';
$iInstituicaoSessao  = db_getsession("DB_instit");

try {

	switch ($oParam->exec) {
		
		case "incluirRegistro" :
		
			$o57_codfon  = $oParam->o57_codfon;
			$o57_anousu  = $oParam->o57_anousu;
			$iAnoUsu     = db_getsession("DB_anousu");
				
			$sSqlInsert  = " insert into 
					                    orcfontes select o57_codfon    ,
                                               {$o57_anousu} ,
                                               o57_fonte     ,
                                               o57_descr     ,
                                               o57_finali
					                                from orcfontes 
					                               where o57_codfon  = {$o57_codfon} 
					                                 and o57_anousu  = {$iAnoUsu} ";
			
			$rsInsert = db_query($sSqlInsert);
			
			if ( $rsInsert == false ) {
				throw new Exception(  " Erro ao Adicionar Registro: " . pg_last_error()  );
			}
			
			$oRetorno->sMensagem = "Registro Adicionado com Sucesso.";
				
				
		break;
		
		
		case "getOrcFontes" :

		  $o57_codfon = $oParam->iCodigo;
		  $o57_fonte  = $oParam->iFonte;
		  $aRegistros = Array();
			if ($o57_codfon != '' || $o57_fonte != '' ) {
				
			  $sWhere = " 1 = 1 ";
			  
			  if ($o57_codfon != '' ) {
			  	$sWhere .= " and o57_codfon = {$o57_codfon} ";
			  }
			  
			  if ($o57_fonte != '' ) {
			  	$sWhere .= " and o57_fonte ilike '{$o57_fonte}%' ";
			  }
			  
			  $sSqlPesquisa = " Select * from orcfontes where {$sWhere} order by  o57_anousu, o57_codfon ";
			  $rsPesquisa   = db_query($sSqlPesquisa);
			  
			  for ( $i = 0; $i < pg_numrows($rsPesquisa); $i++  ) {
			  	
			  	$oDados = db_utils::fieldsMemory($rsPesquisa, $i);
			  	
			  	$oRegistros = new stdClass();
			  	$oRegistros->o57_codfon   = $oDados->o57_codfon ;
			  	$oRegistros->o57_anousu   = $oDados->o57_anousu ;
			  	$oRegistros->o57_fonte    = $oDados->o57_fonte  ;
			  	$oRegistros->o57_descr    = $oDados->o57_descr  ;
			  	$oRegistros->o57_finali   = $oDados->o57_finali ; 
			  	$aRegistros[] = $oRegistros;
			  }
			}	

    $oRetorno->aDados = $aRegistros;
			
		break;

	}

	$oRetorno->sMensagem = urlencode($oRetorno->sMensagem);

} catch (Exception $eErro){

	$oRetorno->iStatus   = 2;
	$oRetorno->sMensagem = urlencode($eErro->getMessage());
}

echo $oJson->encode($oRetorno);