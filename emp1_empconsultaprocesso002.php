<?
/*
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

require("libs/db_stdlib.php");
require("libs/db_utils.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("dbforms/db_funcoes.php");
include("classes/db_matordem_classe.php");

$oGet = db_utils::postMemory($HTTP_GET_VARS);
//var_dump($oGet);exit;

$sWhere = "";

if(trim($oGet->e60_codemp)!=''){
	
	$iAnoUsu = db_getsession("DB_anousu");
	
	$aCodEmp = explode('/',$oGet->e60_codemp);
	
	if (count($aCodEmp) == 2) {
	 $iAnoUsu    = $aCodEmp[1];
	 $e60_codemp = $aCodEmp[0];   	   
	}
	
	if($sWhere == ""){
    $sWhere .= " e60_codemp='".$e60_codemp."' and e60_anousu = ".$iAnoUsu;
  }else{
    $sWhere .= " and e60_codemp='".$e60_codemp."' and e60_anousu = ".$iAnoUsu;
  }
}
if(trim($oGet->z01_numcgm)!=''){
  if($sWhere == ""){
    $sWhere .= " z01_numcgm=".$oGet->z01_numcgm;
  }else{
    $sWhere .= " and z01_numcgm=".$oGet->z01_numcgm;
  }
}

if ( isset($oGet->e03_numeroprocesso) && !empty($oGet->e03_numeroprocesso) ) {
  
  $sProcesso = addslashes($oGet->e03_numeroprocesso);
  if($sWhere == ""){
    $sWhere .= " e03_numeroprocesso = '{$sProcesso}' ";
  }else{
    
    $sWhere .= " and e03_numeroprocesso = '{$sProcesso}' ";
  }
  
}

if ( isset($oGet->dtini) && !empty($oGet->dtini) && isset($oGet->dtfim) && !empty($oGet->dtfim) ) {
  
  if(trim($oGet->dtini) != '' && trim($oGet->dtfim) != '' ){
    if($sWhere == ""){
      $sWhere .= " e50_data between '".$oGet->dtini."' and '".$oGet->dtfim."'";
    }else{  
      $sWhere .= " and e50_data between '".$oGet->dtini."' and '".$oGet->dtfim."'";
    }
  }else if(trim($oGet->dtini) != ''){
    if($sWhere == ""){
      $sWhere .= " e50_data = '".$oGet->dtini."' ";
    }else{
      $sWhere .= " and e50_data = '".$oGet->dtini."' ";
    }
  }else if(trim($oGet->dtfim) != ''){
    if($sWhere == ""){
      $sWhere .= " e50_data = '".$oGet->dtfim."' ";
    }else{
      $sWhere .= " and e50_data = '".$oGet->dtfim."' ";
    }
  }
}

      $sWhere .= " and e60_instit = " . db_getsession("DB_instit");

$sSql = "


select 	e50_codord, 
	e50_data, 
	e60_codemp, 
	e60_anousu, 
	z01_numcgm, 
	z01_nome, 
	e60_instit || '-' || nomeinstabrev as nomeinstabrev, 
	e03_numeroprocesso, 
        ( select array_to_string(array_accum(e69_numero),', ') from pagordemnota a inner join pagordem b on b.e50_codord = a.e71_codord inner join empnota on empnota.e69_codnota = a.e71_codnota where b.e50_codord = pagordem.e50_codord ) as e69_numero,
	e53_valor, 
	e53_vlranu, 
	e53_vlrpag,
	( select array_to_string(array_accum(distinct c70_data),', ') from conlancamord inner join conlancam on c70_codlan = c80_codlan inner join conlancamdoc on c71_codlan = c80_codlan inner join conhistdoc on c71_coddoc = c53_coddoc where c80_codord = e50_codord and c53_tipo in (30,31) ) as dl_data_pagamento,
	( select sum(case when c53_tipo = 30 then c70_valor else c70_valor*-1 end) from conlancamord inner join conlancam on c70_codlan = c80_codlan inner join conlancamdoc on c71_codlan = c80_codlan inner join conhistdoc on c71_coddoc = c53_coddoc where c80_codord = e50_codord and c53_tipo in (30,31) ) as dl_pago_contabil,
	( select sum( e23_valorretencao ) from retencaopagordem inner join retencaoreceitas on e20_sequencial = e23_retencaopagordem where e50_codord = e20_pagordem ) as e23_valorretencao

from pagordemprocesso 
inner join pagordem on e50_codord = e03_pagordem 
inner join empempenho on e50_numemp = e60_numemp 
inner join db_config on codigo = e60_instit 
inner join cgm on z01_numcgm = e60_numcgm 
inner join pagordemele on e53_codord = e50_codord 

                    ";

if($sWhere != ""){
	$sWhere = " where ".$sWhere; 
}
$sSql .= $sWhere;
$sSql .=	"				  order by e50_codord"; 

//where e03_numeroprocesso = '1762/2014';
//where e03_numeroprocesso = '13807/2014';

//die($sSql);

if ($situacao == 1) {
  $sSql = "select * from ( $sSql ) as x where e53_valor = e53_vlrpag and e53_vlranu = 0";
} elseif ( $situacao == 2) {
  $sSql = "select * from ( $sSql ) as x where e53_valor <> e53_vlrpag and e53_vlranu = 0";
} elseif ( $situacao == 3) {
  $sSql = "select * from ( $sSql ) as x where e53_vlranu > 0";
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="estilos.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table height="100%" border="0"  align="center" cellspacing="0" bgcolor="#CCCCCC">
  <tr>
    <td align="center" valign="top">
    <? 
        $funcao_js = $oGet->funcao_js;
        db_lovrot($sSql,15,"()","",$funcao_js,"","NoMe",array (),false);
    ?>
    </td>
   </tr>
</table>
</body>
</html>
