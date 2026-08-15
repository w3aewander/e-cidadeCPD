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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_libcontabilidade.php"));

$debug = false;
$bErro = false;
$sqlerro = false;
//CRIA A TABELA DE HISTÓRICO SE NECESSÁRIO
db_query("CREATE TABLE IF NOT EXISTS caixa.w_ageatualizadat(
	e80_sequencial integer not null, 
	e80_codage integer not null, 
	e80_dataant date not null, 
	e80_dataatu date not null, 
	e80_codusu integer not null, 
	e80_data timestamp not null, 
	PRIMARY KEY(e80_sequencial));");

if (isset ($processar)) {
	db_query("begin");
	$bDataAnt = "SELECT e80_data from empage where e80_codage = $c50_codage";
	$rDataAnt = db_query($bDataAnt);
	$data_ant = pg_result($rDataAnt,0,0);
	if(pg_numrows($rDataAnt) == 0){
		$sqlerro = true;
		$bErro   = true;
		db_fim_transacao(true);
	} else {
		$data_atu = $c50_dataatu_ano.'-'.$c50_dataatu_mes.'-'.$c50_dataatu_dia;	
		$atAgenda  = "UPDATE empage SET e80_data = '$data_atu' WHERE e80_codage = $c50_codage";
		$rsAgenda = db_query($atAgenda);
		if($rsAgenda == false){
			$bErro = true;
			break;
		}
		$bNumAgeAtuDat  = db_query("SELECT COALESCE(MAX(e80_sequencial),0) FROM caixa.w_ageatualizadat");
		$rNumAgeAtuDat  = pg_result($bNumAgeAtuDat,0,0)+1;
		$insAgeAtuDat   = "INSERT INTO caixa.w_ageatualizadat VALUES ($rNumAgeAtuDat,$c50_codage, '$data_ant', '$data_atu',".db_getsession("DB_id_usuario").",now())";
		db_query($insAgeAtuDat);
	}
	
	if ($bErro) {
		$sOperFim = "rollback";
	}else{
		$sOperFim = "commit";
	}

db_query($sOperFim);
}

$clrotulo = new rotulocampo;
$clrotulo->label("c50_codage");

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

          if(document.form1.c50_codage.value == '' && document.form1.c50_codage.value == '') {
            alert('Informe a Agenda para alteração.');
            return false;
          }
          if(document.form1.c50_dataatu.value == '' && document.form1.c50_dataatu.value == '') {
            alert('Indique a data para alteração.');
            return false;
          }

        var sMensagemConfirm = "Você está prestes a alterar a data da agenda ";
        sMensagemConfirm    += "selecionada.\n\nConfirma esta operação?";
        if (!confirm(sMensagemConfirm)) {
        	return false;
        }
        return true;
      }

      function js_focarCampo() {

          $("c50_codage").focus();
      }
    </script>
  </head>

  <body bgcolor="#CCCCCC" style="margin-top:30px" onLoad="js_focarCampo();">
    <center>
      <form name="form1" id="form1"method="post" action="">
        <fieldset style="width: 600px;">
          <legend><b>Alterar Data da Agenda</b></legend>
          <table border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td align="left" valign="top" bgcolor="#CCCCCC">
                <center>
                    <table border="0">
                      <tr>
          	            <td nowrap><b>Agenda:</b>
                      	</td>
                      	<td>
          	             <?php db_input('c50_codage',5,$Ic50_codage,true,'text',1);?>
          	            </td>
                      </tr>

                      <tr>
	      <td nowrap><b>Data:</b>
	      </td>	
	      <td>	
	       <?php db_inputdata('c50_dataatu',@$c50_dataatu_dia,@$c50_dataatu_mes,@$c50_dataatu_ano,true,'text',1);
	       ?>
	      </td>
                      </tr>
                    </table>

                </center>
              </td>
            </tr>
          </table>
        </fieldset>
        <input style="margin-top: 10px;" name="processar" type="submit" id="db_opcao" value="Processar" onclick='return js_verifica();' >
      </form>
    </center>
  <?php db_menu(db_getsession("DB_id_usuario"), db_getsession("DB_modulo"), db_getsession("DB_anousu"), db_getsession("DB_instit"));?>
  </body>
</html>
<script>

$('c50_codage').value = "";
$('c50_dataatu').value = "";

</script>
<?php
if (isset ($processar)) {
	if ($sqlerro == false) {
		db_msgbox("Agenda atualizada com sucesso!");
	} else {
		db_msgbox("Agenda não encontrada!");
	}
}
?>
