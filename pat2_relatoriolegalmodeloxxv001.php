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
$dtFinal_dia = date("d", db_getsession("DB_datausu"));
$dtFinal_mes = date("m", db_getsession("DB_datausu"));
$dtFinal_ano = date("Y", db_getsession("DB_datausu"));
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
<form class="container" name="form1" method="post">
	<fieldset>
  	<legend>Demonstrativo de Im&oacute;veis nao Inventariados</legend>
    <table class="form-container">
      <tr><td title="Exercicio"><b>Exerc&iacute;cio</b></td><td><?php db_text("exercicio", 4, 4, $db_nome = "", $dbh_nome = "", $validacao = 1) ?></td></tr>
      <tr><td title="Matricula"><b>Matr&iacute;cula</b></td><td><?php db_text("matricula", 20, 20, $db_nome = "", $dbh_nome = "", $validacao = 1) ?></td></tr>
      <tr><td title="Nome Responsavel"><b>Nome Respons&aacute;vel</b></td><td><?php db_text("nome", 60, 60)  ?></td></tr>
      <tr>
        <td><b>Tipo:</b></td>
        <td>
          <?php
            $aTipoRelatorio = array( 1 => "PDF");


            db_select("iTipoRelatorio", $aTipoRelatorio, true, 1);
          ?>
        </td>
      </tr>
    </table>
	</fieldset>
	<input name="relatorio" type="button" onclick='js_abre();'  value="Emitir">
</form>
<?php db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));?>
</body>
</html>
<script>

function js_abre(){

  var sFonte = "pat2_relatoriolegalmodeloxxv002.php";
  var sExercicio = $F('db_exercicio');
  var iTipoRelatorio     = $F("iTipoRelatorio");
  var sMatricula = $F('db_matricula');
  var sNome = $F("db_nome");

  if (sExercicio == ""){
  
    alert("Exercicio precisa ser preenchido.");
    return false;
  }

  var sQuery       = "";

  var sQuery  = "?exercicio="   + sExercicio;
      sQuery += "&matricula="+ sMatricula;
      sQuery += "&nome_responsavel="+ sNome;

  var jan     = window.open(sFonte+sQuery,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
  jan.moveTo(0,0);
}
</script>
<script>

</script>
