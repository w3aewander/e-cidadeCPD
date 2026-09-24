<?php

require ("libs/db_stdlib.php");
require ("libs/db_utils.php");
require ("libs/db_conecta.php");
include ("libs/db_sessoes.php");
include ("libs/db_usuariosonline.php");
include ("dbforms/db_funcoes.php");
require("libs/db_app.utils.php");
include("dbforms/db_classesgenericas.php");


function testa($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}

function retornaIdPrevia($id){
	$sql = pg_query("SELECT iddaprevia FROM historicopreviadespesas WHERE id = {$id}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["iddaprevia"];
}

function buscaDadosInclusao($iddaprevia){
	$sql = pg_query("SELECT * FROM historicopreviadespesas WHERE iddaprevia = {$iddaprevia} AND acaousuario = 'incluir' ");
	$resultado = pg_fetch_all($sql);
	return $resultado[0];
}

function buscaDadosAlteracao($iddaprevia){
	$sql = pg_query("SELECT * FROM historicopreviadespesas WHERE iddaprevia = {$iddaprevia} AND acaousuario = 'alterar' ORDER BY id");
	$resultado = pg_fetch_all($sql);
	return $resultado;
}

$id = $_GET["id"];
$idprevia = retornaIdPrevia($id);

$dadosinclusao = buscaDadosInclusao($idprevia);
$datainclusao = implode("/", array_reverse(explode("-", $dadosinclusao["datausuario"])));

$dadosalteracao = buscaDadosAlteracao($idprevia);


 
?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="margin-top: 30px">

<center>
<table border="0">
 <tr>
  <td>
  <fieldset><legend><b>Dotação</b></legend>
  <table>
  	<tr>
    	<td nowrap title="Exercício"><b>Exercício:</b></td>
    	<td>
    		<input type="text" size="4" name="" readonly value="<?=$dadosinclusao['o58_anousu']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>
  	</tr>  	

  	<tr>
    	<td nowrap title="Estrutural"><b>Estrutural Despesa:</b></td>
    	<td>
    		<input type="text" size="70" name="" readonly value="<?=$dadosinclusao['o50_estrutdespesa']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>
  	</tr>
    
  	<tr>
    	<td nowrap title="Reduzido"><b>Reduzido:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o58_coddot']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>
  	</tr>
  
  	<tr>
  		<td nowrap title="Instituição"><b>Instituição:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o58_instit']?>">
    		<input type="text" size="55" name="" readonly value="<?=$dadosinclusao['nomeinst']?>">
    	<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>
  	</tr>
  	<?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
  	<tr>
    	<td> </td>
    	<td>    		
    		<input type="text" size="11" name="" readonly value="<?=$alterado['o58_instit']?>">
    		<input type="text" size="55" name="" readonly value="<?=$alterado['nomeinst']?>">
    		<span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
    	</td>
  	</tr>
  	<?php endforeach; ?>

  	<tr>
  		<td nowrap title="Código Órgão"><b>Código Órgão:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o58_orgao']?>">
    		<input type="text" size="55" name="" readonly value="<?=$dadosinclusao['o40_descr']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>    
  	</tr>
  	<?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
  	<tr>
    	<td> </td>
    	<td>    		
    		<input type="text" size="11" name="" readonly value="<?=$alterado['o58_orgao']?>">
    		<input type="text" size="55" name="" readonly value="<?=$alterado['o40_descr']?>">
    		<span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
    	</td>
  	</tr>
  	<?php endforeach; ?>

	<tr>
  		<td nowrap title="Código Unidade"><b>Código Unidade:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o58_unidade']?>">
    		<input type="text" size="55" name="" readonly value="<?=$dadosinclusao['o41_descr']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>    
  	</tr>
  	<?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
  	<tr>
    	<td> </td>
    	<td>    		
    		<input type="text" size="11" name="" readonly value="<?=$alterado['o58_unidade']?>">
    		<input type="text" size="55" name="" readonly value="<?=$alterado['o41_descr']?>">
    		<span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
    	</td>
  	</tr>
  	<?php endforeach; ?>

  	<tr>
  		<td nowrap title="Código da Função"><b>Código da Função:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o58_funcao']?>">
    		<input type="text" size="55" name="" readonly value="<?=$dadosinclusao['o52_descr']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>    
  	</tr>
  	<?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
  	<tr>
    	<td> </td>
    	<td>    		
    		<input type="text" size="11" name="" readonly value="<?=$alterado['o58_funcao']?>">
    		<input type="text" size="55" name="" readonly value="<?=$alterado['o52_descr']?>">
    		<span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
    	</td>
  	</tr>
  	<?php endforeach; ?>

  	<tr>
  		<td nowrap title="SubFunção"><b>SubFunção:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o58_subfuncao']?>">
    		<input type="text" size="55" name="" readonly value="<?=$dadosinclusao['o53_descr']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>
  	</tr>
  	<?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
  	<tr>
    	<td> </td>
    	<td>    		
    		<input type="text" size="11" name="" readonly value="<?=$alterado['o58_subfuncao']?>">
    		<input type="text" size="55" name="" readonly value="<?=$alterado['o53_descr']?>">
    		<span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
    	</td>
  	</tr>
  	<?php endforeach; ?>

  	<tr>
  		<td nowrap title="Programas Orçamento"><b>Programas Orçamento:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o58_programa']?>">
    		<input type="text" size="55" name="" readonly value="<?=$dadosinclusao['o54_descr']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>
  	</tr>
  	<?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
  	<tr>
    	<td> </td>
    	<td>    		
    		<input type="text" size="11" name="" readonly value="<?=$alterado['o58_programa']?>">
    		<input type="text" size="55" name="" readonly value="<?=$alterado['o54_descr']?>">
    		<span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
    	</td>
  	</tr>
  	<?php endforeach; ?>

  	<tr>
  		<td nowrap title="Projetos / Atividades"><b>Projetos / Atividades:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o58_projativ']?>">
    		<input type="text" size="55" name="" readonly value="<?=$dadosinclusao['o55_descr']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>
  	</tr>
  	<?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
  	<tr>
    	<td> </td>
    	<td>    		
    		<input type="text" size="11" name="" readonly value="<?=$alterado['o58_projativ']?>">
    		<input type="text" size="55" name="" readonly value="<?=$alterado['o55_descr']?>">
    		<span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
    	</td>
  	</tr>
  	<?php endforeach; ?>

  	<tr>
  		<td nowrap title="Elemento"><b>Elemento:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o56_elemento']?>">
    		<input type="text" size="55" name="" readonly value="<?=$dadosinclusao['o56_descr']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>
  	</tr>
  	<?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
  	<tr>
    	<td> </td>
    	<td>    		
    		<input type="text" size="11" name="" readonly value="<?=$alterado['o56_elemento']?>">
    		<input type="text" size="55" name="" readonly value="<?=$alterado['o56_descr']?>">
    		<span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
    	</td>
  	</tr>
  	<?php endforeach; ?>

  	<tr>
  		<td nowrap title="Tipo de Recurso"><b>Tipo de Recurso:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o58_codigo']?>">
    		<input type="text" size="55" name="" readonly value="<?=$dadosinclusao['o15_descr']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>
  	</tr>
  	<?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
  	<tr>
    	<td> </td>
    	<td>    		
    		<input type="text" size="11" name="" readonly value="<?=$alterado['o58_codigo']?>">
    		<input type="text" size="55" name="" readonly value="<?=$alterado['o15_descr']?>">
    		<span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
    	</td>
  	</tr>
  	<?php endforeach; ?>

  	<tr>
  		<td nowrap title="Localizador dos Gastos"><b>Localizador dos Gastos:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o58_localizadorgastos']?>">
    		<input type="text" size="55" name="" readonly value="<?=$dadosinclusao['o11_descricao']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>
  	</tr>
  	<?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
  	<tr>
    	<td> </td>
    	<td>    		
    		<input type="text" size="11" name="" readonly value="<?=$alterado['o58_localizadorgastos']?>">
    		<input type="text" size="55" name="" readonly value="<?=$alterado['o11_descricao']?>">
    		<span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
    	</td>
  	</tr>
  	<?php endforeach; ?>

  	<tr>
  		<td nowrap title="C.Peculiar/C. Aplicação"><b>C.Peculiar/C. Aplicação:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o58_concarpeculiar']?>">
    		<input type="text" size="55" name="" readonly value="<?=$dadosinclusao['c58_descr']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>
  	</tr>
  	<?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
  	<tr>
    	<td> </td>
    	<td>    		
    		<input type="text" size="11" name="" readonly value="<?=$alterado['o58_concarpeculiar']?>">
    		<input type="text" size="55" name="" readonly value="<?=$alterado['c58_descr']?>">
    		<span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
    	</td>
  	</tr>
  	<?php endforeach; ?>

  	<tr>
    	<td nowrap title="Previsão"><b>Previsão:</b></td>
    	<td>
    		<input type="text" size="11" name="" readonly value="<?=$dadosinclusao['o58_valor']?>">
    		<span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    	</td>
  	</tr>
  	<?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
  	<tr>
    	<td> </td>
    	<td>    		
    		<input type="text" size="11" name="" readonly value="<?=$alterado['o58_valor']?>">    		
    		<span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
    	</td>
  	</tr>
  	<?php endforeach; ?>

  </table>
  </fieldset>
  </td>
  </tr>
  </table>
</center>

</body>
</html>