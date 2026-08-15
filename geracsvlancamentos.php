<?php

require(modification("libs/db_stdlib.php"));
require(modification("libs/db_conecta.php"));
include(modification("libs/db_sessoes.php"));
include(modification("libs/db_usuariosonline.php"));
include(modification("dbforms/db_funcoes.php"));
include(modification("classes/db_conlancamval_classe.php"));
include(modification("classes/db_conlancamdig_classe.php"));
db_postmemory($HTTP_POST_VARS);
parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);

$clconlancamval = new cl_conlancamval;
$clconlancamval->rotulo->label("c69_sequen");
$clconlancamval->rotulo->label("c69_codlan");
$clconlancamval->rotulo->label("c69_codhist");
$clconlancamdig = new cl_conlancamdig;
$clconlancamdig->rotulo->label("c78_chave");

$anousu = db_getsession("DB_anousu");
$iInstituicao = db_getsession("DB_instit");

$campoclicado = $_GET["campo"];
$valorinserido = $_GET["valor"];


$xql = "select * from (select c69_sequen, c69_codlan, c69_codhist as db_c69_codhist, c50_descr, c69_credito, c69_debito, c69_valor, c69_anousu as db_c69_anousu, c69_data from conlancamval left join conhist on c50_codhist = c69_codhist inner join conlancam on c70_codlan = c69_codlan left join conlancaminstit on c02_codlan = c70_codlan left outer join conlancamdig on c78_codlan = c70_codlan where c02_instit = {$iInstituicao} and c69_anousu = {$anousu} order by c69_sequen) as x where {$campoclicado}::text ILIKE '{$valorinserido}%' order by {$campoclicado}";


$sql = pg_query($xql);
$resultado = pg_fetch_all($sql);

$cabecalho = utf8_encode("Sequen;Cód Lan;Descrição;Conta Crédito;Conta Débito;Valor;Data Lanç");
//$valorestela = fopen("tmp/lancamentosescrituracaocontabil.csv", "w"); fwrite($valorestela, $cabecalho); fclose($valorestela);



$arquivo = fopen("tmp/lancamentosescrituracaocontabil.csv", "w");
fwrite($arquivo, $cabecalho . "\n");
foreach ($resultado as $linha) {
  $valor = number_format($linha["c69_valor"], 2, ',', '.');
  $data = implode("/", array_reverse(explode("-", $linha["c69_data"])));
  $info = $linha["c69_sequen"] . ";" . $linha["c69_codlan"] . ";" . utf8_encode($linha["c50_descr"]) . ";" . $linha["c69_credito"] . ";" . $linha["c69_debito"] . ";" . $valor . ";" . $data;
  
  fwrite($arquivo, $info . "\r\n");    
}
fclose($arquivo);
 
$file_url = 'tmp/lancamentosescrituracaocontabil.csv';
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
readfile($file_url);

unlink($file_url);
exit();