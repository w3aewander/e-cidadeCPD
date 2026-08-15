<?php
error_reporting(E_ALL); 
ini_set('display_errors', '1');

/**
 * Script autaliza_bens.php
 * @author Sérgio Navarro
 * Navarro Tecnologia
 * @date 12/04/2021
 * @version 1.0
 */

/**
 * Script para autalização do campo t52_valaqu da tabela parimonial.bens
 * Cabeçalho dos campos da tabela CSV.
 *
 * t52_bem,
 * t52_ident,
 * t52_dtaqu,
 * t52_descr,
 * t52_valaqu,
 */

/**
 * Conexao com o banco de dados...
 * @param nenhum
 * @return ponteiro para pdo
 */

//ppa_ldo_2021

$inst = 45;
function conecta(){
  try {
    $pdo = new PDO("pgsql:dbname='voltaredonda'; host=10.1.0.51;  port=6432; user=postgres; password='';");
    $pdo->exec( "select fc_startsession();" );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
  } catch (PDOException $e){
    echo "Erro: " . $e->getMessage();
  }
}

//Conecta no banco de dados
$pdo = conecta();
if(!$pdo) {
  die ("Não foi possível conectar ao banco de origem! Tente novamente.");
}else{
  //pg_set_client_encoding($pdo, 'LATIN1');
}

/**
 *
 * Remove os caracteres "-/." dos
 * @param $text string
 * @return $str_tratado string
*/
function trata_campo($text) {
     $str_tratado = trim($text);
     $str_tratado = str_replace('.','',$text);
     $str_tratado = str_replace('-','',$str_tratado);
     $str_tratado = str_replace('/','',$str_tratado);

    return $str_tratado;
}


function localiza_ben($pdo,$id,$inst){

  echo ("Localizando o Bem $id da Instituição $inst...\n\n");

  try {
    $stm = $pdo->prepare("SELECT COUNT(t52_bem) AS quant_placa FROM patrimonio.bens WHERE t52_bem = :id AND t52_instit = :instituicao");
    $stm->execute(array("id"          => $id,
                        "instituicao" => $inst
                      ));
    $res = $stm->fetchAll(PDO::FETCH_OBJ);
    return $res ? $res[0]->quant_placa : false;

  } catch (PDOException $e){
    echo "Erro: " . $e->getMessage();
  }
}

function updatevalorbem($pdo, $valor,$id,$inst){
  try{
    $stmt = $pdo->prepare("UPDATE patrimonio.bens SET t52_valaqu = :t52_valaqu WHERE t52_bem = :id AND t52_instit = :instituicao");
    $stmt->execute(array(
        "id"          => $id,
        "instituicao" => $inst,
        "t52_valaqu"  => $valor
      ));
    return ($stmt->rowCount() > 0) ? $stmt : false;
  } catch(PDOExpcetion $e){
    echo "Erro: " . $e->getMessage();
  }
}

function updatevalorbemdepreciacao($pdo, $valoratual, $codbem){
  try{
    $stmt = $pdo->prepare("UPDATE patrimonio.bensdepreciacao SET t44_valoratual= :t44_valoratual WHERE t44_bens = :t44_bens");
    $stmt->execute(array(
        "t44_bens"          => $codbem,
        "t44_valoratual"    => $valoratual
      ));
    return ($stmt->rowCount() > 0) ? $stmt : false;
  } catch(PDOExpcetion $e){
    echo "Erro: " . $e->getMessage();
  }
}

$csv = "imoveis_saae.csv";
$pFile = new SplFileObject($csv);
$arr_lines = array();
$varlog = array();
$varlog2 = array();
$i = 0;
$j = 0;

while (!$pFile->eof()) {
  $line = $pFile->fgets();
  $arr_fields = explode("|", $line);
  $arr_line = array();
  $arr_line["id"] = $arr_fields[0];
  $arr_line["valoraquisicao"] = str_replace(",", ".", $arr_fields[1] );
  $arr_line["valorresidual"] = str_replace(",", ".", $arr_fields[1] );
  $arr_line["valoratual"] = str_replace(",", ".", $arr_fields[1] );

  print("-------------------------------------------------------------------------------\n\n");
  $linha = 'Bem: ' . $arr_line["id"] .  ' - ' . ' Vador Aquisicao: ' . $arr_line["valoraquisicao"] . ' - ' . ' Vador Atual: ' . $arr_line["valoratual"] ;
  echo  $linha . "\n";
  echo "<br>";

 
  if(localiza_ben($pdo,$arr_line["id"],$inst) == 1){
    
    updatevalorbem($pdo,$arr_line["valoraquisicao"],$arr_line["id"],$inst);
    updatevalorbemdepreciacao($pdo, $arr_line["valoratual"], $arr_line["id"]);
 
  } elseif(localiza_ben($pdo,$arr_line["id"],$inst) == 0) {    
  
    $varlog2[$j] = $linha;
    $j++;
  }

}
$pFile = null;

$arqlog = '/var/www/homologacao/script/thiago/tmp/arq_log_imoveis.txt';
$pfilelog = fopen($arqlog, 'a');
fwrite($pfilelog,  print_r($varlog, TRUE));
fclose($pfilelog);

$arqlog2 = '/var/www/homologacao/script/thiago/tmp/arq_log2_imoveis.txt';
$pfilelog = fopen($arqlog2, 'a');
fwrite($pfilelog,  print_r($varlog2, TRUE));
fclose($pfilelog);

echo "<br>\n";
echo 'FIM DA ATUALIZAÇÃO!';
echo "<br>\n";

?>
