<?
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBselller Servicos de Informatica
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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_libdicionario.php"));
require_once(modification("libs/db_libcontabilidade.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_classesgenericas.php"));
require_once(modification("classes/db_conparametro_classe.php"));


function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}


if($_POST){
  
  $conta = $_POST["numeroconta"];
  $descricao = $_POST["descricaoconta"];

  $sql = pg_query($conn, "INSERT INTO cadastroreceitastce(codigoconta, descricaoconta) VALUES('".$conta."', '".$descricao."')");

  

  if($sql){
      echo "<script>alert('Cadastro efetuado com sucesso.')</script>";
    } else {
      $error = pg_last_error($conn);      
      echo "<script>alert('Houve um erro no banco de dados. Contate o suporte.');</script>";
    }
}







?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<?
  db_app::load("scripts.js");
  db_app::load("prototype.js");
  db_app::load("strings.js");
  db_app::load("dbautocomplete.widget.js");
  db_app::load("DBViewContaBancaria.js");
  db_app::load("dbmessageBoard.widget.js");
  db_app::load("estilos.css");
  db_app::load("dbtextField.widget.js");
  db_app::load("dbcomboBox.widget.js");
  db_app::load("prototype.maskedinput.js");
  db_app::load("windowAux.widget.js");
  db_app::load("AjaxRequest.js");
?>
<link href="estilos.css" rel="stylesheet" type="text/css">
<style>
  select {width: 98%;}
  textarea {width: 100%;}
  input#c90_estrutcontabil:disabled{background-color: #DEB887;
                                    color:black}
</style>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1">


<form name="form1" id='form1' method="post" action="#" style="margin-top: 20px">
<center>
  <br />
  <fieldset style="width: 500px;">
    <legend><b>Cadastro das Receitas do TCE-RJ</b></legend>
  	<table border="0" width="500px;">
  	  
  	  <tr>
        <td nowrap="nowrap">
           <b>Conta</b>
        </td>
        <td>
          <input type="text" name="numeroconta" maxlength="13" minlength="13" size="50" onkeypress="return forcaNumeros(event)">
        </td>
      </tr>
  	  <tr>
  	    <td><b>Descrição:</b></td>
  	    <td>
  	      <input type="text" name="descricaoconta" maxlength="200" size="50">
  	    </td>
  	  </tr>  	  
    </table>
  </fieldset>
  <br>
  <input type="submit" value="Salvar"  />
  &nbsp;
  
</center>
</form>
<?
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</body>
</html>


<script>
   function forcaNumeros(evt) {
        var ASCIICode = (evt.which) ? evt.which : evt.keyCode
        if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
            return false;
        return true;
    }
</script>

