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
require_once("libs/db_utils.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("libs/db_app.utils.php");
require_once("dbforms/db_funcoes.php");
require_once("libs/db_libcontabilidade.php");

$debug = false;
$bErro = false;
$sqlerro = false;

if (isset ($processar)) {
	db_query("begin");
	$atItemEmpenho = "update empempitem set e62_servicoquantidade = false where e62_numemp = $e60_numemp;";
	$rsItemEmpenho = db_query($atItemEmpenho);
	if($rsItemEmpenho == false){
		$bErro = true;
		break;
	}
		
	if ($bErro) {
		$sOperFim = "rollback";
	}else{
		$sOperFim = "commit";
	}
db_query($sOperFim);
}

$clrotulo = new rotulocampo;
$clrotulo->label("e60_numemp");
?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
    <link href="estilos.css" rel="stylesheet" type="text/css">
      <script>
      var USE_PCASP = "<?php echo USE_PCASP ? "true" : "false";?>";

      function js_verifica() {

          if(document.form1.e60_numemp.value == '' && document.form1.e60_numemp.value == '') {
            alert('Indique o sequencial do empenho.');
            return false;
          }
        var sMensagemConfirm = "Você está prestes a alterar o controle do empenho informado para Serviço. ";
        sMensagemConfirm    += "\n\nConfirma esta operação?";
        if (!confirm(sMensagemConfirm)) {
        	return false;
        }
        return true;
      }

      function js_focarCampo() {

          $("e60_numemp").focus();
      }
    </script>
  </head>

  <body bgcolor="#CCCCCC" style="margin-top:30px" onLoad="js_focarCampo();">
    <center>
      <form name="form1" id="form1"method="post" action="">
        <fieldset style="width: 600px;">
          <legend><b>Alterar Empenho para Servi&ccedil;o</b></legend>
          <table border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td align="left" valign="top" bgcolor="#CCCCCC">
                <center>
                    <table border="0">
                      <tr>
          	            <td nowrap title="<?php echo @$Te60_numemp?>">
          	              <?php db_ancora(@$Le60_numemp,"js_pesquisae60_numemp(true);",1); ?>
                      	</td>
                      	<td>
          	             <?php db_input('e60_numemp',15,$Ie60_numemp,true,'text',1," onchange='js_pesquisae60_numemp(false);'");?>
          	            </td>
                      </tr>
                    </table>
                </center>
              </td>
            </tr>
          </table>
        </fieldset>
        <input style="margin-top: 10px;" name="processar" type="submit" id="db_opcao" value="Processar" onclick='return js_verifica();'>
      </form>
    </center>
  <?php db_menu(db_getsession("DB_id_usuario"), db_getsession("DB_modulo"), db_getsession("DB_anousu"), db_getsession("DB_instit"));?>
  </body>
</html>
<script>
/**
  * Funções para o filtro por empenho
  */

function js_pesquisae60_numemp(mostra) {
  if(mostra == true) {
    js_OpenJanelaIframe('top.corpo','db_iframe_empempenho','func_empempenho.php?funcao_js=parent.js_mostraempempenho1|e60_numemp','Pesquisa',true);
  } else {
     if(document.form1.e60_numemp.value != '') {
        js_OpenJanelaIframe('top.corpo','db_iframe_empempenho','func_empempenho.php?pesquisa_chave='+document.form1.e60_numemp.value+'&funcao_js=parent.js_mostraempempenho','Pesquisa',false);
     } else {
       document.form1.e60_numemp.value = '';
     }
  }
}

function js_mostraempempenho(chave,erro) {
  if(erro == true){

    document.form1.e60_numemp.focus();
    document.form1.e60_numemp.value = '';
  }
}

function js_mostraempempenho1(chave1,x) {

  document.form1.e60_numemp.value = chave1;
  db_iframe_empempenho.hide();
}


$('e60_numemp').value = "";

</script>
<?php
if (isset ($processar)) {
	if ($sqlerro == false) {
		db_msgbox("Empenho atualizado com sucesso!");
	} else {
		db_msgbox("Empenho não encontrado!");
	}
}
?>
