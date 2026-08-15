<?php
//error_reporting(E_ALL); 
//ini_set('display_errors', '1');

require ("libs/db_stdlib.php");
require ("libs/db_utils.php");
require ("libs/db_conecta.php");
include ("libs/db_sessoes.php");
include ("libs/db_usuariosonline.php");
include ("dbforms/db_funcoes.php");

/*
function buscarValoresDotacao($orgao, $ano, $unidade, $funcao, $subfuncao, $programa, $projativ, $codele){
	$sql = pg_query("SELECT sum(o58_valor) as o58_valor FROM orcdotacao WHERE o58_orgao = {$orgao} AND o58_anousu = {$ano} AND o58_unidade = {$unidade} AND o58_funcao = {$funcao} AND o58_subfuncao = {$subfuncao} AND o58_programa = {$programa} AND o58_projativ = {$projativ} AND o58_codele = {$codele}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["o58_valor"];
}
*/

function buscaCodElemento($elemento, $ano){
	$sql = pg_query("SELECT o56_elemento FROM orcelemento WHERE o56_codele = {$elemento} AND o56_anousu = {$ano}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["o56_elemento"];
}

/*
function buscarValoresDotacao($orgao, $ano, $unidade, $funcao, $subfuncao, $programa, $projativ, $codele){
	$sql = pg_query("SELECT sum(o58_valor::double precision) as o58_valor FROM previadespesas WHERE o58_orgao = '{$orgao}' AND o58_anousu = '{$ano}' AND o58_unidade = '{$unidade}' AND o58_funcao = '{$funcao}' AND o58_subfuncao = '{$subfuncao}' AND o58_programa = '{$programa}' AND o58_projativ = '{$projativ}' AND o56_elemento = '{$codele}'");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["o58_valor"];
}
*/

function buscarValoresDotacao($orgao, $ano){
	$sql = pg_query("SELECT sum(o58_valor::double precision) as o58_valor FROM previadespesas WHERE o58_orgao = '{$orgao}' AND o58_anousu = '{$ano}'");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["o58_valor"];
}

$orgao = $_GET["orgao"];
$ano = $_GET["ano"];
$unidade = $_GET["unidade"];
$funcao = $_GET["funcao"];
$subfuncao = $_GET["subfuncao"];
$programa = $_GET["programa"];
$projativ = $_GET["projativ"];
//$codele = $_GET["elemento"];
$codele = (strlen($_GET["elemento"]) < 6 ) ? buscaCodElemento($_GET["elemento"], $ano) : $_GET["elemento"];

$zvalor = buscarValoresDotacao($orgao, $ano, $unidade, $funcao, $subfuncao, $programa, $projativ, $codele);

?>

<?php if($zvalor) : ?>
	<td><b>Somatória das Dotações:</b></td>
    <td>      
      <input type="text" name="sdd" id="sdd" style="margin-left: 10px;background-color: #DEB887;" readonly value="<?=$zvalor?>">
    </td>
<?php else: ?>
	<td><b><b>Somatória das Dotações:</b></b></td>
  <td>
   	<input type="text" name="sdd" id="sdd" style="margin-left: 10px;background-color: #DEB887;" readonly>
  </td>
<?php endif; ?>

