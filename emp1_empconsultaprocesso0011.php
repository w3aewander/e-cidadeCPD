<?
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

require("libs/db_stdlib.php");
require("libs/db_utils.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("dbforms/db_funcoes.php");
include("classes/db_matordem_classe.php");
require_once("fpdf151/pdf.php");

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


select  e50_codord, 
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
$sSql .=        "                                 order by e50_codord";

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

$head4 = "Consulta por Processo\n";
$sSql0 = "SELECT codigo, nomeinst FROM db_config where codigo = ".db_getsession("DB_instit");
$rsSql0 = pg_query($sSql0);
if(pg_num_rows($rsSql0) > 0){
	db_fieldsmemory($rsSql0, 0);
	$head4 .= "Instituição: $codigo - $nomeinst";
}

$oPdf = new PDF("L");
$oPdf->Open();
$oPdf->AliasNbPages();
$oPdf->SetAutoPageBreak(true);
$oPdf->setfillcolor(235);
$iAlturaLinha = 5;
$oPdf->AddPage();
$oPdf->SetFont('arial', '', 6);

$oPdf->SetX(10);
$oPdf->cell(25, $iAlturaLinha, "Ordem:",        "LTBR", 0, "C", 1);
$oPdf->cell(25, $iAlturaLinha, "Emissão:",       "LTBR", 0, "C", 1);
$oPdf->cell(25, $iAlturaLinha, "Número do Empenho:",   "LTBR", 0, "C", 1);
$oPdf->cell(25, $iAlturaLinha, "Número do Processo:",   "LTBR", 0, "C", 1);
$oPdf->cell(25, $iAlturaLinha, "Exercício:", "LTBR", 0, "C", 1);
$oPdf->cell(25, $iAlturaLinha, "Numcgm:",         "LTBR", 0, "C", 1);
$oPdf->cell(128, $iAlturaLinha, "Nome/Razão Social:",    "LTBR", 1, "C", 1);
$oPdf->SetX(10);
$oPdf->cell(50, $iAlturaLinha, "Nome da Instituição:", "LTBR", 0, "C", 1);
$oPdf->cell(34, $iAlturaLinha, "Número da NF:",     "LTBR", 0, "C", 1);
$oPdf->cell(33, $iAlturaLinha, "Valor:",     "LTBR", 0, "C", 1);
$oPdf->cell(33, $iAlturaLinha, "Anulado:",     "LTBR", 0, "C", 1);
$oPdf->cell(33, $iAlturaLinha, "Pago:",     "LTBR", 0, "C", 1);
$oPdf->cell(29, $iAlturaLinha, "Data Pagamento:",     "LTBR", 0, "C", 1);
$oPdf->cell(33, $iAlturaLinha, "Pago Contabil:",        "LTBR", 0, "C", 1);
$oPdf->cell(33, $iAlturaLinha, "Valor Final da Rentenção:",       "LTBR", 1, "C", 1);

$rsSql = pg_query($sSql);

$totalValor     = 0;
$totalAnulado   = 0;
$totalPago      = 0;
$totalContabil  = 0;
$totalRentencao = 0;

for ($i=0; $i < pg_num_rows($rsSql); $i++) {
	db_fieldsmemory($rsSql, $i); 
        $oPdf->SetX(10);
        $oPdf->cell(25, $iAlturaLinha, $e50_codord, "LT", 0, "L", 0);
        $oPdf->cell(25, $iAlturaLinha, db_formatar($e50_data,'d'),   "T", 0, "L", 0);
        $oPdf->cell(25, $iAlturaLinha, $e60_codemp,   "T", 0, "L", 0);
        $oPdf->cell(25, $iAlturaLinha, $e03_numeroprocesso,   "T", 0, "L", 0);
        $oPdf->cell(25, $iAlturaLinha, $e60_anousu, "T", 0, "L", 0);
        $oPdf->cell(25, $iAlturaLinha, $z01_numcgm,         "T", 0, "L", 0);
        $oPdf->cell(128, $iAlturaLinha, $z01_nome,    "TR", 1, "L", 0);
        $oPdf->SetX(10);
        $oPdf->cell(50, $iAlturaLinha, $nomeinstabrev, "LB", 0, "L", 0);
        $oPdf->cell(34, $iAlturaLinha, $e69_numero,     "B", 0, "L", 0);
        $oPdf->cell(33, $iAlturaLinha, "R$ ".db_formatar($e53_valor,'f'),     "B", 0, "L", 0);
        $oPdf->cell(33, $iAlturaLinha, "R$ ".db_formatar($e53_vlranu,'f'),     "B", 0, "L", 0);
        $oPdf->cell(33, $iAlturaLinha, "R$ ".db_formatar($e53_vlrpag,'f'),     "B", 0, "L", 0);
        $oPdf->cell(29, $iAlturaLinha, db_formatar($dl_data_pagamento,'d'),     "B", 0, "L", 0);
        $oPdf->cell(33, $iAlturaLinha, "R$ ".db_formatar($dl_pago_contabil,'f'),        "B", 0, "L", 0);
        $oPdf->cell(33, $iAlturaLinha, "R$ ".db_formatar($e23_valorretencao,'f'),       "BR", 1, "L", 0);
    
  if($oPdf->getY() > 190){
          $oPdf->AddPage();
          $oPdf->SetX(10);
          $oPdf->cell(25, $iAlturaLinha, "Ordem:",        "LTBR", 0, "C", 1);
          $oPdf->cell(25, $iAlturaLinha, "Emissão:",       "LTBR", 0, "C", 1);
          $oPdf->cell(25, $iAlturaLinha, "Número do Empenho:",   "LTBR", 0, "C", 1);
          $oPdf->cell(25, $iAlturaLinha, "Número do Processo:",   "LTBR", 0, "C", 1);
          $oPdf->cell(25, $iAlturaLinha, "Exercício:", "LTBR", 0, "C", 1);
          $oPdf->cell(25, $iAlturaLinha, "Numcgm:",         "LTBR", 0, "C", 1);
          $oPdf->cell(128, $iAlturaLinha, "Nome/Razão Social:",    "LTBR", 1, "C", 1);
          $oPdf->SetX(10);
          $oPdf->cell(50, $iAlturaLinha, "Nome da Instituição:", "LTBR", 0, "C", 1);
          $oPdf->cell(34, $iAlturaLinha, "Número da NF:",     "LTBR", 0, "C", 1);
          $oPdf->cell(33, $iAlturaLinha, "Valor:",     "LTBR", 0, "C", 1);
          $oPdf->cell(33, $iAlturaLinha, "Anulado:",     "LTBR", 0, "C", 1);
          $oPdf->cell(33, $iAlturaLinha, "Pago:",     "LTBR", 0, "C", 1);
          $oPdf->cell(29, $iAlturaLinha, "Data Pagamento:",     "LTBR", 0, "C", 1);
          $oPdf->cell(33, $iAlturaLinha, "Pago Contabil:",        "LTBR", 0, "C", 1);
          $oPdf->cell(33, $iAlturaLinha, "Valor Final da Rentenção:",       "LTBR", 1, "C", 1);
  }

$totalValor     += $e53_valor;
$totalAnulado   += $e53_vlranu;
$totalPago      += $e53_vlrpag;
$totalContabil  += $dl_pago_contabil;
$totalRentencao += $e23_valorretencao;
}

if($oPdf->getY() > 190) $oPdf->AddPage();
$oPdf->ln();
$oPdf->SetX(10);
$oPdf->cell(84, $iAlturaLinha, "", "TBL", 0, "L", 1);
$oPdf->cell(33, $iAlturaLinha, "Total Valor", "TB", 0, "L", 1);
$oPdf->cell(33, $iAlturaLinha, "Total Anulado", "TB", 0, "L", 1);
$oPdf->cell(33, $iAlturaLinha, "Total Pago", "TB", 0, "L", 1);
$oPdf->cell(29, $iAlturaLinha, "", "TB", 0, "L", 1);
$oPdf->cell(33, $iAlturaLinha, "Total Pago Contabil", "TB", 0, "L", 1);
$oPdf->cell(33, $iAlturaLinha, "Total Valor Final da Rentenção", "TBR", 1, "L", 1);
$oPdf->SetX(10);
$oPdf->cell(84, $iAlturaLinha, "",     "TBL", 0, "R", 1);
$oPdf->cell(33, $iAlturaLinha, "R$ ".db_formatar($totalValor,'f'),     "TB", 0, "L", 1);
$oPdf->cell(33, $iAlturaLinha, "R$ ".db_formatar($totalAnulado,'f'),     "TB", 0, "L", 1);
$oPdf->cell(33, $iAlturaLinha, "R$ ".db_formatar($totalPago,'f'),     "TB", 0, "L", 1);
$oPdf->cell(29, $iAlturaLinha, "",     "TB", 0, "L", 1);
$oPdf->cell(33, $iAlturaLinha, "R$ ".db_formatar($totalContabil,'f'),        "TB", 0, "L", 1);
$oPdf->cell(33, $iAlturaLinha, "R$ ".db_formatar($totalRentencao,'f'),       "TBR", 1, "L", 1);


$oPdf->Output();
?>
