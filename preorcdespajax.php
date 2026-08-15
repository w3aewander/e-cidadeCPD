<?php
//error_reporting(E_ALL); 
//ini_set('display_errors', '1');

require ("libs/db_stdlib.php");
require ("libs/db_utils.php");
require ("libs/db_conecta.php");
include ("libs/db_sessoes.php");
include ("libs/db_usuariosonline.php");
include ("dbforms/db_funcoes.php");

function buscaValorDentro($orgao, $ano){		
	$sql = pg_query("SELECT * FROM controlepreorcdesp WHERE orgao = {$orgao} AND ano = {$ano}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0];
}

function buscaValoresDotacao($orgao, $ano){		
	$inst = db_getsession("DB_instit");
	$sql = pg_query("SELECT sum(o58_valor::double precision) as o58_valor FROM previadespesas WHERE o58_orgao = '{$orgao}' AND o58_anousu = '{$ano}' AND o58_instit = '{$inst}' ");
	$resultado = pg_fetch_all($sql);
	
	//var_dump($resultado[0]);
	return $resultado[0]["o58_valor"];
	//return floatval(str_replace(',', '.', $resultado[0]["o58_valor"]));
}

$orgao = $_GET["orgao"];
$ano = $_GET["ano"];

$buscaValorFixo = buscaValorDentro($orgao, $ano);
$buscaValorDotacao = buscaValoresDotacao($orgao, $ano);


$valortotafixado = $buscaValorFixo["valor"];
$somatoriodotacaoes = $buscaValorDotacao;



?>


	<td>
		<b>Valor Total Fixado:</b>
	</td>

   <td>
    	<input type="text" name="vtf" id="vtf" style="margin-right: 10px;background-color: #DEB887;" readonly value=<?=$valortotafixado?>>
    	<b>Somatória das Dotações:</b>

      <input type="text" name="sdd" id="sdd" style="margin-left: 10px;background-color: #DEB887;" readonly value="<?=$somatoriodotacaoes?>">
    </td>
  

