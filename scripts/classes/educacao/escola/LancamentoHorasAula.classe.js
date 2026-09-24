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

var LancamentoHorasAula = function (iTurma, iEtapa) {

  this.oGridDisciplina     = null;
  this.oWindowAuxAvaliacao = null;
  this.iTurma              = iTurma;
  this.iEtapa              = iEtapa;
  this.iTamanhoJanela      = document.body.getWidth() / 1.2;
  this.sGridDisciplinas    = 'ctnGridDisciplinasAulasDadasPlugin';
  this.sRPC                = "LancamentoHorasAula.RPC.php";
};

LancamentoHorasAula.prototype.renderizarWindowAvaliacao = function() {

  var oSelf = this;

  this.oWindowAuxAvaliacao = new windowAux("wndHorasAula",
                                           "Aulas Dadas",
                                           this.iTamanhoJanela,
                                           360
                                          );

  this.oWindowAuxAvaliacao.setShutDownFunction( function() {
    oSelf.oWindowAuxAvaliacao.destroy();
  });

  var sConteudo  = "<div id='disciplinas_aluno'>                                               "
                 + "  <div id='divListaGrupo'>                                                 "
                 + "    <fieldset style='width:97%; margin-bottom:10px;'>                      "
                 + "      <legend><b>Disciplinas</b></legend>                                  "
                 + "      <div id='" + this.sGridDisciplinas + "' style='width:100%;'></div>   "
                 + "    </fieldset>                                                            "
                 + "    <center>                                                               "
                 + "      <input type='button' id='btnFechar' name='btnFechar' value='Fechar'> "
                 + "    </center>                                                              "
                 + "  </div>                                                                   "
                 + "</div>                                                                     ";

  this.oWindowAuxAvaliacao.setContent(sConteudo);

  var sTitulo = "Aulas Dadas";
  var sHelp   = "Informe o total de aulas dadas para cada disciplina.";

  new DBMessageBoard( 'ctnMsgBoard', sTitulo, sHelp, this.oWindowAuxAvaliacao.getContentContainer());
  this.oWindowAuxAvaliacao.show();

  $('btnFechar').onclick = function (){
    oSelf.oWindowAuxAvaliacao.destroy();
  };
};

LancamentoHorasAula.prototype.buscaPeriodosRegencia = function () {

  var oSelf = this;

  var oParametros = {
    exec : 'buscar',
    iTurma : this.iTurma,
    iEtapa : this.iEtapa
  };

  var oAjax = new AjaxRequest( this.sRPC, oParametros, function( oRetorno, lErro ){
    oSelf.renderizaGridDisciplinas( oRetorno, lErro );
  } );
  oAjax.setMessage( 'Aguarde, buscando os períodos das disciplinas...' );
  oAjax.execute();
};

LancamentoHorasAula.prototype.renderizaGridDisciplinas = function( oRetorno, lErro ) {

  var oSelf = this;
  if ( lErro ) {

     this.oWindowAuxAvaliacao.destroy();
     alert( oRetorno.sMessage.urlDecode() );
     return;
  }

  var iElementos      = oRetorno.aDisciplinas[0].aPeriodos.length;
  var iTamanhoPeriodo = (75 / iElementos);

  var aAling  = ['left'];
  var aHeader = ['Disciplina'];
  var aWidth  = ['25%'];
  for ( var oPeriodo of oRetorno.aDisciplinas[0].aPeriodos) {


    aAling.push('center');
    aHeader.push(oPeriodo.sNome);
    aWidth.push(iTamanhoPeriodo + '%');
  }

  delete this.oGridDisciplina;

  this.oGridDisciplina              = new DBGrid('gridAulasDadas');
  this.oGridDisciplina.nameInstance = 'oGridAulasDadas';

  this.oGridDisciplina.setCellWidth(aWidth);
  this.oGridDisciplina.setCellAlign(aAling);
  this.oGridDisciplina.setHeader(aHeader);
  this.oGridDisciplina.setHeight(120);
  this.oGridDisciplina.show( $(this.sGridDisciplinas) );
  this.oGridDisciplina.clearAll(true);

  for (var oRegencia of oRetorno.aDisciplinas ) {

    var aLinha = [];
    aLinha.push(oRegencia.sDescricao);

    for (var oPeriodo of oRegencia.aPeriodos ) {

      var lBloqueiaPeriodo = oRegencia.lEncerrada || oRegencia.lSomenteAvaliacao;
      aLinha.push( oSelf.createInput(oRegencia.iCodigo, oPeriodo, lBloqueiaPeriodo) );
    }

    oSelf.oGridDisciplina.addRow(aLinha);
  }

  this.oGridDisciplina.renderRows();

  for (var elemento of $$('.input_aulas_dadas')) {
    elemento.observe('change', LancamentoHorasAula.salvarAulasDadas);
  }
};


LancamentoHorasAula.prototype.createInput = function (iRegencia, oDadosPeriodo, lBloqueiaPeriodo) {

  var sId    = 'regencia#'+iRegencia+'#periodo#'+oDadosPeriodo.iCodigo;
  var oInput = document.createElement('input');

  oInput.setAttribute( 'value', oDadosPeriodo.iAulas);
  oInput.setAttribute( 'id', sId);
  oInput.setAttribute( 'ordem', oDadosPeriodo.iOrdem);
  oInput.setAttribute( 'codigo-periodo', oDadosPeriodo.iCodigo);
  oInput.setAttribute( 'regencia', iRegencia);
  oInput.setAttribute( 'maxlength', 4);
  oInput.setAttribute( 'onkeypress', "return js_mask(event, '0-9');");
  oInput.setAttribute( 'ondrop', "return false;");
  oInput.style.width = '100%';

  oInput.addClassName("input_aulas_dadas text-right");

  if (lBloqueiaPeriodo) {

    oInput.setAttribute('disabled', 'disabled');
    oInput.addClassName('readonly');
  }

  return oInput.outerHTML;
}

LancamentoHorasAula.salvarAulasDadas = function () {

 var oInput = this;

  var oParametros = {
    'exec'              : 'salvarAulasDadas',
    'iRegencia'         : oInput.getAttribute('regencia'),
    'iPeriodoAvaliacao' : oInput.getAttribute('codigo-periodo'),
    'iTotalAulas'       : oInput.getValue()
  };

  var oAjax = new AjaxRequest( 'edu4_lancamentoavaliacoesturma.RPC.php', oParametros, function( oRetorno, lErro ){

    alert( oRetorno.sMessage.urlDecode() );

    if ( lErro ) {
      return false;
    }
  });
  oAjax.setMessage( 'Aguarde, salvando horas aula.' );
  oAjax.execute();
};

LancamentoHorasAula.prototype.show = function() {

  if ($('wndHorasAula') != null) {
    return true;
  }

  this.renderizarWindowAvaliacao();
  this.buscaPeriodosRegencia();
};