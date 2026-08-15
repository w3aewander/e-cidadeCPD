<?php

/**
 * Escreve a grade de aproveitamento da progressão parcial do aluno em uma arquivo pdf
 * @author     Andrio Costa <andrio.costa@dbseller.com.br>
 * @package    educacao
 * @subpackage relatorio
 * @example    no construtor sobre o array de aProgressoes
 *             -> todas progressões no array devem estar vínculada a uma mesma turma;
 *             -> sendo progressões de turmas diferentes, deve ser instanciado novamente a classe
 *
 * @version $Revision: 1.6 $
 */
class RelatorioGradeAproveitamentoProgressao extends PDFGradeAproveitamento {

  private $aDiarios;

  /**
   *
   * @param FPDF                     $oPdf         instancia do fpdf
   * @param ProgressaoParcialAluno[] $aProgressoes Array com todas progressões da turma
   * @param Matricula                $oMatricula   Instancia de matricula
   * @param integer                  $iLimiteLinha Tamanho máximo que vai ter a linha da tabela no arquivo
   */
  public function __construct(FPDF $oPdf, $aProgressoes, $oMatricula, $iLimiteLinha) {

    if ( !db_utils::inTransaction() ) {
      db_inicio_transacao();
    }

    foreach ($aProgressoes as $oDadosVinvulo) {
      $this->aDiarios[] = new DiarioProgressaoParcial($oDadosVinvulo->oProgressao, $oDadosVinvulo->oRegencia);
    }

    $this->oPdf         = $oPdf;
    $this->oMatricula   = $oMatricula;
    $this->iLimiteLinha = $iLimiteLinha;
    $this->controleDeFrequencia($oMatricula);

    $oRegencia                    = $this->aDiarios[0]->getRegencia();
    $this->oProcedimentoAvaliacao = $oRegencia->getProcedimentoAvaliacao();
    $this->lApresentarNotaParcial = false;
  }

  /**
   * Imprime o titulo da turma
   */
  private function imprimeTurmaProgressao() {

    $oRegencia = $this->aDiarios[0]->getRegencia();

    $sTurmaEtapa  = "Turma: " . $oRegencia->getTurma()->getDescricao();
    $sTurmaEtapa .= str_repeat(" ", 20);
    $sTurmaEtapa .= "Etapa: " . $oRegencia->getEtapa()->getNome();

    $this->oPdf->SetFont("Arial", "B", 7);
    $this->oPdf->Cell($this->iLimiteLinha, 4, $sTurmaEtapa, 1, 1);
  }

  /**
   * Escreve a grade de avaliação no pdf
   */
  public function montarGrade() {

    $this->imprimeTurmaProgressao();
    $this->montarCabecalho($this->oProcedimentoAvaliacao);

    $aDadosProgressao = $this->montaGradeDisciplinaProgressao();

    $this->oPdf->SetFont("Arial", "", 7);

    foreach ($aDadosProgressao as $oDisciplina) {

      $iAlturaLinha          = 4;
      $iLinhasNomeDisciplina = $this->oPdf->NbLines($this->iTamanhoDisciplina, $oDisciplina->sNome);
      if ( $iLinhasNomeDisciplina > 1) {
        $iAlturaLinha *= $iLinhasNomeDisciplina;
      }

      $iYAntes = $this->oPdf->GetY();
      $this->oPdf->MultiCell($this->iTamanhoDisciplina, 4, $oDisciplina->sNome, 1, "L");

      $this->oPdf->SetY($iYAntes);
      $this->oPdf->SetX( $this->oPdf->lMargin + $this->iTamanhoDisciplina );

      foreach ($oDisciplina->aAproveitamento as $oAvaliacao) {

        if ( !$oAvaliacao->lApareceBoletim ) {
          continue;
        }

        $this->oPdf->SetFont("Arial", "", 7);
        if ($oAvaliacao->oAproveitamento->nAproveitamento == "AMP" || !$oAvaliacao->oAproveitamento->lAtingiuMinimo) {
          $this->oPdf->SetFont("Arial", "B", 7);
        }

        if ($oAvaliacao->lRecuperacao) {

          $this->oPdf->Cell($this->iTamanhoElementosRecuperacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
          continue;
        }

        if ( $oAvaliacao->lResultado ) {

          $this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
          continue;
        }
        $this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
        $this->oPdf->SetFont("Arial", "", 7);
        $this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, $oAvaliacao->oAproveitamento->iFaltas, 1, 0, "C");
      }

      $sPercentualFrequencia = '';
      if ($oDisciplina->oFrequencia->nPercentualFrequencia !== '') {
        $sPercentualFrequencia = "{$oDisciplina->oFrequencia->nPercentualFrequencia}%";
      }
      $this->oPdf->SetFont("Arial", "", 7);
      $this->oPdf->Cell($this->iColunaAD,   $iAlturaLinha, $oDisciplina->oFrequencia->iTotalAulas,                1, 0, "C");
      $this->oPdf->Cell($this->iColunaTF,   $iAlturaLinha, $oDisciplina->oFrequencia->iTotalFaltas,               1, 0, "C");
      $this->oPdf->Cell($this->iColunaFA,   $iAlturaLinha, "{$oDisciplina->oFrequencia->iFaltasAbonadas}",        1, 0, "C");
      $this->oPdf->Cell($this->iColunaFreq, $iAlturaLinha, "{$sPercentualFrequencia}", 1, 0, "C");

      if ( $oDisciplina->oResultadoFinal->nAproveitamentoFinal != '' && $oDisciplina->oResultadoFinal->sResultadoAprovacao != "A") {
        $this->oPdf->SetFont("Arial", "B", 7);
      }

      $mAproveitamentoFinal          = '';
      $sTermoResultadoFinalAbreviado = '';

      if ( $oDisciplina->lConcluida ) {

        $mAproveitamentoFinal          = $oDisciplina->oResultadoFinal->nAproveitamentoFinal;
        $sTermoResultadoFinalAbreviado = $oDisciplina->oResultadoFinal->sTermoResultadoFinalAbreviado;
      }
      $this->oPdf->Cell($this->iColunaAprov, $iAlturaLinha, $mAproveitamentoFinal, 1, 0, "C");
      $this->oPdf->SetFont("Arial", "", 7);
      $this->oPdf->Cell($this->iColunaRF, $iAlturaLinha, $sTermoResultadoFinalAbreviado, 1, 1, "C");
    }
  }

  /**
   * Escreve o minimo para aprovação no pdf
   */
  public function imprimirMinimoParaAprovacao() {

    $this->oPdf->SetFont("Arial", "B", 8);
    $mMinino  = "Mínimo para Aprovação Anual: ";
    $mMinino .= $this->oProcedimentoAvaliacao->getFormaAvaliacao()->getAproveitamentoMinino();

    $this->oPdf->Cell($this->iLimiteLinha, 4, $mMinino, 1, 1, "L");
  }

  /**
   * Para cada progressão parcial vinculada na turma, organiza os dados da disciplina e avaliação em uma stdClass
   *
   * -> usado como base lógica implementada em GradeAproveitamentoAluno->getGradeAproveitamento
   * @return array   com a avaliação de uma disciplina( progressão )
   */
  private function montaGradeDisciplinaProgressao() {

    $aGradeAproveitamento = array();
    foreach ($this->aDiarios as $oDiario) {

      $oRegencia      = $oDiario->getRegencia();
      $iAnoCalendario = $oRegencia->getTurma()->getCalendario()->getAnoExecucao();

      $oDisciplina                  = new stdClass();
      $oDisciplina->iCodigoRegencia = $oRegencia->getCodigo();
      $oDisciplina->lConcluida      = $oDiario->isEncerrado();
      $oDisciplina->sNome           = $oRegencia->getDisciplina()->getNomeDisciplina();
      $oDisciplina->sNomeAbreviado  = $oRegencia->getDisciplina()->getAbreviatura();
      $oDisciplina->sMascaraNota    = ArredondamentoNota::getMascara( $iAnoCalendario );

      foreach ($this->oProcedimentoAvaliacao->getElementos() as $oPeriodo) {

        if ( $oPeriodo->isResultado() && !$oPeriodo->imprimeNoBoletim()) {
          continue;
        }

        $oPeriodoAvalicao                      = new stdClass();
        $oPeriodoAvalicao->iCodigo             = $oPeriodo->getCodigo();
        $oPeriodoAvalicao->sDescricao          = '';
        $oPeriodoAvalicao->sDescricaoAbreviada = '';
        $oPeriodoAvalicao->lApareceBoletim     = true;
        $oPeriodoAvalicao->lResultado          = $oPeriodo->isResultado();
        $oPeriodoAvalicao->lRecuperacao        = false;

        if ($oPeriodo instanceof AvaliacaoPeriodica) {

          $oPeriodoAvalicao->sDescricao          = $oPeriodo->getPeriodoAvaliacao()->getDescricao();
          $oPeriodoAvalicao->sDescricaoAbreviada = $oPeriodo->getPeriodoAvaliacao()->getDescricaoAbreviada();

          // identifica o elemento como um período de recuperacao
          $oElementoDependente = $oPeriodo->getElementoAvaliacaoVinculado();
          if ( !is_null($oElementoDependente) ) {
            $oPeriodoAvalicao->lRecuperacao = true;
          }
        }

        if ($oPeriodo instanceof ResultadoAvaliacao) {

          $oPeriodoAvalicao->sDescricao          = $oPeriodo->getDescricao();
          $oPeriodoAvalicao->sDescricaoAbreviada = $oPeriodo->getDescricaoAbreviada();
          $oPeriodoAvalicao->lApareceBoletim     = $oPeriodo->imprimeNoBoletim();
        }

        $oAvaliacaoPeriodo                 = $this->getAproveitamentoPorPeriodo($oDiario, $oPeriodo );
        $oPeriodoAvalicao->oAproveitamento = $oAvaliacaoPeriodo;
        $oPeriodoAvalicao->sFormaAvaliacao = $oAvaliacaoPeriodo->sFormaAvaliacao;
        $oDisciplina->aAproveitamento[]    = $oPeriodoAvalicao;
      }

      $oDisciplina->oFrequencia = $this->getDadosFrequenciaDaDiscplina($oDiario);

      $oResultadoFinal                                = new stdClass();
      $oResultadoFinal->nAproveitamentoFinal          = '';
      $oResultadoFinal->sTermoResultadoFinal          = '';
      $oResultadoFinal->sTermoResultadoFinalAbreviado = '';

      $oResultadoFinalRegencia = $oDiario->getResultadoFinal();
      if ($oResultadoFinalRegencia->getValorAprovacao() != '') {

        $nValor = ArredondamentoNota::formatar($oResultadoFinalRegencia->getValorAprovacao(), $iAnoCalendario );
        $oResultadoFinal->nAproveitamentoFinal = $nValor;
      }
      $oResultadoFinal->sResultadoAprovacao = $oResultadoFinalRegencia->getResultadoAprovacao();
      $oResultadoFinal->sResultadoFinal     = $oResultadoFinalRegencia->getResultadoFinal();


      if (isset($oResultadoFinal->sResultadoFinal) && !empty($oResultadoFinal->sResultadoFinal)) {

        /**
         * Buscamos os termos do resultado final
         */
        $iCodigoEnsino   = $oRegencia->getTurma()->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();
        $aTermosAprovado = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $oResultadoFinal->sResultadoFinal, $iAnoCalendario);

        $oResultadoFinal->sTermoResultadoFinal          = $aTermosAprovado[0]->sDescricao;
        $oResultadoFinal->sTermoResultadoFinalAbreviado = $aTermosAprovado[0]->sAbreviatura;
      }

      /**
       * caso o aluno aluno tenha evadido
       */
      if ( $oDiario->isEvadido() ) {
        $oResultadoFinal->sTermoResultadoFinalAbreviado = 'EVA';
      }

      $oDisciplina->oResultadoFinal = $oResultadoFinal;
      $aGradeAproveitamento[]       = $oDisciplina;
    }
    return $aGradeAproveitamento;
  }


 /**
   * Retorna os dados da Frequencia do Aluno para a Disciplina
   * Quando o calculo for do tipo global, o percentual de frequencia e calculado em cima de todas as disciplinas
   * @param Regencia $oRegencia
   * @return stdClass
   */
  public function getDadosFrequenciaDaDiscplina( DiarioProgressaoParcial $oDiario) {

    $oDadosFrequencia                                 = new stdClass();
    $oDadosFrequencia->iTotalAulas                    = 0;
    $oDadosFrequencia->iTotalFaltas                   = 0;
    $oDadosFrequencia->iFaltasAbonadas                = 0;
    $oDadosFrequencia->nPercentualFrequencia          = 0;
    $oDadosFrequencia->lReclassificadoBaixaFrequencia = false;

    $oRegencia = $oDiario->getRegencia();
    if (!empty($oDiario)) {

      $oDadosFrequencia->iTotalAulas           = $oRegencia->getTotalDeAulas();
      $oDadosFrequencia->iTotalFaltas          = $oDiario->getTotalFaltas();
      $oDadosFrequencia->iFaltasAbonadas       = $oDiario->getTotalFaltasAbonadas();
      $oDadosFrequencia->nPercentualFrequencia = "{$oDiario->calcularPercentualFrequencia()}";

      if( $oDiario->reclassificadoPorBaixaFrequencia() ) {
        $oDadosFrequencia->lReclassificadoBaixaFrequencia = true;
      }
    }

    return $oDadosFrequencia;
  }

  public function getAproveitamentoPorPeriodo( DiarioProgressaoParcial $oDiario, IElementoAvaliacao $oElemento) {

    $oRegencia = $oDiario->getRegencia();
    $iAno      = $oRegencia->getTurma()->getCalendario()->getAnoExecucao();

    $oAproveitamentoRetorno                   = new stdClass();
    $oAproveitamentoRetorno->nAproveitamento  = '';
    $oAproveitamentoRetorno->iFaltas          = '';
    $oAproveitamentoRetorno->lAmparado        = false;
    $oAproveitamentoRetorno->lApareceBoletim  = true;
    $oAproveitamentoRetorno->sFormaAvaliacao  = '';
    $oAproveitamentoRetorno->lAtingiuMinimo   = true;

    $oAmparo         = $oDiario->getAmparo();
    $oAproveitamento = $oDiario->getAproveitamentosDoPeriodo($oElemento->getCodigo());

    if ($oElemento instanceof ResultadoAvaliacao) {
      $oAproveitamentoRetorno->lApareceBoletim  = $oElemento->imprimeNoBoletim();
    }

    if ( !empty($oAproveitamento) ) {

      $oAproveitamentoRetorno->lEmRecuperacao = $oDiario->emRecuperacao();

      $nAproveitamento = $oAproveitamento->getValorAproveitamento()->getAproveitamentoReal();
      $nAproveitamento = ArredondamentoNota::formatar($nAproveitamento, $iAno );

      $oAproveitamentoRetorno->nMinimoAprovacao = $oAproveitamento->getElementoAvaliacao()->getAproveitamentoMinimo();
      $oAproveitamentoRetorno->sFormaAvaliacao  = $oAproveitamento->getElementoAvaliacao()->getFormaDeAvaliacao()->getTipo();
      $oAproveitamentoRetorno->nAproveitamento  = $nAproveitamento;
      $oAproveitamentoRetorno->iFaltas          = $oAproveitamento->getNumeroFaltas();
      $oAproveitamentoRetorno->iFaltasAbonadas  = $oAproveitamento->getFaltasAbonadas();
      $oAproveitamentoRetorno->sParecer         = $oAproveitamento->getParecer();
      $oAproveitamentoRetorno->sTipoAmparo      = '';
      $oAproveitamentoRetorno->lAtingiuMinimo   = $oAproveitamento->temAproveitamentoMinimo();
      $oAproveitamentoRetorno->aPareceresPadronizados = array();
      if ($oElemento->getFormaDeAvaliacao()->getTipo() == "PARECER" && $oAproveitamentoRetorno->nAproveitamento != '') {

        $oAproveitamentoRetorno->sParecer        = ($nAproveitamento);
        $oAproveitamentoRetorno->nAproveitamento = 'PD';
      }

      if ($oElemento instanceof ResultadoAvaliacao) {
        $oAproveitamentoRetorno->iFaltas = $oDiario->getTotalFaltas();
      }

      $oAproveitamentoRetorno->lAmparado = $oAproveitamento->isAmparado();

      if ($oAproveitamentoRetorno->lAmparado) {

        $oAproveitamentoRetorno->iFaltas          = 0;
        $oAproveitamentoRetorno->nAproveitamento  = "AMP";

        if ( !is_null($oAmparo) && !is_null($oAmparo->getCodigo()) ) {

          if ($oAmparo->getTipoAmparo() == AmparoDisciplina::AMPARO_JUSTIFICATIVA) {

            $oAproveitamentoRetorno->nAproveitamento = $oAmparo->getJustificativa()->getAbreviatura();
            $oAproveitamentoRetorno->sTipoAmparo     = $oAmparo->getJustificativa()->getDescricao();

          } else {

            $oAproveitamentoRetorno->nAproveitamento = $oAmparo->getConvencao()->getAbreviatura();
            $oAproveitamentoRetorno->sTipoAmparo     = $oAmparo->getConvencao()->getDescricao();
          }
        }
      }
    }

    return $oAproveitamentoRetorno;
  }

  /**
   * Imprime as observações de Aprovação pelo Conselho e Evasão do diário da progressão do aluno.
   */
  public function imprimirObservacao() {

    $aObservacoes = array();

    foreach ($this->aDiarios as $oDiario) {

      $oFormaAprovacaoConselho = $oDiario->getResultadoFinal()->getFormaAprovacaoConselho();

      if ( empty($oFormaAprovacaoConselho) ) {
        continue;
      }

      switch ( $oFormaAprovacaoConselho->getFormaAprovacao() ) {

        case AprovacaoConselho::APROVADO_CONSELHO:

          $aObservacoes[] = $this->adicionarObservacaoAprovadoConselho( $oDiario );
          break;

        case AprovacaoConselho::RECLASSIFICACAO_BAIXA_FREQUENCIA:

          $aObservacoes[] = $this->adicionarObservacaoReclassificadoPorBaixaFrequencia( $oDiario );
          break;

        case AprovacaoConselho::APROVADO_CONFORME_REGIMENTO_ESCOLAR:

          $aObservacoes[] = $this->adicionarObservacaoAprovadoConformeRegimentoEscolar( $oDiario );
          break;
      }

      if ( $oDiario->isEvadido() && $oDiario->isEncerrado()) {
        $aObservacoes[] = "Aluno reprovado por evasão em progressão parcial.";
      }
    }

    if ( !empty($aObservacoes) ) {

      $this->oPdf->Cell($this->iLimiteLinha, 4, 'Observações / Mensagens', 1, 1, "L");
      $this->oPdf->SetFont( 'arial', '', 7 );
      $this->oPdf->Multicell( $this->iLimiteLinha, 4, implode("\n- ", $aObservacoes), 1, 'L' );
    }
  }

  /**
   * Retorna a observação referente a Aprovação pelo Conselho
   * @param DiarioProgressaoParcial $oDiario
   * @return string
   */
  private function adicionarObservacaoAprovadoConselho( $oDiario ) {

    $sObservacao = '';
    $oRegencia   = $oDiario->getRegencia();
    $oDocumento  = new libdocumento( 5013 );

    $oDocumento->disciplina    = $oRegencia->getDisciplina()->getNomeDisciplina();
    $oDocumento->etapa         = $oRegencia->getEtapa()->getNome();
    $oDocumento->justificativa = $oDiario->getResultadoFinal()->getFormaAprovacaoConselho()->getJustificativa();
    $oDocumento->nota          = $oDiario->getResultadoFinal()->getFormaAprovacaoConselho()->getAvaliacaoConselho();
    $oDocumento->anomatricula  = $oRegencia->getTurma()->getCalendario()->getAnoExecucao();
    $aParagrafos               = $oDocumento->getDocParagrafos();

    if ( isset( $aParagrafos[1] ) ) {
      $sObservacao = "- {$aParagrafos[1]->oParag->db02_texto}";
    }

    return $sObservacao;
  }

  /**
   * Retorna a observação referente a reclassificação por baixa frequência.
   * @param DiarioProgressaoParcial $oDiario
   * @return string
   */
  private function adicionarObservacaoReclassificadoPorBaixaFrequencia( $oDiario ) {

    $sObservacao = '';
    $oRegencia   = $oDiario->getRegencia();
    $oDocumento  = new libdocumento( 5006 );
    $oDocumento->nome_aluno = $this->oMatricula->getAluno()->getNome();
    $oDocumento->ano        = $oRegencia->getTurma()->getCalendario()->getAnoExecucao();
    $oDocumento->nome_etapa = $oRegencia->getEtapa()->getNome();
    $aParagrafos            = $oDocumento->getDocParagrafos();

    if ( isset( $aParagrafos[1] ) ) {
      $sObservacao = $aParagrafos[1]->oParag->db02_texto;
    }

    return $sObservacao;
  }

  /**
   * Retorna a observação referente a aprovação conforme regimento Escolar.
   * @param DiarioProgressaoParcial $oDiario
   * @return string
   */
  private function adicionarObservacaoAprovadoConformeRegimentoEscolar( $oDiario ) {

    $oRegencia    = $oDiario->getRegencia();
    $sObservacao  = "Disciplina {$oRegencia->getDisciplina()->getNomeDisciplina()}: ";
    $sObservacao .= "Aprovado conforme regimento escolar. Justificativa: ";
    $sObservacao .= $oDiario->getResultadoFinal()->getFormaAprovacaoConselho()->getJustificativa();
    return $sObservacao;
  }

}
