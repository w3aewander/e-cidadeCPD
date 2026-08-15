<?php

require_once(modification("fpdf151/pdf.php"));
require_once(modification("libs/db_sql.php"));
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("classes/db_coremp_classe.php"));
require_once(modification("classes/db_pagordemnota_classe.php"));

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

//testa($_GET);
//die("Confere");

    //[dtDataInicial] => 09/08/2021
    //[dtDataFinal] => 09/08/2021
    //[sTipoOrdem] => empenho
    //[lQuebraConta] => t
    //[iListaEmpenho] => 0
$filtro = $_GET["iListaEmpenho"];
$quebra = $_GET["lQuebraConta"];
//0 - Todos
//1 - Só Covid
//2 - Sem Covid


function buscaPorPeriodo($inicio, $fim){  
  $sql = pg_query("SELECT c75_numemp, c53_coddoc, c70_valor, e50_data from conlancamemp inner join conlancam on c70_codlan = c75_codlan left join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord where e50_data between '{$inicio}' and '{$fim}' AND c53_coddoc = 5 order by e50_data");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaDadosEmpenho($seqempenho){
  $sql = pg_query("SELECT * FROM empempenho WHERE e60_numemp = {$seqempenho}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaCoddoc1($seqempenho){
  $sql = pg_query("SELECT c70_valor from conlancamemp inner join conlancam on c70_codlan = c75_codlan left join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord where c75_numemp = {$seqempenho} AND c53_coddoc = 1 order by e50_data;");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["c70_valor"];
}

function retornaNumProcesso($seqempenho){
  $sql1 = pg_query("SELECT e61_autori from empempaut where empempaut.e61_numemp = {$seqempenho}");
  $resultado1 = pg_fetch_all($sql1);
  $autorizacao = $resultado1[0]["e61_autori"];
  $sql2 = pg_query("SELECT e150_numeroprocesso from empautorizaprocesso where e150_empautoriza = {$autorizacao}");
  $resultado2 = pg_fetch_all($sql2);
  return $resultado2[0]["e150_numeroprocesso"];
}

function buscaDadosCgm($cgm){
  $sql = pg_query("SELECT z01_nome, z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function retornaTipo($codigo){
  $sql = pg_query("SELECT pc50_descr FROM pctipocompra WHERE pc50_codcom = {$codigo}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["pc50_descr"];
}


$iAnoUsoSessao      = db_getsession("DB_anousu");
$iInstituicaoSessao = db_getsession("DB_instit");
$oGet               = db_utils::postMemory($_GET);


$dtinicio = implode("-", array_reverse(explode("/", $oGet->dtDataInicial)));
$dtfim = implode("-", array_reverse(explode("/", $oGet->dtDataFinal)));
$partida = buscaPorPeriodo($dtinicio, $dtfim);
$dados = array();
$indice = 0;

$urlbase = $_SERVER["HTTP_REFERER"];
$urlbase = explode("/relcovid.php", $urlbase);

$totalSemCovid = 0;
$totalCovid = 0;
$totalUnico = 0;

foreach ($partida as $linha) {
  $dadoEmpenho = buscaDadosEmpenho($linha["c75_numemp"]);

  if($filtro == 0){
    $empenhoLancado = buscaCoddoc1($linha["c75_numemp"]);
    $dadosCgm = buscaDadosCgm($dadoEmpenho["e60_numcgm"]);
  
    //$dados[$indice]["seqempenho"] = $linha["c75_numemp"]; 

    $dados[$indice]["covid"] = ($dadoEmpenho["covid"] == "sim") ? "SIM" : "NÃO";
    $dados[$indice]["noprocesso"] = retornaNumProcesso($linha["c75_numemp"]);
    $dados[$indice]["nrocontrato"] = $dadoEmpenho["nrocontrato"];
    $dados[$indice]["nroempenho"] = $dadoEmpenho["e60_codemp"] ."/". $dadoEmpenho["e60_anousu"];
    $dados[$indice]["razaosocial"] = $dadosCgm["z01_nome"];
    $dados[$indice]["cnpj"] = $dadosCgm["z01_cgccpf"];
    $dados[$indice]["objeto"] = $dadoEmpenho["e60_resumo"];
    $dados[$indice]["tipo"] = retornaTipo($dadoEmpenho["e60_codcom"]);
    
    $dados[$indice]["valorempenho"] = number_format($empenhoLancado, 2, ',', '.');
    $dados[$indice]["valorpago"] = number_format($linha["c70_valor"], 2, ',', '.');
    //$dados[$indice]["data"] = implode("/", array_reverse(explode("-", $linha["e50_data"])));
    if($dadoEmpenho["covid"] == "sim"){
      $totalCovid += (float)$linha["c70_valor"];
    }else{
      $totalSemCovid += (float)$linha["c70_valor"];
    }
    


    if($dadoEmpenho["vigencia"]){
      $dados[$indice]["iniciocontrato"] = substr($dadoEmpenho["vigencia"], 0, 2) . "/" . substr($dadoEmpenho["vigencia"], 2, 2) . "/" . substr($dadoEmpenho["vigencia"], 4, 4);
      $dados[$indice]["fimcontrato"] = substr($dadoEmpenho["vigencia"], 8, 2) . "/" . substr($dadoEmpenho["vigencia"], 10, 2) . "/" . substr($dadoEmpenho["vigencia"], 12, 4);
    } else{
      $dados[$indice]["iniciocontrato"] = "";
      $dados[$indice]["fimcontrato"] = "";
    }
  
    if($dadoEmpenho["upload"]){
      $dados[$indice]["download"] =  $urlbase[0] ."/tmp/" . $dadoEmpenho["upload"];  
    } else {
      $dados[$indice]["download"] =  "";
    }
  
    $indice++;
  }elseif($filtro == 1){
    //Só covid
    if($dadoEmpenho["covid"] == "sim"){
      $empenhoLancado = buscaCoddoc1($linha["c75_numemp"]);
      $dadosCgm = buscaDadosCgm($dadoEmpenho["e60_numcgm"]);
      

      $dados[$indice]["covid"] = ($dadoEmpenho["covid"] == "sim") ? "SIM" : "NÃO";
      $dados[$indice]["noprocesso"] = retornaNumProcesso($linha["c75_numemp"]);
      $dados[$indice]["nrocontrato"] = $dadoEmpenho["nrocontrato"];
      $dados[$indice]["nroempenho"] = $dadoEmpenho["e60_codemp"] ."/". $dadoEmpenho["e60_anousu"];
      $dados[$indice]["razaosocial"] = $dadosCgm["z01_nome"];
      $dados[$indice]["cnpj"] = $dadosCgm["z01_cgccpf"];
      $dados[$indice]["objeto"] = $dadoEmpenho["e60_resumo"];
      $dados[$indice]["tipo"] = retornaTipo($dadoEmpenho["e60_codcom"]);
    
      $dados[$indice]["valorempenho"] = number_format($empenhoLancado, 2, ',', '.');
      $dados[$indice]["valorpago"] = number_format($linha["c70_valor"], 2, ',', '.');   
      $totalUnico += (float)$linha["c70_valor"];


      if($dadoEmpenho["vigencia"]){
        $dados[$indice]["iniciocontrato"] = substr($dadoEmpenho["vigencia"], 0, 2) . "/" . substr($dadoEmpenho["vigencia"], 2, 2) . "/" . substr($dadoEmpenho["vigencia"], 4, 4);
        $dados[$indice]["fimcontrato"] = substr($dadoEmpenho["vigencia"], 8, 2) . "/" . substr($dadoEmpenho["vigencia"], 10, 2) . "/" . substr($dadoEmpenho["vigencia"], 12, 4);
      } else{
        $dados[$indice]["iniciocontrato"] = "";
        $dados[$indice]["fimcontrato"] = "";
      }
  
      if($dadoEmpenho["upload"]){
        $dados[$indice]["download"] =  $urlbase[0] ."/tmp/" . $dadoEmpenho["upload"];  
      } else {
        $dados[$indice]["download"] =  "";
      }
  
      $indice++;  
    }
    

  }elseif($filtro == 2){
    //Sem COVID
    if($dadoEmpenho["covid"] != "sim"){
      $empenhoLancado = buscaCoddoc1($linha["c75_numemp"]);
      $dadosCgm = buscaDadosCgm($dadoEmpenho["e60_numcgm"]);
      

      $dados[$indice]["covid"] = ($dadoEmpenho["covid"] == "sim") ? "SIM" : "NÃO";
      $dados[$indice]["noprocesso"] = retornaNumProcesso($linha["c75_numemp"]);
      $dados[$indice]["nrocontrato"] = $dadoEmpenho["nrocontrato"];
      $dados[$indice]["nroempenho"] = $dadoEmpenho["e60_codemp"] ."/". $dadoEmpenho["e60_anousu"];
      $dados[$indice]["razaosocial"] = $dadosCgm["z01_nome"];
      $dados[$indice]["cnpj"] = $dadosCgm["z01_cgccpf"];
      $dados[$indice]["objeto"] = $dadoEmpenho["e60_resumo"];
      $dados[$indice]["tipo"] = retornaTipo($dadoEmpenho["e60_codcom"]);
    
      $dados[$indice]["valorempenho"] = number_format($empenhoLancado, 2, ',', '.');
      $dados[$indice]["valorpago"] = number_format($linha["c70_valor"], 2, ',', '.');
      $totalUnico += (float)$linha["c70_valor"];


      if($dadoEmpenho["vigencia"]){
        $dados[$indice]["iniciocontrato"] = substr($dadoEmpenho["vigencia"], 0, 2) . "/" . substr($dadoEmpenho["vigencia"], 2, 2) . "/" . substr($dadoEmpenho["vigencia"], 4, 4);
        $dados[$indice]["fimcontrato"] = substr($dadoEmpenho["vigencia"], 8, 2) . "/" . substr($dadoEmpenho["vigencia"], 10, 2) . "/" . substr($dadoEmpenho["vigencia"], 12, 4);
      } else{
        $dados[$indice]["iniciocontrato"] = "";
        $dados[$indice]["fimcontrato"] = "";
      }
  
      if($dadoEmpenho["upload"]){
        $dados[$indice]["download"] =  $urlbase[0] ."/tmp/" . $dadoEmpenho["upload"];  
      } else {
        $dados[$indice]["download"] =  "";
      }
  
      $indice++;  
    }
  }//if filtro  
}

$totalCovid = number_format($totalCovid, 2, ',', '.');
$totalSemCovid = number_format($totalSemCovid, 2, ',', '.');
$totalUnico = number_format($totalUnico, 2, ',', '.');


if(($quebra == "t")  && ($filtro == 0)){
  $dadoscomcovid = array();
  $dadossemcovid = array();

  foreach ($dados as $separa){
    if($separa["covid"] == "NÃO"){
      array_push($dadossemcovid, $separa);
    }else{
      array_push($dadoscomcovid, $separa);
    }  
  }
  //testa($dadoscomcovid);
  $dados = array_merge($dadoscomcovid, $dadossemcovid);
  //testa($dados);
  
  //die("Confere");
}



//die("Mostra relatório");


$iAltura              = 4;
$lTroca               = true;
$nValorSoma           = 0;
$nValorTotalBanco     = 0;
$nValorTotalRelatorio = 0;
$iTotalRegistros      = 0;
$iTotalRegistroGeral  = 0;

$head1 = "RELATÓRIO COVID-19";

$oPdf = new PDF();
$oPdf->Open();
$oPdf->AliasNbPages();
$oPdf->SetAutoPageBreak(false);
$oPdf->setfillcolor(235);
$sTipoEmpenho = $aDadosImprimir[0]->tipo;
$iCodigoBanco = $aDadosImprimir[0]->k13_conta;
$iQuantBanco  = 0;


$guardaultimo = 0;
$cc = 0;

//testa($dados); 


foreach ($dados as $linha) {
  if ( $oPdf->gety() > $oPdf->h - 30 || $lTroca) {
    imprimeCabecalho($oPdf, $iAltura);
    $lTroca = false;
  }

  if($linha["covid"] == "NÃO"){
    $guardaultimo = 1;
    $cc++;
  }

  if($guardaultimo == 1 && $cc == 1 && $quebra == "t"){
    $oPdf->ln(10);
    $oPdf->cell(120, $iAltura,"Total com Covid: R$ ".$totalCovid,  0, 0, "R", 1);

    $oPdf->addpage("L");
  }

  $oPdf->setfont('arial','',6);
  $oPdf->cell(15,$iAltura,$linha["covid"],0,0,"C",0);
  $oPdf->cell(20,$iAltura,$linha["noprocesso"],0,0,"C",0);
  $oPdf->cell(20,$iAltura,$linha["nrocontrato"],0,0,"C",0);
  $oPdf->cell(20,$iAltura,$linha["nroempenho"],0,0,"C",0);
  

  $iYold = $oPdf->getY();
  $iXold = $oPdf->getx();
  $oPdf->multicell(45,3,$linha["razaosocial"],0,"L",0);
  $iYlinha = $oPdf->gety();
  $oPdf->setxy($iXold+45,$iYold);
  //$oPdf->cell(45,$iAltura,$linha["razaosocial"],0,0,"C",0);
  //$oPdf->cell(50,$iAltura,"Razão Social Aqui",0,0,"C",0);

  $oPdf->cell(22,$iAltura,$linha["cnpj"],0,0,"C",0);    
  $oPdf->cell(25, $iAltura,$linha["tipo"],0,0,"C",0);
  $oPdf->cell(23, $iAltura,$linha["valorempenho"],   0, 0, "L", 0);

  $iYold = $oPdf->getY();
  $iXold = $oPdf->getx();
  $oPdf->multicell(35,3,$linha["objeto"],0,"L",0);
  $iYlinha = $oPdf->gety();
  $oPdf->setxy($iXold+35,$iYold);

  
  $oPdf->cell(20, $iAltura,$linha["valorpago"],  0, 0, "R", 0);
  $oPdf->cell(20, $iAltura,$linha["iniciocontrato"], 0, 0, "R", 0);
  $oPdf->cell(15, $iAltura,$linha["fimcontrato"], 0, 1, "C", 0);
  //$oPdf->cell(30, $iAltura,$linha["download"], 0, 1, "C", 0);
  $oPdf->sety($iYlinha+1);
}//foreach principal

if($quebra == "t"){
  $oPdf->ln(10);
  $oPdf->cell(120, $iAltura,"Total sem Covid: R$ ".$totalSemCovid,  0, 0, "R", 1);
}


if($quebra != "t"){  

$oPdf->SetFont('arial','b',10);
if($filtro == 0){
  $oPdf->ln(10);
  $oPdf->cell(120, $iAltura,"Total sem Covid: R$ ".$totalSemCovid,  0, 0, "R", 1);
  $oPdf->ln(10);
  $oPdf->cell(120, $iAltura,"Total com Covid: R$ ".$totalCovid,  0, 0, "R", 1);
}else{
  $oPdf->ln(10);
  $oPdf->cell(120, $iAltura,"Total R$ ".$totalUnico,  0, 0, "R", 1);
}
}

$oPdf->Output();

function imprimeCabecalho($oPdf, $iAltura) {
  $oPdf->addpage("L");
  $oPdf->SetFont('arial','b',8);
  
  $oPdf->cell(15, $iAltura, "COVID",     1, 0, "C", 1);
  $oPdf->cell(20, $iAltura, "Nº Processo",        1, 0, "C", 1);
  $oPdf->cell(20, $iAltura, "Nº Contrato",        1, 0, "C", 1);
  $oPdf->cell(20, $iAltura, "Empenho", 1, 0, "C", 1);
  $oPdf->cell(45, $iAltura, "Razão Social",         1, 0, "C", 1);

  $oPdf->cell(22, $iAltura, "CNPJ",        1, 0, "C", 1);
  $oPdf->cell(25, $iAltura, "Tipo de Compra",   1, 0, "C", 1);
  $oPdf->cell(23, $iAltura, "Valor Empenho",          1, 0, "C", 1);

  $oPdf->cell(35, $iAltura, "Objeto",           1, 0, "C", 1);
  $oPdf->cell(20, $iAltura, "Valor Pago",     1, 0, "C", 1);
  $oPdf->cell(20, $iAltura, "Dt. Inicial",       1, 0, "C", 1);
  $oPdf->cell(15, $iAltura, "Dt. Final",            1, 1, "C", 1);
  //281 -1
  $iYlinha = $oPdf->getY();

  /*
  $oPdf->addpage("L");
  $oPdf->SetFont('arial','b',8);
  

  $oPdf->cell(10, $iAltura, "COVID",     1, 0, "C", 1);
  $oPdf->cell(18, $iAltura, "Nº Processo",        1, 0, "C", 1);
  $oPdf->cell(18, $iAltura, "Nº Contrato",        1, 0, "C", 1);
  $oPdf->cell(20, $iAltura, "Empenho", 1, 0, "C", 1);
  $oPdf->cell(40, $iAltura, "Razão Social",         1, 0, "C", 1);
  $oPdf->cell(20, $iAltura, "CNPJ",        1, 0, "C", 1);
  $oPdf->cell(30, $iAltura, "Objeto",           1, 0, "C", 1);
  $oPdf->cell(25, $iAltura, "Tipo de Compra",   1, 0, "C", 1);
  $oPdf->cell(10, $iAltura, "Valor Empenho",          1, 0, "C", 1);
  $oPdf->cell(10, $iAltura, "Valor Pago",     1, 0, "C", 1);
  $oPdf->cell(15, $iAltura, "Dt. Inicial",       1, 0, "C", 1);
  $oPdf->cell(15, $iAltura, "Dt. Final",            1, 1, "C", 1);
  //$oPdf->cell(30, $iAltura, "Download Contrato",            1, 1, "C", 1);
  
  $iYlinha = $oPdf->getY();
  */
}



//===============================================================================================
//===============================================================================================
//===============================================================================================
//===============================================================================================
//===============================================================================================


/*

$dtDataInicialBanco = implode("-", array_reverse(explode("/", $oGet->dtDataInicial)));
$dtDataFinalBanco   = implode("-", array_reverse(explode("/", $oGet->dtDataFinal)));
$oGet->lQuebraConta == "t" ? $oGet->lQuebraConta = true : $oGet->lQuebraConta = false;

$aOrderBy    = array();

$aWhere      = array();
$aWhereConta = array();

if ($oGet->sTipoOrdem == "empenho") {
	if ($oGet->lQuebraConta) {
		$aOrderBy[] = "tipo, k13_conta, k12_data, e60_codemp ";
	} else {

		$aOrderBy[] = "tipo, k12_data, e60_codemp ";
	}
} else {
	if ($oGet->lQuebraConta) {
		$aOrderBy[] = "tipo, k13_conta, k12_data, k12_autent ";
	} else {

		$aOrderBy[] = "tipo, k12_data, k12_autent";
	}
}

$sWhereContaPagadora = "";
if (!empty($oGet->iContaPagadora)) {
  $sWhereContaPagadora = "k13_conta = $oGet->iContaPagadora";
  $aWhere[]            = $sWhereContaPagadora;
}



 //Validar Datas
 
if ( !empty($oGet->dtDataInicial) && !empty($oGet->dtDataFinal) ) {
  
  $aWhere[] = "coremp.k12_data between '{$dtDataInicialBanco}' and '{$dtDataFinalBanco}'";
  $head5    = "Ordem de {$oGet->dtDataInicial} até {$oGet->dtDataFinal}";
} else if (!empty($oGet->dtDataInicial)) {
  
  $aWhere[] = "coremp.k12_data >= '{$dtDataInicialBanco}'";
  $head5    = "Ordem a partir de: {$oGet->dtDataInicial}";
} else if (!empty($oGet->dtDataFinal)) {
  
  $aWhere[] = "coremp.k12_data <= '{$dtDataFinalBanco}'";
  $head5    = "Ordem até: {$oGet->dtDataInicial}";
} else {
  
  $sSqlBuscaDataEmp   = "  select max(coremp.k12_data) as maior,                                    ";
  $sSqlBuscaDataEmp  .= "         min(coremp.k12_data) as menor                                     ";
  $sSqlBuscaDataEmp  .= "    from coremp                                                            ";
  $sSqlBuscaDataEmp  .= "         inner join corrente   on corrente.k12_id     = coremp.k12_id      ";
  $sSqlBuscaDataEmp  .= "                              and corrente.k12_data   = coremp.k12_data    ";
  $sSqlBuscaDataEmp  .= "                              and corrente.k12_autent = coremp.k12_autent  ";
  $sSqlBuscaDataEmp  .= "                              and k12_instit = {$iInstituicaoSessao}       ";
  $sSqlBuscaDataEmp  .= "         inner join saltes     on saltes.k13_conta = corrente.k12_conta    ";
  $sSqlBuscaDataEmp  .= "   where {$sWhereContaPagadora}                                            ";
  $rsBuscaData        = db_query($sSqlBuscaDataEmp);
  $oDadosData         = db_utils::fieldsMemory($rsBuscaData, 0);
  $dtDataInicialBanco = $oDadosData->maior;
  $dtDataFinalBanco   = $oDadosData->menor;
  $head5 = "Ordem de {$oGet->dtDataInicial} até {$oGet->dtDataFinal}";
}




$sWhereEmpenho = "";
if ($oGet->iListaEmpenho == 0) {
  $sWhereEmpenho = '1 = 1';
} else {
  
  if ($oGet->iListaEmpenho == 1) {
    $sWhereEmpenho = "tipo = 'Emp'";
  } else {
    $sWhereEmpenho = "tipo = 'RP'";
  }
}

if (!empty($oGet->iTipoBaixa) && $oGet->iTipoBaixa == 2) {
  $aWhere[] = "k106_sequencial not in (2, 5)";
} elseif (!empty($oGet->iTipoBaixa) && $oGet->iTipoBaixa == 3) {
  $aWhere[] = "k106_sequencial in (2, 5)";
}

$sImplodeWhere     = implode(" and ", $aWhere);                                                                    
$sImplodeOrderBy   = implode(", ", $aOrderBy);                                                                     
$sSqlBuscaEmpenhos  = "  select *                                                                                  ";
$sSqlBuscaEmpenhos .= "   from ( select coremp.k12_empen,                                                          ";
$sSqlBuscaEmpenhos .= "                e60_numemp,                                                                 ";
$sSqlBuscaEmpenhos .= "                e60_codemp,                                                                 ";
$sSqlBuscaEmpenhos .= "                case when e49_numcgm is null                                                ";
$sSqlBuscaEmpenhos .= "                  then e60_numcgm                                                           ";
$sSqlBuscaEmpenhos .= "                    else e49_numcgm                                                         ";
$sSqlBuscaEmpenhos .= "                  end as e60_numcgm,                                                        ";
$sSqlBuscaEmpenhos .= "                k12_codord as e50_codord,                                                   ";
$sSqlBuscaEmpenhos .= "                case when e49_numcgm is null                                                ";
$sSqlBuscaEmpenhos .= "                  then cgm.z01_nome                                                         ";
$sSqlBuscaEmpenhos .= "                    else cgmordem.z01_nome                                                  ";
$sSqlBuscaEmpenhos .= "                  end as z01_nome,                                                          ";
$sSqlBuscaEmpenhos .= "                k12_valor,                                                                  ";
$sSqlBuscaEmpenhos .= "                k12_cheque,                                                                 ";
$sSqlBuscaEmpenhos .= "                e60_anousu,                                                                 ";
$sSqlBuscaEmpenhos .= "                coremp.k12_autent,                                                          ";
$sSqlBuscaEmpenhos .= "                coremp.k12_data,                                                            ";
$sSqlBuscaEmpenhos .= "                k13_conta,                                                                  ";
$sSqlBuscaEmpenhos .= "                k13_descr,                                                                  ";
$sSqlBuscaEmpenhos .= "                case when e60_anousu < {$iAnoUsoSessao} then 'RP' else 'Emp' end as tipo,   ";
$sSqlBuscaEmpenhos .= "                k106_sequencial                                                             ";
$sSqlBuscaEmpenhos .= "           from coremp                                                                      ";
$sSqlBuscaEmpenhos .= "                inner join empempenho        on e60_numemp          = k12_empen             ";
$sSqlBuscaEmpenhos .= "                                            and e60_instit          = {$iInstituicaoSessao} ";
$sSqlBuscaEmpenhos .= "                inner join orcdotacao        on e60_coddot = o58_coddot and e60_anousu = o58_anousu ";
$sSqlBuscaEmpenhos .= "                inner join pagordem          on e50_codord          = k12_codord            ";
$sSqlBuscaEmpenhos .= "                left  join pagordemconta     on e50_codord          = e49_codord            ";
$sSqlBuscaEmpenhos .= "                inner join corrente          on corrente.k12_id     = coremp.k12_id         ";
$sSqlBuscaEmpenhos .= "                                            and corrente.k12_data   = coremp.k12_data       ";
$sSqlBuscaEmpenhos .= "                                            and corrente.k12_autent = coremp.k12_autent     ";
$sSqlBuscaEmpenhos .= "                inner join cgm               on cgm.z01_numcgm      = e60_numcgm            ";
$sSqlBuscaEmpenhos .= "                left  join cgm cgmordem      on cgmordem.z01_numcgm = e49_numcgm            ";
$sSqlBuscaEmpenhos .= "                inner join saltes            on saltes.k13_conta    = corrente.k12_conta    ";
$sSqlBuscaEmpenhos .= "                left  join corgrupocorrente  on k105_id             = corrente.k12_id       ";
$sSqlBuscaEmpenhos .= "                                            and k105_data           = corrente.k12_data     ";
$sSqlBuscaEmpenhos .= "                                            and k105_autent         = corrente.k12_autent   ";
$sSqlBuscaEmpenhos .= "                left  join corgrupotipo      on k106_sequencial     = k105_corgrupotipo     ";
$sSqlBuscaEmpenhos .= "          where {$sImplodeWhere}                                                            ";
$sSqlBuscaEmpenhos .= "          order by {$sImplodeOrderBy}) as xxx                                               ";
$sSqlBuscaEmpenhos .= " where {$sWhereEmpenho}                                                                     ";
$rsExecutaBuscaEmpenho = db_query($sSqlBuscaEmpenhos);
$iLinhasRetornadasBuscaEmpenho = pg_num_rows($rsExecutaBuscaEmpenho);
if ($iLinhasRetornadasBuscaEmpenho == 0) {
  db_redireciona("db_erros.php?fechar=true&db_erro=Não existem empenhos para o filtro selecionado.");
}

$total = 0;

$aDadosImprimir = array();
for ($iRowBusca = 0; $iRowBusca < $iLinhasRetornadasBuscaEmpenho; $iRowBusca++) {
  
  $oDadoEmpenho = db_utils::fieldsMemory($rsExecutaBuscaEmpenho, $iRowBusca);

  if ( $oDadoEmpenho->tipo == "Emp" ) {
    $total += $oDadoEmpenho->k12_valor;
  }

  if ( $oDadoEmpenho->e50_codord > 0 ) {

    $oDaoPagOrdemNota   = db_utils::getDao('pagordemnota');
    $sSqlBuscaOrdemNota = $oDaoPagOrdemNota->sql_query($oDadoEmpenho->e50_codord,null,'e69_numero');
    $rsBuscaOrdemNota   = $oDaoPagOrdemNota->sql_record($sSqlBuscaOrdemNota);
    $iTotalOrdemNota    = $oDaoPagOrdemNota->numrows;
    $aNotasEncontradas  = array();
  	if( $oDaoPagOrdemNota->numrows > 0 ) {
  	  for ($iRowOrdem = 0; $iRowOrdem < $iTotalOrdemNota; $iRowOrdem++ ) {
    		$iNumeroOrdem        = db_utils::fieldsMemory($rsBuscaOrdemNota, $iRowOrdem)->e69_numero;
    		$aNotasEncontradas[] = $iNumeroOrdem;
  	  }
    } else {
      $aNotasEncontradas[] = "0";
    }
  }
  $oDadoEmpenho->aNotas = $aNotasEncontradas;
  $aDadosImprimir[] = $oDadoEmpenho;
  unset($oDadoEmpenho);

}

$iAltura              = 4;
$lTroca               = true;
$nValorSoma           = 0;
$nValorTotalBanco     = 0;
$nValorTotalRelatorio = 0;
$iTotalRegistros      = 0;
$iTotalRegistroGeral  = 0;

$head1 = "RELATÓRIO COVID-19";

$oPdf = new PDF();
$oPdf->Open();
$oPdf->AliasNbPages();
$oPdf->SetAutoPageBreak(false);
$oPdf->setfillcolor(235);
$sTipoEmpenho = $aDadosImprimir[0]->tipo;
$iCodigoBanco = $aDadosImprimir[0]->k13_conta;
$iQuantBanco  = 0;

foreach ($aDadosImprimir as $iIndice => $oDadoEmpenho) {
  
  if ( $oPdf->gety() > $oPdf->h - 30 || $lTroca) {
    imprimeCabecalho($oPdf, $iAltura);
    $lTroca = false;
  }
  
  if ( $iCodigoBanco != $oDadoEmpenho->k13_conta && $oGet->lQuebraConta ) {
  	$oPdf->setfont('arial','B',9);
  	$oPdf->cell(280, $iAltura, "TOTAL DO BANCO ($iQuantBanco registros):  R$ 
    ".db_formatar($nValorTotalBanco,"f"), "T", 1, "L", 1);
  	$oPdf->ln(5);
  	$nValorTotalBanco = 0;
  	$iCodigoBanco     = $oDadoEmpenho->k13_conta;
    $iQuantBanco      = 0;
  }

  if ( $sTipoEmpenho != $oDadoEmpenho->tipo ) {
  	$oPdf->setfont('arial','B',9);
    $sTxtAlteraPagina = "TOTAL DE REGISTROS [1] :  {$iTotalRegistros}   VALOR TOTAL  :R$ ".db_formatar($nValorSoma,"f");
  	$oPdf->cell(280, $iAltura, $sTxtAlteraPagina, "T", 1, "L", 0);
  	$oPdf->ln(3);
  	$iTotalRegistros = 0;
  	$nValorSoma      = 0;
  	$sTipoEmpenho    = $oDadoEmpenho->tipo;
  }
  if ( $iCodigoBanco != $oDadoEmpenho->k13_conta && $oGet->lQuebraConta ) {
    
  	$oPdf->setfont('arial','B',9);

  	$oPdf->cell(280, $iAltura, "TOTAL DO BANCO  :  R$ ".db_formatar($nValorTotalBanco,"f"), "T", 1, "L", 1);
  	$oPdf->ln(5);
  	$nValorTotalBanco = 0;
  	$nValorSoma       = 0;
  	$iCodigoBanco     = $oDadoEmpenho->k13_conta;
  	$sTipoEmpenho     = $oDadoEmpenho->tipo;
  }
 
  $notas    = "";
  $sepnotas = "";
  $oPdf->setfont('arial','',7);
  $oPdf->cell(20,$iAltura,db_formatar($oDadoEmpenho->k12_data, 'd'),0,0,"C",0);
  $oPdf->cell(15,$iAltura,$oDadoEmpenho->k12_autent,0,0,"C",0);
  $oPdf->cell(15,$iAltura,$oDadoEmpenho->k13_conta,0,0,"C",0);
  $oPdf->cell(40,$iAltura,substr($oDadoEmpenho->k13_descr,0,25),0,0,"L",0);
  $oPdf->cell(18,$iAltura,$oDadoEmpenho->k12_empen,0,0,"C",0);
  $oPdf->cell(15,$iAltura,trim($oDadoEmpenho->e60_codemp).'/'.$oDadoEmpenho->e60_anousu,0,0,"C",0);
  $oPdf->cell(15,$iAltura,$oDadoEmpenho->e50_codord,0,0,"C",0);

  if (count($oDadoEmpenho->aNotas) > 0) {
    $sNotasEncontradas = implode(" - ", $oDadoEmpenho->aNotas);
  }

  $iYold = $oPdf->getY();
  $iXold = $oPdf->getx();
  $oPdf->multicell(28,3,$sNotasEncontradas,0,"L",0);
  $iYlinha = $oPdf->gety();
  $oPdf->setxy($iXold+28,$iYold);
  $oPdf->cell(60, $iAltura, $oDadoEmpenho->z01_nome,   0, 0, "L", 0);
  $oPdf->cell(20, $iAltura, db_formatar($oDadoEmpenho->k12_valor, 'f'),  0, 0, "R", 0);
  $oPdf->cell(20, $iAltura, $oDadoEmpenho->k12_cheque, 0, 0, "R", 0);
  $oPdf->cell(15, $iAltura, $oDadoEmpenho->tipo,       0, 1, "C", 0);
  $oPdf->sety($iYlinha+1);
  
  $nValorSoma           += $oDadoEmpenho->k12_valor+0;
  $nValorTotalBanco     += $oDadoEmpenho->k12_valor+0;
  $nValorTotalRelatorio += $oDadoEmpenho->k12_valor+0;
  $iTotalRegistros++;
  $iTotalRegistroGeral++;
  $iQuantBanco++;
  $sTipoEmpenho = $oDadoEmpenho->tipo;

}

$oPdf->setfont('arial','B',9);
if($oGet->lQuebraConta){
  $oPdf->cell(280,$iAltura, "TOTAL DO BANCO ($iQuantBanco registros):  R$ ".db_formatar($nValorTotalBanco,"f"), "T", 1, "L", 1);

	$oPdf->ln(5);
}

$oPdf->cell(280,$iAltura,'TOTAL DE REGISTROS [2] :  '.$iTotalRegistros."   VALOR TOTAL  :R$ ".db_formatar($nValorSoma,"f"),"T",1,"L",0);
$oPdf->ln(10);

$oPdf->setfont('arial','B',12);
$oPdf->cell(280,$iAltura*2,'TOTAL DE GERAL      :  '.$iTotalRegistroGeral."   VALOR GERAL  :R$ ".db_formatar($nValorTotalRelatorio,"f"),"T",0,"L",0);
$oPdf->Output();

function imprimeCabecalho($oPdf, $iAltura) {
  
  $oPdf->addpage("L");
  $oPdf->SetFont('arial','b',8);
  
  $oPdf->cell(20, $iAltura, "Data Autent",     1, 0, "C", 1);
  $oPdf->cell(15, $iAltura, "Cod.Aut.",        1, 0, "C", 1);
  $oPdf->cell(15, $iAltura, "Cod.Con.",        1, 0, "C", 1);
  $oPdf->cell(40, $iAltura, "Descrição Conta", 1, 0, "C", 1);
  $oPdf->cell(18, $iAltura, "Empenho",         1, 0, "C", 1);
  $oPdf->cell(15, $iAltura, "Cód. Emp",        1, 0, "C", 1);
  $oPdf->cell(15, $iAltura, "Ordem",           1, 0, "C", 1);
  $oPdf->cell(28, $iAltura, "Notas Fiscais",   1, 0, "C", 1);
  $oPdf->cell(60, $iAltura, "Credor",          1, 0, "C", 1);
  $oPdf->cell(20, $iAltura, "Vlr. Autent",     1, 0, "C", 1);
  $oPdf->cell(20, $iAltura, "N° Cheque",       1, 0, "C", 1);
  $oPdf->cell(15, $iAltura, "Tipo",            1, 1, "C", 1);
  
  $iYlinha = $oPdf->getY();
}

*/