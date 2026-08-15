<?php
 

include("fpdf151/pdf.php");
include("libs/db_sql.php");
include("classes/db_db_config_classe.php");
include ("libs/db_utils.php");


$cldbconfig = new cl_db_config();
parse_str($HTTP_SERVER_VARS['QUERY_STRING']);

function buscaNomeOrdenador($ano){
  $sql = pg_query("SELECT idusuario FROM registrosprevias WHERE acaousuario = 'tudo' AND tipo = 'receita' AND idtipo = {$ano}");
  $resultado1 = pg_fetch_all($sql);
  $idlogin = $resultado[0]["idusuario"];

  $sql2 = pg_query("SELECT nome FROM db_usuarios WHERE id_usuario = {$idlogin}");
  $resultado2 = pg_fetch_all($sql2);
  return $resultado2[0]["nome"];
}

$instituicao = db_getsession("DB_instit");
$anocorrente = db_getsession("DB_anousu");


$x = substr($_GET["inst"], 0, -1);


if($_GET["inst"]){
  //$i1 = explode(",", $_GET["inst"]);
  $i1 = explode(",", $x);



if($_GET["tipo"] == "csv"){
  $arquivo = fopen("tmp/previareceita.csv", "w");
  fwrite($arquivo, "Reduzido;Estrutural;Recurso;Valor Previsto;Característica Peculiar"."\n");

  $guardacsv = array();
  foreach ($i1 as $instt) {
    $result = pg_query("SELECT * FROM previareceitas WHERE o70_instit = '{$instt}' AND o70_anousu ='{$anocorrente}' ");
    $dados = db_utils::getColectionByRecord($result);
    
    foreach ($dados as $linha){      
      $valor = number_format($linha->o70_valor,2,',','.');
      $linhacsv = "{$linha->o70_codrec};{$linha->o50_estrutreceita} - {$linha->o57_descr};{$linha->o70_codigo} - {$linha->o15_descr};{$valor};{$linha->o70_concarpeculiar} - {$linha->c58_descr}";
      array_push($guardacsv, $linhacsv);  
    }
  }
  

foreach ($guardacsv as $linha) {
  fwrite($arquivo, $linha . "\n");
}
fclose($arquivo);


$file_url = 'tmp/previareceita.csv';
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
readfile($file_url); 
exit();
}//Fim CSV

//if($anocorrente > 2022){
  setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
  date_default_timezone_set('America/Sao_Paulo');
  $datageracao = strftime('%d de %B de %Y', strtotime('today'));
  $nomeordenador = buscaNomeOrdenador($anocorrente);
//}

$pdf = new PDF(); 
$pdf->Open(); 
$pdf->AliasNbPages(); 
$pdf->setfillcolor(235);
$pdf->setfont('arial','b',8);
$alt     = 4;
$pdf->imprime_rodape = false;
$totalgeral = 0;
$z = 1;
foreach ($i1 as $instt) {
  $head3  = "RELATÓRIO ORÇAMENTÁRIO DA RECEITA - {$anocorrente}";
  $head4  = "Instituição - {$instt}";
  
  $result = pg_query("SELECT * FROM previareceitas WHERE o70_instit = '{$instt}' AND o70_anousu ='{$anocorrente}' ");
  $dados = db_utils::getColectionByRecord($result);


  if($z == 1){
    //$head6 = "Recurso - " . $dados[0]->o70_codigo;
    $head6 = "";
  } else {
    $head6 = "";
  }
  
  
  $z++;
  
  $pdf->AddPage("P");
  $pdf->setfont('arial','b',9);
  $pdf->cell(60,$alt,"Estrutural da Receita",0,0,"C",1);
  $pdf->cell(20,$alt,"Recurso",0,0,"C",1);
  $pdf->cell(30,$alt,"Valor",0,1,"C",1);

  $valor = 0;  

  foreach ($dados as $linha){
    
    if($instt == 1){
      
        $pdf->cell(60,$alt,$linha->o50_estrutreceita,0,0,"C",1);
        $pdf->cell(20,$alt,$linha->o70_codigo,0,0,"C",1);    
        $pdf->cell(30,$alt,"R$ " . number_format($linha->o70_valor,2,',','.'),0,1,"C",1);
        $valor += $linha->o70_valor;
        $totalorgao += $linha->o70_valor;
        
        $pdf->setfont('arial','b',9);
        //$pdf->cell(60,$alt,"Estrutural da Receita",0,0,"C",1);
        //$pdf->cell(20,$alt,"Recurso",0,0,"C",1);
        //$pdf->cell(30,$alt,"Valor",0,1,"C",1);
        
      //}
        //$pdf->setfont('arial','b',7);
        //$pdf->cell(170,4,"Total Recurso R$",0,0,"R",0);
        //$pdf->setfont('arial','',7);
        //$pdf->cell(20,4,number_format($totalorgao,2,',','.'),0,1,"L",0);
        //$totalorgao = 0;


    } else {

    $pdf->cell(60,$alt,$linha->o50_estrutreceita,0,0,"C",1);
    $pdf->cell(20,$alt,$linha->o70_codigo,0,0,"C",1);    
    $pdf->cell(30,$alt,"R$ " . number_format($linha->o70_valor,2,',','.'),0,1,"C",1);
    $valor += $linha->o70_valor;
    }
  }
      if($instt == 1){
        $pdf->setfont('arial','b',7);
        $pdf->cell(170,4,"Total Recursos R$",0,0,"R",0);
        $pdf->setfont('arial','',7);
        $pdf->cell(20,4,number_format($totalorgao,2,',','.'),0,1,"L",0);
        $totalorgao = 0;
      }

  $pdf->setfont('arial','b',7);
  $pdf->cell(170,4,"Total Instituição R$",0,0,"R",0);
  $pdf->setfont('arial','',7);
  $pdf->cell(20,4,number_format($valor,2,',','.'),0,1,"L",0);
  $totalgeral += $valor;
}

$pdf->Ln();
$pdf->setfont('arial','b',7);
$pdf->cell(170,4,"Total Geral R$",0,0,"R",0);
$pdf->setfont('arial','',7);
$pdf->cell(20,4,number_format($totalgeral,2,',','.'),0,1,"L",0);

//if($anocorrente > 2022){
  $pdf->Ln(15);
  $pdf->setfont('arial','',10);
  $pdf->cell(170,4,"Volta Redonda, {$datageracao}",0,0,"L",0);

  $pdf->Ln(25);
  $pdf->setfont('arial','b',10);
  $pdf->cell(100,4,"Ordenador da Despesa",0,0,"C",0);
  $pdf->cell(100,4,"Responsável pela digitação dos dados",0,1,"C",0);
  $pdf->setfont('arial','',9);
  $pdf->cell(100,4,"Assinatura e Carimbo",0,0,"C",0);  
  $pdf->cell(100,4,"Assinatura e Carimbo",0,0,"C",0);  
//}

 

$pdf->Output();

exit();




  
} else {
  setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
  date_default_timezone_set('America/Sao_Paulo');
  $datageracao = strftime('%d de %B de %Y', strtotime('today'));
  $nomeordenador = buscaNomeOrdenador($anocorrente);

  $instituicao = db_getsession("DB_instit");
  $head3  = "RELATÓRIO ORÇAMENTÁRIO DA RECEITA - {$anocorrente}";
  $head4  = "Instituição - {$instituicao}";  
  $result = pg_query("SELECT * FROM previareceitas WHERE o70_instit = '{$instituicao}' AND o70_anousu ='{$anocorrente}'");

  $dados = db_utils::getColectionByRecord($result);



$pdf = new PDF(); 
$pdf->Open(); 
$pdf->AliasNbPages(); 
$pdf->setfillcolor(235);
$pdf->setfont('arial','b',8);
$alt     = 4;
$pdf->imprime_rodape = false;

    $pdf->AddPage("P");
    $pdf->setfont('arial','b',9);
    $pdf->cell(60,$alt,"Estrutural da Receita",0,0,"C",1);
    $pdf->cell(20,$alt,"Recurso",0,0,"C",1);
    $pdf->cell(30,$alt,"Valor",0,1,"C",1);
    
  $valor = 0;
foreach ($dados as $linha){    
    $pdf->cell(60,$alt,$linha->o50_estrutreceita,0,0,"C",1);
    $pdf->cell(20,$alt,$linha->o70_codigo,0,0,"C",1);    
    $pdf->cell(30,$alt,"R$ " . number_format($linha->o70_valor,2,',','.'),0,1,"C",1);
    $valor += $linha->o70_valor;
}

$pdf->setfont('arial','b',7);
$pdf->cell(170,4,"Total R$",0,0,"R",0);
$pdf->setfont('arial','',7);
$pdf->cell(20,4,number_format($valor,2,',','.'),0,1,"L",0);

$pdf->Ln(15);
  $pdf->setfont('arial','',10);
  $pdf->cell(170,4,"Volta Redonda, {$datageracao}",0,0,"L",0);

  $pdf->Ln(25);
  $pdf->setfont('arial','b',10);
  $pdf->cell(100,4,"Ordenador da Despesa",0,0,"C",0);
  $pdf->cell(100,4,"Responsável pela digitação dos dados",0,1,"C",0);
  $pdf->setfont('arial','',9);
  $pdf->cell(100,4,"Assinatura e Carimbo",0,0,"C",0);  
  $pdf->cell(100,4,"Assinatura e Carimbo",0,0,"C",0);  


$pdf->Output();
exit();
}