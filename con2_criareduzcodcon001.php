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
//CRIA A TABELA DE HISTÓRICO SE NECESSÁRIO

if (isset ($processar)) {
	db_query("begin");
	$cCodCon  = "create temp table w_plano as select c60_codcon, c60_anousu, nextval('contabilidade.conplanoreduz_c61_reduz_seq') as c61_reduz, codigo, 99 as c61_codigo, 0 from contabilidade.conplano inner join configuracoes.db_config on true where c60_anousu >= 2014 and c60_codcon = $v60_codcon and ( select count(*) from contabilidade.conplanoreduz where c61_codcon = c60_codcon and c61_anousu = c60_anousu and c61_instit = codigo ) = 0 order by codigo;
                     insert into contabilidade.conplanoreduz (select * from w_plano); 
                     insert into contabilidade.conplanoexe (select c60_anousu, c61_reduz, 99, 0 from w_plano);";
	$rsCodCon = db_query($cCodCon);
	if($rsCodCon == false){
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
$clrotulo->label("v60_codcon");
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

          if(document.form1.v60_codcon.value == '') {
            alert('Informe o Codcon.');
            return false;
          }
        var sMensagemConfirm = "Você está prestes a criar o reduzido para esse CodCon ";
        sMensagemConfirm    += "selecionado.\n\nConfirma esta operação?";
        if (!confirm(sMensagemConfirm)) {
        	return false;
        }
        return true;
      }

      function js_focarCampo() {

          $("v60_codcon").focus();
      }
    </script>
  </head>

  <body bgcolor="#CCCCCC" style="margin-top:30px" onLoad="js_focarCampo();">
    <center>
      <form name="form1" id="form1"method="post" action="">
        <fieldset style="width: 600px;">
          <legend><b>Criar Reduzido por Codcon</b></legend>
          <table border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td align="left" valign="top" bgcolor="#CCCCCC">
                <center>
                    <table border="0">
                      <tr>
       	                  <td nowrap><b>CodCon: </b>
               	      </td>
                      <td>
                          <?php db_input('v60_codcon',15,@$Iv60_codcon,true,'text',1); ?>
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
$('v60_codcon').value = "";
</script>
<?php
if (isset ($processar)) {
	if ($sqlerro == false) {
		db_msgbox("Reduzido Criado com Sucesso!");
	} else {
		db_msgbox("Erro na criação do Reduzido!");
	}
}
?>
