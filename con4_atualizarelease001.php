<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));

$sVersaoAtual = 'N/A';
$sProximaVersao = 'N/A';
?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <?php
      db_app::load("scripts.js, strings.js, prototype.js, estilos.css");
    ?>
    <script type="text/javascript" src="scripts/json2.js"></script>
    <script type="text/javascript" src="scripts/widgets/DBInputHora.widget.js"></script>
  </head>
  <body class="body-default">


    <div class="container" >

      <fieldset >
        <legend>Atualização de Release</legend>

          <table class="form-container">

          <tr>
            <td><label>Versão Atual:</label></td>
            <td><input id="versao_atual" type="text" readonly="true" size="10" value="<?php echo $sVersaoAtual; ?>" /></td>
          </tr>

          <tr>
            <td><label>Próxima Versão:</label></td>
            <td><input id="proxima_versao" type="text" readonly="true" size="10" value="<?php echo $sProximaVersao; ?>" /></td>
          </tr>

          <tr><td colspan="2">&nbsp;</td></tr>

          <tr>
            <td colspan="2">
              <center>
                <button id="processar">Atualizar Release</button>
              </center>
            </td>
          </tr>

          </table>
      </fieldset>

      <fieldset >
        <legend>Agendar Atualização</legend>

          <table class="form-container">

          <tr>
            <td><label>Data:</label></td>
            <td>
              <input id="data" type="text" size="10" name="data" value="<?php echo date('d/m/Y'); ?>" onkeydown="return js_mascaraData(this, event)" onchange="return js_validaDbData(this);"/>
              <input id="data_dia" type="hidden" />
              <input id="data_mes" type="hidden" />
              <input id="data_ano" type="hidden" />
            </td>
          </tr>

          <tr>
            <td><label>Hora:</label></td>
            <td><input id="hora" type="text" size="10" value="<?php echo '18:00'; ?>" /></td>
          </tr>

          <tr><td colspan="2">&nbsp;</td></tr>
          
          <tr>
            <td colspan="2">
              <center>
                <button id="agendar">Agendar</button>
              </center>
            </td>
          </tr>

          </table>
      </fieldset>

    </div>

  <?php
      db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
  ?>

  <script type="text/javascript">

    (function() {

      var oDBInputHota  = new DBInputHora($('hora'));

      $('processar').observe("click", function() {

        if ( $F('proxima_versao') == 'N/A' || $F('proxima_versao') == '') {
          alert('Não há atualização disponível.');
          return;  
        }

        var oDadosRequisicao             = new Object();
        oDadosRequisicao.method          = 'GET';
        oDadosRequisicao.asynchronous    = true;
        oDadosRequisicao.onComplete      = function(oAjax){

          var oRetorno = JSON.parse(oAjax.responseText);
          js_removeObj("msgBox");
          alert(oRetorno.sMessage.urlDecode());          

        }

        js_divCarregando( "Aguarde.. Atualizado sistema.", "msgBox" );
        var oAjax  = new Ajax.Request( "con4_atualizarelease.RPC.php?exec=atualizar", oDadosRequisicao );

      });

      $('agendar').observe("click", function() {

        var oParametros = {
          sData: $F('data'),
          sHora: $F('hora')
        }

        var oDadosRequisicao             = new Object();
        oDadosRequisicao.method          = 'GET';
        oDadosRequisicao.asynchronous    = true;
        oDadosRequisicao.parameters   = 'json='+btoa(Object.toJSON(oParametros));
        oDadosRequisicao.onComplete      = function(oAjax){

          var oRetorno = JSON.parse(oAjax.responseText);
          js_removeObj("msgBox");
          alert(oRetorno.sMessage.urlDecode());

        }

        js_divCarregando( "Aguarde.. Agendando atualização.", "msgBox" );        
        var oAjax  = new Ajax.Request( "con4_atualizarelease.RPC.php?exec=agendar", oDadosRequisicao );

      });

      (function() {


        var oDadosRequisicao             = new Object();
          oDadosRequisicao.method          = 'GET';
          oDadosRequisicao.asynchronous    = true;
          oDadosRequisicao.parameters   = 'exec=getDadosIniciais';
          oDadosRequisicao.onComplete      = function(oAjax){

            js_removeObj("msgBox"); 
            var oRetorno = JSON.parse(oAjax.responseText);

            if (oRetorno.sVersaoAtual) {
              $('versao_atual').setValue(oRetorno.sVersaoAtual)
            }

            if (oRetorno.sProximaVersao) {
              $('proxima_versao').setValue(oRetorno.sProximaVersao)
            }

            if (oRetorno.sMessage) {
              alert(oRetorno.sMessage.urlDecode());
            }

          }

        js_divCarregando( "Aguarde.. Buscando dados do sistema.", "msgBox" );        
        var oAjax  = new Ajax.Request( "con4_atualizarelease.RPC.php?exec=getDadosIniciais", oDadosRequisicao );

      })();

    })();

  </script>

  </body>

</html>
