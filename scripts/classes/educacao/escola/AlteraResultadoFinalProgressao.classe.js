require_once("scripts/widgets/DBLookUp.widget.js");

var MSGEALTERARESULTADOFINAL = 'educacao.escola.AlteraResultadoFinal.';


/**
 * Altera o resultado da progressão parcial
 * @param {Object} oDados       objeto com as progressões na turma
 * @param {Object} oElementoRF  informação do elemento que gera o resultado final
 * @param {sting}  sMascara     mascara para o valor
 */
var AlteraResultadoFinalProgressao = function ( oDados, oElementoRF, sMascara ) {

  /**
   * Dados do aluno e suas progressões
   * @type {Object}
   */
  this.oDadosAluno = oDados;

  /**
   * Dados do elemento que gera Resultado Final
   * @type {Object}
   */
  this.oElemento = oElementoRF;

  this.sMascara = sMascara;

  this.sRPC = 'edu4_alterarresultadofinalprogressao.RPC.php';

  this.oWindowContainer     = null;
  this.oCallBackCloseWindow = function() {
    return true;
  };

  this.oBtnSalvar  = AlteraResultadoFinalProgressao.createInput('btnSalvarRF', 'Salvar');
  this.oBtnExcluir = AlteraResultadoFinalProgressao.createInput('btnExlcuirRF', 'Excluir Resultado Final');
  this.oBtnExcluir.setAttribute('disabled', 'disabled');

  this.oCboAvaliacaoNivel  = new Element('select', {'id':'novaAvaliacao', 'disabled':true});
  this.oInputAvaliacaoNota = new Element('input', {'type':'text', 'name':'novaAvaliacao', 'id':'novaAvaliacao',
                                                   'disabled':true, 'class':'field-size2'});

  this.oInputAvaliacaoNota.addEventListener('change', function() {

    this.validaNovaNotaFinal();
  }.bind(this));

  this.oInputAvaliacaoNota.addEventListener('drop', function(event) {

    event.preventDefault();
    return false;
  });

  this.oBtnSalvar.addEventListener('click', function() {

    this.salvar();
  }.bind(this));

  this.oBtnExcluir.addEventListener('click', function() {

    this.remover();
  }.bind(this));
};



/**
 * Seta em cima de qual container irá abrir
 * @param {Object} oWindow
 */
AlteraResultadoFinalProgressao.prototype.setContainer = function (oWindow) {
  this.oWindowContainer = oWindow;
};

/**
 * Ao fechar a janela, recarrega a tela anterior
 * @param {Function} fFunction
 */
AlteraResultadoFinalProgressao.prototype.setCallBackWindow = function (fFunction) {
  this.oCallBackCloseWindow = fFunction;
};

/**
 * cria o container html da view
 * @return {string}
 */
AlteraResultadoFinalProgressao.createContainer = function() {

  var sContainer  = "<div class='container' style='width:610px;'>";

      sContainer += "  <form id='frmAlterarRF'> ";
      sContainer += "    <fieldset  > ";
      sContainer += "      <legend>Alterar Resultado Final</legend> ";
      sContainer += "      <table class='form-container'> ";

      sContainer += "        <tr> ";
      sContainer += "          <td class='field-size4'><label for='cboDisciplina' >Disciplina:</label></td> ";
      sContainer += "          <td> ";
      sContainer += "            <select id='cboDisciplina' > ";
      sContainer += "              <option value=''>Selecione...</option> ";
      sContainer += "            </select> ";
      sContainer += "          </td> ";
      sContainer += "        </tr> ";

      sContainer += "        <tr> ";
      sContainer += "          <td class='field-size4'> ";
      sContainer += "            <label for='nomeProfessor'> ";
      sContainer += "              <span id='labelProfressor' style='display:none;'>Professor:</span> ";
      sContainer += "              <a href='#' id='ancoraProfessor'>Professor:</a> ";
      sContainer += "            </label> ";
      sContainer += "            </td> ";
      sContainer += "          <td> ";
      sContainer += "            <input type='hidden' name='codigoProfessor' id='codigoProfessor' class='field-size2' /> ";
      sContainer += "            <input type='text' name='nomeProfessor' id='nomeProfessor' class='field-size-max readonly' disabled='disabled' /> ";
      sContainer += "          </td> ";
      sContainer += "        </tr> ";

      sContainer += "        <tr> ";
      sContainer += "          <td class='field-size4'><label for='cboFormaAprovacao'>Forma de Aprovação:</label></td> ";
      sContainer += "          <td> ";
      sContainer += "            <select id='cboFormaAprovacao' disabled='disabled' > ";
      sContainer += "              <option value=''>Selecione...</option> ";
      sContainer += "              <option value='1'>Aprovado pelo conselho</option> ";
      sContainer += "              <option value='2'>Reclassificação por baixa frequência</option> ";
      sContainer += "              <option value='3'>Aprovado Conforme Regimento Escolar</option> ";
      sContainer += "            </select> ";
      sContainer += "          </td> ";
      sContainer += "        </tr> ";

      sContainer += "        <tr> ";
      sContainer += "          <td><label for='cboAlterarAvaliacaoFinal'>Alterar Avaliação Final:</label></td> ";
      sContainer += "          <td> ";
      sContainer += "            <select id='cboAlterarAvaliacaoFinal' disabled='disabled' > ";
      sContainer += "              <option value=''>Selecione...</option> ";
      sContainer += "              <option value='1'>Não informar</option> ";
      sContainer += "              <option value='2'>Informar e Substituir</option> ";
      sContainer += "              <option value='3'>Informar e NÃO Substituir</option> ";
      sContainer += "            </select> ";
      sContainer += "          </td> ";
      sContainer += "        </tr> ";

      sContainer += "          <tr nowrap='nowrap' id='linhaNovaAvaliacao'>";
      sContainer += "            <td class='bold' nowrap='nowrap' ><label for=''>Avaliação:</label></td>";
      sContainer += "            <td id='ctnNovaAvaliacao' nowrap='nowrap'> </td>";
      sContainer += "          </tr>";

      sContainer += "        <tr id='legendaAlterarNotaFinal' style='display:none;' >";
      sContainer += "          <td class='bold' style='white-space:pre-line;' colspan=2>";
      sContainer += "            <fieldset class='separator' '>";
      sContainer += "              <legend>Legenda</legend>";
      sContainer += "              <label style='color:#D80000;'> Informar e Substituir:</label>";
      sContainer += "              <label style='font-weight:normal;'>O sistema irá substituir a nota final do aluno ";
      sContainer += "                em todos os relatórios e consultas (boletim de desempenho, histórico escolar,  ";
      sContainer += "                ficha individual, ata de resultados finais entre outros)</label>";
      sContainer += "              </br> ";
      sContainer += "              <label style='color:#D80000;'> Informar e NÃO Substituir:</label>";
      sContainer += "              <label style='font-weight:normal;'>O sistema registrará a nota final nas observações";
      sContainer += "                dos relatórios que contenham o  resultado final do aluno.</label>";
      sContainer += "            </fieldset>";
      sContainer += "          </td>";
      sContainer += "        </tr>";

      sContainer += "        <tr >";
      sContainer += "          <td colspan='2'>";
      sContainer += "            <fieldset class='separator'>";
      sContainer += "              <legend>Justificativa</legend>";
      sContainer += "              <textarea id='justificativaResultado' rows='4' style='width:100%'></textarea>";
      sContainer += "            </fieldset>";
      sContainer += "          </td>";
      sContainer += "        </tr>";

      sContainer += "      </table> ";
      sContainer += "    </fieldset> ";
      sContainer += "    <div id='ctnBotoesAlterarRF'> ";
      sContainer += "    </div> ";

      sContainer += "  </form> ";
      sContainer += "</div>";

  return sContainer;
};

/**
 * cria um input com as informacoes informadas
 * @param  {string} sId    id do elemento
 * @param  {string} sValue valor do elemento
 * @return {Object}
 */
AlteraResultadoFinalProgressao.createInput = function(sId, sValue) {

  var oInput   = document.createElement('input');
  oInput.type  = 'button';
  oInput.id    = sId
  oInput.value = sValue

  return oInput;
}

/**
 * Cria a windown aux
 * @return {void}
 */
AlteraResultadoFinalProgressao.prototype.createWindow = function() {

  this.oWindow = new windowAux("windowAlterarRF", "Alterar Resultado Final", 625, 500);
  this.oWindow.setShutDownFunction( function() {

    this.oWindow.destroy();
    this.oCallBackCloseWindow();
  }.bind(this));

  this.oWindow.setContent(AlteraResultadoFinalProgressao.createContainer());

  var sTitulo = "Aluno: " + this.oDadosAluno.sAluno;
  var sHelp   = "Selecione a disciplina para alterar o Resultado Final." ;

  new DBMessageBoard( 'ctnMsgBoard', sTitulo, sHelp, this.oWindow.getContentContainer());

  if ( !!this.oWindowContainer ) {
    this.oWindow.setChildOf(this.oWindowContainer);
  }

  this.oWindow.show();

  this.atribuirFuncoesValores();

};

/**
 * Atribui a cada campo seus callbacks e altera o stado dos campos conforme parâmetros
 * @return {void}
 */
AlteraResultadoFinalProgressao.prototype.atribuirFuncoesValores = function() {

  // atribui ao selecet disciplina as progressões
  for ( var oProgressao of this.oDadosAluno.aRegencias ) {

    var oOption = new Option(oProgressao.sRegencia, oProgressao.iRegencia);
    oOption.setAttribute('progressao', oProgressao.iProgressao);

    if ( oProgressao.lEncerrado || oProgressao.lEvadido ||
         (oProgressao.oResultadoFinal.sResultadoFinal == 'A' && oProgressao.oResultadoFinal.iAprovadoPeloConselho == 0)) {
      oOption.setAttribute('disabled', 'disabled');
    }
    $('cboDisciplina').add(oOption);
  }

  // define função ao trocar disciplina
  $('cboDisciplina').addEventListener('change', function() {

    this.validaDisciplinaSelecionada();
  }.bind(this));

  // de acordo com a forma de avaliação adiciona o input
  switch( this.oElemento.sFormaAvaliacao ) {

    case 'NOTA':

      $('ctnNovaAvaliacao').appendChild(this.oInputAvaliacaoNota);
      break;
    case 'NIVEL':

      $('ctnNovaAvaliacao').appendChild(this.oCboAvaliacaoNivel);
      break;
  }

  $('cboFormaAprovacao').addEventListener('change', function() {
    AlteraResultadoFinalProgressao.validaFormaAprovacaoSelecionada(this);
  });

  $('cboAlterarAvaliacaoFinal').addEventListener('change', function() {
    AlteraResultadoFinalProgressao.validaAlterarAvaliacaoSelecionada(this)
  });

  $('ctnBotoesAlterarRF').appendChild(this.oBtnSalvar);
  $('ctnBotoesAlterarRF').appendChild(this.oBtnExcluir);

  var oInput1 = document.createElement('input');
  var oInput2 = document.createElement('input');
  var oLookUp = new DBLookUp( $('ancoraProfessor'), oInput1, oInput2, {
    sArquivo: 'func_rechumano.php',
    sLabel: 'Pesquisa de Professor',
    sObjetoLookUp: 'db_iframe_rechumano',
    zIndex: '1500',
    aCamposAdicionais : ['ed20_i_codigo', 'z01_nome']
  });

  oLookUp.setCallBack('onClick', function(aCampos) {

    $('codigoProfessor').value     = aCampos[2];
    $('nomeProfessor').value       = aCampos[3];
    $('labelProfressor').innerHTML = aCampos[3];
  });
};


AlteraResultadoFinalProgressao.prototype.show = function() {

  this.createWindow();
};


/**
 * Limpa os dados do Formulário
 */
AlteraResultadoFinalProgressao.prototype.formReset = function( lTudo ) {

  if ( lTudo ) {

    $('frmAlterarRF').reset();
    return;
  }

  $('labelProfressor').innerHTML      = '';
  $('codigoProfessor').value          = '';
  $('nomeProfessor').value            = '';
  $('cboFormaAprovacao').value        = '';
  $('cboAlterarAvaliacaoFinal').value = '';
  $('novaAvaliacao').value            = '';
  $('justificativaResultado').value   = '';
  $('cboFormaAprovacao').setAttribute('disabled', 'disabled');
  $('cboAlterarAvaliacaoFinal').setAttribute('disabled', 'disabled');
  $('novaAvaliacao').setAttribute('disabled', 'disabled');

  if ( this.oElemento.sFormaAvaliacao == 'NIVEL') {
    $('novaAvaliacao').value = $('novaAvaliacao').options[0].value;
  }
};

/**
 * Altera os estado dos elementos conforme situação da disciplina
 * @return {void}
 */
AlteraResultadoFinalProgressao.prototype.validaDisciplinaSelecionada = function () {

  var cboDisciplina       = $('cboDisciplina');
  var lAprovadoAvaliacao  = true;
  var lAprovadoFrequencia = true;

  /**
   * Se disciplina já possue aprovação pelo conselho, busca os dados da alteração do resultado final e altera o formulário
   * para o modo de exclusão
   */
  for ( var oProgressao of this.oDadosAluno.aRegencias ) {

    if (oProgressao.iRegencia == cboDisciplina.value ) {

      lAprovadoAvaliacao  = oProgressao.oResultadoFinal.lAprovadoAvaliacao;
      lAprovadoFrequencia = oProgressao.oResultadoFinal.lAprovadoFrequencia;
    }

    // se for diferente de zero aluno possui aprovação pelo conselho
    if ( oProgressao.iRegencia == cboDisciplina.value && oProgressao.iAprovadoPeloConselho != 0) {

      this.buscarDadosAprovacaoConselho();
      return ;
    }
  }
  AlteraResultadoFinalProgressao.liberarBotoes(false);

  this.formReset(false);

  $('labelProfressor').style.display = 'none';
  $('ancoraProfessor').style.display = '';
  $('cboFormaAprovacao').removeAttribute('disabled');
  $('justificativaResultado').removeAttribute('disabled');

  $('cboFormaAprovacao').options[1].removeAttribute('disabled');
  $('cboFormaAprovacao').options[2].removeAttribute('disabled');
  $('cboFormaAprovacao').options[3].removeAttribute('disabled');

  if ( lAprovadoAvaliacao ) {

    $('cboFormaAprovacao').options[1].setAttribute('disabled', 'disabled');
    $('cboFormaAprovacao').options[3].setAttribute('disabled', 'disabled');
  }

  if ( lAprovadoFrequencia ) {
    $('cboFormaAprovacao').options[2].setAttribute('disabled', 'disabled');
  }
};

/**
 * Valida Forma de Aprovação selecionada.
 * Sempre que marcado como: Aprovado pelo conselho, devemos liberar o select para cliente selecionar como deseja
 * alterar o resultado final
 * @returns {void}
 */
AlteraResultadoFinalProgressao.validaFormaAprovacaoSelecionada = function (oCboFormaAprovacao) {

  var oCboAlterarAvaliacaoFinal = $('cboAlterarAvaliacaoFinal');
  var oInputAvaliacao           = $('novaAvaliacao');

  if (oInputAvaliacao.type == 'text') {
    oInputAvaliacao.value = '';
  }

  oCboAlterarAvaliacaoFinal.value = '';
  oCboAlterarAvaliacaoFinal.setAttribute('disabled', 'disabled');
  oInputAvaliacao.setAttribute('disabled', 'disabled');

  $('legendaAlterarNotaFinal').style.display = 'none';

  if ( oCboFormaAprovacao.value == 1 ) {

    oCboAlterarAvaliacaoFinal.removeAttribute('disabled');
    $('legendaAlterarNotaFinal').style.display = '';
  }
};

/**
 * Valida o status do select Alterar Nota Final, sempre que selecionado como:
 *  - Informar e Substituir ou Informar e NÃO Substituir : devemos liberar para informar a avaliação
 * @returns {void}
 */
AlteraResultadoFinalProgressao.validaAlterarAvaliacaoSelecionada = function (oCboAlterarAvaliacao) {

  var oInputAvaliacao = $('novaAvaliacao');
  if (oInputAvaliacao.type == 'text') {
    oInputAvaliacao.value = '';
  }

  oInputAvaliacao.setAttribute('disabled', 'disabled');
  if ( [2,3].in_array(oCboAlterarAvaliacao.value) ) {
    oInputAvaliacao.removeAttribute('disabled');
  }
};


/**
 * Valida se a nota informada esta dentro dos parâmetros da turma
 * @returns {Boolean}
 */
AlteraResultadoFinalProgressao.prototype.validaNovaNotaFinal = function () {

  var nNota       = +this.oInputAvaliacaoNota.value;
  var nNotaMinima = +this.oElemento.mMinimoAprovacao;
  var nNotaMaxima = +this.oElemento.iMaiorValor;

  if ( nNota < nNotaMinima ) {

    alert(_M(MSGEALTERARESULTADOFINAL+'avaliacao_abaixo_minimo', {'mMinimo':this.oElemento.mMinimoAprovacao}) );
    this.oInputAvaliacaoNota.value = '';
    return false;
  }

  if ( nNota > nNotaMaxima ) {

    var oErro = {'mMinimo':this.oElemento.mMinimoAprovacao, 'mMaximo': this.oElemento.iMaiorValor}
    alert(_M(MSGEALTERARESULTADOFINAL+'avaliacao_fora_intervalo', oErro));
    this.oInputAvaliacaoNota.value = '';
    return false;
  }

  if (!DBViewAvaliacao.ValidacaoVariacaoNota(nNota, this.oElemento.nVariacao, this.sMascara)) {

    alert('Intervalo de nota deve ser de ' + this.oElemento.nVariacao);
    this.oInputAvaliacaoNota.value = '';
    return false;
  }

  js_observeMascaraNota(this.oInputAvaliacaoNota, this.sMascara);
  return true;
};

/**
 * Valida se os campos obrigatórios estão preenchidos
 * @return {boolena}
 */
AlteraResultadoFinalProgressao.validaFormulario = function () {

  if ($F('cboDisciplina') == '') {

    alert( 'Selecione a disciplina.' );
    return false;
  }

  if ($F('cboFormaAprovacao') == '') {

    alert( _M(MSGEALTERARESULTADOFINAL + "selecione_forma_avaliacao"));
    return false;
  }

  if ($F('cboFormaAprovacao') == 1 && $F('cboAlterarAvaliacaoFinal') == '' ) {

    alert( _M(MSGEALTERARESULTADOFINAL + "selecione_altera_nota_final"));
    return false;
  }

  if ([2, 3].in_array($F('cboAlterarAvaliacaoFinal')) && $F('novaAvaliacao') == '') {

    alert(_M(MSGEALTERARESULTADOFINAL + "informe_avaliacao"));
    return false;
  }

  if ($F('justificativaResultado') == '') {

    alert( _M(MSGEALTERARESULTADOFINAL + "informe_justificativa") );
    return false;
  }

  return true;
};

/**
 * Salva um alteração do resultado final
 * @return {void}
 */
AlteraResultadoFinalProgressao.prototype.salvar = function() {

  if ( !AlteraResultadoFinalProgressao.validaFormulario() ) {
    return false;
  }

  oParametros = {
    'exec'              : 'salvar',
    'iProgressao'       : $('cboDisciplina').options[$('cboDisciplina').selectedIndex].getAttribute('progressao'),
    'iRegencia'         : $F('cboDisciplina'),
    'iProfessor'        : $F('codigoProfessor'),
    'iFormaAprovacao'   : $F('cboFormaAprovacao'),
    'iAlterarAvaliacao' : $F('cboAlterarAvaliacaoFinal'),
    'sNovaAvaliacao'    : $F('novaAvaliacao'),
    'sJustificativa'    : $F('justificativaResultado')
  };

  var oAjax = new AjaxRequest( this.sRPC, oParametros, function( oRetorno, lErro ) {

    alert( oRetorno.sMessage );

    if ( lErro ) {
      return;
    }

    this.atualizaFormaAprovacao($F('cboFormaAprovacao'));
    this.formReset(true);

  }.bind(this));

  oAjax.setMessage( "Salvando alteração do resultado final." );
  oAjax.execute();

};


/**
 * Remove uma alteração do resultado final
 * @return {void}
 */
AlteraResultadoFinalProgressao.prototype.remover = function() {

  if ($('cboDisciplina'))

  var oParametros = {
    'exec'        : 'excluir',
    'iProgressao' : $('cboDisciplina').options[$('cboDisciplina').selectedIndex].getAttribute('progressao'),
    'iRegencia'   : $F('cboDisciplina')
  };

  var oAjax = new AjaxRequest( this.sRPC, oParametros, function( oRetorno, lErro ) {


    alert( oRetorno.sMessage );

    this.atualizaFormaAprovacao(0);
    this.formReset(true);

    AlteraResultadoFinalProgressao.liberarBotoes(false);

  }.bind(this));

  oAjax.setMessage( "Excluindo alteração do resultado final." );
  oAjax.execute();

};


/**
 * atualiza em memória a situação da forma de aprovação
 * @param  {integer} iFormaAprovacao
 * @return {void}
 */
AlteraResultadoFinalProgressao.prototype.atualizaFormaAprovacao = function(iFormaAprovacao) {

  for ( var oProgressao of this.oDadosAluno.aRegencias ) {
    if (oProgressao.iRegencia == $F('cboDisciplina') ) {
      oProgressao.iAprovadoPeloConselho = iFormaAprovacao;
    }
  }
};

/**
 * controla estado dos botões Salvar e Excluir
 * @param  {boolean} lInverter
 * @return {void}
 */
AlteraResultadoFinalProgressao.liberarBotoes = function( lInverter ) {

  $('btnSalvarRF').removeAttribute('disabled');
  $('btnExlcuirRF').setAttribute('disabled', 'disabled');

  if (lInverter) {

    $('btnSalvarRF').setAttribute('disabled', 'disabled');
    $('btnExlcuirRF').removeAttribute('disabled');
  }
}

/**
 * Busca os dados de uma aprovação pelo conselho realizada em uma disciplina
 * @return {void}
 */
AlteraResultadoFinalProgressao.prototype.buscarDadosAprovacaoConselho = function() {

  var oParametros = {
    'exec'        : 'buscar',
    'iProgressao' : $('cboDisciplina').options[$('cboDisciplina').selectedIndex].getAttribute('progressao')
  };

  var oAjax = new AjaxRequest( this.sRPC, oParametros, function( oRetorno, lErro ) {

    $('codigoProfessor').value          = oRetorno.iProfessor;
    $('nomeProfessor').value            = oRetorno.sProfessor;
    $('cboFormaAprovacao').value        = oRetorno.iFormaAprovacao;
    $('cboAlterarAvaliacaoFinal').value = oRetorno.iAlterarNotaFinal;
    $('novaAvaliacao').value         = oRetorno.sNovaAvaliacao;
    $('justificativaResultado').value   = oRetorno.sJustificativaResultado;

    $('labelProfressor').style.display = '';
    $('ancoraProfessor').style.display = 'none';
    $('labelProfressor').innerHTML     = 'Professor:';

    $('cboFormaAprovacao').setAttribute('disabled', 'disabled');
    $('cboAlterarAvaliacaoFinal').setAttribute('disabled', 'disabled');
    $('novaAvaliacao').setAttribute('disabled', 'disabled');
    $('justificativaResultado').setAttribute('disabled', 'disabled');

    AlteraResultadoFinalProgressao.liberarBotoes(true);

  }.bind(this));

  oAjax.setMessage( "Buscando dados da alteração do resultado final." );
  oAjax.execute();
}
