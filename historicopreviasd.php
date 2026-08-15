<?php

require ("libs/db_stdlib.php");
require ("libs/db_utils.php");
require ("libs/db_conecta.php");
include ("libs/db_sessoes.php");
include ("libs/db_usuariosonline.php");
include ("dbforms/db_funcoes.php");
require("libs/db_app.utils.php");
include("dbforms/db_classesgenericas.php");

$ano = db_getsession("DB_anousu");


$sql = "SELECT id, o58_anousu, o50_estrutdespesa, o58_coddot, o58_instit, nomeinst, o58_orgao, o40_descr, o58_unidade, o41_descr, o58_funcao, o52_descr, o58_subfuncao, o53_descr,
	o58_programa, o54_descr, o58_projativ, o55_descr, o56_elemento, o56_descr, o58_codigo, o15_descr, o58_localizadorgastos, o11_descricao, o58_concarpeculiar, c58_descr,
	o58_valor, loginusuario, acaousuario, iddaprevia FROM historicopreviadespesas WHERE o58_anousu = '{$ano}' ORDER BY iddaprevia, id";

$funcao_js = "mostra";

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
<button><a href="preorcdesp.php">Voltar</a></button>
<center>
	<div style="width: 90%;margin:0 auto;overflow: auto">
		<table height="70%" border="0"  align="center" cellspacing="0" bgcolor="#CCCCCC">
  			<tr><td height="63" align="center" valign="top"></td></tr>
  			<tr><td align="center" valign="top"><?php db_lovrot($sql,15,"()","", $funcao_js); ?></td></tr>
		</table>
	</div>
</center>

</body>
</html>
<script>
	function mostra(){
		return true;
	}
	var campousuario = document.getElementsByName("loginusuario");
	var campoacao = document.getElementsByName("acaousuario");
	var campoidprevia = document.getElementsByName("iddaprevia");
	campousuario[0].value = "Usuário";
	campoacao[0].value = "Ação";
	campoidprevia[0].value = "Id da Prévia";
	
	var tabela = document.getElementById("TabDbLov");
	var linhas = tabela.getElementsByTagName("tr");

	for (var i = 2; i < linhas.length - 1; i++) {
		linhas[i].setAttribute("onclick", "mostraid("+linhas[i].firstChild.innerText+")");
	}

	function mostraid(id){
		js_OpenJanelaIframe('','dados','mostrahistoricopreviasd.php?id='+id ,'Dados da Prévia',true);
	}

</script>
<?php db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit")); ?>