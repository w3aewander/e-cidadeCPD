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




$sql = "SELECT id, o70_anousu, o70_codrec, o50_estrutreceita, o57_descr, o70_codigo, o15_descr, o70_valor, o70_reclan, o70_concarpeculiar, c58_descr, o70_instit, loginusuario, acaousuario,iddaprevia FROM historicopreviareceitas WHERE o70_anousu = '{$ano}' ORDER BY iddaprevia, id ";
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
<button><a href="preorcrece.php">Voltar</a></button>
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
		js_OpenJanelaIframe('','dados','mostrahistoricopreviasr.php?id='+id ,'Dados da Prévia',true);
	}

</script>
<?php db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit")); ?>