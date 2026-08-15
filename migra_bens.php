<?php
error_reporting(E_ALL); 
ini_set('display_errors', '1');

/**
 * Script migra_bens.php
 * @author Sérgio Navarro
 * Navarro Tecnologia
 * @date 13/06/2021
 * @version 1.0
 */

/**
 * Script para migração
 * Cabeçalho dos campos da tabela CSV.


* Tabelas Envolvidas:
 bens -> chave primária: t52_bem (sequencial)
 * t52_bem (Sequencial)
 * t52_codcla (Coluna 8 - codcla)
 * t52_numcgm (OBS. Incluir o valor 118751)
 * t52_valaqu (Coluna 13 - valaqu)
 * t52_dtaqu  (Coluna 0  - dtaqu)
 * t52_ident  (Coluna 9  - placa)
 * t52_descr  (Coluna 5  - descr)
 * t52_obs    (Coluna 12 - obs_bens)
 * t52_depart (Coluna 7  - depart)
 * t52_instit (OBS. Incluir o valor 96) 


 bensdepreciacao   -> chave primária: t44_sequencial  -> chave estrangeira(bens): t44_bens
 t44_benstipoaquisicao    (Coluna 3  - situac)
 t44_benstipodepreciacao  (OBS. Incluir o valor 5)
 t44_vidautil             (OBS. Incluir o valor 10)
 t44_valoratual           (Coluna 14 - vconv)
 t44_valorresidual        (Coluna 15 - vref)
 t44_ultimaavaliacao      (Coluna 2  - dtconv)

 benstipoaquisicao -> chave primária: t45_sequencial (Coluna 2)

 histbem           -> chave primária: t56_histbem(sequencial) -> chave estrangeira(bens): t56_codbem 
 * t56_data   (Coluna 1 - dtbaixa)
 * t56_depart (Coluna 7 - depart)
 * t56_situac (Coluna 4 - benstipoaquisicao)
 * t56_histor (OBS. Incluir o valor  'Importação de Dados')


 bensbaix          -> chave primária: t55_codbem   -> chave estrangeira(bens): t55_codbem 
 * t55_baixa  (Coluna 1 - dtbaixa)
 * t55_motivo (OBS. Incluir o valor 13)
 * t55_obs    (Coluna 6 - obs_bensbaix)
- Tabela de Motivos da baixa
3   FURTO 
4   DOAÇÃO 
5   BEM INSERVÍVEL 
6   BEM LEILOADO 
7   BEM IMPRESTÁVEL 
8   BENS EM DUPLICIDADE 
9   REGISTRO INDEVIDO 
10    LANÇAMENTO ERRADO 
11    VENDA DIRETA 
12    BEM DESTRUÍDO 
13    NAO INFORMADO 


 bensplaca         -> chave primária: t41_codigo(sequencial)  -> chave estrangeira(bens): t41_bem
 * t41_placaseq 
 * t41_obs      (OBS. Incluir o valor  'Importação de Dados')
 * t41_data     (Coluna 0  - dtaqu)
 * t41_usuario  (OBS. Incluir o valor  1)

 bensmater         -> chave primária: t53_codbem      -> chave estrangeira(bens): t53_codbem

 * t53_ntfisc (Coluna 10 - ntfisc)
 * t53_empen  (Coluna 11 - codemp)
 * t53_ordem  (OBS. Incluir o valor  0)
 * t53_garant 

 empempenho        -> chave primária: e60_numemp    


 
 */

/**
 * Conexao com o banco de dados...
 * @param nenhum
 * @return ponteiro para pdo
 */

//patrimonial

//$depart = 1483;
$cgm = 66937;
$instit = 45;

function conecta(){
  try {
    $pdo = new PDO("pgsql:dbname='voltaredonda'; host=10.1.0.51;  port=5432; user='ecidade'; password='db#vltrdnd12';");
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

function remove_acento($text) {

  $comAcentos = array('à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'O', 'Ù', 'Ü', 'Ú');
  $semAcentos = array('a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U');

  $nova_string = strtoupper(str_replace($comAcentos, $semAcentos, $text));
 
  return $nova_string;

}



function seqbem($pdo){
  try {
    $stm = $pdo->prepare("SELECT MAX(t52_bem) + 1 AS seq FROM patrimonio.bens");
    $stm->execute();
    $res = $stm->fetchAll(PDO::FETCH_OBJ);
    return $res ? $res[0]->seq : false;

  } catch (PDOException $e){
    echo "Erro: " . $e->getMessage();
  }
}

function seqbemplaca($pdo){
  try {
    $stm = $pdo->prepare("SELECT MAX(t41_codigo) + 1 AS seq FROM patrimonio.bensplaca");
    $stm->execute();
    $res = $stm->fetchAll(PDO::FETCH_OBJ);
    return $res ? $res[0]->seq : false;

  } catch (PDOException $e){
    echo "Erro: " . $e->getMessage();
  }
}

function placaseq($pdo,$codbens){ 
  try {
    $stm = $pdo->prepare("SELECT t41_placaseq + 1 AS placaseq FROM patrimonio.bensplaca WHERE t41_bem = :codbem");
    $stm->execute(array("codbem" => $codbens));
    $res = $stm->fetchAll(PDO::FETCH_OBJ);
    return $res ? $res[0]->placaseq : false;

  } catch (PDOException $e){
    echo "Erro: " . $e->getMessage();
  }
}


function placaseq2($pdo){ 
  try {

    $stm = $pdo->prepare("SELECT t41_placaseq + 1 AS placaseq2 FROM patrimonio.bensplaca");
    $stm->execute(array("codbem" => $codbens));
    $res = $stm->fetchAll(PDO::FETCH_OBJ);
    return $res ? $res[0]->placaseq2 : false;

  } catch (PDOException $e){
    echo "Erro: " . $e->getMessage();
  }
}


function bensdepre($pdo){
  try {
    $stm = $pdo->prepare("SELECT MAX(t44_sequencial) + 1 AS seq FROM patrimonio.bensdepreciacao");
    $stm->execute();
    $res = $stm->fetchAll(PDO::FETCH_OBJ);
    return $res ? $res[0]->seq : false;

  } catch (PDOException $e){
    echo "Erro: " . $e->getMessage();
  }
}


function benstipodepre($pdo,$codclas){
  try {
    $stm = $pdo->prepare("SELECT t64_benstipodepreciacao AS tipodepre FROM patrimonio.clabens WHERE t64_codcla = :codclas");
    $stm->execute(array("codclas" => $codclas));
    $res = $stm->fetchAll(PDO::FETCH_OBJ);
    return $res ? $res[0]->tipodepre : false;
  } catch (PDOException $e){
    echo "Erro: " . $e->getMessage();
  }
}


function isertbens($pdo,$t52_bem,$t52_codcla,$t52_numcgm,$t52_valaqu,$t52_dtaqu,$t52_ident,$t52_descr,$t52_obs,$t52_depart,$t52_instit){
  try{
    $stmt = $pdo->prepare("INSERT INTO patrimonio.bens(t52_bem, t52_codcla, t52_numcgm, t52_valaqu, t52_dtaqu, t52_ident, t52_descr, t52_obs, t52_depart, t52_instit) VALUES (:t52_bem, :t52_codcla, :t52_numcgm, :t52_valaqu, :t52_dtaqu, :t52_ident, :t52_descr, :t52_obs, :t52_depart, :t52_instit)");
    $stmt->execute(array(
        't52_bem'    => $t52_bem,
        't52_codcla' => $t52_codcla,
        't52_numcgm' => $t52_numcgm,
        't52_valaqu' => $t52_valaqu,
        't52_dtaqu'  => $t52_dtaqu,
        't52_ident'  => $t52_ident,
        't52_descr'  => $t52_descr,
        't52_obs'    => $t52_obs,
        't52_depart' => $t52_depart,
        't52_instit' => $t52_instit
      ));
    return ($stmt->rowCount() > 0) ? $stmt : false;
  } catch(PDOExpcetion $e){
    echo "Erro: " . $e->getMessage();
  }
}

function isertbensplaca($pdo,$t41_codigo,$t41_bem,$t41_placaseq,$t41_obs,$t41_data,$t41_usuario){
  try{
    $stmt = $pdo->prepare("INSERT INTO patrimonio.bensplaca(t41_codigo, t41_bem, t41_placaseq, t41_obs, t41_data, t41_usuario) VALUES (:t41_codigo, :t41_bem, :t41_placaseq, :t41_obs, :t41_data, :t41_usuario)");
    $stmt->execute(array(
        't41_codigo'   => $t41_codigo,
        't41_bem'      => $t41_bem,
        't41_placaseq' => $t41_placaseq,
        't41_obs'      => $t41_obs,
        't41_data'     => $t41_data,
        't41_usuario'  => $t41_usuario
      ));
    return ($stmt->rowCount() > 0) ? $stmt : false;
  } catch(PDOExpcetion $e){
    echo "Erro: " . $e->getMessage();
  }
}

function isertbensmater($pdo,$t53_codbem,$t53_ntfisc,$t53_empen){
  try{
    $stmt = $pdo->prepare("INSERT INTO patrimonio.bensmater(t53_codbem, t53_ntfisc, t53_empen) VALUES (:t53_codbem, :t53_ntfisc, :t53_empen)");
    $stmt->execute(array(
        't53_codbem'   => $t53_codbem,
        't53_ntfisc'   => $t53_ntfisc,
        't53_empen'    => $t53_empen
      ));
    return ($stmt->rowCount() > 0) ? $stmt : false;
  } catch(PDOExpcetion $e){
    echo "Erro: " . $e->getMessage();
  }
}

function isertbensdepre($pdo,$t44_sequencial,$t44_bens,$t44_benstipoaquisicao,$t44_benstipodepreciacao,$t44_valoratual,$t44_valorresidual,$t44_ultimaavaliacao){
  try{
    $stmt = $pdo->prepare("INSERT INTO patrimonio.bensdepreciacao(t44_sequencial, t44_bens, t44_benstipoaquisicao, t44_benstipodepreciacao, t44_valoratual, t44_valorresidual, t44_ultimaavaliacao) VALUES (:t44_sequencial, :t44_bens, :t44_benstipoaquisicao, :t44_benstipodepreciacao, :t44_valoratual, :t44_valorresidual, :t44_ultimaavaliacao)");
    $stmt->execute(array(
        't44_sequencial'          => $t44_sequencial,
        't44_bens'                => $t44_bens,
        't44_benstipoaquisicao'   => $t44_benstipoaquisicao,
        't44_benstipodepreciacao' => $t44_benstipodepreciacao,
        't44_valoratual'          => $t44_valoratual,
        't44_valorresidual'       => $t44_valorresidual,
        't44_ultimaavaliacao'     => $t44_ultimaavaliacao
      ));
    return ($stmt->rowCount() > 0) ? $stmt : false;
  } catch(PDOExpcetion $e){
    echo "Erro: " . $e->getMessage();
  }
} 

function isertbensbaix($pdo,$t55_codbem,$t55_baixa,$t55_motivo,$t55_obs){
  try{
    $stmt = $pdo->prepare("INSERT INTO patrimonio.bensbaix(t55_codbem, t55_baixa, t55_motivo, t55_obs) VALUES (:t55_codbem, :t55_baixa, :t55_motivo, :t55_obs)");
    $stmt->execute(array(
        't55_codbem'  => $t55_codbem,
        't55_baixa'   => $t55_baixa,
        't55_motivo'  => $t55_motivo,
        't55_obs'     => $t55_obs
      ));
    return ($stmt->rowCount() > 0) ? $stmt : false;
  } catch(PDOExpcetion $e){
    echo "Erro: " . $e->getMessage();
  }
}

$csv = "migracao_patrimonio_10_12.csv";
$pFile = new SplFileObject($csv);
$arr_lines = array();
$varlog = array();
$varlog2 = array();
$i = 0;
$vlaqui = 0;
$reg = 0;

$l=0;
while (!$pFile->eof()) {
  $line = $pFile->fgets();
  $arr_fields = explode("|", $line);
  $arr_line = array();
  
  $arr_line["dtaqu"]              = $arr_fields[0];
  $arr_line["dtbaixa"]            = $arr_fields[1];
  $arr_line["dtconv"]             = $arr_fields[2];
  $arr_line["situac"]             = $arr_fields[3];
  $arr_line["benstipoaquisicao"]  = $arr_fields[4];
  $arr_line["descr"]              = $arr_fields[5];
  $arr_line["motivo"]             = $arr_fields[6];
  $arr_line["obs_bensbaix"]       = $arr_fields[7];
  $arr_line["depart"]             = $arr_fields[8];  
  $arr_line["codcla"]             = $arr_fields[9];
  $arr_line["placa"]              = $arr_fields[10];
  $arr_line["ntfisc"]             = $arr_fields[11];
  //$arr_line["codemp"]             = $arr_fields[11];
  $arr_line["obs_bens"]           = $arr_fields[12];
  $arr_line["valaqu"]             = str_replace(",", ".", $arr_fields[13]);
  $arr_line["vconv"]              = $arr_fields[14];
  $arr_line["vref"]               = $arr_fields[15];
  
  $l++;
  print("-------------------------------------------------------------------------------\n\n");
  $linha = 'Dt Aquisição: ' . $arr_line["dtaqu"] .  ' - ' 
  . ' Dt baixa: ' . $arr_line["dtbaixa"] .  ' - ' 
  . ' Dt Conservação: ' . $arr_line["dtconv"]  .  ' - ' 
  . ' Situação: ' . $arr_line["situac"] .  ' - ' 
  . ' Tipo Aquisição: ' . $arr_line["benstipoaquisicao"] .  ' - ' 
  . ' Descrição: ' . $arr_line["descr"] . ' - ' 
  . ' Motivo: '  . $arr_line["motivo"]  . ' - ' 
  . ' Obs: ' . $arr_line["obs_bensbaix"] .  ' - ' 
  . ' Obs Baixa: ' . $arr_line["obs_bensbaix"] .  ' - ' 
  . ' Depart: ' . $arr_line["depart"]  .  ' - ' 
  . ' Cod Clas: ' . $arr_line["codcla"]  .  ' - ' 
  . ' Placa: ' . $arr_line["placa"]  .  ' - ' 
  . ' Nota: ' . $arr_line["ntfisc"] . ' - ' 
  . ' Obs: ' . $arr_line["obs_bens"]  .  ' - ' 
  . ' Vlr Aquisição: ' . $arr_line["valaqu"]  .  ' - ' 
  . ' Vlr Conservação: ' . $arr_line["vconv"]  .  ' - ' 
  . ' Vlr Referencia: ' . $arr_line["vref"];

  echo  $linha . "\n";
  echo "<br>\n";
  echo "Read Linha: $l";
  echo "<br>\n";

  $cobem = seqbem($pdo);
  $codcla = empty($arr_line["codcla"]) ? 921 : $arr_line["codcla"];
  //$depart = empty($arr_line["depart"]) ? 1291 : $arr_line["depart"];
  $depart = 1256;
  $codemp = 0;
  $descr = substr($arr_line["descr"], 0,100);
  $obs =  'Descr: '. $arr_line["descr"] . '|' . 'Obs: ' . $arr_line["obs_bens"];
  $obs_bensbaix = $arr_line["obs_bensbaix"];
  //$vlraqui = empty($arr_line["valaqu"]) ? 0 : $arr_line["valaqu"];
  $vlraqui = empty($arr_line["vconv"]) ? 0 : $arr_line["vconv"];
  $vlrconv = empty($arr_line["vconv"]) ? 0 : $arr_line["vconv"];
  $vlrref  = empty($arr_line["vref"]) ? 0 : $arr_line["vref"];
  $codbemplaca = seqbemplaca($pdo);
  $placaseq = placaseq($pdo,$cobem - 1);
  $t41_obs  = 'Importação de Dados';
  $data = date ('Y/m/d');
  $codusuario = 1;
  $codbemdepre = bensdepre($pdo);
  $tipoaquis = 10003;
  $codtipodepre = benstipodepre($pdo,$codcla);
  $valorresidual = 0;
  $motbaixa = empty($arr_line["motivo"]) ? 13 : $arr_line["motivo"];
  $i++;
 
  $dtbaixa = $arr_line["dtbaixa"];

  

//if ($dtbaixa <= '2018/12/31' and $depart == 1256){
//  echo "<br>\n";
//  echo $dtbaixa;
//  echo "<br>\n";
//  echo $depart;
//  echo "<br>\n";  
//  die('ACHEI');
//}



  if ($dtbaixa <= '2018/12/31'){

    if (isertbens($pdo,$cobem,$codcla,$cgm,$vlraqui,$arr_line["dtaqu"],$arr_line["placa"],$descr,$obs,$depart,$instit))
    {
      $varlog[$i] = $linha;   
      isertbensmater($pdo,$cobem,$arr_line["ntfisc"],$codemp);
      isertbensplaca($pdo,$codbemplaca,$cobem,$placaseq,$t41_obs,$data,$codusuario);
      isertbensdepre($pdo,$codbemdepre,$cobem,$tipoaquis,$codtipodepre,$vlraqui,$valorresidual,$data);
      isertbensbaix($pdo,$cobem,$arr_line["dtbaixa"],$motbaixa,$obs_bensbaix);
      $vlaqui = $vlraqui + $vlaqui;
      $reg++;
    
    }else{    
    
      $varlog2[$i] = $linha;
    }

  }


}


$pFile = null;

$dir=dirname(__FILE__);

$arqlog = $dir . '/arq_log_moveis.txt';
$pfilelog = fopen($arqlog, 'a');
fwrite($pfilelog,  print_r($varlog, TRUE));
fclose($pfilelog);

$arqlog2 = $dir . '/arq_log2_moveis.txt';
$pfilelog = fopen($arqlog2, 'a');
fwrite($pfilelog,  print_r($varlog2, TRUE));
fclose($pfilelog);

echo "<br>\n";
echo 'Total do valor de aquiscao:   ' . number_format($vlaqui,2,",",".");
echo "<br>\n";
echo "Total de registros atualizados: $reg";
echo "<br>\n";
echo "<br>\n";
echo "Arquivos de Logs:  $arqlog    e    $arqlog2";
echo "<br>\n";
echo "<br>\n";
echo 'FIM DA INCLUSAO DE REGISTRO!';
echo "<br>\n";

?>