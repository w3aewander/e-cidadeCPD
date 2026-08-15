<?php
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
require_once(modification("libs/db"."_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_utils.php"));

$iInstituicao = db_getsession("DB_instit");
$clorcfontes  = new cl_orcfontes;
$clorcfontes->rotulo->label();

$db_opcao = 1;

?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <?php

      db_app::load("estilos.css, grid.style.css");
      db_app::load("scripts.js, prototype.js, strings.js, arrays.js");
      db_app::load("widgets/windowAux.widget.js, widgets/dbtextField.widget.js");
      db_app::load("dbmessageBoard.widget.js, dbcomboBox.widget.js, datagrid.widget.js");
      db_app::load("widgets/DBLancador.widget.js, widgets/DBAncora.widget.js");
    ?>
    <style type="text/css">

    .inputdata {
      width:120px;
    }

    .select {
      width:150px;
    }

    .conta {
      display:"";
    }

    </style>

  </head>
  <body style="margin-top:30px;">
    <center>
      <fieldset style="width: 700px;">
        <legend><strong>Cadastro de Fontes de Receita(orcfontes) </strong></legend>
        <table style="width:650px">
          
           <tr>
            <td nowrap="nowrap">
              <strong>Fonte:</strong>
            </td>

            <td>
              <?php 
                db_input('o57_codfon', 10,$Io57_fonte,true,'text', 1);
                db_input('o57_fonte' , 30,$Io57_fonte,true,'text',1 );
              ?>
            </td>
          </tr>    
          
          
          <tr>
            <td colspan="2" align="center">
              <input type="button" name="btnPesquisar" id ="btnPesquisar" value="Pesquisar" onclick="js_pesquisar();" >
            </td>
          </tr>    
          
          
          <tr>
            <td colspan="2"><div style="margin-top: 20px;" id='ctnGridOrcFontes' ></div></td>
          </tr>
          
        
         <tr>
           <td colspan="2">
         
			        <fieldset style="margin-top: 20px;">
			        <legend>Dados Para Inclusão</legend>      
			          
				        <table align="left">
				          <tr>
				            <td nowrap="nowrap">
				              <strong>Código:</strong>
				            </td>
				
				            <td>
				              <?php 
				                db_input('o57_codfonIncluir', 10,$Io57_codfon,true,'text', 1);
				              ?>
				            </td>
				          </tr>    
				        
				           <tr>
				            <td nowrap="nowrap">
				              <strong>Ano:</strong>
				            </td>
				
				            <td>
				              <?php 
				                db_input('o57_anousu', 10,$Io57_anousu,true,'text', 1);
				              ?>
				            </td>
				          </tr>           
				        </table>
			        
			        </fieldset>
        
        </td>
       </tr> 
        
        <?php 
        if (db_getsession('DB_login') === "dbseller" && db_getsession("DB_id_usuario") === "1") { ?>
	        <tr>
	          <td colspan="2" align='center'>
	            <input type="button" style="margin-top: 20px;" name="btnProcessar" id ="btnProcessar" value="Incluir" onclick="js_incluir();" >
	          </td>
	        </tr>
      <?php } ?>  
        
        </table>
        
      </fieldset>
      
    </center>
  </body>
</html>

<?php
  db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>

<script type="text/javascript">

var sUrl = 'orc1_incluirorcfontes.RPC.php';

function js_incluir(){

	  var o57_codfonIncluir = $F('o57_codfonIncluir');
	  var o57_anousu        = $F('o57_anousu');

    if (o57_codfonIncluir == "" || o57_anousu == "" ) {

      alert("Digite o Código e o Ano da Fonte a ser Adicionada.");
    	return false;
    }

	  var oObject             = new Object();
        oObject.exec        = "incluirRegistro";
        oObject.o57_codfon  = o57_codfonIncluir; 
        oObject.o57_anousu  = o57_anousu; 

			js_divCarregando('Aguarde, Adicionando Registros...','msgBox');
			
			new Ajax.Request (sUrl,{
			                       method:'post',
			                       parameters:'json='+Object.toJSON(oObject),
			                       onComplete:js_retornoIncluir
			                      }
			                 );
}


function js_retornoIncluir(oJson) {

	  js_removeObj("msgBox");
	  var oRetorno = eval("("+oJson.responseText+")");
	  
	  alert(oRetorno.sMensagem.urlDecode());
	  js_pesquisar();
}	  


function js_pesquisar(){

	  var oObject            = new Object();
        oObject.exec       = "getOrcFontes";
        oObject.iCodigo    = $F('o57_codfon');
        oObject.iFonte     = $F('o57_fonte');

  js_divCarregando('Aguarde, buscando Registros...','msgBox');

  new Ajax.Request (sUrl,{
                         method:'post',
                         parameters:'json='+Object.toJSON(oObject),
                         onComplete:js_retornoPesquisar
                        }
                   );
}



function js_retornoPesquisar(oJson) {

	  js_removeObj("msgBox");
	  var oRetorno = eval("("+oJson.responseText+")");
	  
	  if (oRetorno.iStatus == 2) {

	    alert(oRetorno.sMensagem.urlDecode());
	    return false;
	  }

	  oGridOrcFontes.clearAll(true);
	  
	  oRetorno.aDados.each( function( oDados, iIndice  ){

		  aRow    = [];
		  aRow[0] = oDados.o57_codfon;
		  aRow[1] = oDados.o57_anousu;
		  aRow[2] = oDados.o57_fonte;
		  aRow[3] = oDados.o57_descr;
		  oGridOrcFontes.addRow(aRow);

	  });

	  oGridOrcFontes.renderRows(); 
}
  
function js_gridOrcFontes() {

	  oGridOrcFontes = new DBGrid('OrcFontes');
	  oGridOrcFontes.nameInstance = 'oGridOrcFontes';
	  
	  oGridOrcFontes.setCellWidth(new Array( '10%' ,
	                                         '10%' , 
	                                         '20%' ,
	                                         '60%'
	                                           ));
	  
	  oGridOrcFontes.setCellAlign(new Array( 'right'  ,
	                                         'right'  ,
	                                         'center',
	                                         'left'  
	                                           ));
	  
	  oGridOrcFontes.setHeader(new Array( 'Código'   ,
                                        'Ano'      ,
                                        'Fonte'    ,
                                        'Descrição'
	                                        ));

	  oGridOrcFontes.setHeight(200);
	  oGridOrcFontes.show($('ctnGridOrcFontes'));
	  oGridOrcFontes.clearAll(true);
	  
	}


js_gridOrcFontes();

</script>