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

function zbuscaCodElemento($elemento, $ano){
	$sql = pg_query("SELECT o56_elemento FROM orcelemento WHERE o56_codele = {$elemento} AND o56_anousu = {$ano}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["o56_elemento"];
}

/*
function zbuscarValoresDotacao($orgao, $ano, $unidade, $funcao, $subfuncao, $programa, $projativ, $codele){
	$sql = pg_query("SELECT sum(o58_valor::double precision) as o58_valor FROM previadespesas WHERE o58_orgao = '{$orgao}' AND o58_anousu = '{$ano}' AND o58_unidade = '{$unidade}' AND o58_funcao = '{$funcao}' AND o58_subfuncao = '{$subfuncao}' AND o58_programa = '{$programa}' AND o58_projativ = '{$projativ}' AND o56_elemento = '{$codele}'");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["o58_valor"];
}
*/

function zbuscaValorDentro($orgao, $ano){	
	$sql = pg_query("SELECT * FROM controlepreorcdesp WHERE orgao = {$orgao} AND ano = {$ano}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["valor"];
}




function zbuscaValoresDotacao($orgao, $ano){
	$sql = pg_query("SELECT sum(o58_valor::double precision) as o58_valor FROM previadespesas WHERE o58_orgao = '{$orgao}' AND o58_anousu = '{$ano}'");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["o58_valor"];
}

/*
function buscaValoresPrevistos($orgao, $unidade, $funcao, $subfuncao, $programa, $projativ, $codele){
	//var_dump($ano, $orgao, $unidade, $funcao, $subfuncao, $programa, $projativ, $codele); die("Confere");
	$sql = pg_query("SELECT * FROM valoresprevistos WHERE orgao = {$orgao} AND unidade = {$unidade} AND funcao = {$funcao} AND subfuncao = {$subfuncao} AND programa = {$programa} AND projativ = {$projativ} AND elemento = '{$codele}'");
	$resultado = pg_fetch_all($sql);
	return $resultado;
}
*/
function buscaValoresPrevistos($orgao, $unidade, $funcao, $subfuncao, $programa, $projativ, $codele, $recurso, $localizador, $cpeculiar){
	//var_dump($ano, $orgao, $unidade, $funcao, $subfuncao, $programa, $projativ, $codele); die("Confere");
	var_dump("SELECT * FROM valoresprevistos WHERE orgao = {$orgao} AND unidade = {$unidade} AND funcao = {$funcao} AND subfuncao = {$subfuncao} AND programa = {$programa} AND projativ = {$projativ} AND elemento = '{$codele}' AND recurso = {$recurso} AND localizador = {$localizador} AND cpeculiar = '{$cpeculiar}'");
	$sql = pg_query("SELECT * FROM valoresprevistos WHERE orgao = {$orgao} AND unidade = {$unidade} AND funcao = {$funcao} AND subfuncao = {$subfuncao} AND programa = {$programa} AND projativ = {$projativ} AND elemento = '{$codele}' AND recurso = {$recurso} AND localizador = {$localizador} AND cpeculiar = '{$cpeculiar}'");
	$resultado = pg_fetch_all($sql);
	return $resultado;
}

$orgao = $_GET["orgao"];
$ano = $_GET["ano"];


$unidade = $_GET["unidade"];
$funcao = $_GET["funcao"];
$subfuncao = $_GET["subfuncao"];
$programa = $_GET["programa"];
$projativ = $_GET["projativ"];
$codele = $_GET["elemento"];

$recurso = $_GET["recurso"];
$localizador = $_GET["localizador"];
$cpeculiar = $_GET["cpeculiar"];

$zvalorfixado = zbuscaValorDentro($orgao, $ano);
$zsomatorio = zbuscaValoresDotacao($orgao, $ano);
//$previsoes = buscaValoresPrevistos($orgao, $unidade, $funcao, $subfuncao, $programa, $projativ, $elemento);
$previsoes = buscaValoresPrevistos($orgao, $unidade, $funcao, $subfuncao, $programa, $projativ, $elemento, $recurso, $localizador, $cpeculiar);

?>

<input type="hidden" name="zxvalorfixado" id="zxvalorfixado" value="<?=$zvalorfixado?>">
<input type="hidden" name="zsomatorio" id="zsomatorio" value="<?=$zsomatorio?>">
<input type="hidden" name="zvalorantigo" id="zvalorantigo">
<?php foreach ($previsoes as $valor) : ?>
	<input type="hidden" name="v<?=$valor['ano']?>" id="v<?=$valor['ano']?>" value="<?=$valor['valor']?>">

<?php endforeach; ?>




