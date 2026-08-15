<?php
/**
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
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));

?>
<html>
<head>
  <title>Microsist</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <script type="text/javascript" src="scripts/scripts.js"></script>
  <script type="text/javascript" src="scripts/strings.js"></script>
  <script type="text/javascript" src="scripts/prototype.js"></script>
  <script type="text/javascript" src="scripts/datagrid.widget.js"></script>
  <script type="text/javascript" src="scripts/AjaxRequest.js"></script>
  <script type="text/javascript" src="scripts/widgets/windowAux.widget.js"></script>
  <script type="text/javascript" src="scripts/widgets/DBInputHora.widget.js"></script>
  <script type="text/javascript" src="scripts/widgets/DBHint.widget.js"></script>
  <script type="text/javascript" src="scripts/widgets/DBModal.widget.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="container">

  <fieldset style="width: 800px;">
    <legend class="bold">Atualização de Melhorias</legend>
    <div id="ctnMelhorias"></div>
  </fieldset>
  <p>
    <input type="button" id="btnAgendar" value="Agendar Atualizações" onclick="agendar()"/>
    <input type="button" id="btnVerificarAtualiacao" value="Verificar Atualizações" onclick="sincronizar()"/>
  </p>

</div>

<?php db_menu(); ?>
</body>
</html>
<script type="text/javascript">

  const PATH_RPC = 'con4_entregacontinua.RPC.php';

  var aHorarios = new Array();


  var oGridMelhoria = new DBGrid('oGridMelhoria');
  oGridMelhoria.nameInstance = 'oGridMelhoria';
  oGridMelhoria.setHeader(['Código', 'Título', 'Ação', 'Link']);
  oGridMelhoria.setCellAlign(['left', 'left', 'center', 'center']);
  oGridMelhoria.setCellWidth(['5%', '70%', '25%', '0%']);
  oGridMelhoria.setHeight(400);
  oGridMelhoria.aHeaders[3].lDisplayed = false;
  oGridMelhoria.show($('ctnMelhorias'));

  function sincronizar() {

    new AjaxRequest(
      PATH_RPC,
      {'exec' : 'sincronizar'},
      function (oRetorno, lErro) {

        if (lErro) {
          alert(oRetorno.mensagem.urlDecode());
          return;
        }

        if (!oRetorno.sincronizado) return;

        verificarAtualizacao();
      }
    ).setMessage('Aguarde, sincronizando atualizações...').execute();

  }

  function verificarAtualizacao() {

    new AjaxRequest(
      PATH_RPC,
      {'exec' : 'carregarAtualizacoes'},
      function (oRetorno, lErro) {

        oGridMelhoria.clearAll(true);
        if (lErro) {

          alert(oRetorno.mensagem.urlDecode());
          return false;
        }

        oRetorno.atualizacoes.each(
          function(oAtualizacao, iIndice) {

            oGridMelhoria.addRow(
              [
                oAtualizacao.id,
                oAtualizacao.titulo.urlDecode(),
                "<input type='button' value='Resumo' onclick='resumo(\"" +oAtualizacao.titulo.urlDecode()+ "\" , \""+oAtualizacao.resumo.urlDecode()+"\")'/> <input type='button' value='Atualizar' onclick='atualizar("+iIndice+")'/>",
                JSON.stringify(oAtualizacao)
              ]
            );
          }
        );
        oGridMelhoria.renderRows();

        // criacao dos hints
        oRetorno.atualizacoes.each(function(oAtualizacao, iIndice) {
          createHint($(oGridMelhoria.aRows[iIndice].aCells[1].sId), oAtualizacao.titulo.urlDecode());
        });

      }
    ).setMessage('Aguarde, carregando atualizações disponíveis...').execute();
  }

  function atualizar(iLinhaSelecionada) {

    var oRowGrid = oGridMelhoria.aRows[iLinhaSelecionada];
    var oMelhoria = JSON.parse(oRowGrid.aCells[3].getValue());
    var iCodigoMelhoria = oMelhoria.id;

    var aMelhoriasSelecionadas = [ oMelhoria ];
    if (iLinhaSelecionada == 0 && !confirm('Confirma a atualização da melhoria de código '+iCodigoMelhoria+'?')) {
      return false;
    }

    if (iLinhaSelecionada > 0) {

      var sMensagem = "Ao atualizar a melhoria com código "+iCodigoMelhoria+", todas as melhorias anteriores ";
      sMensagem += "também serão atualizadas.";
      sMensagem += "\n\n";
      sMensagem += "Confirma a atualização de todas as melhorias?";

      if (!confirm(sMensagem)) {
        return false;
      }

      aMelhoriasSelecionadas = [];
      for (var iLinhaGrid = 0; iLinhaGrid <= iLinhaSelecionada; iLinhaGrid++) {
        aMelhoriasSelecionadas.push( JSON.parse(oGridMelhoria.aRows[iLinhaGrid].aCells[3].getValue()) );
      }
    }

    new AjaxRequest(
      PATH_RPC,
      {exec: 'atualizar', melhorias: aMelhoriasSelecionadas},
      function (oRetorno) {

        alert(oRetorno.mensagem.urlDecode());
        verificarAtualizacao();
      }

    ).setMessage("Aguarde, atualizando o sistema...").execute();
  }

  function agendar() {

    $('btnAgendar').disabled = true;

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

    var oWindow = new windowAux('wAgendamento', 'Agendar Atualização da Melhorias', 500, 400);
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
      $('btnAgendar').disabled = false;
      oWindow.destroy();
    });

    // evento do botao adicionar do window aux
    $('adicionar').observe('click', function() {

      oGridHorarios.clearAll(true);

      if ( $F('sHorario') != '' ) {
        aHorarios.push($F('sHorario'));
      }

      for( var iPosicao = 0; iPosicao < aHorarios.length; iPosicao++ ) {

        var aLinha = [
          '',
          aHorarios[iPosicao],
          '<input type="button" value="E" id="excluir" onClick="excluirHorario(' + iPosicao +');">'
        ]
        oGridHorarios.addRow(aLinha);
      }

      oGridHorarios.renderRows();

      $('sHorario').value = '';
      $('sHorario').focus();
    });

    $('salvar').observe('click', function() {

      var oParametros       = new Object();
      oParametros.exec      = 'agendar';
      oParametros.aHorarios = aHorarios;

      new AjaxRequest( 
        PATH_RPC, 
        {
          exec: 'agendar',
          aHorarios: aHorarios
        }, 
        function retornoAgendarAtualizacao( oRetorno, lErro ){
          alert(oRetorno.sMessage.urlDecode());
        }
      ).setMessage('Salvando agendamento.').execute();

    });

    // busca os horarios agendados
    new AjaxRequest( 
      PATH_RPC, 
      {exec: 'getHorariosAgendados'}, 
      function retornoBuscaHorarios( oRetorno, lErro ){

        aHorarios = new Array();

        oRetorno.aHorarios.each(function(sHorario){

          var aHorarioFormatado = sHorario.split("");
          aHorarioFormatado.splice(2, 0, ":");
          aHorarios.push(aHorarioFormatado.join(""));
        });

        $('adicionar').click();

      }
    ).setMessage('Buscando horários agendados').execute();

  }

  function excluirHorario( iPosicao ) {
    aHorarios.splice( iPosicao, 1);
    $('adicionar').click();
  }

  function resumo( sTitulo, sResumo ) {

    var oModal = new DBModal();
    oModal.setTitle( sTitulo );
    oModal.setContent( sResumo );
    oModal.show();

  }

  function createHint(oElement, sText) {
    return DBHint.build(oElement, {text: sText, showEvents: ['onmouseover'], hideEvents: ['onmouseout']});
  }

</script>
