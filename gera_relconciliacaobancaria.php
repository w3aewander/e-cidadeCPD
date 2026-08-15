<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2009  DBselller Servicos de Informatica             
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


include("libs/db_liborcamento.php");
include("fpdf151/pdf.php");
require("libs/db_utils.php");
include("classes/db_orctiporec_classe.php");
include("libs/db_sql.php");
function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

if(isset($_GET["p"]) && $_GET["p"] == "todos"){  
  $sql = pg_query("SELECT * FROM conferecnab240 ORDER BY id DESC");
  $vigencia = "Todos";
}

if(isset($_GET["p"]) && $_GET["p"] != "todos"){
  $periodo = $_GET["p"];
  $sql = pg_query("SELECT * FROM conferecnab240 WHERE ano = {$periodo} ORDER BY id");
  $vigencia = $periodo;
}

if(isset($_GET["pi"])){
  $pi = implode("-", array_reverse(explode("/", $_GET["pi"])));
  $pf = implode("-", array_reverse(explode("/", $_GET["pf"])));
  $sql = pg_query("SELECT * FROM conferecnab240 WHERE data BETWEEN '{$pi}' AND '{$pf}' ORDER BY id");
  $vigencia = $_GET["pi"] ." a ". $_GET["pf"];
}

$dados = pg_fetch_all($sql);



$pdf = new PDF(); 
$pdf->Open(); 
$pdf->AliasNbPages(); 





$head3 = "Arquivos Processados (LOG)";
$head4 = "Período: " . $vigencia;


$pdf->ln(2);
$pdf->AddPage(); 
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(220);
$pdf->SetFont('Arial','B',9);
  
$pdf->Cell(60,6,"ARQUIVO",1,0,"C",1);
$pdf->Cell(60,6,"DATA PROCESSAMENTO",1,0,"C",1);
$pdf->Cell(60,6,"USUÁRIO",1,1,"C",1);
  
$pdf->SetFont('Arial','B',9);

foreach ($dados as $linha){
  $dp = implode("/", array_reverse(explode("-", $linha["data"])));
  if ($pdf->gety() > $pdf->h - 30 ){
      $pdf->addpage();
      $pdf->SetFont('Arial','B',9);
      $pdf->Cell(60,6,"ARQUIVO",1,0,"C",1);
      $pdf->Cell(60,6,"DATA PROCESSAMENTO",1,0,"C",1);
      $pdf->Cell(60,6,"USUÁRIO",1,1,"C",1);
    }
    $pdf->setfont('arial','',7);
    $pdf->cell(60,4,$linha["nome"],1,0,"C",0);
    $pdf->cell(60,4,$dp,1,0,"C",0);
    $pdf->cell(60,4,$linha["login"],1,1,"C",0);
  }





$pdf->ln(5);





$pdf->Output();

?>