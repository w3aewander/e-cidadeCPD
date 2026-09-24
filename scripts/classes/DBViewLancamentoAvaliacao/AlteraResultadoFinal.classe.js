require_once("scripts/classes/DBViewLancamentoAvaliacao/DBViewLancamentoAvaliacao.classe.js");
require_once("scripts/DBFormCache.js");

var MSGEALTERARESULTADOFINAL = 'educacao.escola.AlteraResultadoFinal.';

/**
 * Funçao para alterar o resultado final do aluno
 * Aluno já deve possuir resultado final lançado
 * @param {Number} nNota Nota a ser Verificada
 * @param {Number} nVariacao variacao que a nota deve estar .
 * @return boolean
 */
DBViewAvaliacao.AlteraResultadoFinal = function (oTurma, iRegencia, sRegencia) {

  oTurma.aDisciplinas.each(function( oDisciplina ) {

    if( oDisciplina.iCodigo == iRegencia ) {
      oTurma.sTipoProcedimentoAvaliacao = oDisciplina.sFormaAvaliacao;
    }
  });

  /**
   * Dados da turma que esta sendo alterado o resultado final
   * @exemple
   * ({iTurma:"680",
   *   iEtapa:"17",
   *   sEscola:"EMEF BARAO DO RIO BRANCO",
   *   sCalendario:"CALENDÁC1RIO 2013",
   *   sCurso:"ENSINO FUNDAMENTAL 9 ANOS",
   *   sBaseCurricular:"ANOS FINAIS",
   *   sTurma:"8º ANO",
   *   sEtapa:"8º ANO",
   *   sProcedimentoAvaliacao:"NOTA",
   *   sTurno:"MANHÃ"
   *  }
   * )
   */
  this.oTurma = oTurma;

  /**
   * Informações sobre a regencia que esta sendo trabalhada
   */
  this.iRegencia = iRegencia;
  this.sRegencia = sRegencia;

  /**
   * Variável para receber a intancia da grid
   * @var {Object}
   */
  this.oGridResultados = {};

  /**
   * Rpc utilizado pela classe
   * @var {string}
   */
  this.sRpcPadrao = 'edu4_alteraresultadofinal.RPC.php';

  this.oDBFormCache = null;

  /**
   * Flag para indicar se houve erro de validação no salvamento
   * Quando true, impede que o callback normal seja executado ao fechar a janela
   * @var {boolean}
   */
  this.lErroValidacao = false;

  /** ********************************************************
   *  ----------     Componentes da interface     ------------
   * ********************************************************* */
  this.oCboAlunos    = document.createElement('select');
  this.oCboAlunos.id = 'alunosDisponiveis';
  this.oCboAlunos.add(new Option("Selecione", ""));

  this.oCboFormaAprovacao    = document.createElement('select');
  this.oCboFormaAprovacao.id = 'formaAprovacao';
  this.oCboFormaAprovacao.add(new Option('Selecione', ''));
  this.oCboFormaAprovacao.add(new Option('Aprovado pelo conselho', '1'));
  this.oCboFormaAprovacao.add(new Option('Reclassificação por baixa frequência', '2'));
  this.oCboFormaAprovacao.add(new Option('Aprovado Conforme Regimento Escolar', '3'));
  this.oCboFormaAprovacao.disabled    = true;

  this.oInputCodigoProfessor      = document.createElement('input');
  this.oInputCodigoProfessor.type = 'text';
  this.oInputCodigoProfessor.id   = 'codigoProfessor';
  this.oInputCodigoProfessor.name = 'codigoProfessor';
  this.oInputCodigoProfessor.size = 10;
  this.oInputCodigoProfessor.addClassName('readonly');

  this.oInputNomeProfessor      = document.createElement('input');
  this.oInputNomeProfessor.type = 'text';
  this.oInputNomeProfessor.id   = 'nomeProfessor';
  this.oInputNomeProfessor.name = 'nomeProfessor';
  this.oInputNomeProfessor.size = 80;
  this.oInputNomeProfessor.addClassName('readonly');

  this.oAncoraProfessor      = document.createElement('a');
  this.oAncoraProfessor.href = '#';
  this.oAncoraProfessor.id   = 'ancoraProfessor';
  this.oAncoraProfessor.update('Professor');

  /**
   * Ao alterar o resultado final, quando selecionado:
   * - Forma Avaliação "Aprovado pelo conselho", devemos permitir que o usuário informe:
   *   -- como deseja alterar o resultado final (através do campo "Alterar Nota Final"
   *   -- informar uma nova avaliação (Quando "Alterar Nota Final" =  2 e 3)
   */
  this.oCboAlterarNotaFinal = new Element('select', {'id':'alterarNotaFinal', 'disabled':true});
  this.oCboAlterarNotaFinal.add( new Option ('Selecione', ''));
  this.oCboAlterarNotaFinal.add( new Option ('Não informar', 1));
  this.oCboAlterarNotaFinal.add( new Option ('Informar e Substituir', 2));
  this.oCboAlterarNotaFinal.add( new Option ('Informar e NÃO Substituir', 3));

  // Novo: Seletor de período para alteração de nota
  this.oCboPeriodoAlteracao = new Element('select', {'id':'periodoAlteracao', 'disabled':true});
  this.oCboPeriodoAlteracao.add( new Option ('Selecione o período', ''));

  /**
   * Somente um dos objetos abaixos estarão visiveis para o usuário, a forma de avaliação da turma é quem define qual
   */
  this.oCboAvaliacaoNivel  = new Element('select', {'id':'novaAvaliacao', 'disabled':true});
  this.oInputAvaliacaoNota = new Element('input', {'type':'text', 'name':'novaAvaliacao', 'id':'novaAvaliacao',
                                                   'disabled':true, 'size':10});

  this.oCallBackCloseWindow = function() {
    return true;
  };

  this.getParametros();
};

/**
 * Ao fechar a janela, recarrega a tela anterior
 * @param {Function} fFunction
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.setCallBackWindow = function (fFunction) {
  this.oCallBackCloseWindow = fFunction;
};

/**
 * Seta em cima de qual container irá abrir
 * @param {Object} oWindow
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.setContainer = function (oWindow) {
  this.oWindowContainer = oWindow;
};

/**
 * Realia a chamada das funções para renderizar a View
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.show = function () {

  var oSelf = this;
  this.montaJanela();
  $('ctnCboAluno').appendChild(this.oCboAlunos);
  $('ctnProfessor').appendChild(this.oInputCodigoProfessor);
  $('ctnProfessor').appendChild(this.oInputNomeProfessor);
  $('ctnFormaAprovacao').appendChild(this.oCboFormaAprovacao);
  $('ctnLinkProfessor').appendChild(this.oAncoraProfessor);

  $('ctnAlterarNotaFinal').appendChild(this.oCboAlterarNotaFinal);
  $('ctnPeriodoAlteracao').appendChild(this.oCboPeriodoAlteracao);

  this.oCboFormaAprovacao.onchange = function () {
    oSelf.validaFormaAprovacaoSelecionada();

    /**
     * Autor: Uemerson Santana
     * Data: 14/10/2025
     * Demanda: 17872
     *
     * Limpar aviso e habilitar botão quando mudar forma de aprovação
     */
    var oLinhaAvisoProvaFinal = $('linhaAvisoProvaFinal');
    if (oLinhaAvisoProvaFinal) {
      oLinhaAvisoProvaFinal.style.display = 'none';
    }

    var oBotaoSalvar = $('salvarResultado');
    if (oBotaoSalvar) {
      oBotaoSalvar.disabled = false;
      oBotaoSalvar.style.opacity = '1';
      oBotaoSalvar.style.cursor = 'pointer';
    }
  };

  this.oCboAlterarNotaFinal.onchange = function () {
    oSelf.validaTipoAlterarNotaFinal();
  };

  /**
   * Autor: Uemerson Santana
   * Data: 14/10/2025
   * Demanda: 17872
   *
   * Listener para limpar aviso e habilitar botão quando mudar período
   */
  this.oCboPeriodoAlteracao.onchange = function () {
    // Limpar campo de avaliação
    if (oSelf.oInputAvaliacaoNota) {
      oSelf.oInputAvaliacaoNota.value = '';
    }

    // Ocultar aviso
    var oLinhaAvisoProvaFinal = $('linhaAvisoProvaFinal');
    if (oLinhaAvisoProvaFinal) {
      oLinhaAvisoProvaFinal.style.display = 'none';
    }

    // Habilitar botão Salvar
    var oBotaoSalvar = $('salvarResultado');
    if (oBotaoSalvar) {
      oBotaoSalvar.disabled = false;
      oBotaoSalvar.style.opacity = '1';
      oBotaoSalvar.style.cursor = 'pointer';
    }
  };

  if (this.oParametros.sFormaAvaliacao == 'NIVEL') {

    $('ctnNovaAvaliacao').appendChild(this.oCboAvaliacaoNivel);
    this.oParametros.aConceitos.each( function (oConceito) {
      oSelf.oCboAvaliacaoNivel.add(new Option(oConceito.sConceito, oConceito.sConceito));
    });

  } else {

    $('ctnNovaAvaliacao').appendChild(this.oInputAvaliacaoNota);
    this.oInputAvaliacaoNota.onchange = function () {
      oSelf.validaNovaNotaFinal();
    }
  }

  $('ancoraProfessor').onclick = function () {
    oSelf.pesquisaProfessor();
  };


  this.getAlunosReprovados();
  this.montaGrid();
  this.configuraPeriodosDisponiveis();

  /**
   * Seta funcção no onchange do combo dos alunos
   */
  $('alunosDisponiveis').onchange = function () {
    oSelf.validaBloqueioOptionFormaAprovacao();
  };

  $('salvarResultado').onclick = function () {
    oSelf.salvarResultadoFinal();
  };

  if( this.oTurma.sTipoProcedimentoAvaliacao != 'PARECER' ) {

    this.oDBFormCache = new DBFormCache( 'oDbFormCache', 'AlteraResultadoFinal.classe.js' );
    this.oDBFormCache.setElements( new Array( $('alterarNotaFinal') ) );
  }
};


/**
 * Cria a instancia da View e renderiza
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.montaJanela = function () {

  var iAltura  = document.body.clientHeight / 1.3;
  var oSelf    = this;

  /**
   * Cria instancia da window
   */
  this.oWindowResultadoFinal = new windowAux("wndResultadoFinal",
                                             "Alterar Resultado Final",
                                             900,
                                             iAltura
                                            );
  this.oWindowResultadoFinal.allowCloseWithEsc(false);

  /**
   * Seta função para destruir a janela
   */
  this.oWindowResultadoFinal.setShutDownFunction(function () {

    /**
     * IMPORTANTE: Sempre chama o callback do pai para forçar reload dos dados
     * Isso garante que após um erro de validação, a tela pai seja atualizada
     * com os dados corretos do backend
     */
    oSelf.oCallBackCloseWindow();

    // Reseta a flag de erro após o fechamento
    oSelf.lErroValidacao = false;

    oSelf.oWindowResultadoFinal.destroy();
  });

  var sConteudo  = "<div class='container' id='container' style='width:840px;'>";
      sConteudo += "  <center>";
      sConteudo += "    <div>";
      sConteudo += "      <fieldset>";
      sConteudo += "        <legend>Alterar Resultado Final</legend>";
      sConteudo += "        <table class='form-container' style='width:100%'>";
      sConteudo += "          <tr>";
      sConteudo += "            <td class='bold'>Aluno:</td>";
      sConteudo += "            <td id='ctnCboAluno' nowrap='nowrap'>";
      sConteudo += "            </td>";
      sConteudo += "          </tr>";
      sConteudo += "          </tr>";
      sConteudo += "          <tr>";
      sConteudo += "            <td class='bold' id='ctnLinkProfessor'></td>";
      sConteudo += "            <td id='ctnProfessor' nowrap='nowrap'>";
      sConteudo += "            </td>";
      sConteudo += "          </tr>";
      sConteudo += "          <tr nowrap='nowrap'>";
      sConteudo += "            <td class='bold' nowrap='nowrap'>Forma de Aprovação:</td>";
      sConteudo += "            <td id='ctnFormaAprovacao' nowrap='nowrap'>";
      sConteudo += "            </td>";
      sConteudo += "          </tr>";

      sConteudo += "          <tr nowrap='nowrap' id='linhaAlterarNotaFinal'>";
      sConteudo += "            <td class='bold' nowrap='nowrap'>Alterar Avaliação Final:</td>";
      sConteudo += "            <td id='ctnAlterarNotaFinal' nowrap='nowrap'>";
      sConteudo += "            </td>";
      sConteudo += "          </tr>";

      sConteudo += "          <tr nowrap='nowrap' id='linhaPeriodoAlteracao' style='display:none;'>";
      sConteudo += "            <td class='bold' nowrap='nowrap'>Período a Alterar:</td>";
      sConteudo += "            <td id='ctnPeriodoAlteracao' nowrap='nowrap'>";
      sConteudo += "            </td>";
      sConteudo += "          </tr>";

      sConteudo += "          <tr nowrap='nowrap' id='linhaNovaAvaliacao'>";
      sConteudo += "            <td class='bold' nowrap='nowrap'>Avaliação:</td>";
      sConteudo += "            <td id='ctnNovaAvaliacao' nowrap='nowrap'>";
      sConteudo += "            </td>";
      sConteudo += "          </tr>";

      /**
       * Autor: Uemerson Santana
       * Data: 14/10/2025
       * Demanda: 17872
       *
       * Aviso para Prova Final: Nota deve ser >= 5.0
       */
      sConteudo += "          <tr nowrap='nowrap' id='linhaAvisoProvaFinal' style='display:none;'>";
      sConteudo += "            <td colspan='2'>";
      sConteudo += "              <div id='avisoProvaFinalMenorQue5' style='background-color:#FFE6E6; border:2px solid #D80000; border-radius:5px; padding:10px; margin:5px 0;'>";
      sConteudo += "                <label style='color:#D80000; font-weight:bold;'>ATENÇÃO - PROVA FINAL:</label>";
      sConteudo += "                <label style='color:#D80000; font-weight:normal;'> A nota informada deve ser igual ou maior que 5,0 para que o aluno possa ser aprovado.</label>";
      sConteudo += "              </div>";
      sConteudo += "            </td>";
      sConteudo += "          </tr>";

      sConteudo += "          <tr id='legendaAlterarNotaFinal' style='display:none;' >";
      sConteudo += "            <td class='bold' style='white-space:pre-line;' colspan=2>";
      sConteudo += "              <fieldset class='separator' '>";
      sConteudo += "                <legend>Legenda</legend>";
      sConteudo += "                <label style='color:#D80000;'> Informar e Substituir:</label>";
      sConteudo += "                <label style='font-weight:normal;'>O sistema irá substituir a nota final do aluno ";
      sConteudo += "                  em todos os relatórios e consultas (boletim de desempenho, histórico escolar,  ";
      sConteudo += "                  ficha individual, ata de resultados finais entre outros)</label>";
      sConteudo += "                </br> ";
      sConteudo += "                <label style='color:#D80000;'> Informar e NÃO Substituir:</label>";
      sConteudo += "                <label style='font-weight:normal;'>O sistema registrará a nota final nas observações";
      sConteudo += "                  dos relatórios que contenham o  resultado final do aluno.</label>";
      sConteudo += "              </fieldset>";
      sConteudo += "            </td>";
      sConteudo += "          </tr>";

      sConteudo += "          <tr >";
      sConteudo += "            <td colspan='2'>";
      sConteudo += "              <fieldset class='separator'>";
      sConteudo += "                <legend>Justificativa</legend>";
      sConteudo += "                <textarea id='justificativaResultado' rows='4' style='width:100%'></textarea>";
      sConteudo += "              </fieldset>";
      sConteudo += "            </td>";
      sConteudo += "          </tr>";
      sConteudo += "        </table>";
      sConteudo += "      </fieldset>";
      sConteudo += "      <input type='button' id='salvarResultado' name='salvarResultado' value='Salvar' />";
      sConteudo += "    </div>";
      sConteudo += "    <div>";
      sConteudo += "      <fieldset style='width:97%'>";
      sConteudo += "        <legend>Alterar Resultado Final</legend>";
      sConteudo += "        <div id='ctnGridAlteraResultadoFinal'></div>";
      sConteudo += "      </fieldset>";
      sConteudo += "    </div>";
      sConteudo += "  </center>";
      sConteudo += "</div>";


  var sMsg  = 'Alterar o resultado final para a disciplina: ';
      sMsg += this.sRegencia;

  var sHelpMsgBox  = 'Selecione o aluno para realizar a alteração do resultado final';
      sHelpMsgBox += ' para a disciplina: ' + this.sRegencia;

  this.oWindowResultadoFinal.setContent(sConteudo);
  new DBMessageBoard('msgBoardAvaliacao', sMsg, sHelpMsgBox, this.oWindowResultadoFinal.getContentContainer());



  if (this.oWindowContainer != '') {
    oSelf.oWindowResultadoFinal.setChildOf(this.oWindowContainer);
  }

  this.oWindowResultadoFinal.show();
};

/**
 *
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.getAlunosReprovados = function () {

  $('alunosDisponiveis').options.length = 0;
  $('alunosDisponiveis').add(new Option("Selecione", ""));
  var oParametro       = new Object();
  oParametro.exec      = 'getAlunosAlteraResultadoFinal';
  oParametro.iTurma    = this.oTurma.iTurma;
  oParametro.iEtapa    = this.oTurma.iEtapa;
  oParametro.iRegencia = this.iRegencia;

  var oSelf = this;

  js_divCarregando("Aguarde, alunos resultados reprovados.", "msgBox");
  new Ajax.Request(this.sRpcPadrao,
                   { method:     'post',
                     parameters: 'json='+Object.toJSON(oParametro),
                     onComplete: function(oAjax) {
                                   oSelf.retornoAlunosReprovados(oAjax);
                                 }
                   }
                  );
};

DBViewAvaliacao.AlteraResultadoFinal.prototype.retornoAlunosReprovados = function (oAjax) {

  js_removeObj("msgBox");
  var oRetorno = JSON.parse(oAjax.responseText);

  oRetorno.aAlunos.each( function (oAluno) {

    var oOption       = document.createElement('option');
    oOption.value     = oAluno.iMatricula;
    oOption.innerHTML = oAluno.sAluno.urlDecode();
    oOption.setAttribute('lReprovadoFrequencia', oAluno.lReprovadoFrequencia);
    oOption.setAttribute('lReprovadoNota', oAluno.lReprovadoNota);

    $('alunosDisponiveis').appendChild(oOption);
  });


};


DBViewAvaliacao.AlteraResultadoFinal.prototype.montaGrid = function () {

  this.oGridResultados              = new DBGrid('gridResultados');
  this.oGridResultados.nameInstance = 'oGridResultados';
  this.oGridResultados.setHeight(100);
  this.oGridResultados.setCellWidth(new Array('30%', '30%', '15%', '15', '10%'));
  this.oGridResultados.setCellAlign(new Array('left', 'left', 'center', 'left', 'center'));
  this.oGridResultados.setHeader(new Array('Aluno', 'Professor', 'Data/Hora', 'Forma Aprovação', 'Ação'));

  this.oGridResultados.show($('ctnGridAlteraResultadoFinal'));

  this.getAlunosAprovadosPeloConselho();
};

/**
 * Busca alunos aprovados pelo conselho
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.getAlunosAprovadosPeloConselho = function () {

  var oParametro       = new Object();
  oParametro.exec      = 'getAlunosAprovadosPeloConselhoPorRegencia';
  oParametro.iTurma    = this.oTurma.iTurma;
  oParametro.iEtapa    = this.oTurma.iEtapa;
  oParametro.iRegencia = this.iRegencia;

  var oSelf = this;

  this.oGridResultados.clearAll(true);
  js_divCarregando("Aguarde, buscando alunos com resultados alterados.", "msgBox");
  new Ajax.Request(this.sRpcPadrao,
                   { method:     'post',
                     parameters: 'json='+Object.toJSON(oParametro),
                     onComplete: function(oAjax) {
                                   oSelf.populaGrid(oAjax);
                                 }
                   }
                  );
};

/**
 * Renderiza as linhas da grid conforme resultado obtido pela chamada do metodo:
 * DBViewAvaliacao.AlteraResultadoFinal.prototype.buscaDadosGrid
 * @param {Object}
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.populaGrid = function (oAjax) {

  js_removeObj("msgBox");
  var oRetorno = JSON.parse(oAjax.responseText);
  var oSelf    = this;

  this.oGridResultados.clearAll(true);

  oRetorno.aResultados.each( function (oResultado) {

    var oExcluir  = document.createElement('input');
    oExcluir.type = 'button';
    oExcluir.name = 'excluirResultado' + oResultado.iAprovConselho;
    oExcluir.id   = 'excluirResultado' + oResultado.iAprovConselho;
    oExcluir.setAttribute('value', 'E');

    var oCtnAcao  = document.createElement('div');
    oCtnAcao.appendChild(oExcluir);

    var aLinha    = new Array();
        aLinha[0] = oResultado.sAluno.urlDecode();
        aLinha[1] = oResultado.sProfessor.urlDecode();
        aLinha[2] = oResultado.dtData + ' ' + oResultado.sHora;
        aLinha[3] = oResultado.sFormaAprovacao.urlDecode();
        aLinha[4] = oCtnAcao.outerHTML;

    oSelf.oGridResultados.addRow(aLinha);

  });

  this.oGridResultados.renderRows();

  /**
   * Seta as funções nos botões de ação em tempo de execução
   */
  oRetorno.aResultados.each( function (oResultado) {

    $('excluirResultado'+oResultado.iAprovConselho).onclick = function() {
      oSelf.excluirResultadoFinal(oResultado.iMatricula);
    };
  });
};


/**
 * Exclui a linha da grid
 * @param {int} iAprovConselho código sequencial da aprovconselho
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.excluirResultadoFinal = function (iMatricula) {

  if (confirm('Tem certeza que deseja excluir?')) {

    var oParametro        = new Object();
    oParametro.exec       = 'excluirAlteracaoResultadoFinal';
    oParametro.iTurma     = this.oTurma.iTurma;
    oParametro.iEtapa     = this.oTurma.iEtapa;
    oParametro.iRegencia  = this.iRegencia;
    oParametro.iMatricula = iMatricula;

    var oSelf = this;

    js_divCarregando("Aguarde, excluindo alteração do resultado.", "msgBox");
    new Ajax.Request(this.sRpcPadrao,
                     { method:     'post',
                       parameters: 'json='+Object.toJSON(oParametro),
                       onComplete: function(oAjax) {
                                     oSelf.retornoExcluirResultadoFinal(oAjax);
                                   }
                     }
                    );
  }
};

DBViewAvaliacao.AlteraResultadoFinal.prototype.retornoExcluirResultadoFinal = function (oAjax) {

  js_removeObj("msgBox");
  var oRetorno = JSON.parse(oAjax.responseText);

  alert(oRetorno.message.urlDecode());
  if (oRetorno.status == 1) {

     this.getAlunosAprovadosPeloConselho();
     this.getAlunosReprovados();
  }

};


DBViewAvaliacao.AlteraResultadoFinal.prototype.validaFormulario = function () {

  if ($F('alunosDisponiveis') == '') {

    alert( _M(MSGEALTERARESULTADOFINAL + "selecione_aluno") );
    return false;
  }

  if ($F('formaAprovacao') == '') {

    alert( _M(MSGEALTERARESULTADOFINAL + "selecione_forma_avaliacao"));
    return false;
  }

  if ($F('formaAprovacao') == 1 && $F('alterarNotaFinal') == '' && this.oTurma.sTipoProcedimentoAvaliacao != 'PARECER') {

    alert( _M(MSGEALTERARESULTADOFINAL + "selecione_altera_nota_final"));
    return false;
  }

  // Validação: Período a Alterar é obrigatório quando "Aprovado pelo conselho" (formaAprovacao == 1)
  /**
   * Autor: Uemerson Santana
   * Data: 29/09/2025
   * Demanda: 17872
   */
  if ($F('formaAprovacao') == 1 && $F('alterarNotaFinal') == 2) {
    if (!$F('periodoAlteracao') || $F('periodoAlteracao') == '') {
      alert('Campo Período a Alterar é obrigatório quando a Forma de Aprovação é "Aprovado pelo conselho".');
      return false;
    }
  }

  if ([2, 3].in_array($F('alterarNotaFinal')) && $F('novaAvaliacao') == '') {

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
 * Inclui uma aprovação pelo conselho
 *
 * Autor: Uemerson Santana
 * Data: 14/10/2025
 * Demanda: 17872
 *
 * IMPORTANTE: Para Prova Final (período 10), envia requisição 2 vezes
 * devido a problema de cache que impede atualização na primeira tentativa.
 * A primeira requisição limpa o cache, a segunda funciona corretamente.
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.salvarResultadoFinal = function (iAprovConselho) {

  if (!this.validaFormulario()) {
    return false;
  }

  var iPeriodoAlteracao = $F('periodoAlteracao');
  var oSelf = this;

  // Se for Prova Final (código 10), enviar requisição 2 vezes
  if (iPeriodoAlteracao == 10) {

    js_divCarregando("Aguarde, salvando alteração do resultado.", "msgBox");

    // 1ª requisição - limpa cache (pode falhar ou não)
    this.enviarRequisicaoSalvar(function() {
      // 2ª requisição - funciona com cache limpo
      oSelf.enviarRequisicaoSalvar();
    });

    return;
  }

  // Para outros períodos, enviar apenas 1 vez
  this.enviarRequisicaoSalvar();
};

/**
 * Método auxiliar para enviar requisição de salvar
 *
 * Autor: Uemerson Santana
 * Data: 14/10/2025
 * Demanda: 17872
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.enviarRequisicaoSalvar = function (fCallback) {

  var oParametro                = new Object();
  oParametro.exec               = 'salvarAlteracaoResultadoFinal';
  oParametro.iTurma             = this.oTurma.iTurma;
  oParametro.iEtapa             = this.oTurma.iEtapa;
  oParametro.iRegencia          = this.iRegencia;
  oParametro.iMatricula         = $F('alunosDisponiveis');
  oParametro.iProfessor         = $F('codigoProfessor');
  oParametro.iFormaAprovacao    = $F('formaAprovacao');
  oParametro.iAlterarNotaFinal  = $F('alterarNotaFinal');
  oParametro.iPeriodoAlteracao  = $F('periodoAlteracao');
  oParametro.sAvaliacaoConselho = $F('novaAvaliacao');
  oParametro.sJustificativa     = encodeURIComponent(tagString($F('justificativaResultado')));

  var oSelf = this;

  if (!fCallback) {
    js_divCarregando("Aguarde, salvando alteração do resultado.", "msgBox");
  }

  new Ajax.Request(this.sRpcPadrao,
                   { method:     'post',
                     parameters: 'json='+Object.toJSON(oParametro),
                     onComplete: function(oAjax) {
                                   if (fCallback) {
                                     // Se tem callback, é a primeira requisição - não mostrar resultado
                                     fCallback();
                                   } else {
                                     // Segunda requisição ou períodos normais - mostrar resultado
                                     oSelf.retornoSalvarResultadoFinal(oAjax);
                                   }
                                 }
                   }
                  );

};

/**
 * Retorno do: DBViewAvaliacao.AlteraResultadoFinal.prototype.salvarResultadoFinal
 * @param {Object} oAjax
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.retornoSalvarResultadoFinal = function (oAjax) {

  js_removeObj("msgBox");
  var oRetorno = JSON.parse(oAjax.responseText);

  alert(oRetorno.message.urlDecode());
  if (oRetorno.status == 1) {

    if( this.oTurma.sTipoProcedimentoAvaliacao != 'PARECER' && $('formaAprovacao').value == 1 ) {
      this.oDBFormCache.save();
    }

    this.lErroValidacao = false;  // Sucesso - reseta flag de erro
    this.getAlunosAprovadosPeloConselho();
    this.getAlunosReprovados();
    this.limpaFormulario();
  } else {
    // Erro - limpar cache, formulário, marcar erro e recarregar lista
    this.lErroValidacao = true;  // Marca que houve erro de validação
    if( this.oDBFormCache ) {
      this.oDBFormCache.clear();
    }
    this.limpaFormulario();
    this.getAlunosReprovados();
  }
};

/**
 * Lookup para pesquisar um professor
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.pesquisaProfessor = function () {

  /**
   * ATENÇÃO: a variável 'oInstanciaAlteraResultadoFinal' foi colocado no escopo global
   * para atender o retorno da lookup. Após receber os dados ela será deletada
   */
  oInstanciaAlteraResultadoFinal = this;
  var sUrl = 'func_rechumano.php?';
  sUrl += 'funcao_js=parent.oInstanciaAlteraResultadoFinal.retornoProfessor|ed20_i_codigo|z01_nome';
  js_OpenJanelaIframe('','db_iframe_rechumano', sUrl, 'Pesquisa Professor', true);

  /**
   * Muda o zIndex do iframe de pesquisa para aparecer em cima dos outros frames abertos
   */
  $('Jandb_iframe_rechumano').style.zIndex = 10000;

};

DBViewAvaliacao.AlteraResultadoFinal.prototype.retornoProfessor = function () {

  $('codigoProfessor').value = arguments[0];
  $('nomeProfessor').value   = arguments[1];

  db_iframe_rechumano.hide();
  delete oInstanciaAlteraResultadoFinal;
};


/**
 * Adiciona/Remove bloqueio nos options do select da Forma de Aprovação
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.validaBloqueioOptionFormaAprovacao = function () {

  $('formaAprovacao').disabled = false;
  var oOptionSelecionado = $('alunosDisponiveis').options[$('alunosDisponiveis').selectedIndex];

  if (oOptionSelecionado.value == '') {

    $('formaAprovacao').value    = '';
    $('formaAprovacao').disabled = true;
    $('alterarNotaFinal').value  = '';
    $('alterarNotaFinal').setAttribute( 'disabled', 'disabled' );
    $('novaAvaliacao').setAttribute( 'disabled', 'disabled' );
    $('legendaAlterarNotaFinal').style.display = 'none';
  }

  /**
   * option[1] == Aprovado pelo conselho
   * option[2] == Reclassificação por baixa frequência
   * option[3] == Aprovado por regimento escolar
   */
  $('formaAprovacao').options[1].removeAttribute('disabled');
  $('formaAprovacao').options[2].removeAttribute('disabled');
  $('formaAprovacao').options[3].removeAttribute('disabled');

  if (oOptionSelecionado.getAttribute('lreprovadonota') == 'false') {
    $('formaAprovacao').options[1].setAttribute('disabled', 'disabled');
  }
  if (oOptionSelecionado.getAttribute('lreprovadofrequencia') == 'false') {
    $('formaAprovacao').options[2].setAttribute('disabled', 'disabled');
  }
};

/**
 * Limpa os dados do Formulário
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.limpaFormulario = function() {

  $('alunosDisponiveis').value      = '';
  $('codigoProfessor').value        = '';
  $('nomeProfessor').value          = '';
  $('formaAprovacao').value         = '';
  $('justificativaResultado').value = '';
  $('alterarNotaFinal').value       = '';
  $('novaAvaliacao').value          = '';
  $('formaAprovacao').disabled      = true;
  $('alterarNotaFinal').setAttribute('disabled', 'disabled');
  $('novaAvaliacao').setAttribute('disabled', 'disabled');

  if (this.oParametros.sFormaAvaliacao == 'NIVEL') {
    $('novaAvaliacao').value = $('novaAvaliacao').options[0].value;
  }

};


/**
 * Busca os parâmetros
 *  - nota minima para aprovação na turma;
 *  - forma de avaliação (Nota, Conceito);
 *    -- Se for conceito, deve trazer um array com os conceitos (do mínimo para cima)
 *    -- Se nota, deve devolver a mascara configurada para o ano;
 *  - Variação da nota;
 * @returns {void}
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.getParametros = function() {

  var oParametro           = new Object();
      oParametro.exec      = 'getParametros';
      oParametro.iTurma    = this.oTurma.iTurma;
      oParametro.iEtapa    = this.oTurma.iEtapa;
      oParametro.iRegencia = this.iRegencia;

  var oSelf = this;

  var oRequest              = {};
      oRequest.method       = 'post';
      oRequest.parameters   = 'json='+Object.toJSON(oParametro);
      oRequest.asynchronous = false;
      oRequest.onComplete   = function (oAjax) {

    var oRetorno      = JSON.parse(oAjax.responseText);
    oSelf.oParametros = oRetorno.oParametros;
  };

  new Ajax.Request(this.sRpcPadrao, oRequest);
};

/**
 * Valida Forma de Aprovação selecionada.
 * Sempre que marcado como: Aprovado pelo conselho, devemos liberar o select para cliente selecionar como deseja
 * alterar o resultado final
 * @returns {void}
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.validaFormaAprovacaoSelecionada = function () {

  this.oInputAvaliacaoNota.value  = '';
  this.oCboAlterarNotaFinal.value = '';
  this.oCboAlterarNotaFinal.setAttribute('disabled', 'disabled');
  this.oCboAvaliacaoNivel.setAttribute('disabled', 'disabled');
  this.oInputAvaliacaoNota.setAttribute('disabled', 'disabled');

  $('legendaAlterarNotaFinal').style.display = 'none';

  if ( $F('formaAprovacao') == 1 && this.oParametros.sFormaAvaliacao != 'PARECER' ) {

    this.oCboAlterarNotaFinal.removeAttribute('disabled');
    $('legendaAlterarNotaFinal').style.display = '';
    this.oDBFormCache.load();

    // Mostrar seletor de período quando for alterar nota final
    $('linhaPeriodoAlteracao').style.display = '';
    this.oCboPeriodoAlteracao.removeAttribute('disabled');

    if( this.oParametros.sFormaAvaliacao == 'NIVEL' ) {
      this.oCboAvaliacaoNivel.removeAttribute( 'disabled' );
    }
  } else {
    // Ocultar seletor de período quando não for alterar nota final
    $('linhaPeriodoAlteracao').style.display = 'none';
    this.oCboPeriodoAlteracao.setAttribute('disabled', 'disabled');
  }

  if( $F('alterarNotaFinal') == 2 || $F('alterarNotaFinal') == 3 ) {
    this.oInputAvaliacaoNota.removeAttribute('disabled');
  }
};

/**
 * Valida o status do select Alterar Nota Final, sempre que selecionado como:
 *  - Informar e Substituir ou Informar e NÃO Substituir : devemos liberar para informar a avaliação
 * @returns {void}
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.validaTipoAlterarNotaFinal = function () {

  this.oInputAvaliacaoNota.value = '';
  this.oCboAvaliacaoNivel.setAttribute('disabled', 'disabled');
  this.oInputAvaliacaoNota.setAttribute('disabled', 'disabled');

  if ( [2, 3].in_array($F('alterarNotaFinal')) ) {

    this.oCboAvaliacaoNivel.removeAttribute('disabled');
    this.oInputAvaliacaoNota.removeAttribute('disabled');
  }
};

/**
 * Valida se a nota informada esta dentro dos parâmetros da turma
 *
 * Autor: Uemerson Santana
 * Data: 06/10/2025
 * Demanda: 17872 - Remover validação de nota mínima para 4º Bimestre e Prova Final
 *
 * REGRA DE NEGÓCIO:
 * - Para 4º BIMESTRE e PROVA FINAL: NÃO validar nota mínima no client-side
 *   (A validação da MÉDIA FINAL >= 5.0 será feita no backend PHP)
 * - Para outros períodos: Manter validação de nota mínima
 *
 * @returns {Boolean}
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.validaNovaNotaFinal = function () {

  if (this.oInputAvaliacaoNota.value == '') {
    return false;
  }

  var oNota       = new Number(this.oInputAvaliacaoNota.value);
  var oNotaMinima = new Number(this.oParametros.mAvaliacaoMinima);
  var oNotaMaxima = new Number(this.oParametros.nMaiorValorNota);

  // Obter período selecionado
  var sPeriodoSelecionado = $F('periodoAlteracao');

  /**
   * IMPORTANTE: Para 3º Trimestre (código 8), 4º Bimestre (código 4) e Prova Final (código 10),
   * NÃO validar nota mínima no client-side.
   *
   * Motivo: A nota do período pode ser < 5.0, mas a MÉDIA FINAL pode ser >= 5.0
   * Exemplos:
   * - 4º Bim: nota 1.7 no 4º bim pode resultar em média dos 4 bimestres = 5.0
   * - Prova Final: nota 5.9 na prova pode resultar em (média anual 4.1 + 5.9) / 2 = 5.0
   *
   * A validação da média final será feita no backend PHP após recalcular.
   */
  var bPeriodoComValidacaoEspecial = (sPeriodoSelecionado === '4' || sPeriodoSelecionado === '8' || sPeriodoSelecionado === '10');

  if (!bPeriodoComValidacaoEspecial && oNota.valueOf() < oNotaMinima.valueOf()) {

    alert(_M(MSGEALTERARESULTADOFINAL+'avaliacao_abaixo_minimo', {'mMinimo':this.oParametros.mAvaliacaoMinima}) );
    this.oInputAvaliacaoNota.value = '';
    return false;
  }

  if ( oNota.valueOf() > oNotaMaxima.valueOf() ) {

    var oErro = {'mMinimo':this.oParametros.mAvaliacaoMinima, 'mMaximo': this.oParametros.nMaiorValorNota}
    alert(_M(MSGEALTERARESULTADOFINAL+'avaliacao_fora_intervalo', oErro));
    this.oInputAvaliacaoNota.value = '';
    return false;
  }

  if (!DBViewAvaliacao.ValidacaoVariacaoNota(oNota.valueOf(), this.oParametros.mVariacao, this.oParametros.sMascara)) {

    alert('Intervalo de nota deve ser de '+this.oParametros.mVariacao);
    this.oInputAvaliacaoNota.value = '';
    return false;
  }

  /**
   * Autor: Uemerson Santana
   * Data: 14/10/2025
   * Demanda: 17872
   *
   * VALIDAÇÃO VISUAL PARA PROVA FINAL: Mostrar aviso e bloquear botão Salvar
   * quando a nota for < 5.0 para Prova Final
   */
  var oLinhaAvisoProvaFinal = $('linhaAvisoProvaFinal');
  var oBotaoSalvar = $('salvarResultado');

  if (sPeriodoSelecionado === '10') { // Prova Final
    if (oNota.valueOf() < 5.0) {
      // Mostrar aviso (mostrar a linha TR)
      if (oLinhaAvisoProvaFinal) {
        oLinhaAvisoProvaFinal.style.display = '';
      }
      // Desabilitar botão Salvar
      if (oBotaoSalvar) {
        oBotaoSalvar.disabled = true;
        oBotaoSalvar.style.opacity = '0.5';
        oBotaoSalvar.style.cursor = 'not-allowed';
      }
    } else {
      // Ocultar aviso (ocultar a linha TR)
      if (oLinhaAvisoProvaFinal) {
        oLinhaAvisoProvaFinal.style.display = 'none';
      }
      // Habilitar botão Salvar
      if (oBotaoSalvar) {
        oBotaoSalvar.disabled = false;
        oBotaoSalvar.style.opacity = '1';
        oBotaoSalvar.style.cursor = 'pointer';
      }
    }
  } else {
    // Para outros períodos, ocultar aviso e habilitar botão
    if (oLinhaAvisoProvaFinal) {
      oLinhaAvisoProvaFinal.style.display = 'none';
    }
    if (oBotaoSalvar) {
      oBotaoSalvar.disabled = false;
      oBotaoSalvar.style.opacity = '1';
      oBotaoSalvar.style.cursor = 'pointer';
    }
  }

  js_observeMascaraNota(this.oInputAvaliacaoNota, this.oParametros.sMascara);
  return true;
};

/**
 * Valida e configura o período de alteração baseado no tipo de ensino
 * @returns {void}
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.validaPeriodoAlteracao = function () {

  // Limpar opções anteriores
  this.oCboPeriodoAlteracao.options.length = 0;
  this.oCboPeriodoAlteracao.add(new Option('Selecione o período', ''));

  // Determinar tipo de ensino baseado na turma
  var sTipoEnsino = this.oTurma.sBaseCurricular || '';
  var isAnosFinais = sTipoEnsino.indexOf('FINAIS') > -1 || sTipoEnsino.indexOf('FINAL') > -1;
  var isEJA = sTipoEnsino.indexOf('EJA') > -1;

  if (isEJA) {
    // EJA: apenas 4º Bimestre (sem Prova Final)
    /**
     * Autor: Uemerson Santana
     * Data: 29/09/2025
     * Demanda: 17872 - EJA não tem Prova Final, apenas 4º Bimestre pode ser alterado
     */
    this.oCboPeriodoAlteracao.add(new Option('4º Bimestre', '4'));
    this.oCboPeriodoAlteracao.value = '4'; // Selecionar automaticamente
  } else if (isAnosFinais) {
    // Anos Finais: permitir escolher entre 4º bimestre ou Prova Final
    this.oCboPeriodoAlteracao.add(new Option('4º Bimestre', '4'));
    this.oCboPeriodoAlteracao.add(new Option('Prova Final', '10'));
  } else {
    // Anos Iniciais: fixar no 3º trimestre (código 8 no banco de dados)
    /**
     * Autor: Uemerson Santana
     * Data: 29/09/2025
     * Demanda: 17872
     */
    this.oCboPeriodoAlteracao.add(new Option('3º Trimestre', '8'));
    this.oCboPeriodoAlteracao.value = '8'; // Selecionar automaticamente
  }
};

/**
 * Configura os períodos disponíveis baseado no tipo de ensino
 * @returns {void}
 */
DBViewAvaliacao.AlteraResultadoFinal.prototype.configuraPeriodosDisponiveis = function () {

  // Determinar tipo de ensino baseado na turma
  var sTipoEnsino = this.oTurma.sBaseCurricular || '';
  var isAnosFinais = sTipoEnsino.indexOf('FINAIS') > -1 || sTipoEnsino.indexOf('FINAL') > -1;
  var isEJA = sTipoEnsino.indexOf('EJA') > -1;

  if (isEJA) {
    // EJA: mostrar seletor com apenas 4º Bimestre (sem Prova Final)
    /**
     * Autor: Uemerson Santana
     * Data: 29/09/2025
     * Demanda: 17872 - EJA não tem Prova Final, apenas 4º Bimestre pode ser alterado
     */
    $('linhaPeriodoAlteracao').style.display = '';
    this.oCboPeriodoAlteracao.add(new Option('4º Bimestre', '4'));
    this.oCboPeriodoAlteracao.value = '4';
  } else if (isAnosFinais) {
    // Anos Finais: mostrar seletor de período
    $('linhaPeriodoAlteracao').style.display = '';
    this.validaPeriodoAlteracao(); // Preencher opções do select
  } else {
    // Anos Iniciais: ocultar seletor e fixar no 3º trimestre (código 8 no banco de dados)
    /**
     * Autor: Uemerson Santana
     * Data: 29/09/2025
     * Demanda: 17872
     */
    $('linhaPeriodoAlteracao').style.display = 'none';
    this.oCboPeriodoAlteracao.add(new Option('3º Trimestre', '8'));
    this.oCboPeriodoAlteracao.value = '8';
  }
};
