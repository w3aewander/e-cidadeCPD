<?php 
//error_reporting(E_ALL); 
//ini_set('display_errors', '1');


require_once("fpdf151/pdf.php");
require_once("libs/db_sql.php");

//require_once("libs/db_stdlib.php");
 
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_utils.php");
require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");
require_once("dbforms/db_classesgenericas.php");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function verifica($bem){
  $sql = pg_query("SELECT t55_codbem FROM bensbaix WHERE t55_codbem = {$bem}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}



$inicial = $_GET["inicio"];
$final = $_GET["fim"];
$tipo = $_GET["tipo"];
$placa = $_GET["placa"];


//$sql = pg_query("SELECT t52_ident, t52_descr, t63_agregarvalor, t63_processoadm, t63_justificativa FROM bens INNER JOIN benscorr ON t52_bem = t63_codbem WHERE t52_instit = ".db_getsession("DB_instit")." AND t63_exercicio BETWEEN {$inicial} and {$final} order by t52_ident::numeric");

if(!empty($placa)){
  $sql = pg_query("SELECT t52_bem, t52_ident, t52_descr, t63_agregarvalor, t63_processoadm, t63_justificativa FROM bens INNER JOIN benscorr ON t52_bem = t63_codbem WHERE t52_instit = ".db_getsession("DB_instit")." AND t63_dataprocessamento BETWEEN '{$inicial}' and '{$final}' AND t52_ident = '{$placa}' order by t52_ident::numeric");  

}else{
  $sql = pg_query("SELECT t52_bem, t52_ident, t52_descr, t63_agregarvalor, t63_processoadm, t63_justificativa FROM bens INNER JOIN benscorr ON t52_bem = t63_codbem WHERE t52_instit = ".db_getsession("DB_instit")." AND t63_dataprocessamento BETWEEN '{$inicial}' and '{$final}' order by t52_ident::numeric");
}

//$sql = pg_query("SELECT t52_bem, t52_ident, t52_descr, t63_agregarvalor, t63_processoadm, t63_justificativa FROM bens INNER JOIN benscorr ON t52_bem = t63_codbem WHERE t52_instit = ".db_getsession("DB_instit")." AND t63_dataprocessamento BETWEEN '{$inicial}' and '{$final}' order by t52_ident::numeric");
if($tipo == "t"){
  $nometipo = "Todos";
}
if($tipo == "b"){  
  $nometipo = "Baixados";  
}
if($tipo == "nb"){  
  $nometipo = "Não Baixados";
}


$resultado = pg_fetch_all($sql);



$pdf = new PDF();
$pdf->Open();
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(false);
$pdf->setfillcolor(235);
$pdf->imprime_rodape = false;
$total          = 0;
$troca          = 1;
$alt            = 4;
$totalclas      = 0;

$total_valor    = 0;
$total_valor_cl = 0;
$total_valor_dv = 0;
$total_valor_dp = 0;

$totaldiv       = 0;
$t64_class_ant  = '';
$t30_codigo_ant = '';
$depto_ant      = "";
$temdiv = false;

$vtotal = 0;

//testa($pdf); die("Confere");
//for ($x = 0; $x<$numrows; $x++) {  
//$pdf->cell(20,$alt,"AGREGAÇÕES PATRIMONIAIS",1,1,"C",1); 
$head1 = "RELATÓRIO DE AGREGAÇÕES PATRIMONIAIS";
$head2 = "Período de {$inicial} a {$final}";
$head3 = "Filtro: {$nometipo}";
if(!empty($placa)){
  $head4 = "Placa {$placa}";
}
//$pdf->cell(180,$alt,"AGREGAÇÕES PATRIMONIAIS",1,1,"C",1); 
foreach ($resultado as $dados) {
  

  
  if ($pdf->gety() > $pdf->h - 30 || $troca != 0 ) {
    $pdf->addpage();
    $pdf->setfont('arial','b',7.5);
    $pdf->cell(187,$alt,"AGREGAÇÕES PATRIMONIAIS",1,1,"C",1); 
    //$pdf->cell(20,$alt,"Código",1,0,"C",1);
    $pdf->cell(12,$alt,"Placa",1,0,"C",1);    
    $pdf->cell(65,$alt,"Descrição",1,0,"C",1);    
    $pdf->cell(20,$alt,"Valor",1,0,"C",1);    
    $pdf->cell(20,$alt,"Processo Adm.",1,0,"C",1);    
    $pdf->cell(70,$alt,"Observação",1,1,"C",1);
    $troca = 0;
  }
  $verifica = verifica($dados["t52_bem"]);
    if($tipo == "b" && !$verifica){
      continue;
    }

    if($tipo == "nb" && $verifica){
      continue;
    }

    $pdf->setfont('arial','',6);
    $current_y = $pdf->GetY();
    $current_x = $pdf->GetX();
    $pdf->MultiCell(12, 4, $dados["t52_ident"], 0, 'L');
    $end_y = $pdf->GetY();

    $current_x = $current_x + 12;
    $pdf->SetXY($current_x, $current_y);
    $pdf->MultiCell(65, 4, $dados["t52_descr"], 0, 'L');
    $end_y = ($pdf->GetY() > $end_y)?$pdf->GetY() : $end_y;

    $current_x = $current_x + 65;
    $pdf->SetXY($current_x, $current_y); 
    $pdf->MultiCell(20, 4, "R$ " . db_formatar($dados["t63_agregarvalor"],"f"), 0, 'L');
    $end_y = ($pdf->GetY() > $end_y)?$pdf->GetY() : $end_y;

    $current_x = $current_x + 20;
    $pdf->SetXY($current_x, $current_y);    
    $pdf->MultiCell(20, 4, $dados["t63_processoadm"], 0, 'L');
    $end_y = ($pdf->GetY() > $end_y)?$pdf->GetY() : $end_y;

    $current_x = $current_x + 20;
    $pdf->SetXY($current_x, $current_y);
    $pdf->MultiCell(70, 4, $dados["t63_justificativa"], 0, 'L');
    $end_y = ($pdf->GetY() > $end_y)?$pdf->GetY() : $end_y;

    //$pdf->cell(187,1,"",0,1,"C",1);
    
    
    $i++;
    $pdf->SetY($end_y);    
  $vtotal += $dados["t63_agregarvalor"];
}



$pdf->setfont('arial','b',8);

    $pdf->cell((110-$cols),$alt,'TOTAL:  '.db_formatar($vtotal,'f'),"T",0,"L",0);
    //$pdf->cell(25,$alt,db_formatar($vtotal,'f'),"T",0,"R",0);

$pdf->Output();







/*
//$head1 = "Relatório de Bens sem Depreciação.";
$oPdf  = new PDF("P");
$oPdf->Open();
$oPdf->AliasNbPages();
$oPdf->SetAutoPageBreak(false);

$iHeigth             = 4;
$iWidth              = 100;
$lPrimeiroLaco       = true;

$vtotal = 0;
//foreach ($oBensSemDepreciacao as $iIndiceBens => $oBens) {
foreach ($resultado as $linha) {

  if ($oPdf->gety() > $oPdf->h - 30 || $lPrimeiroLaco) {

    setHeader($oPdf, $iHeigth);
    $lPrimeiroLaco = false;
  }
  $oPdf->SetFont("arial", "", 7);
  $oPdf->Cell(10,  $iHeigth, $linha["t52_ident"], "TB", 0);
  $oPdf->Cell(80,  $iHeigth, $linha["t52_descr"], "TB", 0);
  $oPdf->Cell(30,  $iHeigth, number_format($linha["t63_agregarvalor"], 2, ',', '.'), "TB", 1);
  $oPdf->Cell(30,  $iHeigth, $linha["t63_processoadm"], "TB", 0);
  $oPdf->Cell(80,  $iHeigth, $linha["t63_justificativa"], "TB", 0);


  //$oPdf->Cell(100, $iHeigth, substr($oBens->t52_descr, 0, 64), "TB", 0);
  //$oPdf->Cell(30,  $iHeigth, $oBens->t41_placa . $oBens->t41_placaseq, "TB", 0);
  //$oPdf->Cell(30,  $iHeigth, $oBens->t64_class, "TB", 1);
  //$oPdf->Cell(30,  $iHeigth, number_format($oBens->t52_valaqu, 2, ',', '.'), "TB", 1);
  $vtotal += $linha["t63_agregarvalor"];;}
$vtotal = number_format($vtotal, 2, ',', '.');

$oPdf->Cell(100,  $iHeigth, "Valor Total: R$ {$vtotal}", "TB", 0);

function setHeader($oPdf, $iHeigth) {

  $oPdf->setfont('arial', 'b', 9);
  $oPdf->AddPage();
  $oPdf->setfillcolor(235);
  $oPdf->Cell(10,  $iHeigth, "Placa", 1, 0, "C", 1);  
  $oPdf->Cell(80, $iHeigth, "Descrição",     1, 0, "C", 1);  
  $oPdf->Cell(30,  $iHeigth, "Valor Agregado", "TBL", 1, "C", 1);
  $oPdf->Cell(30, $iHeigth, "Processo Admin.", 1, 0, "C", 1);  
  $oPdf->Cell(80, $iHeigth, "Observação",     1, 0, "C", 1);  

}


$oPdf->Output();

*/