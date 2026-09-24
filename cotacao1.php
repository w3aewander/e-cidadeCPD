<?


require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("libs/db_utils.php");
require_once("dbforms/db_funcoes.php");
require_once("classes/db_cflicita_classe.php");
require_once("classes/db_pctipocompratribunal_classe.php");
require_once("classes/db_pccflicitapar_classe.php");
require_once("model/licitacao/LicitacaoModalidade.model.php");

db_postmemory($HTTP_POST_VARS);
function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function verificaSequencial($sequencial){
  $sql = pg_query("SELECT * FROM tiposituacao WHERE sequencial = {$sequencial}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}



if($_GET["acao"] == "existe"){
  if(!$_POST["incluir"]){
    echo "<script>alert('Erro ao incluir. Sequencial já existente.')</script>";  
  }
}

if($_POST){
  $confere = verificaSequencial($_POST["sequencialtipo"]);
  
  if($confere){
    header('Location: cotacao1.php?acao=existe');
  } else {
    pg_query("INSERT INTO tiposituacao(sequencial, descricao) VALUES({$_POST['sequencialtipo']}, '{$_POST['descricaotipo']}')");
    echo "<script>alert('Sequencial incluído com sucesso.');</script>";
  }
  
}

?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" style="margin-top: 25px">
<table border="0" align="center" cellspacing="0" cellpadding="0" >
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" bgcolor="#CCCCCC">
    <center>
    <form name="form1" method="post" action="<?=$db_action?>">
      <fieldset>
        <legend><b>Tipo de Situação</b></legend>
          <table border="0">
            <tr>
              <td nowrap><b>Sequencial:</b></td>
              <td> 
                <input type="text" name="sequencialtipo" id="sequencialtipo" size="15" required onkeypress="return forcaNumero(event)">      
              </td>
            </tr>

            <tr>
              <td nowrap><b>Descrição:</b></td>
              <td> 
                <input type="text" name="descricaotipo" maxlength="25" size="40" required>      
              </td>
            </tr>  
          </table>
      </fieldset>

      <table>
        <tr><td colspan="2">&nbsp;</td></tr>
        <tr>
          <td>
            <input name="incluir" type="submit" id="incluir" value="Incluir">             
          </td>
          <td><input name="pesquisar" type="button" id="pesquisar" value="Pesquisar" onclick="js_pesquisa();" ></td>
    
    <?php /* ?>
    <td>
      <input name="pesquisar" type="button" id="pesquisar" value="Pesquisar" onclick="js_pesquisa();">
    </td>
    <?php */ ?>
  </tr>
</table>






    </form>
    <script>
      function forcaNumero(evt){
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
          return false;
        }
        return true;
      }

      function js_pesquisa(){
        js_OpenJanelaIframe('top.corpo','db_iframe_liclocal','func_tipolicita.php?funcao_js=parent.js_preenchepesquisa|l26_codigo','Pesquisa',true);
      }
      function js_preenchepesquisa(chave){
        db_iframe_liclocal.hide();
      }

    </script>
    </center>
  </td>
  </tr>
</table>
<? db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
  ?>
</body>
</html>