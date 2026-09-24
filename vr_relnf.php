<?


require(modification("libs/db_stdlib.php"));
require(modification("libs/db_utils.php"));
require(modification("libs/db_conecta.php"));
include(modification("libs/db_sessoes.php"));
include(modification("libs/db_usuariosonline.php"));
include(modification("dbforms/db_funcoes.php"));
include(modification("classes/db_matordem_classe.php"));


function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

$oGet = db_utils::postMemory($HTTP_GET_VARS);



$sWhere = "";
if(trim($oGet->e69_numero)!=''){
	if($sWhere == ""){
		$sWhere .= " e69_numero='".$oGet->e69_numero."' ";
	}else{
		$sWhere .= " and e69_numero='".$oGet->e69_numero."' ";
	}
}
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

if(trim($oGet->m51_codordem)!=''){
  if($sWhere == ""){
    $sWhere .= " m51_codordem=".$oGet->m51_codordem;
  }else{
    $sWhere .= " and m51_codordem=".$oGet->m51_codordem;
  }
}


if ( isset($oGet->e04_numeroprocesso) && !empty($oGet->e04_numeroprocesso) ) {
  
  $sProcesso = addslashes($oGet->e04_numeroprocesso);
  if($sWhere == ""){
    
    $sWhere .= " e04_numeroprocesso ilike '%{$sProcesso}%' ";
  }else{
    
    $sWhere .= " and e04_numeroprocesso ilike '%{$sProcesso}%' ";
  }
  
}



if ( isset($oGet->dtini) && !empty($oGet->dtini) && isset($oGet->dtfim) && !empty($oGet->dtfim) ) {
  
  if(trim($oGet->dtini) != '' && trim($oGet->dtfim) != '' ){
    if($sWhere == ""){
      $sWhere .= " e69_dtnota between '".$oGet->dtini."' and '".$oGet->dtfim."'";
    }else{  
      $sWhere .= " and e69_dtnota between '".$oGet->dtini."' and '".$oGet->dtfim."'";
    }
  }else if(trim($oGet->dtini) != ''){
    if($sWhere == ""){
      $sWhere .= " e69_dtnota = '".$oGet->dtini."' ";
    }else{
      $sWhere .= " and e69_dtnota = '".$oGet->dtini."' ";
    }
  }else if(trim($oGet->dtfim) != ''){
    if($sWhere == ""){
      $sWhere .= " e69_dtnota = '".$oGet->dtfim."' ";
    }else{
      $sWhere .= " and e69_dtnota = '".$oGet->dtfim."' ";
    }
  }
}

$sSql = "
          select distinct empnotaord.m72_codordem, 
                          empnota.e69_codnota, 
                          empnota.e69_numero, 
                          substr(z01_nome,1,30) as z01_nome, 
                          empnota.e69_numemp, 
									        empnota.e69_dtrecebe, 
									        empnotaele.e70_valor, 
									        empnotaele.e70_vlrliq, 
									        empnotaele.e70_vlranu ,
                          empnotaprocesso.e04_numeroprocesso
                     from empnota 
									        inner join empnotaele  on e69_codnota = e70_codnota 
									        inner join db_usuarios on db_usuarios.id_usuario = empnota.e69_id_usuario 
									        inner join empempenho  on empempenho.e60_numemp   = empnota.e69_numemp 
									        inner join cgm         on cgm.z01_numcgm = empempenho.e60_numcgm 
									        left join pagordemnota on e71_codnota = empnota.e69_codnota 
									                              and e71_anulado is false 
									        left join pagordem     on e71_codord = e50_codord 
									        left join pagordemele  on e53_codord   = pagordemnota.e71_codord 
									        left join empnotaord   on m72_codnota = e69_codnota 
									        left join matordem     on m72_codordem    = m51_codordem 
									        left join matordemanu  on m51_codordem = m53_codordem
									        left join empnotaitem  on e69_codnota  =  e72_codnota 
									        left join empempitem   on e62_sequencial = e72_empempitem
                          left join empnotaprocesso on empnota.e69_codnota = empnotaprocesso.e04_empnota
                    ";
if($sWhere != ""){
	$sWhere = " where ".$sWhere; 
}
$sSql .= $sWhere;
$sSql .=	"				  order by e69_codnota "; 

$sqldados = pg_query($sSql);
$dados = pg_fetch_all($sqldados);

//testa($dados);
//die("Confere");

if($_GET["tipo"] == "csv"){

$arquivo = fopen("tmp/relatorio_notas_fiscais.csv", "w");
fwrite($arquivo, "Código;Seq. da Nota;Número da NF;Nome/Razão Social;Empenho;Data do Recebimento;Valor;Valor Liquidado;Valor Anulado;Nº do Processo"."\n");

$guardacsv = array();
foreach ($dados as $linha){
  if(trim($linha["e69_numero"] == "S/N")){continue;}
  $cod = trim($linha["m72_codordem"]);
  $codnota = $linha["e69_codnota"];
  $numeronf = trim($linha["e69_numero"]);
  //$numeronf_tratado = '="' . $numeronf . '"';
  $numeronf_tratado = '"' . $numeronf . '"';
  $nome = trim($linha["z01_nome"]);
  $seqempenho = $linha["e69_numemp"];
  $data = implode("/", array_reverse(explode("-", $linha["e69_dtrecebe"])));
  $valor = number_format($linha["e70_valor"],2,',','.');
  $valorliq = number_format($linha["e70_vlrliq"],2,',','.');
  $valoranul = number_format($linha["e70_vlranu"],2,',','.');
  $nprocesso = trim($linha["e04_numeroprocesso"]);
      
  $linhacsv = "{$cod};{$codnota};{$numeronf_tratado};{$nome};{$seqempenho};{$data};{$valor};{$valorliq};{$valoranul};{$nprocesso}";
  array_push($guardacsv, $linhacsv);  
}



foreach ($guardacsv as $linha) {
  fwrite($arquivo, utf8_encode($linha) . "\n");
}
fclose($arquivo);


$file_url = 'tmp/relatorio_notas_fiscais.csv';
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
readfile($file_url); 
exit();
}