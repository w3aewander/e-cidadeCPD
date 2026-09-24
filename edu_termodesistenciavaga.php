<?php
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

require_once(modification("fpdf151/pdfwebseller.php"));
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("std/db_stdClass.php"));

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function buscaDadosAluno($codaluno){
  $sql = pg_query("SELECT * FROM aluno WHERE ed47_i_codigo = {$codaluno}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaResponsavel($codaluno){
  $sql = pg_query("SELECT ed47_v_nome, ed47_v_mae, ed47_v_pai, ed47_c_nomeresp FROM aluno WHERE ed47_i_codigo = {$codaluno}");
  $resultado = pg_fetch_all($sql);

  if($resultado[0]["ed47_v_mae"]){
    return $resultado[0]["ed47_v_mae"];
  }

  if($resultado[0]["ed47_v_pai"]){
    return $resultado[0]["ed47_v_pai"];
  }

  if($resultado[0]["ed47_c_nomeresp"]){
    return $resultado[0]["ed47_c_nomeresp"];
  }

  return "Sem responsável";
}

function ajustaNome($nome){  
  $nomemin = strtolower($nome);
  $novonome = explode(" ", $nomemin);
  $nome = "";
  foreach ($novonome as $palavra){
    if(strlen($palavra) > 2){
      $nome .= ucfirst($palavra) . " ";
    }else{
      $nome .= $palavra . " ";
    }
  }
  return trim($nome);
}

$oJson       = new services_json();
$oParametros = new stdClass();
$oGet        = db_utils::postMemory($_GET);

$dadosaluno = buscaDadosAluno($_GET['aluno']);
$nomealuno = trim(ajustaNome($dadosaluno["ed47_v_nome"]));
$responsavel = trim(ajustaNome(buscaResponsavel($_GET['aluno'])));
$etapa = utf8_decode($_GET["etapa"]);
$assinatura = explode("-", $_GET["assinatura"]);
$nomeass = $assinatura[0];
$cargoass = $assinatura[1];
$anomatricula = date("Y");
$obs = utf8_decode($_GET["obs"]);

$oPdf = new PDF();
$oPdf->AliasNbPages();
$oPdf->setFillColor(220);
$oPdf->Open();
$oPdf->SetAutoPageBreak(false, 10);
$oPdf->imprime_rodape = false;
$oPdf->addpage("P");  
$sTexto = "Eu, {$responsavel}, responsável pelo(a) aluno(a) {$nomealuno}, desisto da vaga para cursar o {$etapa} no ano de {$anomatricula}.";  
$oPdf->setfont('arial','b',20);
$oPdf->SetY($oPdf->getY() + 10);
$oPdf->Cell(192, 5, "Termo de Desistência de Vaga", 0, 1, "C");
$oPdf->Ln(5 * 2);
$oPdf->Ln(6);

$oPdf->setfont('arial','',14);
$oPdf->setXY(16, $oPdf->GetY());
$oPdf->multicell(180, 5 + 4, $sTexto, 0, "J", 0, 0);
$oPdf->Ln(5 * 2);

$oPdf->setfont('arial','',14);
$oPdf->setXY(16, $oPdf->GetY());
$oPdf->multicell(180, 5 + 4,"Observações: " . $obs, 0, "J", 0, 0);
$oPdf->Ln(5 * 2);

$oDiaAtual  = new DBDate(date("Y-m-d"));
$sMunicipio = "VOLTA REDONDA";
$DiaExtenso  = " {$sMunicipio}, " . $oDiaAtual->getDia() . " de " . trim(DBDate::getMesExtenso((int)$oDiaAtual->getMes()));
$DiaExtenso .= " de " . $oDiaAtual->getAno();

$oPdf->Cell("192", 5 + 6, $DiaExtenso, 0, 1, "C");
$oPdf->ln(5 * 3);
$oPdf->Line(50, $oPdf->GetY(), 152, $oPdf->GetY());
$oPdf->ln(5);  
$oPdf->setfont('arial','',12);
$oPdf->Cell("192", 5, $nomeass, 0, 1, "C");
$oPdf->Cell("192", 5 + 4, $cargoass,   0, 1, "C");
$oPdf->Output();