require_once("scripts/classes/DBViewLancamentoAvaliacao/DBViewLancamentoAvaliacao.classe.js");
require_once("scripts/classes/DBViewLancamentoAvaliacao/DisciplinaTurma.classe.js");
require_once("scripts/classes/DBViewLancamentoAvaliacao/AlunoTurma.classe.js");
require_once("scripts/classes/DBViewLancamentoAvaliacao/PeriodoTurma.classe.js");
require_once('scripts/widgets/DBToggleList.widget.js');
require_once("scripts/datagrid.widget.js");
require_once("scripts/object.js");
require_once("scripts/widgets/DBLookUp.widget.js");
require_once("scripts/AjaxRequest.js");

LancamentoAmparo = function ( oDadosAmparo ) {

  /**
   * Largura da janela
   * @var {integer}
   */

  this.iTamanhoJanela = document.body.getWidth() / 1.1;

  /**
   * Altura da janela
   * @var {integer}
   */
  this.iAlturaJanela  = document.body.getHeight();

  /**
   * RPC para as requisições
   * @var {string}
   */
  this.sUrlRPC = 'edu4_amparoprogressao.RPC.php';

  /**
   * Onde será renderizado a windown. Se vazio, cria uma janela.
   */
  this.oView = '';

  this.oTogglePeriodos = null;

  this.oWindowContainer = null;

  this.oWindowPai = null;

  this.oDadosAmparo = oDadosAmparo;

  this.iProgressaoSelecionada = null;

  /**
   * Variável para setar uma função a ser excutada ao fechar a janela
   * @var {function}
   */
  this.oCallBackCloseWindow = function() {
    return true;
  };
};

LancamentoAmparo.prototype.montaHTML = function() {

  var oContainerAux = '';
  oContainerAux    += '<div class="container" style="width:580px;">';
  oContainerAux    += '  <form id="frmAmparo">';
  oContainerAux    += '    <fieldset>';
  oContainerAux    += '      <legend>Amparo</legend>';
  oContainerAux    += '      <fieldset class="separator">';
  oContainerAux    += '        <legend>Selecione a Disciplina</legend>';
  oContainerAux    += '        <div style="width: 95%; text-align: left;">';
  oContainerAux    += '          <label for="cboDisciplina" class="bold field-size2" style="margin-right: 8px;">';
  oContainerAux    += '            Disciplina:';
  oContainerAux    += '          </label>';
  oContainerAux    += '          <select id="cboDisciplina" style="width: calc(100% - 83px);">';
  oContainerAux    += '            <option value="">Selecione...</option>';
  oContainerAux    += '          </select>';
  oContainerAux    += '        </div>';
  oContainerAux    += '      </fieldset>';
  oContainerAux    += '      <fieldset class="separator">';
  oContainerAux    += '        <legend>Selecione os Períodos</legend>';
  oContainerAux    += '         <div id="ctnPeriodoTurma" class="subcontainer"></div>';
  oContainerAux    += '      </fieldset>';
  oContainerAux    += '      <fieldset class="separator">';
  oContainerAux    += '        <legend>Amparo</legend>';
  oContainerAux    += '        <table class="form-container" style="width: 90%">';
  oContainerAux    += '          <tr>';
  oContainerAux    += '            <td class="field-size2">';
  oContainerAux    += '              <label for="cboTipoAmparo" class="bold " >';
  oContainerAux    += '                Tipo Amparo:';
  oContainerAux    += '              </label>';
  oContainerAux    += '            </td>';
  oContainerAux    += '            <td>';
  oContainerAux    += '              <select id="cboTipoAmparo" >';
  oContainerAux    += '                <option value="">Selecione...</option>';
  oContainerAux    += '                <option value="J">Amparo com Justificativa</option>';
  oContainerAux    += '                <option value="C">Amparo com Convenção</option>';
  oContainerAux    += '              </select>';
  oContainerAux    += '            </td>';
  oContainerAux    += '          </tr>';
  oContainerAux    += '          <tr id="linhaJustificativa"  style="display:none">';
  oContainerAux    += '            <td class="field-size2">';
  oContainerAux    += '              <label id="labelTipoAmparo" style:"display: none;"></label>';
  oContainerAux    += '              <a href="#" id="ancoraTipoAmparo" ></a>';
  oContainerAux    += '            </td>';
  oContainerAux    += '            <td>';
  oContainerAux    += '              <input type="text" class="field-size2" id="iTipoAmparo" oninput="js_ValidaCampos(this,1,\'Campo\',\'t\',\'f\',event);" />';
  oContainerAux    += '              <input type="text" id="sCodigoTipoAmparo"  />';
  oContainerAux    += '            </td>';
  oContainerAux    += '          </tr>';
  oContainerAux    += '        </table>';
  oContainerAux    += '      </fieldset>';
  oContainerAux    += '    </fieldset>';
  oContainerAux    += '    <input type="button" value="Salvar" id="btnSalvarAmparo"/>';
  oContainerAux    += '    <input type="button" disabled="disabled" value="Excluir Amparo" id="btnExcluirAmparo"/>';
  oContainerAux    += '  </form>';
  oContainerAux    += '</div>';

  return oContainerAux;
};

LancamentoAmparo.prototype.criarWindow = function () {

  var oSelf = this;

  this.oWindowAmparo = new windowAux("wndAmparo", "Lançar Amparo", this.iTamanhoJanela, this.iAlturaJanela);

  if ( !empty( this.oWindowPai ) ) {
    this.oWindowAmparo.setChildOf( this.oWindowPai );
  }
  this.oWindowAmparo.setShutDownFunction( function() {

    oSelf.oCallBackCloseWindow();
    oSelf.oWindowAmparo.destroy();
  });

  var oContainerAux = this.montaHTML();

  this.oTogglePeriodos = new DBToggleList([{sId:    'sPeriodo',
                                            sLabel: 'Período'
                                           }],
                                          {},
                                          'sTogglePeriodos'
                                         );

  this.oTogglePeriodos.closeOrderButtons();

  var sMsg     = "Aluno: " + this.oDadosAmparo.sAluno;
  var sHelpMsg = "Informe os períodos a serem amparados.";

  if (this.oView != "") {
    this.oView.innerHTML = oContainerAux;
  } else {

    this.oWindowAmparo.setContent(oContainerAux);
    this.oWindowAmparo.oMessageBoard = new DBMessageBoard('msgBoardLancamentoAmparo',
                                                          sMsg,
                                                          sHelpMsg,
                                                          this.oWindowAmparo.getContentContainer()
                                                         );

    if (this.oWindowContainer != null) {
      this.oWindowAmparo.setChildOf(this.oWindowContainer);
    }
    this.oWindowAmparo.show();
  }

  this.oTogglePeriodos.show($('ctnPeriodoTurma'));
  this.preencheDisciplina();
  this.defineFuncaoDisciplina();
  this.defineFuncaoTipoAmparo();
  this.defineAcoesBotoes();
};

LancamentoAmparo.prototype.preencheDisciplina = function() {

  for ( var oRegencia of this.oDadosAmparo.aRegencias ) {

    var oOpcaoDisciplina = new Element('option', {'value':oRegencia.iRegencia }).update(oRegencia.sRegencia.urlDecode());
    if ( oRegencia.lEncerrado || oRegencia.lEvadido) {
      oOpcaoDisciplina.setAttribute('disabled', 'disabled');
    }

    $('cboDisciplina').add(oOpcaoDisciplina);
  }

  if ( this.oDadosAmparo.aRegencias.length == 1 ) {

    var oProgressao = this.oDadosAmparo.aRegencias[0];
    if ( oProgressao.lEncerrado || oProgressao.lEvadido ) {
      return;
    }
    $('cboDisciplina').value = oProgressao.iRegencia;
    this.changeDisciplina();
  }
};

LancamentoAmparo.prototype.changeDisciplina = function() {

    var lRegenciaAmparada = false;

    this.oTogglePeriodos.clearAll();
    this.liberaFormulario();
    this.iProgressaoSelecionada = null;

    $('cboTipoAmparo').value = '';
    $('cboTipoAmparo').addEventListener('change', this.changeTipoAmparo(), false);

    for ( var oRegencia of this.oDadosAmparo.aRegencias ) {

      if ( oRegencia.iRegencia == $F('cboDisciplina') ) {

        this.iProgressaoSelecionada = oRegencia.iProgressao;

        for ( var oPeriodo of oRegencia.aPeriodos ) {

          var oDadoPeriodo      = {};
          oDadoPeriodo.iPeriodo = oPeriodo.iPeriodo;
          oDadoPeriodo.sPeriodo = oPeriodo.sDescricaoPeriodo.urlDecode();


          if ( oPeriodo.lAmparado ) {
            this.oTogglePeriodos.addSelected(oDadoPeriodo);
            lRegenciaAmparada = true;
          } else {
            this.oTogglePeriodos.addSelect(oDadoPeriodo);
          }
        }
      }
    }
    this.oTogglePeriodos.show($('ctnPeriodoTurma'));

    if ( lRegenciaAmparada ) {
      this.buscaTipoAmparo();
    }
};

LancamentoAmparo.prototype.defineFuncaoDisciplina = function() {

  var _this = this;

  $('cboDisciplina').onchange = function() {

    _this.changeDisciplina();
  };
};

LancamentoAmparo.prototype.changeTipoAmparo = function() {

    var oLookTipoAmparo = null;
    $('iTipoAmparo').removeAttribute('lang');
    $('sCodigoTipoAmparo').removeAttribute('lang');
    $('iTipoAmparo').removeClassName('field-size2');
    $('sCodigoTipoAmparo').removeClassName('field-size8');
    $('sCodigoTipoAmparo').removeClassName('readonly');
    $('iTipoAmparo').value             = '';
    $('sCodigoTipoAmparo').value       = '';
    $('labelTipoAmparo').style.display = 'none';

    switch ( $F('cboTipoAmparo') ) {
      case 'J':

        $('iTipoAmparo').setAttribute( 'lang', 'ed06_i_codigo' );
        $('sCodigoTipoAmparo').setAttribute( 'lang', 'ed06_c_descr' );

        $('ancoraTipoAmparo').innerHTML = 'Justificativa:';
        $('labelTipoAmparo').innerHTML = 'Justificativa:';

        oLookTipoAmparo = new DBLookUp( $('ancoraTipoAmparo'), $('iTipoAmparo'), $('sCodigoTipoAmparo'), {
          sArquivo: 'func_justificativa.php',
          sLabel: 'Pesquisa de Justificativas',
          sObjetoLookUp: 'db_iframe_justificativa',
          zIndex: '1500'
        });

        $('linhaJustificativa').style.display = 'table-row';
        break;

      case 'C':

        $('iTipoAmparo').setAttribute( 'lang', 'ed250_i_codigo' );
        $('sCodigoTipoAmparo').setAttribute( 'lang', 'ed250_c_descr' );

        $('ancoraTipoAmparo').innerHTML = 'Convenção:';
        $('labelTipoAmparo').innerHTML = 'Convenção:';

        oLookTipoAmparo = new DBLookUp( $('ancoraTipoAmparo'), $('iTipoAmparo'), $('sCodigoTipoAmparo'), {
          sArquivo: 'func_convencaoamp.php',
          sLabel: 'Pesquisa de Convenção',
          sObjetoLookUp: 'db_iframe_justificativa',
          zIndex: '1500'
        });

        $('linhaJustificativa').style.display = 'table-row';
        break;

      default:

        $('ancoraTipoAmparo').innerHTML = '';
        $('linhaJustificativa').style.display = 'none';
        break;
    }
};

LancamentoAmparo.prototype.defineFuncaoTipoAmparo = function() {

  var _this = this;
  $('cboTipoAmparo').onchange = function(){

    _this.changeTipoAmparo();
  };
};

LancamentoAmparo.prototype.buscaTipoAmparo = function() {

  var _this = this;
  var oParametros = { "exec":"buscaTipoAmparo", "iProgressao":this.iProgressaoSelecionada };
  var oAjax = new AjaxRequest( this.sUrlRPC, oParametros, function( oRetorno, lErro ) {

    if ( lErro ) {

      alert( oRetorno.sMessage );
      return;
    }

    $('cboTipoAmparo').value     = oRetorno.sTipoAmparo;
    $('cboTipoAmparo').addEventListener('change', _this.changeTipoAmparo(), false);
    $('iTipoAmparo').value       = oRetorno.iJustificativaConvencao;
    $('sCodigoTipoAmparo').value = oRetorno.sJustificativaConvencao;

    _this.bloqueiaFormulario();
  });

  oAjax.setMessage( "Aguarde, buscando o tipo de amparo..." );
  oAjax.execute();
};

LancamentoAmparo.prototype.show = function() {

  if ( !this.oWindow ) {
    this.criarWindow();
  }
};

LancamentoAmparo.prototype.setWindowPai = function (oWindowPai) {
  this.oWindowPai = oWindowPai;
};


LancamentoAmparo.prototype.defineAcoesBotoes = function() {

  var _this = this;

  $('btnSalvarAmparo').onclick = function() {

    if ( !_this.validaFormulario() ) {
      return;
    }

    var oParametros                     = {};
    oParametros.aPeriodosAmparados      = [];
    oParametros.exec                    = "salvar";
    oParametros.iProgressao             = _this.iProgressaoSelecionada;
    oParametros.iRegencia               = $F('cboDisciplina')
    oParametros.sTipoAmparo             = $F('cboTipoAmparo');
    oParametros.iJustificativaConvencao = $F('iTipoAmparo');

    for ( var oPeriodo of _this.oTogglePeriodos.getSelected() ) {
      oParametros.aPeriodosAmparados.push( oPeriodo.iPeriodo );
    }

    var oAjax = new AjaxRequest( _this.sUrlRPC, oParametros, function( oRetorno, lErro ) {

      for ( var oRegencia of _this.oDadosAmparo.aRegencias ) {
        if ( oRegencia.iRegencia == $F('cboDisciplina') ) {
          for ( var oPeriodo of oRegencia.aPeriodos ) {
            for ( var oPeriodoSelecionado of _this.oTogglePeriodos.getSelected() ) {
              if ( oPeriodoSelecionado.iPeriodo ==  oPeriodo.iPeriodo) {
                oPeriodo.lAmparado = true;
              }
            }
          }
        }
      }

      alert( oRetorno.sMessage.urlDecode() );
      _this.limparFormulario();
    });

    oAjax.setMessage( "Aguarde, buscando o tipo de amparo..." );
    oAjax.execute();

  };

  $('btnExcluirAmparo').onclick = function() {

    var oParametros                     = {};
    oParametros.aPeriodosAmparados      = [];
    oParametros.exec                    = "excluir";
    oParametros.iProgressao             = _this.iProgressaoSelecionada;
    oParametros.iRegencia               = $F('cboDisciplina')
    oParametros.sTipoAmparo             = $F('cboTipoAmparo');
    oParametros.iJustificativaConvencao = $F('iTipoAmparo');

    var oAjax = new AjaxRequest( _this.sUrlRPC, oParametros, function( oRetorno, lErro ) {

    for ( var oRegencia of _this.oDadosAmparo.aRegencias ) {
      if ( oRegencia.iRegencia == $F('cboDisciplina') ) {
        for ( var oPeriodo of oRegencia.aPeriodos ) {
          oPeriodo.lAmparado = false;
        }
      }
    }

      alert( oRetorno.sMessage.urlDecode() );
      _this.limparFormulario();
    });

    oAjax.setMessage( "Aguarde, buscando o tipo de amparo..." );
    oAjax.execute();

  };
};

LancamentoAmparo.prototype.validaFormulario = function() {

  var _this = this;

  if ( empty($F('cboDisciplina')) ) {

    alert('Selecione uma disciplina.');
    return false;
  }

  if ( empty(_this.oTogglePeriodos.getSelected()) ) {

    alert('Selecione ao menos um período.');
    return false;
  }

  if ( empty($F('cboTipoAmparo')) ) {

    alert('Selecione o tipo de amparo.');
    return false;
  }

  if ( empty($F('iTipoAmparo')) ) {

    alert('Selecione uma justificativa ou convenção.');
    return false;
  }

  return true;
};

LancamentoAmparo.prototype.bloqueiaFormulario = function() {

  $('cboTipoAmparo').setAttribute('disabled', 'disabled');
  $('cboTipoAmparo').addClassName('readonly');
  $('iTipoAmparo').setAttribute('disabled', 'disabled');
  $('iTipoAmparo').addClassName('readonly');
  $('labelTipoAmparo').style.display  = "";
  $('ancoraTipoAmparo').style.display = "none";
  $('btnExcluirAmparo').removeAttribute('disabled');
  $('btnSalvarAmparo').setAttribute('disabled', 'disabled');
  this.oTogglePeriodos.disable();
};

LancamentoAmparo.prototype.liberaFormulario = function() {

  $('cboTipoAmparo').removeAttribute('disabled');
  $('cboTipoAmparo').removeClassName('readonly');
  $('iTipoAmparo').removeAttribute('disabled');
  $('iTipoAmparo').removeClassName('readonly');
  $('labelTipoAmparo').style.display  = "none";
  $('ancoraTipoAmparo').style.display = "";
  $('btnExcluirAmparo').setAttribute('disabled', 'disabled');
  $('btnSalvarAmparo').removeAttribute('disabled');
  this.oTogglePeriodos.enable();
};

LancamentoAmparo.prototype.limparFormulario = function() {

  $('cboDisciplina').value   = '';
  $('cboTipoAmparo').value   = '';
  $('cboDisciplina').addEventListener('change', this.changeDisciplina(), false);
  $('cboTipoAmparo').addEventListener('change', this.changeTipoAmparo(), false);
}

/**
 * Seta uma funcção para ser executada ao se fechar a janela
 * @param {function} fFunction
 * @returns void
 */
LancamentoAmparo.prototype.setCallBackWindow = function (fFunction) {
  this.oCallBackCloseWindow = fFunction;
};
