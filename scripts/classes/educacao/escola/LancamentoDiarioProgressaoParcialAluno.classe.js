require_once('estilos/DBViewLancamentoAvaliacao.css');
require_once("scripts/DBFormCache.js");
require_once("scripts/classes/DBViewLancamentoAvaliacao/DBViewLancamentoAvaliacao.classe.js");
require_once("scripts/classes/DBViewLancamentoAvaliacao/ValidacaoVariacaoNota.classe.js");
require_once("scripts/classes/educacao/escola/LancamentoAmparoProgressao.classe.js");
require_once("scripts/classes/educacao/escola/AlteraResultadoFinalProgressao.classe.js");


/**
 * Classe responsável por lançar as avaliações das progressões do aluno
 *
 * @example para acessar algumas iformações da classe
 *
 *   Todo elemento/input de avaliação pode ser acessado pelo seu id. O id é criado no seguinte padrão:
 *     'progressao_' + <codigo_progressao> + '_periodo_'+<codigo_periodo>;
 *     'progressao_' + oProgressao.iProgressao + '_periodo_'+oAproveitamento.iPeriodo;
 *
 *   Todo input de falta pode ser acessado pelo seu id. O id é criado no seguinte padrão:
 *     'falta_progressao_' + iProgressao + '_periodo_' +oAvaliacao.iPeriodo;
 *
 * @param {strig}   sInstancia
 * @param {string}  sProgressao Código das progressões do aluno selecionado na turma, separado por virgula
 * @param {array}   aProgressao array com o código de progressões de todos os alunos vínculados na turma
 * @param {integer} iTurma      código da turma
 * @param {integer} iEtapa      código da etapa
 *
 * @author Andrio Costa <andrio.costa@dbseller.com.br>
 */
var LancamentoDiarioProgressaoParcialAluno = function ( sInstancia, sProgressao, aProgressao, iTurma, iEtapa) {

  this.sInstancia  = sInstancia;
  this.aProgressao = aProgressao;
  this.iTurma      = iTurma;
  this.iEtapa      = iEtapa;

  /**
   * Progressões aluno atual
   * @type {array}
   */
  this.aProgressaoAluno = sProgressao.split(",");

  this.sRPC     = 'edu4_diarioprogressaoparcial.RPC.php';
  this.sMessage = 'educacao.escola.LancamentoDiarioProgressaoParcialAluno.';

  this.oBtnAmparo       = LancamentoDiarioProgressaoParcialAluno.createInput();
  this.oBtnAmparo.value = 'Amparo';

  this.oBtnAlterarRF          = LancamentoDiarioProgressaoParcialAluno.createInput();
  this.oBtnAlterarRF.value    = 'Alterar Resultado Final';
  this.oBtnAlterarRF.disabled = true;

  this.oBtnAlterarRF.addEventListener('click', function () {

    if ( this.lWindowAuxiliarIsOpem ) {
      return;
    }

    for (var oElemento of this.oDadosTurma.aElementos) {

      if ( oElemento.lGeraResultadoFinal ){
        oElementoRF = oElemento;
      }
    }

    var oWndAlteraResultadoFinal = new AlteraResultadoFinalProgressao(this.oDadosAmparo, oElementoRF, this.sMascaraFormatacao);
    oWndAlteraResultadoFinal.setContainer(this.oWindow);

    oWndAlteraResultadoFinal.setCallBackWindow(function() {
      this.getAvaliacoesAluno();
      this.lWindowAuxiliarIsOpem = false;
    }.bind(this));

    this.lWindowAuxiliarIsOpem = true;
    oWndAlteraResultadoFinal.show();
  }.bind(this));

  /**
   * @type {windowAux}
   */
  this.oWindow = null;

  /**
   * Objeto com os dados da turma
   * @type {Object}
   */
  this.oDadosTurma = null;

  /**
   * @type {DBGridMultiCabecalho}
   */
  this.oGridAproveitamento = null;

  this.sMascaraFormatacao = '';

  /**
   * Resumos dos dados do aluno, usado para enviar os dados do aluno para view de AMPARO e ALTERAR RESULTADO FINAL
   * @type {Object}
   */
  this.oDadosAmparo = {};

  /**
   * Controle para liberar o botão Alterar Resultado Final
   * @var {boolean}
   */
  this.lPermiteAlteraResultadoFinal = false;

  /**
   * Controla se a grade de aproveitamento foi alterada.
   * Se a grade for alterada devemos avisar o usuário para ele realizar a persistencia dos dados
   * @var {boolean}
   */
  this.lGradeAlterada = false;


  /**
   * Progressões do aluno na turma e suas avaliações
   * Guarda as alterações da grade. Ao clicar em salvar é enviado para RPC
   * @type {Array}
   */
  this.aProgressoes = [];

  /**
   * Controle para não abrir as janelas enquanto as mesmas estão abertas
   * @type {Boolean}
   */
  this.lWindowAuxiliarIsOpem = false;

};

/**
 * Cria a string com os elementos html para renderizar a janela
 * @return {string}
 */
LancamentoDiarioProgressaoParcialAluno.createContainer = function() {

  var sStyle = " height: 35px; padding-left: 5px; margin: 0px;";

  var sContainer  = "<div>";

      sContainer += "  <div style='margin: 0; padding:0; width: 100%; display:inline-block;' > ";
      sContainer += "    <fieldset style='"+sStyle+"  width: 48%; float: left;' > ";
      sContainer += "      <legend>Opções</legend>";
      sContainer += "      <div id='ctnBotoes' ></div>";
      sContainer += "    </fieldset>";

      sContainer += "    <fieldset style='"+sStyle+"  width: 49%; float: right;'> ";
      sContainer += "      <legend>Forma de Avaliação</legend>";
      sContainer += "      <div id='ctnInfAvaliacaoPeriodo' > <span id='legendaFormaAvaliacao' ></span> </div>";
      sContainer += "    </fieldset>";
      sContainer += "  </div> ";

      sContainer += "  <fieldset style='clear:both;'>";
      sContainer += "    <legend>Grade de Aproveitamento</legend>";
      sContainer += "    <div id='ctnGradeAproveitamento'></div>";
      sContainer += "  </fieldset>";

      sContainer += "  <div class='subcontainer'> ";
//      sContainer += "    <input type='button' value='Anterior' id='btnAnterior' disabled='disabled' />";
      sContainer += "    <input type='button' value='Salvar'   id='btnSalvarAvaliacoes' disabled='disabled' />";
//      sContainer += "    <input type='button' value='Próximo'  id='btnProximo' disabled='disabled' />";
      sContainer += "  </div> ";

      sContainer += "</div>";

  return sContainer;
};

/**
 * Cria a windowAux
 * @return {void}
 */
LancamentoDiarioProgressaoParcialAluno.prototype.createWindow = function() {

  this.oWindow = new windowAux("wndLancarAvaliações", "Lançamento de Avaliações", (document.body.getWidth() - 20), 600);

  this.oWindow.setShutDownFunction( function() {

    if (this.validaSeGradeFoiAlterada()) {
      return false;
    }

    this.oWindow.destroy();
    $('btnAulasDadas').removeAttribute('disabled');
  }.bind(this));

  this.oWindow.setContent(LancamentoDiarioProgressaoParcialAluno.createContainer());

  var sTitulo = "Aluno: <span id='cntNomeAluno'> </span>";
  var sHelp   = "Turma: <span id='cntTurma'> </span> " ;

  new DBMessageBoard( 'ctnMsgBoard', sTitulo, sHelp, this.oWindow.getContentContainer());
  this.oWindow.show();

  this.oWindow.addEvent('keyup', function (Event) {

    var iTecla = Event.which;
    if (iTecla == KEY_ENTER && this.lGradeAlterada) {
      this.salvarAvaliacoes();
    }

    Event.preventDefault();
    Event.stopPropagation();
    return false;
  }.bind(this));

  $('ctnBotoes').appendChild(this.oBtnAmparo);
  $('ctnBotoes').appendChild(this.oBtnAlterarRF);
};

/**
 * Busca as informações da turma, como os períodos das regencias, os termos para RF...
 * @return {void}
 */
LancamentoDiarioProgressaoParcialAluno.prototype.getDadosTurma = function() {

  oParametros              = { 'exec' : 'buscarDadosTurma', 'iTurma' : this.iTurma, 'iEtapa' : this.iEtapa };
  oParametros.aProgressoes = this.aProgressaoAluno;

  var _this = this;
  var oAjax = new AjaxRequest( this.sRPC, oParametros, function( oRetorno, lErro ) {

    if ( !_this.oWindow ) {
      _this.createWindow();
    }

    _this.oDadosTurma = oRetorno;

    $('cntNomeAluno').innerHTML = oRetorno.sAluno;
    $('cntTurma').innerHTML     = oRetorno.sTurma;

    _this.createGradeAvaliacoes();

    _this.getAvaliacoesAluno();

    $('btnSalvarAvaliacoes').addEventListener('click', function() {

      this.salvarAvaliacoes();
    }.bind(_this));


  });

  oAjax.setMessage( _M( this.sMessage + "buscando_dados" ) );
  oAjax.execute();
};

LancamentoDiarioProgressaoParcialAluno.prototype.createGradeAvaliacoes = function() {

  /**
   * aGrupos represenca a primeira linha do cabeçalho da grid
   * aHeader representa a segunda linha do cabeçalho da grid
   */
  var aAlign  = ['left'];
  var aHeader = ['Disciplina', 'Evadir'];
  var aWidth  = ['15%', '7%'];

  var aGrupos = [];
  // cria o primeiro grupo informando que a coluna 0 faz parte do grupo
  var oGrupo = {'descricao': "Disciplinas do Aluno", 'aColunas' : [0, 1] };
  aGrupos.push(oGrupo);

  var iUltimoPeriodo = 0;
  console.log("=====================");
  console.log(this.oDadosTurma.aElementos);
  console.log("=====================");

  this.oDadosTurma.aElementos.each(function (oPeriodo, iPeriodo) {

    // _this.aPeriodosAvaliacao[oPeriodo.iOrdemAvaliacao] = oPeriodo;
    var oGrupoPeriodo       = {};
    oGrupoPeriodo.descricao = oPeriodo.sDescricaoPeriodoAbreviado.urlDecode();
    oGrupoPeriodo.aColunas  = [iPeriodo+1, iPeriodo+2];

    aGrupos.push(oGrupoPeriodo);
    aHeader.push(oPeriodo.sFormaAvaliacao);

    aHeader.push('Falta');
    aAlign.push('center', 'center');
    aWidth.push('7%', '7%');

    iUltimoPeriodo = iPeriodo+2;
  });

  var oGrupoRF       = {};
  oGrupoRF.descricao = 'Resultado Final';
  oGrupoRF.aColunas  = new Array(iUltimoPeriodo+3, iUltimoPeriodo+4);
  aGrupos.push(oGrupoRF);

  aHeader.push('Aproveitamento');
  aHeader.push('RF');
  aAlign.push('center', 'center');
  aWidth.push('8%', '9%');

  delete this.oGridAproveitamento;
  this.oGridAproveitamento = new DBGridMultiCabecalho('oGridAproveitamento');
  this.oGridAproveitamento.setCellWidth(aWidth);
  this.oGridAproveitamento.setCellAlign(aAlign);
  this.oGridAproveitamento.setHeader(aHeader);

  aGrupos.each(function(oGrupo, iSeq) {
    this.oGridAproveitamento.adicionarGrupo(oGrupo.descricao, oGrupo.aColunas, '0');
  }.bind(this));

  this.oGridAproveitamento.setHeight(200);
  this.oGridAproveitamento.show($('ctnGradeAproveitamento'));
  this.oGridAproveitamento.clearAll(true);
};

LancamentoDiarioProgressaoParcialAluno.prototype.getAvaliacoesAluno = function() {

  oParametros              = { 'exec' : 'buscarAvaliacoesAluno', 'iTurma' : this.iTurma, 'iEtapa' : this.iEtapa };
  oParametros.aProgressoes = this.aProgressaoAluno;
  this.oGridAproveitamento.clearAll(true);
  var _this = this;
  var oAjax = new AjaxRequest( this.sRPC, oParametros, function( oRetorno, lErro ) {

    this.sMascaraFormatacao = oRetorno.sMascaraFormatacao;
    this.aProgressoes       = oRetorno.aAvaliacaoDisciplina;

    this.oDadosAmparo.aRegencias = [];

    // se deve habilitar o botão para alterar o resultado final
    var lLiberarBotaoAlterarRF   = false;

    for ( var oProgressao of oRetorno.aAvaliacaoDisciplina ) {

      var oRegencia                   = {};
      oRegencia.iRegencia             = oProgressao.iRegencia
      oRegencia.sRegencia             = oProgressao.sRegencia;
      oRegencia.iProgressao           = oProgressao.iProgressao;
      oRegencia.iAprovadoPeloConselho = oProgressao.oResultadoFinal.iAprovadoPeloConselho;
      oRegencia.lEncerrado            = oProgressao.lEncerrado;
      oRegencia.lEvadido              = oProgressao.lEvadido;
      oRegencia.aPeriodos             = [];

      var aLinha = [];
      aLinha.push( oProgressao.sRegencia );
      aLinha.push( LancamentoDiarioProgressaoParcialAluno.createInputEvadido(oProgressao) );

      for( var oAvaliacao of oProgressao.aAvaliacoes ) {

        if ( !oAvaliacao.lResultado ) {

          var oPeriodo = this.getPeriodoPorCodigo( oAvaliacao.iPeriodo );

          var oPeriodoAmparo               = {};
          oPeriodoAmparo.iPeriodo          = oPeriodo.iPeriodo;
          oPeriodoAmparo.sDescricaoPeriodo = oPeriodo.sDescricaoPeriodo;
          oPeriodoAmparo.lAmparado         = oAvaliacao.lAmparado;

          oRegencia.aPeriodos.push( oPeriodoAmparo );
        }

        var oInputAvaliacao = '';
        switch(oAvaliacao.sFormaAvaliacao) {
          case 'NOTA':

            oInputAvaliacao = LancamentoDiarioProgressaoParcialAluno.createInputNota(oAvaliacao, oProgressao.iProgressao, this.sMascaraFormatacao);
            break;
          case 'NIVEL':

            oInputAvaliacao = LancamentoDiarioProgressaoParcialAluno.createInputConceito(oAvaliacao, oProgressao.iProgressao, oProgressao.sFrequenciaGlobal);
            break;
          case 'PARECER':

            oInputAvaliacao = LancamentoDiarioProgressaoParcialAluno.createInputParecer.bind(this)(oAvaliacao, oProgressao.iProgressao );
            break;
        }

        var oInputFalta = LancamentoDiarioProgressaoParcialAluno.createInputFalta(oAvaliacao, oProgressao.iProgressao, oProgressao.sFrequenciaGlobal);
        aLinha.push(oInputAvaliacao.outerHTML);
        aLinha.push(oInputFalta.outerHTML);
      }

      oElementoRF = LancamentoDiarioProgressaoParcialAluno.trataValorResultadoFinal.bind(this)(oProgressao);

      // valor do aproveitamento do RF
      aLinha.push("<div style='text-align:center;'>"+oProgressao.oResultadoFinal.nValor+"</div>");
      aLinha.push(oElementoRF.outerHTML);

      this.oGridAproveitamento.addRow(aLinha);

      oRegencia.oResultadoFinal = oProgressao.oResultadoFinal;
      this.oDadosAmparo.aRegencias.push( oRegencia );

      if ( oProgressao.oResultadoFinal.sResultadoFinal == 'R' || oProgressao.oResultadoFinal.iAprovadoPeloConselho != 0) {
        lLiberarBotaoAlterarRF = true;
      }
    }

    this.oGridAproveitamento.renderRows();
    this.oGridAproveitamento.setHighlight();

    this.oDadosAmparo.sAluno = this.oDadosTurma.sAluno;
    var iLinhaGrid = 0;
    for ( var oProgressao of this.aProgressoes ) {

      this.setaFuncoesEvadido(oProgressao);

      for( var oAvaliacao of oProgressao.aAvaliacoes ) {

        this.setaFuncoesNota(oProgressao, oAvaliacao, iLinhaGrid);
        this.setaFuncoesFalta(oProgressao, oAvaliacao, iLinhaGrid);
      }

      /**
       * Forma de aprovacao ( 1 - APROVADO_CONSELHO)
       * Alterar Avaliação Final ( 2 - Informar e Susbtituir e 3 - Informar e Não Susbtituir)
       */
      if ( oProgressao.oResultadoFinal.iAprovadoPeloConselho == 1 &&
           [2,3].in_array(oProgressao.oResultadoFinal.iAlterarNotaFinal) ) {

        // número de colunas de avaliação + colunas fixas (disciplina, evadir e aproveitamento)
        var iColunaRF     = (+oProgressao.aAvaliacoes.length * 2) + 3 ;
        var sHintConselho = "Nota do Conselho de Classe: " + oProgressao.oResultadoFinal.sAvaliacaoConselho;
        oParametros = {iWidth:'220', oPosition : {sVertical : 'T', sHorizontal : 'L'}};
        this.oGridAproveitamento.setHint(iLinhaGrid, iColunaRF, sHintConselho, oParametros);
      }

      iLinhaGrid++
    }

    this.defineAcoesBotoes(lLiberarBotaoAlterarRF);

  }.bind(this) );
  oAjax.setMessage( _M( this.sMessage + "buscando_avaliacoes_aluno" ) );
  oAjax.execute();
};

LancamentoDiarioProgressaoParcialAluno.prototype.show = function() {

  this.getDadosTurma();

};

LancamentoDiarioProgressaoParcialAluno.trataValorResultadoFinal = function(oProgressao) {

  var sDescricao      = '';
  var oResultadoFinal = oProgressao.oResultadoFinal;

  if (oResultadoFinal.sResultadoFinal != '') {

    for ( oTermo of this.oDadosTurma.aTermosEncerramento ) {

      if ( oTermo.sReferencia == oResultadoFinal.sResultadoFinal ) {

        sDescricao = oTermo.sSigla;

        /**
         * Só pode deixar limpar o resultado final se aluno não aprovado pelo conselho
         * No momento em que um aluno foi aprovado pelo conselho, ele sempre estará aprovado
         */
        if ( (oResultadoFinal.nValor == '' && oProgressao.sFrequenciaGlobal != 'F') &&
             oResultadoFinal.iAprovadoPeloConselho == 0 ) {
          sDescricao = '';
        }
        break;
      }
    }

    if (oResultadoFinal.sResultadoFinal == 'REC' || oResultadoFinal.sResultadoFinal == 'EVA' ) {
      sDescricao = oResultadoFinal.sResultadoFinal;
    }
  }

  /**
   * Estilizamos o resultado final do aluno conforme a forma de aprovação do mesmo.
   */
  var oDivResultadoFinal = document.createElement('div');
  oDivResultadoFinal.addClassName('resultadoFinalPadrao');

  var sClasse = 'resultadoFinalPadrao';

  switch (oResultadoFinal.iAprovadoPeloConselho) {

    case 1:

      oDivResultadoFinal.removeClassName('resultadoFinalPadrao');
      oDivResultadoFinal.addClassName('resultadoFinalAprovadoConselho');
      sClasse = 'resultadoFinalAprovadoConselho';
      break;

    case 2:

      oDivResultadoFinal.removeClassName('resultadoFinalPadrao');
      oDivResultadoFinal.addClassName('resultadoFinalReclassificadoBaixaFrequencia');
      sClasse = 'resultadoFinalReclassificadoBaixaFrequencia';
      break;

    case 3:

      oDivResultadoFinal.removeClassName('resultadoFinalPadrao');
      oDivResultadoFinal.addClassName('resultadoFinalConformeRegimentoEscolar');
      sClasse = 'resultadoFinalConformeRegimentoEscolar';
      break;
  }

  oDivResultadoFinal.innerHTML = sDescricao.toUpperCase();
  if (sDescricao != '') {
    this.lPermiteAlteraResultadoFinal = true;
  }

  return oDivResultadoFinal;
};


LancamentoDiarioProgressaoParcialAluno.createInput = function() {

  var oInput  = document.createElement('input');
  oInput.type = 'button';
  oInput.style.margin = ' 1px 6px ';
  return oInput
};

LancamentoDiarioProgressaoParcialAluno.createInputFalta = function(oAvaliacao, iProgressao, sFrequenciaGlobal) {

  var oFalta       = document.createElement( 'input' );
  oFalta.type      = 'text';
  oFalta.maxLength = 3;
  oFalta.name      = 'falta_progressao_' + iProgressao + '_periodo_' +oAvaliacao.iPeriodo;
  oFalta.id        = 'falta_progressao_' + iProgressao + '_periodo_' +oAvaliacao.iPeriodo;

  oFalta.setAttribute('value', oAvaliacao.iFaltas);
  oFalta.setAttribute('rel',"ignore-css");

  oFalta.addClassName('tamanhoElemento alignRight elemento ' + oAvaliacao.iPeriodo+'_falta');
  oFalta.setAttribute('onkeypress', 'return js_mask(event, "0-9")');

  if ( oAvaliacao.lFaltaBloqueada ) {

    oFalta.setAttribute('readonly', 'readonly');
    oFalta.addClassName("readonly");
  }
  oFalta.setAttribute('progressao', iProgressao);
  oFalta.setAttribute('periodo', oAvaliacao.iPeriodo);

  return oFalta;
};

/**
 * Cria o checkbox para evadir o aluno
 * @param  {Object} oProgressao
 * @return {string}
 */
LancamentoDiarioProgressaoParcialAluno.createInputEvadido = function(oProgressao) {

  var oInput     = document.createElement('input');
  oInput.type    = 'checkbox';
  oInput.id      = 'evadiu_progressao_' + oProgressao.iProgressao;

  if (oProgressao.lEvadido) {
    oInput.setAttribute('checked', 'checked');
  }

  if (oProgressao.lEncerrado) {
    oInput.setAttribute('disabled', 'disabled');
  }

  oInput.setAttribute('progressao', oProgressao.iProgressao);
  oInput.addClassName(" chk-evadido ");

  return oInput.outerHTML;
}

/**
 * Monta um input para lançar as notas
 * @param  {Object}  oAvaliacao
 * @param  {integer} iProgressao
 * @param  {string}  sMascara
 * @return {Object}
 */
LancamentoDiarioProgressaoParcialAluno.createInputNota = function (oAvaliacao, iProgressao, sMascara) {

  var oInput   = document.createElement('input');
  oInput.type  = 'text';
  oInput.id    = 'progressao_' + iProgressao + '_periodo_'+oAvaliacao.iPeriodo;
  oInput.setAttribute('value', oAvaliacao.nNota);
  oInput.setAttribute('rel',"ignore-css");
  oInput.setAttribute('mascara', sMascara);
  oInput.addClassName('tamanhoElemento alignRight elemento nota_' + oAvaliacao.iPeriodo + '_' + iProgressao);


  if ( oAvaliacao.lBloqueiaPeriodo ) {

    oInput.addClassName('readonly');
    oInput.setAttribute( 'readonly', 'readonly' );
  }

  if ( !oAvaliacao.lMinimoAtingido && oAvaliacao.nNota != '' ) {
    oInput.addClassName('bold');
  }

  if (oAvaliacao.lAmparado) {
    oInput.setAttribute('value', 'AMP');
  }

  oInput.setAttribute('progressao', iProgressao);
  oInput.setAttribute('periodo', oAvaliacao.iPeriodo);

  return oInput;
};

/**
 * Monta um campo para lançar o NIVEL
 * @param  {Object}  oAvaliacao
 * @param  {integer} iProgressao
 * @param  {string}  sControleFrequencia
 * @return {Object}
 */
LancamentoDiarioProgressaoParcialAluno.createInputConceito = function (oAvaliacao, iProgressao, sControleFrequencia) {

  var sId       = 'progressao_' + iProgressao + '_periodo_'+oAvaliacao.iPeriodo;
  var oConceito = document.createElement('select');
  oConceito.addClassName('alignLeft');
  oConceito.add( new Option ('', '') );

  for ( var oTipoConceito of oAvaliacao.aConceito ) {

    var oOption = new Option(oTipoConceito.sConceito, oTipoConceito.sConceito);
    oOption.setAttribute('ordem', oTipoConceito.iOrdem);

    if ( oTipoConceito.sConceito == oAvaliacao.nNota ){
      oOption.setAttribute('selected', 'selected');
    }

    oConceito.add(oOption);
  }

  /**
   * no lançameto por turma valida a forma de obtencao como o comentado, mais não entendi pq.. ver depois
   * (oAvaliacao.lBloqueiaPeriodo && oAvaliacao.sFormaObtencao != 'AT')
   */
  if ( oAvaliacao.lBloqueiaPeriodo || oAvaliacao.lEncerrado || sControleFrequencia == 'F') {

    oConceito   = document.createElement('input');
    oConceito.type  = 'text';
    oConceito.setAttribute('value', oAvaliacao.nNota);
    oConceito.setAttribute('readonly', 'readonly');
    oConceito.addClassName('alignCenter readonly');
  }

  oConceito.id  = sId;
  oConceito.addClassName('tamanhoElemento elemento nota_' + oAvaliacao.iPeriodo + '_' + iProgressao);
  oConceito.setAttribute('rel',"ignore-css");
  oConceito.setAttribute('progressao', iProgressao);
  oConceito.setAttribute('periodo', oAvaliacao.iPeriodo);

  /**
   * Quando aluno esta amparado, não devemos apresentar os conceitos e sim uma string 'AMP'
   */
  if (oAvaliacao.lAmparado) {
    oConceito.setAttribute('value', 'AMP');
  }

  return oConceito;
};

/**
 * Cria um os input para lançamento dos pareceres.
 * -> Atenção que o contexto da função foi alterado pelo bind.
 *
 * @param  {object} oAvaliacao
 * @param  {integer} iProgressao
 * @return {Object}
 */
LancamentoDiarioProgressaoParcialAluno.createInputParecer = function (oAvaliacao, iProgressao) {

  var sId         = 'progressao_' + iProgressao + '_periodo_'+oAvaliacao.iPeriodo;
  var oInput      = LancamentoDiarioProgressaoParcialAluno.createInput();
  oInput.type     = 'text';
  oInput.id       = sId;
  oInput.readonly = true;
  oInput.setAttribute('value', oAvaliacao.nNota);
  oInput.setAttribute('rel',"ignore-css");
  oInput.setAttribute('progressao', iProgressao);
  oInput.setAttribute('periodo', oAvaliacao.iPeriodo);
  oInput.addClassName('readonly tamanhoElemento elemento alignRight nota_' + oAvaliacao.iPeriodo + '_' + iProgressao);

  // se for resultado
  var oParecerCombo = null;
  if ( oAvaliacao.lResultado ) {

    oInput.removeClassName('tamanhoElemento');
    oInput.addClassName('fiftyPercentWidth');

    oParecerCombo    = document.createElement('select');
    oParecerCombo.id = 'parecerFinal_'+oAvaliacao.iPeriodo + '_Progressao_' + iProgressao;
    oParecerCombo.addClassName('noMarginPadding fiftyPercentWidth');
    oParecerCombo.setAttribute('rel',"ignore-css");
    oParecerCombo.setAttribute('progressao', iProgressao);
    oParecerCombo.setAttribute('periodo', oAvaliacao.iPeriodo);

    if (oAvaliacao.lEncerrado) {
      oParecerCombo.setAttribute('disabled', 'disabled');
    }

    /**
     * Percorre os termos de encerramento transformando-os em options
     */
    for ( var oTermo of this.oDadosTurma.aTermosEncerramento ) {

      if (oTermo.sReferencia == 'P') {
        return;
      }

      var oOption = new Option(oTermo.sDescricao, oTermo.sReferencia);

      if ( this.oDadosTurma.lAprovacaoAutomatica && oTermo.sReferencia == 'A' ) {
        oOption.setAttribute('selected', 'selected');
      } else if (oAvaliacao.lMinimoAtingido && oTermo.sReferencia == 'A' ) {
        oOption.setAttribute('selected', 'selected');
      } else if (!oAvaliacao.lMinimoAtingido && oTermo.sReferencia == 'R' ) {
        oOption.setAttribute('selected', 'selected');
      }
      oParecerCombo.add(oOption);
    }

    if ( oAvaliacao.emRecuperacao ) {

      var oOption = new Option('EM RECUPERAÇÃO', 'rec');
      oOption.setAttribute('selected','selected');
      oParecerCombo.add(oOption);
    }

    if ( this.oDadosTurma.lAprovacaoAutomatica ) {
      oParecerCombo.disabled = true;
    }
  }

  var oDiv           = document.createElement('div');
  oDiv.style.display = 'block';
  oDiv.addClassName('noMarginPadding');
  oDiv.addClassName('tamanhoElemento');
  oDiv.style.height ='100%';
  oDiv.appendChild( oInput );

  if ( !!oParecerCombo ) {
    oDiv.appendChild( oParecerCombo );
  }

  return oDiv;
};

/**
 * Controla se pode-se lançar/informar nota/avaliacao para a disciplina do aluno
 * @param  {Object} oProgressao
 * @param  {Object} oAproveitamento
 * @return {Boolean}
 */
LancamentoDiarioProgressaoParcialAluno.validaSePodeSerInformadoNota = function (oProgressao, oAproveitamento)  {

  var lFuncaoPreencheNota = true;
  if (oProgressao.sFrequenciaGlobal == 'F' || oAproveitamento.lAmparado || oProgressao.lEncerrado || oAproveitamento.lResultado) {
    lFuncaoPreencheNota  = false;
  }

  /**
   * Quando é resultado final ATRIBUIDO, se avaliação ainda não esta encerrada, devemos setar funções na NOTA
   */
  if ( (oAproveitamento.lResultado && oAproveitamento.sFormaObtencao == 'AT') && !oProgressao.lEncerrado) {
    lFuncaoPreencheNota  = true;
  }
  return lFuncaoPreencheNota;
};

LancamentoDiarioProgressaoParcialAluno.prototype.setaFuncoesFalta = function (oProgressao, oAvaliacao, iLinhaGrid) {

  if (oAvaliacao.lFaltaBloqueada) {
    return;
  }

  var oFalta = $('falta_progressao_' + oProgressao.iProgressao + '_periodo_' +oAvaliacao.iPeriodo);
  oFalta.onchange = function () {

    if ( LancamentoDiarioProgressaoParcialAluno.validaAulasDoPeriodo(oFalta, oAvaliacao) ) {

      oAvaliacao.iFaltas  = oFalta.value;
      oAvaliacao.lEditado = true;
      this.lGradeAlterada = true;
      $('btnSalvarAvaliacoes').removeAttribute('disabled');
    }
  }.bind(this);

};

/**
 * Valida se as faltas digitadas não ultrapassam o valou lançado para o período.
 * @param  {Object} oElemento  input de falta que disparou o evento
 * @param  {Object} oAvaliacao dados da avaliação no período
 * @return {Boolean}
 */
LancamentoDiarioProgressaoParcialAluno.validaAulasDoPeriodo = function(oElemento, oAvaliacao) {

  var iFaltas       = new Number(oElemento.value).valueOf();
  var iAulasPeriodo = new Number(oAvaliacao.iAulasPeriodo).valueOf();
  var sMensagem     = '';

  var lLancamentoValido = true;
  if ( iFaltas > iAulasPeriodo ) {

    lLancamentoValido = false;
    sMensagem         = 'Número de faltas é maior que o número de aulas para o período: '+ iAulasPeriodo;
  }

  if (!lLancamentoValido) {

    alert(sMensagem);
    setTimeout(function () {
                 oElemento.focus();
               },10
              );
    return false;
  }

  return true;
};

/**
 * Atribui as eventos ao input para lançar a avaliação do aluno
 * @param  {Object} oProgressao
 * @param  {Object} oAproveitamento
 * @param  {integer} iLinhaGrid
 * @return {void}
 */
LancamentoDiarioProgressaoParcialAluno.prototype.setaFuncoesNota = function (oProgressao, oAvaliacao, iLinhaGrid ) {

  var lFuncaoPreencheNota = LancamentoDiarioProgressaoParcialAluno.validaSePodeSerInformadoNota(oProgressao, oAvaliacao);

  var sNomeId = 'progressao_' + oProgressao.iProgressao + '_periodo_'+oAvaliacao.iPeriodo;
  var oNota   = $(sNomeId);
  oNota.addEventListener('focus', function () {
    this.getInformacoesPeriodo(oAvaliacao.iPeriodo);
  }.bind(this));

  switch (oAvaliacao.sFormaAvaliacao) {

    case 'PARECER':

      break;
    case 'NOTA':

        if ( !oAvaliacao.lAmparado ) {
          js_observeMascaraNota(oNota, this.sMascaraFormatacao);
        }

        if ( lFuncaoPreencheNota ) {

          oNota.onchange = function () {

            this.preencheNotaDisciplina(oAvaliacao, oNota);
          }.bind(this);
        }
      break;

    default:

      if (lFuncaoPreencheNota) {

        oNota.onchange = function () {
          this.preencheNotaDisciplina(oAvaliacao, oNota);
        }.bind(this);
      }
      break;
  }

  if ( oAvaliacao.lAmparado) {

    var iColuna = (+oAvaliacao.iOrdem * 2) ;
    oParametros = {iWidth:'90', oPosition : {sVertical : 'T', sHorizontal : 'L'}};
    this.oGridAproveitamento.setHint(iLinhaGrid, iColuna, "<b>Amparado</b>", oParametros);
  }
};

/**
 * @todo  getPeriodoPorCodigo é a antiga getDadosPeriodo na classe DBViewLancamentoAvaliacaoTurma
 *
 * Busca o período pelo código do período
 *
 * @param  {integer} iPeriodo código do período
 * @return {Object}
 */
LancamentoDiarioProgressaoParcialAluno.prototype.getPeriodoPorCodigo = function(iPeriodo) {

  var oPeriodoRetorno = '';
  for ( var oPeriodo of this.oDadosTurma.aElementos ) {

    if (oPeriodo.iPeriodo == iPeriodo) {

      oPeriodoRetorno = oPeriodo;
      break;
    }
  }

  return oPeriodoRetorno;
};

/**
 * Busca as informações do período e apresenta as informações no fieldset Forma de Avaliação
 * @param  {integer} iPeriodo codigo do período
 * @return {void}
 */
LancamentoDiarioProgressaoParcialAluno.prototype.getInformacoesPeriodo = function(iPeriodo) {

  var oPeriodo = this.getPeriodoPorCodigo(iPeriodo);

  var sMensagemFormaAvaliacao = "<b>Tipo de resultado: " +oPeriodo.sFormaAvaliacao+"</b><br>";

  switch(oPeriodo.sFormaAvaliacao) {

    case 'NIVEL':

      var aConceitos = [];
      for( var oConceito of oPeriodo.aConceitos) {
        aConceitos.push(oConceito.sDescricaoConceito);
      }
      sMensagemFormaAvaliacao += "Níveis: " + aConceitos.implode(', ');
      sMensagemFormaAvaliacao += " com o mínimo para aprovação: "+oPeriodo.mMinimoAprovacao;
      break;

    case 'NOTA':

      sMensagemFormaAvaliacao += "Notas de "+oPeriodo.iMenorValor+" a "+oPeriodo.iMaiorValor+", ";
      sMensagemFormaAvaliacao += "com variação de "+oPeriodo.nVariacao+", com o ";
      sMensagemFormaAvaliacao += "mínimo para aprovação de "+oPeriodo.mMinimoAprovacao;
      break;

    case 'PARECER':

      sMensagemFormaAvaliacao += "<br>";
      break
  }

  $('legendaFormaAvaliacao').innerHTML = sMensagemFormaAvaliacao;
  $('legendaFormaAvaliacao').title     = sMensagemFormaAvaliacao.replace('<br>', ' - ');
};

/**
 * Trata o valor digitado pelo usuário no campo nota e grava valor na sessão
 *
 * @param   {Object}  oAluno
 * @param   {Object}  oAvaliacao
 * @param   {Element} oElement
 * @returns {boolean}
 */
LancamentoDiarioProgressaoParcialAluno.prototype.preencheNotaDisciplina = function(oAvaliacao, oElement) {

  var iMenorValor      = +oAvaliacao.nMenorValor;
  var iMaiorValor      = +oAvaliacao.nMaiorValor;
  var mMinimoAprovacao = +oAvaliacao.mAproveitamentoMinino;
  var nVariacao        = +oAvaliacao.nVariacao;
  var nValorNota       = oElement.value;

  if ( oAvaliacao.sFormaAvaliacao == 'NOTA' ) {

    if ( nValorNota != '') {

    nValorNota = +oElement.value;
    if (nValorNota != '' && (nValorNota < iMenorValor || nValorNota > iMaiorValor)) {

      alert('Nota deve ser entre '+iMenorValor+' e '+iMaiorValor+'!');
      oElement.value = '';
      return false;
    }

    if (!DBViewAvaliacao.ValidacaoVariacaoNota(nValorNota, nVariacao, this.sMascaraFormatacao)) {

      alert('Intervalo de nota deve ser de '+nVariacao);
      oElement.value = '';
      return false;
    }

    oElement.removeClassName('bold');
    if (nValorNota < mMinimoAprovacao) {
      oElement.addClassName('bold');
    }
  }
  }

  if (oElement.type == 'select-one') {
    oAvaliacao.iOrdemConceito = oElement.options[oElement.selectedIndex].getAttribute('ordem');
  }

  oAvaliacao.nNota = nValorNota;
  oAvaliacao.lMinimoAtingido = (nValorNota >= mMinimoAprovacao);
  oAvaliacao.lEditado        = true;

  this.lGradeAlterada = true;
  $('btnSalvarAvaliacoes').removeAttribute('disabled');

  return true;
};


LancamentoDiarioProgressaoParcialAluno.prototype.setaFuncoesEvadido = function(oProgressao) {

  var oChkEvadido = $('evadiu_progressao_'+oProgressao.iProgressao);
  oChkEvadido.addEventListener('click', function() {

    oProgressao.lEvadido = oChkEvadido.checked;
    this.lGradeAlterada = true;
    $('btnSalvarAvaliacoes').removeAttribute('disabled');
  }.bind(this));

};

LancamentoDiarioProgressaoParcialAluno.prototype.salvarAvaliacoes = function() {

  $('btnSalvarAvaliacoes').setAttribute('disabled', 'disabled');
  oParametros = { 'exec' : 'salvarAvaliacoes', 'aProgressoes' : this.aProgressoes };

  var oAjax = new AjaxRequest( this.sRPC, oParametros, function( oRetorno, lErro ) {

    this.lGradeAlterada = false;
    $('btnSalvarAvaliacoes').setAttribute('disabled', 'disabled');
    alert(oRetorno.sMessage);
    this.getAvaliacoesAluno();

  }.bind(this));

  oAjax.setMessage( _M( this.sMessage + "salvando_avaliacoes" ) );
  oAjax.execute();
};

/**
 * Verifica se a grid foi alterada e avisa o usuário caso sim.
 * @returns {Boolean}
 */
LancamentoDiarioProgressaoParcialAluno.prototype.validaSeGradeFoiAlterada = function () {

  if (this.lGradeAlterada) {

    var sMsgConfirm  = 'Grade de aproveitamento foi alterada, se fechar a janela, perderá todas as modificações ';
        sMsgConfirm += 'realizadas.\nDeseja mesmo fechar a janela sem salvar?';
    if (confirm(sMsgConfirm)) {
      return false;
    }
    return true;
  }
  return false;
};

LancamentoDiarioProgressaoParcialAluno.prototype.defineAcoesBotoes = function( lLiberarBotaoAlterarRF ) {

  this.oBtnAlterarRF.setAttribute('disabled', 'disabled');

  if (lLiberarBotaoAlterarRF) {
    this.oBtnAlterarRF.removeAttribute('disabled');
  }

  this.oBtnAmparo.onclick = function() {

    if ( this.lWindowAuxiliarIsOpem ) {
      return;
    }

    var lTodasDisciplinasEstaoEncerradas = true;
    for ( var oRegencia of this.oDadosAmparo.aRegencias ) {

      if ( !oRegencia.lEncerrado ) {
        lTodasDisciplinasEstaoEncerradas = false;
        break;
      }
    }

    if ( lTodasDisciplinasEstaoEncerradas ) {

      alert('Todas progressões estão encerradas.');
      return;
    }

    var oViewLancamentoAmparo = new LancamentoAmparo( this.oDadosAmparo );
    oViewLancamentoAmparo.setWindowPai( this.oWindow );

    oViewLancamentoAmparo.setCallBackWindow( function() {

      this.getAvaliacoesAluno();
      this.lWindowAuxiliarIsOpem = false;
    }.bind(this));

     this.lWindowAuxiliarIsOpem = true;
    oViewLancamentoAmparo.show();
  }.bind(this);
};
