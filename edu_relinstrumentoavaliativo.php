<?
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
require_once("libs/db_app.utils.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");

$iEscola = db_getsession("DB_coddepto");



function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function confereObs($c, $t, $p){
  $sql = pg_query("SELECT id FROM regocorr WHERE calendario = {$c} AND turma = {$t} AND periodo = {$p}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["id"];
}

if($_POST){
  $calendario = $_POST["xcalendario"];
  $turma = $_POST["xturma"];
  $periodo = $_POST["xperiodo"];
  $obs = $_POST["xobs"];

  //$confere = confereObs($calendario, $turma, $periodo);

  
}


?>

<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <link rel="stylesheet" type="text/css" href="estilos.css">
    <script type="text/javascript" src="scripts/scripts.js"></script>
    <script type="text/javascript" src="scripts/prototype.js"></script>
    <script type="text/javascript" src="scripts/classes/educacao/escola/ListaCalendario.classe.js"></script>
    <script type="text/javascript" src="scripts/classes/educacao/escola/ListaTurma.classe.js"></script>
    <script type="text/javascript" src="scripts/classes/educacao/escola/ListaPeriodoAvaliacao.classe.js"></script>
    <script type="text/javascript" src="scripts/classes/educacao/escola/ListaDisciplinas.classe.js"></script>
    <script type="text/javascript" src="scripts/widgets/DBToggleList.widget.js"></script>
    <script type="text/javascript" src="scripts/widgets/dbcomboBox.widget.js"></script>    
  </head>
  <body bgcolor="#cccccc">

    <div class="container" id="cntRegistroOcorrencia">
      <form id="frmRegistroOcorrencia" method="post" action="edu_relinstrumentoavaliativo2.php">
        <fieldset style="width: 500px">
          <legend>Diário de Classe - Instrumento Avaliativo</legend>
          <table class="form-container">
            <tr>
              <td nowrap="nowrap" class="field-size3">Calendário:</td>
              <td nowrap="nowrap" id='listaCalendarios' ></td>
            </tr>
            <tr>
              <td nowrap="nowrap">Turma:</td>
              <td nowrap="nowrap" id='listaTurmas'></td>
            </tr>
            <tr>
              <td nowrap="nowrap">Período:</td>
              <td nowrap="nowrap" id='listaPeriodos'></td>
            </tr>
            
            <tr style="visibility:hidden;">
              <td nowrap="nowrap">Páginas</td>
              <td nowrap="nowrap" >
                <select id='numeroPaginas' >
                  <option value='1' selected="selected">1</option>
                  <option value='2'>2</option>
                  <option value='3'>3</option>
                  <option value='4'>4</option>
                  <option value='5'>5</option>
                </select>
              </td> 
            </tr>

            <tr id="vazio"></tr>


            <tr>
          <td>
            <? db_ancora("<b>Assinatura Adicional: </b>", "js_pesquisaRecHumano(true);", 1); ?>
          </td>
          <td colspan="3">
            <?
              db_input("0", 6, $Ied20_i_codigo, true, "text", 1, "onChange='js_pesquisaRecHumano(false);'");
              db_input("z01_numcgm", 10, $Iz01_numcgm, true, "hidden", 3);
              db_input("z01_nome", 73, $Iz01_nome, true, "text", 3);
            ?>
          </td>
        </tr>
        <tr>
            <td style="width: 150px"><b>Atividades: </b></td>
            <td id='ctnAtividades'></td>
          </tr>
          </table>
          
          
          
        </fieldset>
        
        <input type="hidden" value=<?php echo $iEscola ?> id="iEscola">

        
        
        
        <input type="button" value="Relatório" id="imprimir" disabled >
      
      </form>
    </div>
  </body>
  <?
    db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
  ?>
</html>
<script>

  var iEscola   = $F("iEscola");
  var oTurma    = new DBViewFormularioEducacao.ListaTurma();
  var oPeriodo  = new DBViewFormularioEducacao.ListaPeriodoAvaliacao();
  //var oRegencia = new DBViewFormularioEducacao.ListaDisciplinas();
  //oRegencia.show( $('listaRegencias') ) ;

  var oCalendario = new DBViewFormularioEducacao.ListaCalendario();
      oCalendario.setEscola(iEscola);
      oCalendario.getCalendarios();

  /**
   * Função realizada ao alterar o calendário
   * @return {function}
   */
  var fFunctionChangeCalendario = function() {

    var oCalendarioSelecionado = oCalendario.getSelecionados();
    
    oTurma.limpar();
    oPeriodo.limpaElemento();
    //oRegencia.clear();
    //$('imprimir').setAttribute('disabled', 'disabled');

    if ( oCalendarioSelecionado.iCalendario != "" ) {

      oTurma.setEscola(iEscola);
      oTurma.setCalendario(oCalendarioSelecionado.iCalendario);
      oTurma.getTurmas();
    } 
  };

  /**
   * Função de callBack após seleção para turma
   * @return {function}
   */
  var fFunctionCallbackOnChangeTurma = function() {

    var oTurmaSelecionado = oTurma.getSelecionados();

    if (oTurmaSelecionado.codigo_turma == "") {
      //$('imprimir').setAttribute('disabled', 'disabled');
      oPeriodo.limpaElemento();
      oRegencia.clear();
      return;
    } 
    oPeriodo.getPeriodos(oTurmaSelecionado.codigo_turma, oTurmaSelecionado.codigo_etapa, 2);
    //oRegencia.getDisciplinas(oTurmaSelecionado.codigo_turma, oTurmaSelecionado.codigo_etapa, false);
  }

  /**
   * Função de callBack após o carregamento para turma
   * @return {function} 
   */
  var fFunctionCallBackLoadTurma = function() {

    var oTurmaSelecionado = oTurma.getSelecionados();
    if (oTurmaSelecionado.codigo_turma == "") {
      return;
    }    
    oPeriodo.getPeriodos(oTurmaSelecionado.codigo_turma, oTurmaSelecionado.codigo_etapa, 2);
    //oRegencia.getDisciplinas(oTurmaSelecionado.codigo_turma, oTurmaSelecionado.codigo_etapa, false);
  }

  /**
   * Função realizada ao alterar período
   * @return {function}
   */
  var fFunctionChangePeriodo = function()  {

    var oPeriodoSelecionado = oPeriodo.getSelecionado();

    $('imprimir').setAttribute('disabled', 'disabled');
    
    if( oPeriodoSelecionado.iCodigo != "" ) {

      $('imprimir').removeAttribute('disabled');
      return;
    }
  }

  /**
   * Função realizada após carregamento dos períodos
   * @return {function}
   */
  var fFunctionLoadPeriodo = function() {

    var oPeriodoSelecionado = oPeriodo.getSelecionado();

    if( oPeriodoSelecionado.iCodigo != "" ) {

      //$('imprimir').removeAttribute('disabled');
      return;
    }

    //$('imprimir').setAttribute('disabled', 'disabled');
  }

  /**
  * seta os callback do calendário
  */
  oCalendario.setOnChangeCallBack(fFunctionChangeCalendario);
  oCalendario.show($('listaCalendarios'));

  /**
  * Seta callback na turma
  */
  oTurma.setCallbackOnChange(fFunctionCallbackOnChangeTurma);
  oTurma.setCallBackLoad(fFunctionCallBackLoadTurma);
  oTurma.show($('listaTurmas'));

  /**
   * Seta callback no periodo
   */
  oPeriodo.setCallBackChange(fFunctionChangePeriodo);
  oPeriodo.setCallBackLoad(fFunctionLoadPeriodo);
  oPeriodo.show($('listaPeriodos'));  
  
 /**
 * Função para imprimir os dados do formulário
 * @return
 */

  $('imprimir').observe("click", function () {

    
    

    var oCalendarioSelecionado  = oCalendario.getSelecionados();
    var oTurmaSelecionada       = oTurma.getSelecionados();
    var oPeriodoSelecionado     = oPeriodo.getSelecionado();
    var assadicional = document.getElementById("0").value;

    var nome = document.getElementById("z01_nome").value;
    var atv = document.getElementById("cboAtividades").options[document.getElementById("cboAtividades").selectedIndex].text;

    

    

    //var aRegencias              = [];

    /*oRegencia.getSelecionados().each( function(oRegenciaSelecionada) {
      aRegencias.push( oRegenciaSelecionada.iRegencia );
    });*/

    var sUrlRelatorio = 'edu_relinstrumentoavaliativo2.php';
    sUrlRelatorio    += '?escola='      + iEscola;
    sUrlRelatorio    += '&calendario='  + oCalendarioSelecionado.iCalendario;
    sUrlRelatorio    += '&turma='       + oTurmaSelecionada.codigo_turma;
    sUrlRelatorio    += '&periodo='     + oPeriodoSelecionado.iCodigo;
    if(assadicional){
      sUrlRelatorio += '&adicional='+assadicional+'&na='+encodeURIComponent(nome)+'&aa='+encodeURIComponent(atv);
    }else{
      sUrlRelatorio += '&adicional=nao';
    }
    
            
    jan = window.open(sUrlRelatorio,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0');
    jan.moveTo(0,0);
  });
  
  function js_pesquisaRecHumano(lMostra) {

if (lMostra) {

  js_OpenJanelaIframe('CurrentWindow.corpo',
                      'db_iframe_rechumano',
                      'func_rechumanoescolanovo.php?funcao_js=parent.js_mostraRecHumano|0|z01_nome|z01_numcgm',
                      'Pesquisa Recurso Humano',
                      true
                     );
} else if ($F('0') != '') {

  js_OpenJanelaIframe('CurrentWindow.corpo',
                      'db_iframe_rechumano',
                      'func_rechumanoescolanovo.php?funcao_js=parent.js_mostraRecHumano1&pesquisa_chave='+$F('0'),
                      'Pesquisa Recurso Humano',
                      false
                     );
} else {

  $('0').value = '';
  $('z01_nome').value      = '';
  $('z01_numcgm').value    = '';
  oCboAtividades.clearItens();
  oCboAtividades.setDisable(true);
}
}

function js_mostraRecHumano() {

$('0').value = arguments[0];
$('z01_nome').value      = arguments[1];
$('z01_numcgm').value    = arguments[2];
db_iframe_rechumano.hide();
js_atividadesDocente();
}

function js_mostraRecHumano1() {

$('z01_nome').value   = arguments[0];
$('z01_numcgm').value = arguments[1];

if (arguments[1] == true) {

  $('0').value = '';
  $('z01_nome').value      = arguments[0];
  $('z01_numcgm').value    = '';
  oCboAtividades.setDisable(true);
} else {
  js_atividadesDocente();
}
}


oCboAtividades = new DBComboBox("cboAtividades", "oCboAtividades", null, "330px");
oCboAtividades.addItem("", "");
oCboAtividades.setDisable(true);
oCboAtividades.show($('ctnAtividades'));

function js_atividadesDocente() {

var oParametro     = new Object();
oParametro.exec    = 'buscaAtividadesServidor';
oParametro.iNumCgm = $F('z01_numcgm');

js_divCarregando("Aguarde, carregando as atividades do funcionário.", "msgBox");
var oAjax = new Ajax.Request(
                             'edu_educacaobase.RPC.php',
                             {
                               method: 'post',
                               parameters: 'json='+Object.toJSON(oParametro),
                               onComplete: js_retornaAtividadesDocente
                             }
                            );
}

function js_retornaAtividadesDocente(oResponse) {

oCboAtividades.setEnable(true);
oCboAtividades.clearItens();
oCboAtividades.addItem("", "");
js_removeObj("msgBox");
var oRetorno = eval('('+oResponse.responseText+')');

if (oRetorno.aAtividades.length > 0) {

 oRetorno.aAtividades.each(function(oLinha, iSeq) {

   oCboAtividades.addItem(oLinha.iCodigo, oLinha.sDescricao.urlDecode());
   if (oRetorno.aAtividades.length == 1) {
     oCboAtividades.setValue(oLinha.iCodigo);
   }
 });
}

}


</script>