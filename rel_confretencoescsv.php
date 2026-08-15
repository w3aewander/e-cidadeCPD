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

$di = implode("-", array_reverse(explode("/", $_GET["di"])));
$df = implode("-", array_reverse(explode("/", $_GET["df"])));
$xinst = $_GET["instituicao"];



//RELATÓRIO DE MOVIMENTAÇÃO
function buscaOps($di, $df){    
      $sql = pg_query("SELECT e50_codord from pagordem left join db_usuarios on db_usuarios.id_usuario = pagordem.e50_id_usuario inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join concarpeculiar on concarpeculiar.c58_sequencial = empempenho.e60_concarpeculiar inner join pagordemnota on e71_codord = e50_codord inner join empnota on e71_codnota = e69_codnota where 1=1 and e50_data between '{$di}' and '{$df}' order by e50_codord");
      
      while ($linha = pg_fetch_object($sql)) {
        $resultado[] = $linha;
      }
        
      return $resultado;
}

function buscaOpsInst($inst, $di, $df){    
      $sql = pg_query("SELECT e50_codord from pagordem left join db_usuarios on db_usuarios.id_usuario = pagordem.e50_id_usuario inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join concarpeculiar on concarpeculiar.c58_sequencial = empempenho.e60_concarpeculiar inner join pagordemnota on e71_codord = e50_codord inner join empnota on e71_codnota = e69_codnota where 1=1 and e50_data between '{$di}' and '{$df}' AND e60_instit = {$inst} order by e50_codord");
      
      while ($linha = pg_fetch_object($sql)) {
        $resultado[] = $linha;
      }
        
      return $resultado;
}

function buscaDados($op){
      $sql = pg_query("SELECT * from pagordem left join db_usuarios on db_usuarios.id_usuario = pagordem.e50_id_usuario inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join concarpeculiar on concarpeculiar.c58_sequencial = empempenho.e60_concarpeculiar inner join pagordemnota on e71_codord = e50_codord inner join empnota on e71_codnota = e69_codnota where 1=1 AND e50_codord= {$op} order by e50_codord");
      while ($linha = pg_fetch_object($sql)) {
        $resultado[] = $linha;
      }
        
      return $resultado;
}




function buscaRetencoesPelaOP($op){
    
      $sql = pg_query("SELECT distinct e48_cgm, tabrec.*, retencaotiporec.*, retencaoreceitas.*, e27_empagemov, e27_principal, retencaoreceitasadicionais.*, tiposerviconotafiscal.e18_descricao, retencaoreceitasprodutorrural.*, emptiposervicoobra.* from retencaoreceitas inner join retencaotiporec on retencaotiporec.e21_sequencial = retencaoreceitas.e23_retencaotiporec inner join retencaopagordem on retencaopagordem.e20_sequencial = retencaoreceitas.e23_retencaopagordem inner join tabrec on tabrec.k02_codigo = retencaotiporec.e21_receita inner join retencaotipocalc on retencaotipocalc.e32_sequencial = retencaotiporec.e21_retencaotipocalc inner join pagordem on pagordem.e50_codord = retencaopagordem.e20_pagordem inner join pagordemnota on pagordem.e50_codord = pagordemnota.e71_codord inner join empnota on pagordemnota.e71_codnota = empnota.e69_codnota inner join retencaoempagemov on e23_sequencial = e27_retencaoreceitas left join empagemovslips on e27_empagemov = k107_empagemov left join slipempagemovslips on k107_sequencial = k108_empagemovslips left join retencaoreceitasadicionais on e23_sequencial = e19_retencaoreceitas left join tiposerviconotafiscal on e19_tiposerviconotafiscal = e18_sequencial left join retencaotiporeccgm on e48_retencaotiporec = retencaotiporec.e21_sequencial left join retencaoreceitasprodutorrural on e23_sequencial = e158_retencaoreceitas left join emptiposervicoobra on empnota.e69_numemp = e154_numemp where e20_pagordem = {$op} and e23_ativo = true and e71_anulado = false and e27_principal is true order by e21_sequencial");
      while ($linha = pg_fetch_object($sql)) {
        $resultado[] = $linha;
      }
        
      return $resultado;
      
    
}

function buscaElementoPelaOp($op){
      $sql = pg_query("SELECT *,e53_valor - e53_vlranu as saldo, e53_valor - e53_vlranu - e53_vlrpag as saldo_final from pagordemele inner join pagordem on pagordem.e50_codord = pagordemele.e53_codord inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join orcelemento on orcelemento.o56_codele = pagordemele.e53_codele and orcelemento.o56_anousu = empempenho.e60_anousu inner join empelemento on empelemento.e64_numemp = empempenho.e60_numemp and orcelemento.o56_codele = empelemento.e64_codele where pagordemele.e53_codord = {$op}");

        while ($linha = pg_fetch_object($sql)) {
          $resultado[] = $linha;
        }                
        $elemento = substr($resultado[0]->o56_elemento, 0, 7);
        $selecao = array("3339030", "3339039", "3339036", "3449052"); //oficial
      //$selecao = array("3339030", "3339039", "3339036", "3449052", "3319011"); //teste
        
        
        if(in_array($elemento, $selecao)){
          return true;
        }
        return false;
    } 

function buscaElementoPelaOp2($op){    
      $sql = pg_query("SELECT *,e53_valor - e53_vlranu as saldo, e53_valor - e53_vlranu - e53_vlrpag as saldo_final from pagordemele inner join pagordem on pagordem.e50_codord = pagordemele.e53_codord inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join orcelemento on orcelemento.o56_codele = pagordemele.e53_codele and orcelemento.o56_anousu = empempenho.e60_anousu inner join empelemento on empelemento.e64_numemp = empempenho.e60_numemp and orcelemento.o56_codele = empelemento.e64_codele where pagordemele.e53_codord = {$op}");

      while ($linha = pg_fetch_object($sql)) {
        $resultado[] = $linha;
      }
      return $resultado;
      
      $elemento = substr($resultado[0]->o56_elemento, 0, 7);
      $valor = $resultado[0]->e53_valor;
      $retorno = array();
      $retorno["valor"] = $valor;
      $retorno["elemento"] = $elemento;
      return $retorno;
    }


//Todas as OPs aqui.



$ops = array();
if($_GET["inst"] == "tudo"){
  $buscaOps = buscaOps($di, $df);  
}else{
  $buscaOps = buscaOpsInst($xinst, $di, $df);  
}

foreach ($buscaOps as $op){
  array_push($ops, $op->e50_codord);
}




$ops2 = array();
foreach ($ops as $op){
  $elemento = buscaElementoPelaOp($op);
  if($elemento){
    array_push($ops2, $op);
  }
}



//$ops2 = array("423608");

$cabecalho = "OP;CGM;CNPJ;NOME;ELEMENTO;VALOR OP;VALOR RET;TIPO RET;DATA OP";
$linhona = array();
array_push($linhona, $cabecalho);
foreach ($ops2 as $op) {
  $dados = buscaDados($op);
  $cgm = $dados->z01_numcgm;
  $cnpj = $dados->z01_cgccpf;
  $nome = trim(utf8_encode($dados->z01_nome));
  $nome = str_replace(",", " ", $nome);
  $data = $dados->e50_data;
  
  $elementos = buscaElementoPelaOp2($op);
  $elemento = $elementos["elemento"];
  $xvalorop = $elementos["valor"];
  $retencoes = buscaRetencoesPelaOP($op);
  
  $linha = "";

  if(empty($retencoes)){
    $valorop = $xvalorop;
    $valorrt = "";
    $linha = $op .";". $cgm .";". $cnpj .";". $nome .";". $elemento .";". $valorop .";". $valorrt . ";" . "" . ";". $data;
    array_push($linhona, $linha);
  }else{
    foreach ($retencoes as $retencao) {
      $valorop = ($retencao->e23_valorbase) ? $retencao->e23_valorbase : "";    
      $valorrt = ($retencao->e23_valorretencao) ? $retencao->e23_valorretencao : "";
      $tiporet = trim(utf8_encode($retencao->k02_drecei));
      $linha = $op .";". $cgm .";". $cnpj .";". $nome .";". $elemento .";". $valorop .";". $valorrt .";". $tiporet . ";". $data;
      array_push($linhona, $linha);
    }  
  }


  
}

//testa($linhona); die("Confere");


$arquivo = fopen("dados_retencoes.csv", "w");
foreach ($linhona as $linha) {
  fwrite($arquivo, $linha . "\n");
}
fclose($arquivo);


$file_url = 'dados_retencoes.csv';
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
readfile($file_url); 
exit();
