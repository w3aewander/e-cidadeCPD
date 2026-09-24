<?php
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("dbforms/db_funcoes.php"));

$aConfig = PluginService::getPluginConfig(new Plugin(null, 'atualiza_patch'));

if ( !$aConfig )  {
  $aConfig = parse_ini_file('plugins/atualiza_patch/config.ini', true);
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
<script language="JavaScript" type="text/javascript" src="scripts/datagrid.widget.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/widgets/datagrid/plugins/DBPesquisa.plugin.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/widgets/windowAux.widget.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/widgets/dbtextField.widget.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/widgets/dbtextFieldData.widget.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/widgets/DBInputHora.widget.js"></script>

<link href="estilos.css" rel="stylesheet" type="text/css">
<link href="estilos/grid.style.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default">

  <form class="container" style="width: 820px">

    <fieldset>
      <legend>Gerenciar Atualizações</legend>
      <table class="form-container">
        <tr>
          <td>
            <label>URL:</label>
          </td>
          <td>
            <input type="text" size="30" readonly value="<?php echo $aConfig['sUrlPatches']; ?>"/>
          </td>
        </tr>
        <tr>
          <td>
            Última Atualização:
          </td>
          <td>
            <input id="data" name="data" readonly value="<?php echo $aConfig['sDataAtualizacao']; ?>" />
          </td>
        </tr>
      </table>
    </fieldset>


    <fieldset id="ctnchangeLog">
      <div id="changeLog"></div>
    </fieldset>

    <input type="button" id="agendar"     value="Agendar Atualizações" />
    <input type="button" id="check"      value="Verificar Atualizações" />
    <input type="button" id="atualizar"  value="Atualizar" />

  </form>

</body>
<?php
    db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</html>
<script>

var sUrl      = "con4_atualizapatch.RPC.php";
var aHorarios = new Array();

$('atualizar').style.display = 'none';

(function() {

  var oGridChangeLog     = new DBGrid('gridChangeLog');
    oGridChangeLog.nameInstance = "gridChangeLog";
    oGridChangeLog.setCellAlign(new Array("center","left"));
    oGridChangeLog.setCellWidth(new Array("10%","90%"));
    oGridChangeLog.setHeader(new Array("Tarefa","ChangeLog"));
    oGridChangeLog.setHeight('400px;');
    oGridChangeLog.show($('changeLog'));
    oGridChangeLog.setPesquisa(0);



  /**
    * Funcao de callback da primeira requisicao
   */
  function js_retornoTarefasAtualizadas(oAjax) {

      var oRetorno = JSON.parse(oAjax.responseText),
          oChangelog = JSON.parse(oRetorno.oChangelog);

      oGridChangeLog.clearAll(true);

      var aChangelogOrdenado = []

      for (var iTarefa in oChangelog) {

        var oTarefa = oChangelog[iTarefa];

        aChangelogOrdenado.push(oTarefa);

      }

      aChangelogOrdenado.sort(function(a,b) {
        return a.iTarefa - b.iTarefa;
      })

      for (var i = 0; i < aChangelogOrdenado.length; i++) {

        var oTarefa = aChangelogOrdenado[i];

        oGridChangeLog.addRow( [ oTarefa.iTarefa, oTarefa.sDescricao ] );

      }

      oGridChangeLog.renderRows();
      js_removeObj( "msgBox" );

  }


  /**
    * Primeira requisicao para buscar as tarefas atualizadas
   */
  js_divCarregando( "Buscando tarefas atualizadas.", "msgBox" );
  doAjax({exec: "getTarefasAtualizadas" }, js_retornoTarefasAtualizadas);


  $('check').observe('click', function() {

    this.disabled = true;

    js_divCarregando( "Verificando novas atualizações", "msgBox");

    doAjax({exec: "checkUpdates"}, function(oAjax) {

      var oRetorno = JSON.parse(oAjax.responseText),
          lTemAtualizacao = false;

      if (oRetorno.iStatus == 2) {
        alert(oRetorno.sMessage.urlDecode())
        js_removeObj("msgBox");
        return;
      }

      for ( var iTarefa in oRetorno.oNovasTarefas ) {

        var oTarefa = oRetorno.oNovasTarefas[iTarefa];

        lTemAtualizacao = true;
        oGridChangeLog.addRow(['<strong>' + oTarefa.iTarefa + '<strong>', oTarefa.sDescricao]);

      }

      if (lTemAtualizacao) {

        oGridChangeLog.clearAll()
        oGridChangeLog.renderRows();
        $('check').style.display = 'none';
        $('atualizar').style.display = '';

      } else {
        alert("Nenhuma atualização encontrada.");
      }

      js_removeObj("msgBox");

    })

    this.disabled = false;
  });

  $('atualizar').observe('click', function() {

    this.disabled = true;

    var _this = this;

    js_divCarregando( "Aplicando novas atualizações", "msgBox");

    doAjax({exec: "atualizar"}, function(oAjax) {

      var oRetorno = JSON.parse(oAjax.responseText);

      alert(oRetorno.sMessage.urlDecode())
      js_removeObj("msgBox");

      if (oRetorno.iStatus == 2) {

        _this.disabled = false;
        return false;
      }

      window.location = window.location;

    });

  });

  $('agendar').observe('click', function() {

    $('agendar').disabled = true;
    var sContent = '';

      sContent += " <div class='container' style='width:95%'>                                 ";
      sContent += " <fieldset>                                                                ";
      sContent += "   <legend>Grade de Horários</legend>                                      ";
      sContent += "   <table>                                                                 ";
      sContent += "     <tr>                                                                  ";
      sContent += "       <td>Horário:</td>                                                   ";
      sContent += "       <td>                                                                ";
      sContent += "         <input type='text' id='sHorario' name='sHorario' maxlength='5' /> ";
      sContent += "         <input type='button' id='adicionar' value='Adicionar' />          ";
      sContent += "       </td>                                                               ";
      sContent += "     </tr>                                                                 ";
      sContent += "   </table>                                                                ";
      sContent += "   <div id='ctnLancadorHorario'></div>                                     ";
      sContent += " </fieldset>                                                               ";
      sContent += " <input type='button' id='salvar' value='Salvar' />                        ";
      sContent += " </div>                                                                    ";

    var oWindow = new windowAux('wAgendamento', 'Agendar Atualização do Patch', 500, 400);
    oWindow.setContent(sContent);
    oWindow.show();

    new DBInputHora( $('sHorario') );

    var oGridHorarios = new DBGrid( "gridHorario" );
        oGridHorarios.nameInstance = 'oGridHorarios';
        oGridHorarios.setHeight(150);
        oGridHorarios.setHeader( new Array( "Codigo", "Horário", "Ação" ) );
        oGridHorarios.setCellAlign( new Array( "center", "center", "center" ) );
        oGridHorarios.setCellWidth( new Array( "20%", "70%", "10%" ) );
        oGridHorarios.aHeaders[0].lDisplayed = false;
        oGridHorarios.show( $('ctnLancadorHorario') );

    oWindow.setShutDownFunction(function() {
      $('agendar').disabled = false;
      oWindow.destroy();
    });

    $('adicionar').observe('click', function() {

      oGridHorarios.clearAll(true);

      if ( $F('sHorario') != '' ) {
        aHorarios.push($F('sHorario'));
      }

      for( var iPosicao = 0; iPosicao < aHorarios.length; iPosicao++ ) {

        var aLinha = new Array();
        aLinha[0]  = '';
        aLinha[1]  = aHorarios[iPosicao];
        aLinha[2]  = '<input type="button" value="E" id="excluir" onClick="excluirHorario(' + iPosicao +');">';
        oGridHorarios.addRow(aLinha);
      }

      oGridHorarios.renderRows();

      $('sHorario').value = '';
      $('sHorario').focus();
    });

    $('salvar').observe('click', function(){

      var oParametros       = new Object();
      oParametros.exec      = 'agendar';
      oParametros.aHorarios = aHorarios;

      var oAjax = new Ajax.Request( "con4_atualizapatch.RPC.php",
                                    { method:     'post',
                                      parameters: 'json='+Object.toJSON(oParametros),
                                      asynchronous: false,
                                      onComplete: function retornoAgendarAtualizacao( oAjax ){

                                        var oRetorno = JSON.parse(oAjax.responseText);
                                        alert(oRetorno.sMessage.urlDecode());
                                      }
                                    }
                                  );

    });


    var oParametros  = new Object();
    oParametros.exec = 'buscarHorarios';

    var oAjax = new Ajax.Request( "con4_atualizapatch.RPC.php",
                              { method:     'post',
                                parameters: 'json='+Object.toJSON(oParametros),
                                asynchronous: false,
                                onComplete: function retornoBuscaHorarios( oAjax ){

                                  aHorarios = new Array();

                                  var oRetorno = JSON.parse(oAjax.responseText);

                                  oRetorno.aHorarios.each(function(sHorario){

                                    var aHorarioFormatado = sHorario.split("");
                                    aHorarioFormatado.splice(2, 0, ":");
                                    aHorarios.push(aHorarioFormatado.join(""));
                                  });

                                  $('adicionar').click();
                                }
                              }
                            );
  })

})();


function excluirHorario( iPosicao ) {

  aHorarios.splice( iPosicao, 1);
  $('adicionar').click();
}

/**
  * Função generica para realizar requisicoes ajax
 */
function doAjax(oData, fCallback) {

  var oDadosRequest = {};
      oDadosRequest.method     = "post";
      oDadosRequest.parameters = 'json='+Object.toJSON(oData);
      oDadosRequest.onComplete = fCallback;

   return new Ajax.Request(sUrl, oDadosRequest);
}

</script>
