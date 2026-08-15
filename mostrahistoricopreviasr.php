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
	$sql = pg_query("SELECT iddaprevia FROM historicopreviareceitas WHERE id = {$id}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["iddaprevia"];
}

function buscaDadosInclusao($iddaprevia){
	$sql = pg_query("SELECT * FROM historicopreviareceitas WHERE iddaprevia = {$iddaprevia} AND acaousuario = 'incluir' ");
	$resultado = pg_fetch_all($sql);
	return $resultado[0];
}

function buscaDadosAlteracao($iddaprevia){
	$sql = pg_query("SELECT * FROM historicopreviareceitas WHERE iddaprevia = {$iddaprevia} AND acaousuario = 'alterar' ORDER BY id");
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
    <td nowrap title="Exercício"><b>Exercício:</b></td>
    <td>
      <input type="text" size="6" name="" readonly value="<?=$dadosinclusao['o70_anousu']?>">
      <span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    </td>
  </tr>

  <tr>
    <td nowrap title="Código Reduzido"><b>Código Reduzido:</b></td>
    <td>
      <input type="text" size="6" name="" readonly value="<?=$dadosinclusao['o70_codrec']?>">
      <span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
    </td>
  </tr>

  <tr>
      <td nowrap title="Código Fonte"><b>Código Fonte:</b></td>
      <td>
        <input type="text" size="22" name="" readonly value="<?=$dadosinclusao['o50_estrutreceita']?>">
        <input type="text" size="55" name="" readonly value="<?=$dadosinclusao['o57_descr']?>">
      <span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
      </td>
    </tr>
    <?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
    <tr>
      <td> </td>
      <td>        
        <input type="text" size="22" name="" readonly value="<?=$alterado['o50_estrutreceita']?>">
        <input type="text" size="55" name="" readonly value="<?=$alterado['o57_descr']?>">
        <span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
      </td>
    </tr>
    <?php endforeach; ?>

    <tr>
      <td nowrap title="Código do Recurso"><b>Código do Recurso:</b></td>
      <td>
        <input type="text" size="4" name="" readonly value="<?=$dadosinclusao['o70_codigo']?>">
        <input type="text" size="30" name="" readonly value="<?=$dadosinclusao['o15_descr']?>">
      <span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
      </td>
    </tr>
    <?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
    <tr>
      <td> </td>
      <td>        
        <input type="text" size="4" name="" readonly value="<?=$alterado['o70_codigo']?>">
        <input type="text" size="30" name="" readonly value="<?=$alterado['o15_descr']?>">
        <span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
      </td>
    </tr>
    <?php endforeach; ?>

    <tr>
      <td nowrap title="Valor Previsto"><b>Valor Previsto:</b></td>
      <td>
        <input type="text" size="15" name="" readonly value="<?=$dadosinclusao['o70_valor']?>">
        <span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
      </td>
    </tr>
    <?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
    <tr>
      <td> </td>
      <td>        
        <input type="text" size="15" name="" readonly value="<?=$alterado['o70_valor']?>">        
        <span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
      </td>
    </tr>
    <?php endforeach; ?>


    <tr>
      <td nowrap title="Receita Lançada"><b>Receita Lançada:</b></td>
      <td>
        <?php $lancado = ($dadosinclusao["o70_reclan"] == false) ? "Não" : "Sim"; ?>
        <input type="text" size="15" name="" readonly value="<?=$lancado?>">
        <span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
      </td>
    </tr>
    <?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"]))); $lancado = ($dadosalteracao["o70_reclan"] == false) ? "Não" : "Sim"; ?>
    <tr>
      <td> </td>
      <td>        
        <input type="text" size="15" name="" readonly value="<?=$lancado?>">        
        <span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
      </td>
    </tr>
    <?php endforeach; ?>


    <tr>
      <td nowrap title="Característica Peculiar"><b>Característica Peculiar:</b></td>
      <td>
        <input type="text" size="4" name="" readonly value="<?=$dadosinclusao['o70_concarpeculiar']?>">
        <input type="text" size="30" name="" readonly value="<?=$dadosinclusao['c58_descr']?>">
      <span><?="Incluído por {$dadosinclusao['loginusuario']} em {$datainclusao}"?></span>
      </td>
    </tr>
    <?php foreach ($dadosalteracao as $alterado) : $dataalteracao = implode("/", array_reverse(explode("-", $alterado["datausuario"])));  ?>
    <tr>
      <td> </td>
      <td>        
        <input type="text" size="4" name="" readonly value="<?=$alterado['o70_concarpeculiar']?>">
        <input type="text" size="30" name="" readonly value="<?=$alterado['c58_descr']?>">
        <span><?="Alterado por {$alterado['loginusuario']} em {$dataalteracao}"?></span>
      </td>
    </tr>
    <?php endforeach; ?>
</table> 
  
 
</center>

</body>
</html>