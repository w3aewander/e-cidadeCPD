<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao 2 da
 *  Licenca como (a seu criterio) qualquer versao mais nova.
 *
 *  Este programa e distribuido na expectativa de ser util, mas SEM
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 *  detalhes.
 *
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 *  junto com este programa; se nao, escreva para a Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *  02111-1307, USA.
 *
 *  Copia da licenca no diretorio licenca/licenca_en.txt
 *                                licenca/licenca_pt.txt
 */

require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");

db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));

?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC>
<form class="container" name="form1" method="post" id="form1">
	<fieldset>
  	<legend>Cadastro de Contas Fora da Conciliacao</legend>
    <table class="form-container">
<tr><td title="banco"><b>Banco</b></td><td><?php db_text("banco", 10, 10) ?></td></tr>      
<tr><td title="Agencia"><b>Agencia</b></td><td><?php db_text("agencia", 10, 10) ?><?php db_text("digagencia", 2, 2) ?></td></tr>
      <tr><td title="Conta"><b>Conta</b></td><td><?php db_text("conta", 10, 10) ?><?php db_text("digconta", 2, 2) ?></td></tr>
      <tr>
        <td><b>Tipo:</b></td>
        <td>
          <?php
            $aTipoConta = array('Conta Corrente' => "Corrente", 'Conta Aplicacao' => "Aplicacao");
            db_select("tipoconta", $aTipoConta, true, 1);
          ?>
        </td>
      </tr>
    </table>
	</fieldset>
 <input name="pesquisar" type="button" onclick='js_abre("pesquisar");'  value="Pesquisar">
	<input name="salvar" type="button" onclick='js_abre("salvar");'  value="Salvar">
  <input name="excluir" type="button" onclick='js_abre("excluir");'  value="Excluir">
</form>

<br><center><table id="tabelaBanco"></table></center>

</body>
</html>
<script>

function js_abre(acao){
	var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
     document.getElementById("tabelaBanco").innerHTML = this.responseText;
    }
  };
	xhttp.open("POST", "cai4_cadastrocontaforaconciliacao002.php", true);
	 xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

	xhttp.send(
		"db_banco="+document.getElementById('db_banco').value+
		"&db_agencia="+document.getElementById('db_agencia').value+
		"&db_digagencia="+document.getElementById('db_digagencia').value+
		"&db_conta="+document.getElementById('db_conta').value+
		"&db_digconta="+document.getElementById('db_digconta').value+
		"&db_tipoconta="+document.getElementById('tipoconta').value+
	        "&"+acao+"=1");
}
</script>

