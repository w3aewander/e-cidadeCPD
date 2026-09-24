<?php


include("fpdf151/pdf.php");
include("libs/db_sql.php");
include("classes/db_db_config_classe.php");
include ("libs/db_utils.php");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}



function buscaNomeOrdenador($ano){
  $sql = pg_query("SELECT idusuario FROM registrosprevias WHERE acaousuario = 'tudo' AND tipo = 'despesa' AND idtipo = {$ano}");
  $resultado1 = pg_fetch_all($sql);
  $idlogin = $resultado[0]["idusuario"];

  $sql2 = pg_query("SELECT nome FROM db_usuarios WHERE id_usuario = {$idlogin}");
  $resultado2 = pg_fetch_all($sql2);
  return $resultado2[0]["nome"];
}

function retornaCodigoLocalizador($sequencial){
  $sql = pg_query("SELECT o11_codigo FROM ppasubtitulolocalizadorgasto WHERE o11_sequencial = {$sequencial}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["o11_codigo"];
}

$cldbconfig = new cl_db_config();

parse_str($HTTP_SERVER_VARS['QUERY_STRING']);

$instituicao = db_getsession("DB_instit");  
$anocorrente = db_getsession("DB_anousu");

$x = substr($_GET["inst"], 0, -1);

if($_GET["inst"]){
  $i1 = explode(",", $x);



$where = "";
foreach ($i1 as $d) {
  $where .= "o58_instit = '{$d}' OR ";
}
$where = substr($where, 0, -4);


//CSV
//================================================================================================
//================================================================================================
//================================================================================================
if($_GET["tipo"] == "csv"){

$arquivo = fopen("tmp/previadespesa.csv", "w");
fwrite($arquivo, utf8_encode("Código;Instituição;Órgão;Unidade;Função;Subfunção;Programa;Proj/Atividade;Elemento Desp;Recurso;Localizador Gastos;Valor")."\n");

$guardacsv = array();
foreach ($i1 as $instt) {
  $result = pg_query("SELECT * FROM previadespesas WHERE o58_instit = '{$instt}' AND o58_anousu = '{$anocorrente}' ORDER BY o58_instit, cast(o58_orgao as double precision), id");
    $dados = db_utils::getColectionByRecord($result);
    

    foreach ($dados as $linha){
      $localizador = retornaCodigoLocalizador($linha->o58_localizadorgastos);
      $valor = number_format($linha->o58_valor,2,',','.');
     $linhacsv = "{$linha->id};{$linha->o58_instit} - {$linha->nomeinst};{$linha->o58_orgao} - {$linha->o40_descr};{$linha->o58_unidade} - {$linha->o41_descr};{$linha->o58_funcao} - {$linha->o52_descr};{$linha->o58_subfuncao} - {$linha->o53_descr};{$linha->o58_programa} - {$linha->o54_descr};{$linha->o58_projativ} - {$linha->o55_descr};{$linha->o56_elemento} - {$linha->o56_descr};{$linha->o58_codigo} - {$linha->o15_descr};{$localizador};{$valor}";
     array_push($guardacsv, $linhacsv);  
    }
}


foreach ($guardacsv as $linha) {
  fwrite($arquivo, utf8_encode($linha) . "\n");
}
fclose($arquivo);


$file_url = 'tmp/previadespesa.csv';
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
readfile($file_url); 
exit();
}//Fim CSV
//================================================================================================
//================================================================================================
//================================================================================================

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
  $head3  = "RELATÓRIO ORÇAMENTÁRIO DA DESPESA - {$anocorrente}";
  $head4  = "Instituição - {$instt}";
  
  $result = pg_query("SELECT * FROM previadespesas WHERE o58_instit = '{$instt}' AND o58_anousu = '{$anocorrente}' ORDER BY o58_instit, cast(o58_orgao as double precision), id");
  $dados = db_utils::getColectionByRecord($result);

  if($z == 1){
    //$head6 = "Órgão - " . $dados[0]->o58_orgao . " - " . $dados[0]->o40_descr;    
    $head6 = "Órgão: {$dados[0]->o58_orgao} - {$dados[0]->o40_descr}";    
  } else {
    $head6 = "";
  }
  
  $z++;
  
  $pdf->AddPage("P");
  $pdf->setfont('arial','b',8);
  $pdf->cell(15,$alt,"Código",0,0,"C",1);
  $pdf->cell(11,$alt,"Órgão",0,0,"C",1);
  $pdf->cell(13,$alt,"Und",0,0,"C",1);
  $pdf->cell(11,$alt,"Função",0,0,"C",1);
  $pdf->cell(16,$alt,"Subfunção",0,0,"C",1);
  $pdf->cell(16,$alt,"Programa",0,0,"C",1);
  $pdf->cell(21,$alt,"Proj/Atividade",0,0,"C",1);
  $pdf->cell(30,$alt,"Elemento Desp",0,0,"C",1);
  $pdf->cell(12,$alt,"Recurso",0,0,"C",1);
  $pdf->cell(10,$alt,"L.G.",0,0,"C",1);
  $pdf->cell(25,$alt,"Valor",0,1,"C",1);

  $valor = 0;
  $guarda = $dados[0]->o58_orgao;
  $totalorgao = 0;
  foreach ($dados as $linha){
    //$head6 = "Órgão - {$linha->o58_orgao}";
    $head6 = "Órgão: {$linha->o58_orgao} - {$linha->o40_descr}";
    $localizador = retornaCodigoLocalizador($linha->o58_localizadorgastos);

    if($instt == 1){
      if($guarda == $linha->o58_orgao){        
        $pdf->cell(15,$alt,$linha->id,0,0,"C",1);
        $pdf->cell(11,$alt,$linha->o58_orgao,0,0,"C",1);
        $pdf->cell(13,$alt,$linha->o58_unidade,0,0,"C",1);
        $pdf->cell(11,$alt,$linha->o58_funcao,0,0,"C",1);
        $pdf->cell(16,$alt,$linha->o58_subfuncao,0,0,"C",1);
        $pdf->cell(16,$alt,$linha->o58_programa,0,0,"C",1);
        $pdf->cell(21,$alt,$linha->o58_projativ,0,0,"C",1);
        $pdf->cell(30,$alt,$linha->o56_elemento,0,0,"C",1);
        $pdf->cell(12,$alt,$linha->o58_codigo,0,0,"C",1);        
        $pdf->cell(10,$alt,$localizador,0,0,"C",1);
        $pdf->cell(25,$alt,"R$ " . number_format($linha->o58_valor,2,',','.'),0,1,"C",1);
        $valor += $linha->o58_valor;
        $totalorgao += $linha->o58_valor;
        $guarda = $linha->o58_orgao;
        //$pdf->setfont('arial','b',7);
        //$pdf->cell(170,4,"Total Órgão R$",0,0,"R",0);
        //$pdf->setfont('arial','',7);
      } else {
        //$head5 = "Órgão - {$linha->o58_orgao}";
        $pdf->setfont('arial','b',7);
        $pdf->cell(170,4,"Total Órgão R$",0,0,"R",0);
        $pdf->setfont('arial','',7);
        $pdf->cell(20,4,number_format($totalorgao,2,',','.'),0,1,"L",0);
        $totalorgao = 0;

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

        $pdf->AddPage("P");
        $pdf->setfont('arial','b',8);
        $pdf->cell(15,$alt,"Código",0,0,"C",1);
        $pdf->cell(11,$alt,"Órgão",0,0,"C",1);
        $pdf->cell(13,$alt,"Unidade",0,0,"C",1);
        $pdf->cell(11,$alt,"Função",0,0,"C",1);
        $pdf->cell(16,$alt,"Subfunção",0,0,"C",1);
        $pdf->cell(16,$alt,"Programa",0,0,"C",1);
        $pdf->cell(21,$alt,"Proj/Atividade",0,0,"C",1);
        $pdf->cell(30,$alt,"Elemento Desp",0,0,"C",1);
        $pdf->cell(12,$alt,"Recurso",0,0,"C",1);
        $pdf->cell(10,$alt,"L.G.",0,0,"C",1);
        $pdf->cell(25,$alt,"Valor",0,1,"C",1);

        $pdf->cell(15,$alt,$linha->id,0,0,"C",1);
        $pdf->cell(11,$alt,$linha->o58_orgao,0,0,"C",1);
        $pdf->cell(13,$alt,$linha->o58_unidade,0,0,"C",1);
        $pdf->cell(11,$alt,$linha->o58_funcao,0,0,"C",1);
        $pdf->cell(16,$alt,$linha->o58_subfuncao,0,0,"C",1);
        $pdf->cell(16,$alt,$linha->o58_programa,0,0,"C",1);
        $pdf->cell(21,$alt,$linha->o58_projativ,0,0,"C",1);
        $pdf->cell(30,$alt,$linha->o56_elemento,0,0,"C",1);
        $pdf->cell(12,$alt,$linha->o58_codigo,0,0,"C",1);        
        $pdf->cell(10,$alt,$localizador,0,0,"C",1);
        $pdf->setfont('arial','b',8);
        $pdf->cell(25,$alt,"R$ " . number_format($linha->o58_valor,2,',','.'),0,1,"C",1);
        $valor += $linha->o58_valor;
        $totalorgao += $linha->o58_valor;
        $guarda = $linha->o58_orgao;
      } 
      
    } else {
      
      $pdf->cell(15,$alt,$linha->id,0,0,"C",1);
      $pdf->cell(11,$alt,$linha->o58_orgao,0,0,"C",1);
      $pdf->cell(13,$alt,$linha->o58_unidade,0,0,"C",1);
      $pdf->cell(11,$alt,$linha->o58_funcao,0,0,"C",1);
      $pdf->cell(16,$alt,$linha->o58_subfuncao,0,0,"C",1);
      $pdf->cell(16,$alt,$linha->o58_programa,0,0,"C",1);
      $pdf->cell(21,$alt,$linha->o58_projativ,0,0,"C",1);
      $pdf->cell(30,$alt,$linha->o56_elemento,0,0,"C",1);
      $pdf->cell(12,$alt,$linha->o58_codigo,0,0,"C",1);
      $pdf->cell(10,$alt,$localizador,0,0,"C",1);
      $pdf->cell(25,$alt,"R$ " . number_format($linha->o58_valor,2,',','.'),0,1,"C",1);
    
      $valor += $linha->o58_valor;
    }
    
    
  }

  if($instt == 1){
      $pdf->setfont('arial','b',7);
      $pdf->cell(170,4,"Total Órgão R$",0,0,"R",0);
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
  $pdf->Ln(10);
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





  //$result = pg_query("SELECT * FROM previadespesas WHERE o58_instit in({$_GET['inst']})");  
  //$result = pg_query("SELECT * FROM previadespesas WHERE {$where} ORDER BY o58_instit");  
} else {
  setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
  date_default_timezone_set('America/Sao_Paulo');
  $datageracao = strftime('%d de %B de %Y', strtotime('today'));
  $nomeordenador = buscaNomeOrdenador($anocorrente);
  
  if($_GET["tipo"] == "csv"){    
    $arquivo = fopen("tmp/previadespesa.csv", "w");
    fwrite($arquivo, utf8_encode("Código;Instituição;Órgão;Unidade;Função;Subfunção;Programa;Proj/Atividade;Elemento Desp;Recurso;Localizador Gastos;Valor") . "\n");
    $guardacsv = array();

    $result = pg_query("SELECT * FROM previadespesas WHERE o58_instit = '{$instituicao}' AND o58_anousu = '{$anocorrente}'");
    $dados = db_utils::getColectionByRecord($result);

    foreach ($dados as $linha){
      $valor = number_format($linha->o58_valor,2,',','.');
      $localizador = retornaCodigoLocalizador($linha->o58_localizadorgastos);
      $linhacsv = "{$linha->id};{$linha->o58_instit};{$linha->o58_orgao};{$linha->o58_unidade};{$linha->o58_funcao};{$linha->o58_subfuncao};{$linha->o58_programa};{$linha->o58_projativ};{$linha->o56_elemento};{$linha->o58_codigo};{$localizador};{$valor}";
      array_push($guardacsv, $linhacsv);  
    }

    foreach ($guardacsv as $linha) {
      fwrite($arquivo, utf8_encode($linha) . "\n");
    }
    fclose($arquivo);

    $file_url = 'tmp/previadespesa.csv';
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary"); 
    header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
    readfile($file_url); 
    exit();
  }

  
  $instituicao = db_getsession("DB_instit");
  $head3  = "RELATÓRIO ORÇAMENTÁRIO DA DESPESA - {$anocorrente}";
  $head4  = "Instituição - {$instituicao}";
  $result = pg_query("SELECT * FROM previadespesas WHERE o58_instit = '{$instituicao}' AND o58_anousu = '{$anocorrente}'");

  $dados = db_utils::getColectionByRecord($result);

  //echo "<pre>";
  //print_r($dados);
  //echo "</pre>";
  //die("Verifica");

$pdf = new PDF(); 
$pdf->Open(); 
$pdf->AliasNbPages(); 
$pdf->setfillcolor(235);
$pdf->setfont('arial','b',8);
$alt     = 4;
$pdf->imprime_rodape = false;

    $pdf->AddPage("P");
    $pdf->setfont('arial','b',9);
    $pdf->cell(10,$alt,"Órgão",0,0,"C",1);
    $pdf->cell(8,$alt,"Und",0,0,"C",1);
    $pdf->cell(12,$alt,"Func",0,0,"C",1);
    $pdf->cell(15,$alt,"Subfunc",0,0,"C",1);
    $pdf->cell(14,$alt,"Programa",0,0,"C",1);
    $pdf->cell(25,$alt,"Proj/Atividade",0,0,"C",1);
    $pdf->cell(30,$alt,"Elemento Desp",0,0,"C",1);
    $pdf->cell(15,$alt,"Recurso",0,0,"C",1);
    $pdf->cell(10,$alt,"L.G.",0,0,"C",1);
    $pdf->cell(25,$alt,"Valor",0,1,"C",1);
    
$valor = 0;
foreach ($dados as $linha){
    $localizador = retornaCodigoLocalizador($linha->o58_localizadorgastos);
    $pdf->cell(10,$alt,$linha->o58_orgao,0,0,"C",1);
    $pdf->cell(8,$alt,$linha->o58_unidade,0,0,"C",1);
    $pdf->cell(12,$alt,$linha->o58_funcao,0,0,"C",1);
    $pdf->cell(15,$alt,$linha->o58_subfuncao,0,0,"C",1);
    $pdf->cell(14,$alt,$linha->o58_programa,0,0,"C",1);
    $pdf->cell(25,$alt,$linha->o58_projativ,0,0,"C",1);
    $pdf->cell(30,$alt,$linha->o56_elemento,0,0,"C",1);
    $pdf->cell(15,$alt,$linha->o58_codigo,0,0,"C",1);
    $pdf->cell(10,$alt,$localizador,0,0,"C",1);
    $pdf->cell(25,$alt,"R$ " . number_format($linha->o58_valor,2,',','.'),0,1,"C",1);
    $valor += $linha->o58_valor;
}


$pdf->setfont('arial','b',7);
$pdf->cell(170,4,"Total R$",0,0,"R",0);
$pdf->setfont('arial','',7);
$pdf->cell(20,4,number_format($valor,2,',','.'),0,1,"L",0);


$pdf->Ln(10);
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