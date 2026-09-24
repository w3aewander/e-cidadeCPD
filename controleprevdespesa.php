<?php

require ("libs/db_stdlib.php");
require ("libs/db_utils.php");
require ("libs/db_conecta.php");
include ("libs/db_sessoes.php");
include ("libs/db_usuariosonline.php");
include ("dbforms/db_funcoes.php");


/*
include ("classes/db_orcdotacao_classe.php");
include ("classes/db_orcdotacaocontr_classe.php");
include ("classes/db_orcelemento_classe.php");
include ("classes/db_orcparametro_classe.php");
include ("classes/db_orcorgao_classe.php");
include ("classes/db_orcunidade_classe.php");
include ("classes/db_orcfuncao_classe.php");
include ("classes/db_orcsubfuncao_classe.php");
include ("classes/db_orcprograma_classe.php");
include ("classes/db_orcprojativ_classe.php");

require("libs/db_app.utils.php");
include("dbforms/db_classesgenericas.php");

include ("classes/db_orctiporec_classe.php");
require ("libs/db_liborcamento.php");
*/

function testa($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}



function buscaOrgaos(){
	$inst = db_getsession("DB_instit");
	$sql = pg_query("SELECT o40_orgao, o40_descr FROM orcorgao WHERE o40_instit = {$inst} AND o40_anousu = 2026 ORDER BY o40_orgao");
	$resultado = pg_fetch_all($sql);
	return $resultado;
}


function buscaValor($orgao, $ano){	
	$sql = pg_query("SELECT * FROM controlepreorcdesp WHERE orgao = {$orgao} AND ano = {$ano}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0];
}

/*

\d orcdotacao
(o58_anousu, o58_orgao) REFERENCES orcorgao(o40_anousu, o40_orgao

SELECT o40_orgao, o40_descr FROM orcorgao WHERE o40_instit = 1 AND o40_anousu = 2026 ORDER BY o40_orgao;

*/




$anousu = db_getsession("DB_anousu");
$orgaos = buscaOrgaos();

if($_POST){
	$orgao = $_POST["orgaos"];
	$ano = $_POST["exercicios"];
	$valor = str_replace(",", ".", $_POST["totaldespesa"]);


	$verifica = buscaValor($orgao, $ano);	
	

	if($verifica){
		$id = $verifica["id"];
		pg_query("UPDATE controlepreorcdesp SET valor = {$valor} WHERE id = {$id}");
	}else{
		pg_query("INSERT INTO controlepreorcdesp(orgao, ano, valor) VALUES({$orgao}, {$ano}, {$valor})");
	}
	header("Location: controleprevdespesa.php?msg=me1");
	
}


if($_GET["msg"]){
	echo "<script>alert('Valores salvos com sucesso.');</script>";
}

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
	<fieldset style="width: 650px;">
		<legend>Controle de Despesa</legend>
	
	<form action="#" method="post">
<table>
	<tr>
		<td><b>Órgão:</b></td>
		<td>
			<select id="orgaos" name="orgaos">
				<option value="0">Selecione um órgão</option>
				<?php foreach ($orgaos as $orgao): ?>
					<option value="<?=$orgao["o40_orgao"]?>"><?=$orgao["o40_orgao"] . " - " . $orgao["o40_descr"]?></option>
				<?php endforeach ?>
			</select>
		</td>
	</tr>

	<tr>
		<td><b>Exercício:</b></td>
		<td>
			<select id="exercicios" name="exercicios">
				<option value="0">Selecione um exercício</option>
				<option value="2026">2026</option>
				<option value="2027">2027</option>
				<option value="2028">2028</option>
				<option value="2029">2029</option>
			</select>
		</td>
	</tr>

	
		<tr id="td">
			<td><b>Total da Despesa:</b></td>
			<td><input type="text" name="totaldespesa" id="totaldespesa" readonly style="background-color: #DEB887;"></td>
		</tr>
	
	
</table>
<input type="submit" name="insere" value="Salvar">
</form>
</fieldset>
</center>

<script>
	
var es = document.getElementById("exercicios");
var og = document.getElementById("orgaos");

es.addEventListener("change", function () {
  if (es.value == 0 || og.value == 0) {
    alert("É obrigatório escolher um órgão e um ano.");
    return false;
  }

  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function () {
    if ((xhr.readyState === 4) && (xhr.status === 200)) {
      document.getElementById("td").innerHTML = xhr.responseText;
      
      var input = document.getElementById("totaldespesa");
      if (input) {
        input.addEventListener("input", function () {
          var valor = input.value;
          var regexValido = /^[0-9,]*$/;

          if (!regexValido.test(valor)) {
            alert("Digite apenas números e vírgula!");
            input.value = valor.replace(/[^0-9,]/g, "");
          }
        });
      }
    }
  };
  xhr.open("GET", "controleprevdespesaajax.php?orgao=" + og.value + "&ano=" + es.value, true);
  xhr.send(null);
}, false);




  orgaos.addEventListener('change', function() {
  	var orgaos = document.getElementById('orgaos');
var exercicios = document.getElementById('exercicios');
var totalDespesa = document.getElementById('totaldespesa');
    if (exercicios.value !== "0") {
      totalDespesa.value = '';
      exercicios.value = "0";
    }
  });

</script>

</body>
</html>



<?
	db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit")); 
?>