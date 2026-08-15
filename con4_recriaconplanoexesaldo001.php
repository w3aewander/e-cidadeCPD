<?PHP
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
require_once(modification("libs/db_"."conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_app.utils.php"));

db_app::load("scripts.js");
db_app::load("strings.js");
db_app::load("prototype.js");
db_app::load("widgets/windowAux.widget.js");
db_app::load("widgets/dbmessageBoard.widget.js");
db_app::load("dbcomboBox.widget.js");
db_app::load("widgets/DBToogle.widget.js");

?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" >

<center>
<form name="form1" method="post" action="">

<fieldset style="margin-top: 30px; width: 400px;">
  <legend ><strong>Recria Conplanoexesaldo</strong></legend>
  
   ** Rotina irá recriar os dados da estrutura conplanoexesaldo.
   <br>
</fieldset>
<?php 
  if (db_getsession('DB_login') === "dbseller" && db_getsession("DB_id_usuario") === "1") {
?>
    <input type="button" value='Processar' name='btnProcessaConplanoExeSaldo' id='btnProcessaConplanoExeSaldo' onclick='js_ProcessaConplanoExeSaldo();' >
<?php 
  } 
?>    
</form>


<fieldset style="margin-top: 30px; width: 400px;" id='fieldsetDisponibilidade'>
  <legend ><strong>Disponibilidade Financeira</strong></legend>
  
  <table align='left' width='100%'>
    <tr> 
      <td> <strong>Estrutural:</strong> </td>
      <td> <input type="text" id='txtEstruturalDisponibilidade' style="width: 100"/><strong> %</strong> </td>
    </tr>
    <tr> 
      <td> <strong>Característica Peculiar:</strong> </td>
      <td> <input type="text" id='txtCaracteristicaPeculiar' style="width: 100"/> </td>
    </tr>  
    <tr> 
      <td> <strong>Recurso:</strong> </td>
      <td> <input type="text" id='txtRecursoDisponibilidade' style="width: 100" /> </td>
    </tr>
    <tr> 
      <td> <strong>Data Inicial:</strong> </td>
      <td> <?php db_inputdata('txtDataInicialDisponibilidade', null, null, null, true, null, 1 );  ?> </td>
    </tr>    
    <tr> 
      <td> <strong>Data Final:</strong> </td>
      <td> <?php db_inputdata('txtDataFinalDisponibilidade', null, null, null, true, null, 1 );  ?> </td>
    </tr>     
  </table>
  
  <?php 
  if (db_getsession('DB_login') === "dbseller" && db_getsession("DB_id_usuario") === "1") {
  ?>
       <input type="button" id='btnDisponibilidade' value='Processa Disponibilidade' onclick="js_ProcessaDisponibilidade();" /> 
  <?php 
  } 
  ?>     
       
</fieldset>  


</center>
<?php
  db_menu(db_getsession("DB_id_usuario"), db_getsession("DB_modulo"),db_getsession("DB_anousu"), db_getsession("DB_instit"));
?>
</body>
</html>

<script>

var oToogleLancamentos = new DBToogle("fieldsetDisponibilidade", false);
    oToogleLancamentos.oLegend.style.cursor = 'pointer';

var sUrlRpc = "con4_recriaconplanoexesaldo.RPC.php";

function js_ProcessaConplanoExeSaldo() {
	  
	  var oObject                    = new Object();
	      oObject.exec               = "recriaConplanoExeSaldo";

	    if ( !confirm("Iniciar Processamento ?") ) {
		      return false;
	  	  }
	      
    js_divCarregando('Aguarde, Processando ConplanoExeSaldo...','msgBox');

	  new Ajax.Request (sUrlRpc,{
	                         method:'post',
	                         parameters:'json='+Object.toJSON(oObject),
	                         onComplete:js_retornoProcessaConplanoExeSaldo
	                        }
	                   );
	}

	function js_retornoProcessaConplanoExeSaldo(oJson) {

		js_removeObj("msgBox");
	  var oRetorno = eval("("+oJson.responseText+")");
	  alert(oRetorno.sMessage.urlDecode());
	}



function js_ProcessaDisponibilidade() {
	  
	  var oObject                    = new Object();
	      oObject.exec               = "processaDisponibilidade";
	      oObject.sEstrutural        = $F("txtEstruturalDisponibilidade");
	      oObject.iRecurso           = $F("txtRecursoDisponibilidade");
	      oObject.sCaracteristica    = $F("txtCaracteristicaPeculiar");
	      oObject.dDataInicial       = $F("txtDataInicialDisponibilidade");
	      oObject.dDataFinal         = $F("txtDataFinalDisponibilidade");
	      
        if (oObject.sEstrutural      == '' ||
        		oObject.iRecurso         == '' ||
        		oObject.sCaracteristica  == '' ||
        		oObject.dDataInicial     == '' ||
        		oObject.dDataFinal       == '' 
             ) {

          alert('Todos os Campos Devem ser Preenchidos.');
          return false;
        }

  	    if ( !confirm("Iniciar Processamento ?") ) {
		      return false;
	  	  }
        
	      
    js_divCarregando('Aguarde, Processando Disponibilidade...','msgBox');

	  new Ajax.Request (sUrlRpc,{
	                         method:'post',
	                         parameters:'json='+Object.toJSON(oObject),
	                         onComplete:js_retornoProcessaDisponibilidade
	                        }
	                   );
	}

	function js_retornoProcessaDisponibilidade(oJson) {

		js_removeObj("msgBox");
	  var oRetorno = eval("("+oJson.responseText+")");
	  alert(oRetorno.sMessage.urlDecode());
	}


</script>

