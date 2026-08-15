<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2009  DBSeller Servicos de Informatica
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

 /**
  * Classe para geracao da grade de aproveitamento do aluno
  * @author Andrio Costa <andrio.costa@dbseller.com.br>
  *         Iuri Guntchnigg <iuri@dbseller.com.br>
  * @package educacao
  * @subpackage avaliacao
  * @version $Revision: 1.48 $
  */
final class GradeAproveitamentoAluno {

  /**
   * Matricula do aluno
   * @var Matricula
   */
  private $oMatricula = null;

  /**
   * Disciplinas da turma
   * @var array
   */
  private $aDisciplinas = array();

  /**
   * Procedimento de avaliacao da turma
   * @var ProcedimentoAvaliacao
   */
  private $oProcedimentoAvaliacao = null;

  /**
   * Periodos de aulas do procedimento de avaliacao da turma
   * @var array
   */
  private $aPeriodos = array();

  /**
   * Numero de casas decimais para formatacao das faltas
   * @var integer
   */
  private $iCasasDecimais    = 0;

  /**
   * Seta se uma string deve ser codificada
   * @var boolean
   */
  private $lEncode = false;

  /**
   * seta as strings devem ser Codificadas em UTF-8
   * @var boolean
   */
  private $lEncodeUTF = false;

  /**
   * Grade de Aproveitamento da Matricula
   * @param Matricula $oMatricula
   * @throws BusinessException
   * @throws ParameterException
   */
  public function __construct(Matricula $oMatricula) {

    if (empty($oMatricula)) {
      throw new ParameterException('Parâmetro $oMatricula não pode ser vazio.');
    }

    $this->oMatricula = $oMatricula;

    $oEtapaOrigem     = $this->getMatricula()->getEtapaDeOrigem();

    /**
     * Verificamos a etapa de origem do aluno, e retornamos o procedimetno de avaliacao correspondente da turma.
     */
    $this->oProcedimentoAvaliacao = $this->oMatricula->getTurma()->getProcedimentoDeAvaliacaoDaEtapa($oEtapaOrigem);

    if (!$this->oProcedimentoAvaliacao) {
      throw new BusinessException('Não existe procedimento de avaliação para a etapa de origem do aluno');
    }
  }

  /**
   * Seta o numero de casas decimais para formatacao do total de frequencia
   * @param integer $iCasasDecimais
   */
  public function setNumeroCasasDecimais ($iCasasDecimais) {
    $this->iCasasDecimais = (int) $iCasasDecimais;
  }

  /**
   * Retorna as disciplinas da turma em que o aluno esta matriculado
   * @return Regencia
   */
  public function getDisciplinas() {

    if (count($this->aDisciplinas) == 0) {
      $this->aDisciplinas = $this->oMatricula->getTurma()
                                 ->getDisciplinasPorEtapa($this->getMatricula()->getEtapaDeOrigem());
    }
    return $this->aDisciplinas;
  }

  /**
   * Retorna a Matricula do aluno
   * @return Matricula
   */
  public function getMatricula () {

    return $this->oMatricula;
  }
  public function getRegencia()
  {
	return $this->oRegencia;
  }

  /**
   * Retorna os periodos de avaliacao da turma em que o aluno esta matriculado
   * @return AvaliacaoPeriodica[]|ResultadoAvaliacao[]
   * @throws BusinessException
   */
  public function getPeriodos() {

    if (count($this->aPeriodos) == 0) {

      if ($this->getMatricula()->getEtapaDeOrigem() == null) {
        throw new BusinessException("Aluno {$this->getMatricula()->getAluno()->getNome()} não possui etapa de Origem.");
      }

      /**
       * Deve retornar apenas os elementos periodicos, e os resultados que sao impressos
       * no boletim
       */
      foreach ($this->oProcedimentoAvaliacao->getElementos() as $oElemento){

        if ($oElemento->isResultado() && !$oElemento->imprimeNoBoletim()) {
          continue;
        }

        $this->aPeriodos[] = $oElemento;
      }
    }

    return $this->aPeriodos;
  }

  /**
   * Retorna o aproveitamento de uma regencia em um periodo
   *
   * @param Regencia           $oRegencia
   * @param IElementoAvaliacao $oElemento
   * @throws BusinessException
   * @throws Exception
   * @return stdClass
   */
  public function getAproveitamentoParaRegenciaPorPeriodo(Regencia $oRegencia, IElementoAvaliacao $oElemento, $disciplina) { //*******************************************************

    $oDiarioClasse                            = $this->getDiarioDeClasse();
    $lAlunoAvaliadoParecer                    = $oDiarioClasse->getMatricula()->isAvaliadoPorParecer();
    $oAproveitamentoRetorno                   = new stdClass();
    $oAproveitamentoRetorno->nAproveitamento  = '';
    $oAproveitamentoRetorno->iFaltas          = '';
    $oAproveitamentoRetorno->iFaltasAbonadas  = 0;
    $oAproveitamentoRetorno->lAmparado        = false;
    $oAproveitamentoRetorno->lEmRecuperacao   = false;
    $oAproveitamentoRetorno->lApareceBoletim  = true;
    $oAproveitamentoRetorno->sFormaAvaliacao  = '';
    $oAproveitamentoRetorno->lAtingiuMinimo   = true;
	$oAproveitamentoRetorno->bRegencia        = $oRegencia;


    /**
     * A propriedade dispensado eh usada quando temos Elementos de avaliacao usados como Recuperacao
     * O Aluno está dispensado de cursar a recuperacao se atingir o Aproveitamento mínimo para a Avaliacao anterior
     */
    $oAproveitamentoRetorno->lDispensado                    = false;
    $oAproveitamentoRetorno->nAproveitamentoPeriodoAnterior = "";

    $oDiarioAvaliacao = $oDiarioClasse->getDisciplinasPorRegencia($oRegencia);
    $oAmparo          = $oDiarioAvaliacao->getAmparo();
    $oAproveitamento  = $oDiarioClasse->getDisciplinasPorRegenciaPeriodo($oRegencia, $oElemento);
    /**
     * Busca o elemento de avaliação da regencia pela ordem do elemento de avaliação informado da turma
     */

       $oElementoDisciplina = $oDiarioAvaliacao->getPeriodoAvaliacaoPorOrdemSequencial($oElemento->getOrdemSequencia());
// o erro estava nesta linha
    if ( is_null($oElementoDisciplina) ) {
	    $oElementoDisciplina = [];
	}
    if ($oElemento instanceof ResultadoAvaliacao) {
      $oAproveitamentoRetorno->lApareceBoletim  = $oElemento->imprimeNoBoletim();
    }
    if ( is_null($oElementoDisciplina) ) {

      $sMsgErro  = "Não foi possível localizar o elemento de avaliação da disciplina ";
      $sMsgErro .= $oDiarioAvaliacao->getDisciplina()->getNomeDisciplina(). " onde a ordem do elemento é: ";
      $sMsgErro .= $oElemento->getOrdemSequencia();
      throw new Exception($sMsgErro);
    }
    $oElemento = $oElementoDisciplina;

    if (!empty($oAproveitamento)) {

      $oAproveitamentoRetorno->lEmRecuperacao = $oDiarioAvaliacao->emRecuperacao();
      $nAproveitamento = $oAproveitamento->getValorAproveitamento()->getAproveitamentoReal();

//	$arq = fopen("/dados/www/homologacao.epdvr.com.br/notagrade.txt","a+");
//	fwrite($arq,$nAproveitamento->nNota);
//	fwrite($arq,"\r\n");
//	fclose($arq);


      $nAproveitamento = ArredondamentoNota::formatar($nAproveitamento,
                                                        $oRegencia->getTurma()->getCalendario()->getAnoExecucao()
                                                       );
      /**
       * Verificamos se o Elemento de Avaliacao possui um outro Elemento de Avaliacao vinculado a ele
       */
      $oElementoAvaliacao = $oAproveitamento->getElementoAvaliacao();

      if ($oElementoAvaliacao instanceof AvaliacaoPeriodica) {

        $oElementoAvaliacaoVinculado = $oElementoAvaliacao->getElementoAvaliacaoVinculado();

        if (!empty($oElementoAvaliacaoVinculado)) {

          $oAproveitamentoPeriodoAnterior = $oDiarioClasse->getDisciplinasPorRegenciaPeriodo($oRegencia,
                                                                                             $oElementoAvaliacaoVinculado);
/*
          if (($oElementoAvaliacaoVinculado->getAproveitamentoMinimo() <=
               $oAproveitamentoPeriodoAnterior->getValorAproveitamento()->getAproveitamento())) {

            $oAproveitamentoRetorno->lDispensado                    = true;
            $oAproveitamentoRetorno->nAproveitamentoPeriodoAnterior = $oAproveitamentoPeriodoAnterior->
                                                                      getValorAproveitamento()->getAproveitamento();
          }
*/
        }
      }

      $oAproveitamentoRetorno->nMinimoAprovacao = $oAproveitamento->getElementoAvaliacao()->getAproveitamentoMinimo();
      $oAproveitamentoRetorno->sFormaAvaliacao  = $oAproveitamento->getElementoAvaliacao()->getFormaDeAvaliacao()->getTipo();
      $oAproveitamentoRetorno->nAproveitamento  = $this->encodeString($nAproveitamento);
      $oAproveitamentoRetorno->iFaltas          = $oAproveitamento->getNumeroFaltas();
      $oAproveitamentoRetorno->iFaltasAbonadas  = $oAproveitamento->getFaltasAbonadas();
      $oAproveitamentoRetorno->sParecer         = $this->encodeString($oAproveitamento->getParecer());
      $oAproveitamentoRetorno->sTipoAmparo      = '';
      $oAproveitamentoRetorno->lAtingiuMinimo   = $oAproveitamento->temAproveitamentoMinimo();
      $oAproveitamentoRetorno->lTemNotaExterna  = $oAproveitamento->isAvaliacaoExterna();
      $oAproveitamentoRetorno->aPareceresPadronizados = array();

//    echo "<pre>";
//    print_r($oElemento);
//    echo "</pre>";
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.txt","a+");
//fwrite($arq, $oAproveitamento->getElementoAvaliacao()->getFormaDeAvaliacao()->getTipo() );
//fwrite($arq,"\r\n");
//fclose($arq);

//      if ( $oElemento->getFormaDeAvaliacao()->getTipo() == "PARECER" && $oAproveitamentoRetorno->nAproveitamento != '') {
      /**
       * Autor: Uemerson Santana
       * Data: 17/12/2025
       * Demanda: 18038
       * Razao: Ajuste para exibir "PD" quando a forma de avaliação ? PARECER e existe
       *        nota/conceito OU parecer descritivo lan?ado. Antes exigia apenas nota/conceito,
       *        impedindo que disciplinas avaliadas somente por parecer descritivo (como TI)
       *        exibissem "PD" no boletim. Alinhado com a tela de Encerramento/Cancelamento
       *        de Avalia??es e com edu_diarioclasseresumoanual002.php.
       */
      if ( $oAproveitamento->getElementoAvaliacao()->getFormaDeAvaliacao()->getTipo() == "PARECER" && ($oAproveitamentoRetorno->nAproveitamento != '' || !empty($oAproveitamentoRetorno->sParecer))) {

        $oAproveitamentoRetorno->nAproveitamento = 'PD';
      }

      /**
       * Autor: Uemerson Santana
       * Data: 17/12/2025
       * Demanda: 18038
       * Razao: Ajuste para exibir "PD" quando o aluno ? avaliado por parecer (campo ed60_c_parecer='S')
       *        e existe nota/conceito OU parecer descritivo lan?ado. Antes exigia apenas nota/conceito,
       *        impedindo que alunos com necessidades especiais avaliados por parecer descritivo
       *        exibissem "PD" no boletim. Alinhado com edu2_fichaindividualaluno002.php que verifica
       *        isAlunoComNecessidadesEspeciais + ed60_c_parecer='S'.
       *
       * Autor: Uemerson Santana
       * Data: 18/12/2025
       * Demanda: 18038 (complemento)
       * Razao: Ajuste adicional para alinhar completamente com a Ficha Individual. Quando o aluno
       *        tem necessidades especiais E est? avaliado por parecer (ed60_c_parecer='S'), deve
       *        exibir "PD" para TODAS as disciplinas, mesmo sem parecer lan?ado para aquela disciplina
       *        espec?fica. Isso garante consist?ncia entre Ficha Individual e Boletim.
       */
      // Verifica se aluno tem necessidades especiais
      $lAlunoComNecessidadesEspeciais = false;
      if ($oDiarioClasse->getMatricula()->getAluno() != null) {
        $iCodigoAluno = $oDiarioClasse->getMatricula()->getAluno()->getCodigoAluno();
        $sSqlNecessidades = "SELECT ed214_i_aluno FROM alunonecessidade WHERE ed214_i_aluno = {$iCodigoAluno}";
        $rsNecessidades = db_query($sSqlNecessidades);
        $lAlunoComNecessidadesEspeciais = (pg_num_rows($rsNecessidades) > 0);
      }

      // Se aluno tem necessidades especiais E est? avaliado por parecer, exibe "PD" para todas as disciplinas
      if ($lAlunoAvaliadoParecer && $lAlunoComNecessidadesEspeciais) {
        $oAproveitamentoRetorno->nAproveitamento = 'PD';
        $oAproveitamentoRetorno->lAtingiuMinimo  = true;
        $oAproveitamentoRetorno->sFormaAvaliacao = "PARECER";
      } elseif ( $lAlunoAvaliadoParecer && ($oAproveitamentoRetorno->nAproveitamento != '' || !empty($oAproveitamentoRetorno->sParecer))) {
        // Caso contr?rio, mant?m a l?gica original: exige nota/conceito OU parecer lan?ado
        $oAproveitamentoRetorno->nAproveitamento = 'PD';
        $oAproveitamentoRetorno->lAtingiuMinimo  = true;
        $oAproveitamentoRetorno->sFormaAvaliacao = "PARECER";
      }

      if ($oAproveitamento->getParecerPadronizado() != "") {

        $aPartesParecer = explode("**", $oAproveitamento->getParecerPadronizado());
        foreach ($aPartesParecer as $sParecer) {

          $aPartesParecer = explode('-', $sParecer);
          $aTextoParecer  = explode("=>", $aPartesParecer[1]);

          $oDadosParecer             = new stdClass();
          $oDadosParecer->iCodigo    = $aPartesParecer[0];
          $oDadosParecer->sDescricao = $this->encodeString($aTextoParecer[0]);
          $oDadosParecer->sLegenda   = '';
          if (count($aTextoParecer) > 1) {
            $oDadosParecer->sLegenda = $this->encodeString($aTextoParecer[1]);
          }
          $oAproveitamentoRetorno->aPareceresPadronizados[] = $oDadosParecer;
        }
      }

      if ($oElementoAvaliacao instanceof ResultadoAvaliacao) {
      	$oAproveitamentoRetorno->iFaltas = $oDiarioAvaliacao->getTotalFaltas();
      }

      $oAproveitamentoRetorno->lAmparado = $oAproveitamento->isAmparado();

      if ($oAproveitamentoRetorno->lAmparado) {

        $oAproveitamentoRetorno->iFaltas          = 0;
        $oAproveitamentoRetorno->nAproveitamento  = "AMP";

        if ($oAmparo != "") {

          if ($oAmparo->getTipoAmparo() == AmparoDisciplina::AMPARO_JUSTIFICATIVA) {

            $oAproveitamentoRetorno->nAproveitamento = $this->encodeString($oAmparo->getJustificativa()->getAbreviatura());
            $oAproveitamentoRetorno->sTipoAmparo     = $this->encodeString($oAmparo->getJustificativa()->getDescricao());

          } else {

            $oAproveitamentoRetorno->nAproveitamento = $this->encodeString($oAmparo->getConvencao()->getAbreviatura());
            $oAproveitamentoRetorno->sTipoAmparo     = $this->encodeString($oAmparo->getConvencao()->getDescricao());
          }
        }
      }
    }

//    echo "<pre>";
//    print_r($oAproveitamentoRetorno);
//    echo "</pre>";

    return $oAproveitamentoRetorno;
  }//********************************************************************************************************************

  /**
   * Retorna os dados da Frequencia do Aluno para a Disciplina
   * Quando o calculo for do tipo global, o percentual de frequencia e calculado em cima de todas as disciplinas
   * @param Regencia $oRegencia
   * @return stdClass
   */
  public function getDadosFrequenciaDaDiscplina(Regencia $oRegencia) {

    $oDiarioClasse                                    = $this->getDiarioDeClasse();
    $oDadosFrequencia                                 = new stdClass();
    $oDadosFrequencia->iTotalAulas                    = 0;
    $oDadosFrequencia->iTotalFaltas                   = 0;
    $oDadosFrequencia->iFaltasAbonadas                = 0;
    $oDadosFrequencia->nPercentualFrequencia          = 0;
    $oDadosFrequencia->lReclassificadoBaixaFrequencia = false;

    $oDadosDisciplina                        = $oDiarioClasse->getDisciplinasPorRegencia($oRegencia);
    if (!empty($oDadosDisciplina)) {

      $oDadosFrequencia->iTotalAulas           = $oRegencia->getTotalDeAulas();
      $oDadosFrequencia->iTotalFaltas          = $oDadosDisciplina->getTotalFaltas();
      $oDadosFrequencia->iFaltasAbonadas       = $oDadosDisciplina->getTotalFaltasAbonadas();

      /**
          * Autor: Uemerson Santana
          * Data: 30/07/2025
          * Demanda: 17633
        */
      // Verificar se ? Anos Finais
      $sNomeCalendarioTmp = $oRegencia->getTurma()->getCalendario()->getDescricao();
      if (strpos($sNomeCalendarioTmp, 'ANOS FINAIS') !== false) {
        // Usar fun??o customizada para Anos Finais
        $iCodigoAlunoTmp = $this->oMatricula->getAluno();
        $iCodigoAlunoTmp = $iCodigoAlunoTmp->getCodigoAluno();
        $iCodigoEscolaTmp = $oRegencia->getTurma()->getEscola()->getCodigo();
        $iCodigoCalendarioTmp = $oRegencia->getTurma()->getCalendario()->getCodigo();

        $objFreqCustom = $this->calcularFrequenciaAnosFinais($iCodigoAlunoTmp, $iCodigoEscolaTmp, $iCodigoCalendarioTmp);
        $oDadosFrequencia->nPercentualFrequencia = "{$objFreqCustom->nPercentualFrequencia}";
        // $oDadosFrequencia->iTotalAulas = $objFreqCustom->iTotalAulas;
        // $oDadosFrequencia->iTotalFaltas = $objFreqCustom->iTotalFaltas;
      } else {
        // Usar m?todo padr?o para outros casos
        $oDadosFrequencia->nPercentualFrequencia = "{$oDadosDisciplina->calcularPercentualFrequencia()}";
      }

      if( $oDadosDisciplina->reclassificadoPorBaixaFrequencia() ) {
        $oDadosFrequencia->lReclassificadoBaixaFrequencia = true;
      }
    }

    unset($oDadosDisciplina);
    return $oDadosFrequencia;
  }

  /**
   * Retorna o Diario de Classe
   * @throws BusinessException
   * @return DiarioClasse
   */
  private function getDiarioDeClasse() {

    $oDiarioClasse = null;

    db_inicio_transacao();
    try {

      $oDiarioClasse = $this->oMatricula->getDiarioDeClasse();

      db_fim_transacao();
    } catch (BusinessException $eErro) {

      throw new BusinessException("Erro ao gerar diário.");
      db_fim_transacao(true);
    }

    return $oDiarioClasse;
  }

  /**
   * Retorna o resultado final da regencia
   * @param Regencia $oRegencia
   * @return AvaliacaoResultadoFinal
   */
  public function getResultadoFinalDaRegencia(Regencia $oRegencia) {
    return $this->getDiarioDeClasse()->getDisciplinasPorRegencia($oRegencia)->getResultadoFinal();
  }

  /**
   * Retorna se a disciplina foi aprovada com progressao parcial
   * @param Regencia $oRegencia
   * @return boolean
   */
  public function aprovadoComProgressaoParcial(Regencia $oRegencia) {
    return $this->getDiarioDeClasse()->getDisciplinasPorRegencia($oRegencia)->aprovadoComProgressaoParcial();
  }

    /**
     * Retorna a grade a grade de aproveitamento da matricula
     * @return Ambigous <NULL, array>
     * @throws BusinessException
     * @throws DBException
     * @throws ParameterException
     */
  public function getGradeAproveitamento($periodoP) {


    $aGradeAproveitamento = null;
    $oMatricula           = $this->getMatricula();
	$pegaerro = '';
    foreach ($this->getDisciplinas() as $oRegencia) {

        if (!$oRegencia->isLancadaNoHistorico()) {
            continue;
        }

      $iAnoCalendario                     = $oRegencia->getTurma()->getCalendario()->getAnoExecucao();
      $oDisciplina                        = new stdClass();
      $oDisciplina->iCodigoRegencia       = $oRegencia->getCodigo();
      $oDisciplina->sNome                 = $this->encodeString($oRegencia->getDisciplina()->getNomeDisciplina());

      $oDisciplina->sNomeAbreviado        = $this->encodeString($oRegencia->getDisciplina()->getAbreviatura());
      $oDisciplina->sMascaraNota          = ArredondamentoNota::getMascara( $iAnoCalendario );
      $oDisciplina->lTemProgressaoParcial = false;
      if( count( $oMatricula->getAluno()->getProgressaoParcial() ) > 0 ) {
        foreach( $oMatricula->getAluno()->getProgressaoParcial() as $oProgressaoParcialAluno ) {

          if(    $oProgressaoParcialAluno->getEtapa()->getOrdem() < $oRegencia->getEtapa()->getOrdem()
              && !$oProgressaoParcialAluno->isConcluida()
            ) {

            $iCodigoDisciplinaProgressao = $oProgressaoParcialAluno->getDisciplina()->getCodigoDisciplina();
            $iCodigoDisciplinaRegencia   = $oRegencia->getDisciplina()->getCodigoDisciplina();

            if ($iCodigoDisciplinaProgressao == $iCodigoDisciplinaRegencia ){
              $oDisciplina->lTemProgressaoParcial = true;
            }
          }
        }
      }

      $oDisciplina->oNotaParcial        = new stdClass();
      $oDisciplina->oNotaParcial->nNota = null;
      foreach ($this->getPeriodos() as $oPeriodo) {

        $oPeriodoAvalicao                      = new stdClass();
        $oPeriodoAvalicao->iCodigo             = $oPeriodo->getCodigo();
        $oPeriodoAvalicao->sDescricao          = '';
        $oPeriodoAvalicao->sDescricaoAbreviada = '';
        $oPeriodoAvalicao->lApareceBoletim     = true;
        $oPeriodoAvalicao->lResultado          = $oPeriodo->isResultado();
        $oPeriodoAvalicao->lRecuperacao        = false;



        if ($oPeriodo instanceof AvaliacaoPeriodica) {

          $oPeriodoAvalicao->sDescricao          = $this->encodeString($oPeriodo->getPeriodoAvaliacao()->getDescricao());
          $oPeriodoAvalicao->sDescricaoAbreviada = $this->encodeString($oPeriodo->getPeriodoAvaliacao()->getDescricaoAbreviada());

          // identifica o elemento como um per?odo de recuperacao
          $oElementoDependente = $oPeriodo->getElementoAvaliacaoVinculado();
          if ( !is_null($oElementoDependente) ) {
            $oPeriodoAvalicao->lRecuperacao = true;
          }
        }
        if ($oPeriodo instanceof ResultadoAvaliacao) {

          $oPeriodoAvalicao->sDescricao          = $this->encodeString($oPeriodo->getDescricao());
          $oPeriodoAvalicao->sDescricaoAbreviada = $this->encodeString($oPeriodo->getDescricaoAbreviada());
          $oPeriodoAvalicao->lApareceBoletim     = $oPeriodo->imprimeNoBoletim();

          if ( $oPeriodo->isResultado() && $oPeriodo->geraResultadoFinal() ) {
            $sNota = $this->getDiarioDeClasse()->getDisciplinasPorRegencia($oRegencia)->getNotaParcial( $oPeriodo );
            $oDisciplina->oNotaParcial->nNota = $sNota;
          }
        }


//*************  O erro acontecia aqui quando ia imprimir RECUPERA??ES da disciplina TECNOLOGIA E INOVA??O,
// não existe esta coluna no procedimento de avaliação de mat?rias por conceito, ent?o não retorna dados e da erro
// verificar se no caso de ANOS FINAIS existe alguma outra mat?ria que seja por conceito
/*
Anos finais - colunas procedimento avaliacao
LINGUA PORTUGUESA==1º BIMESTRE                    TECNOLOGIA E INOVA??O==1º BIMESTRE
LINGUA PORTUGUESA==2º BIMESTRE                    TECNOLOGIA E INOVA??O==2º BIMESTRE
LINGUA PORTUGUESA==RECUPERAÇÃO SEMESTRAL          proc avaliacao não tem essa colula - primeiro erro
LINGUA PORTUGUESA==3? BIMESTRE                    TECNOLOGIA E INOVA??O==3? BIMESTRE
LINGUA PORTUGUESA==4? BIMESTRE                    TECNOLOGIA E INOVA??O==4? BIMESTRE
LINGUA PORTUGUESA==MÉDIA ANUAL                    não tem P. A.
LINGUA PORTUGUESA==RECUPERAÇÃO FINAL              não tem P. A.
LINGUA PORTUGUESA==NOTA FINAL                     CONCEITO FINAL

Anos iniciais - colunas procedimento avaliacao
LINGUA PORTUGUESA==1? TRIMESTRE                   TECNOLOGIA E INOVA??O==1? TRIMESTRE
LINGUA PORTUGUESA==2? TRIMESTRE                   TECNOLOGIA E INOVA??O==2? TRIMESTRE
LINGUA PORTUGUESA==3? TRIMESTRE                   TECNOLOGIA E INOVA??O==3? TRIMESTRE
LINGUA PORTUGUESA==MÉDIA FINAL                    TECNOLOGIA E INOVA??O==MÉDIA FINAL

Anos finais tem 8 colunas para notas e cinco para conceito
Anos iniciais tem 4 colunas para todas as materias
o erro acontece na primeira coluna que não existe em conceito
*/

//************************************************************************************************************************************************
/*
  if ($oDisciplina->sNome <> 'TECNOLOGIA E INOVAÇÃO' )
  {
//	   if( $oPeriodoAvalicao->sDescricao == $periodoP )
//	   {
           $oAvaliacaoPeriodo                 = $this->getAproveitamentoParaRegenciaPorPeriodo($oRegencia, $oPeriodo, $oDisciplina->sNome );
//	   }

  }else{
	  if(    $oPeriodoAvalicao->sDescricao == '1º BIMESTRE'  or $oPeriodoAvalicao->sDescricao == '2º BIMESTRE'
 	      or $oPeriodoAvalicao->sDescricao == '3º BIMESTRE'
	      or $oPeriodoAvalicao->sDescricao == '4º BIMESTRE'
//Per?odo de Avalia??o não encontrado no Di?rio do Aluno. Contate o Suporte.
//Ao tentar lan?ar notas para verificar se o erro sumiria deu a mensagem acima
	      or $oPeriodoAvalicao->sDescricao == '1º TRIMESTRE' or $oPeriodoAvalicao->sDescricao == '2º TRIMESTRE' or $oPeriodoAvalicao->sDescricao == '3º TRIMESTRE'
		  or $oPeriodoAvalicao->sDescricao == 'MÉDIA FINAL'
		)
	  {
*/
         // o erro foi corrigido na linha 212, porque ele não estava encontrando o elemento de avaliação da disciplina TECNOLOGIA E INOVA??O devido o procedimento de
		 // avaliação ser diferente e não ter recupera??o, acrescentei o valor vazio para quando isso acontecer e corrigiu
         $oAvaliacaoPeriodo                 = $this->getAproveitamentoParaRegenciaPorPeriodo($oRegencia, $oPeriodo, $oDisciplina->sNome );
//	  }else{
//		 $oAvaliacaoPeriodo = [];
//	  }
//  }


//************************************************************************************************************************************************
        $oPeriodoAvalicao->oAproveitamento = $oAvaliacaoPeriodo;
        $oPeriodoAvalicao->sFormaAvaliacao = $oAvaliacaoPeriodo->sFormaAvaliacao;

        if ($oMatricula->isAvaliadoPorParecer() ) {
          $oPeriodoAvalicao->sFormaAvaliacao = 'PARECER';
        }
	    $oDisciplina->aAproveitamento[]    = $oPeriodoAvalicao;
	}


      /**
       * Busca a frequencia
       */
      $oDisciplina->oFrequencia = $this->getDadosFrequenciaDaDiscplina($oRegencia);

      /**
       * Buscamos o resultado final
       */
      $oResultadoFinal                                = new stdClass();
      $oResultadoFinal->nAproveitamentoFinal          = '';
      $oResultadoFinal->sTermoResultadoFinal          = '';
      $oResultadoFinal->sTermoResultadoFinalAbreviado = '';
      $oResultadoFinal->lAprovadoProgressaoParcial    = $this->aprovadoComProgressaoParcial($oRegencia);

      $oResultadoFinalRegencia = $this->getResultadoFinalDaRegencia($oRegencia);

      if ($oResultadoFinalRegencia->getValorAprovacao() != '') {

        $nValor = ArredondamentoNota::formatar($oResultadoFinalRegencia->getValorAprovacao(),
                                              $iAnoCalendario
                                             );
        $oResultadoFinal->nAproveitamentoFinal = $nValor;
          if ($oResultadoFinalRegencia->getResultadoAvaliacao()->getFormaDeObtencao() == 'AP') {
              $oResultadoFinal->nAproveitamentoFinal = '-';
          }
      }

      $oResultadoFinal->sResultadoAprovacao = $oResultadoFinalRegencia->getResultadoAprovacao();
      $oResultadoFinal->sResultadoFinal     = $oResultadoFinalRegencia->getResultadoFinal();

      /**
       * Se aluno foi aprovado pelo conselho de classe e a avaliação foi informada como SUBSTITUIR, a avaliação
       * final ? substituida pela informada na altera??o do resultado final
       */
      $oAprovadoConselho = $oResultadoFinalRegencia->getFormaAprovacaoConselho();

      if ( !is_null($oAprovadoConselho) && $oAprovadoConselho->getFormaAprovacao() == AprovacaoConselho::APROVADO_CONSELHO
           && $oAprovadoConselho->getAlterarNotaFinal() == AprovacaoConselho::INFORMAR_E_SUBSTITUIR ) {

        $oResultadoFinal->nAproveitamentoFinal = $oAprovadoConselho->getAvaliacaoConselho();
        $oResultadoFinal->sResultadoAprovacao  = 'A';
      }

      /**
       * caso o aluno tem a situacao Avancado, ou Classificado, ele deve constar como aprovado.
       */
      if ($this->oMatricula->getSituacao() == 'AVANÇADO' || $this->oMatricula->getSituacao() == 'CLASSIFICADO') {
        $oResultadoFinal->sResultadoFinal = 'A';
      }
      $oDisciplina->oResultadoFinal = $oResultadoFinal;

      if (isset($oResultadoFinal->sResultadoFinal) && !empty($oResultadoFinal->sResultadoFinal)) {

        /**
         * Buscamos os termos do resultado final
         */
        $iCodigoEnsino   = $this->getMatricula()->getTurma()->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();
        $aTermosAprovado = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $oResultadoFinal->sResultadoFinal, $iAnoCalendario);

        $oResultadoFinal->sTermoResultadoFinal          = $this->encodeString($aTermosAprovado[0]->sDescricao);
        $oResultadoFinal->sTermoResultadoFinalAbreviado = $this->encodeString($aTermosAprovado[0]->sAbreviatura);
      }
//    echo "<pre>";
//    print_r($oDisciplina);
//    echo "</pre>";

    $aGradeAproveitamento[] = $oDisciplina;
//}	//**************************************************        retirar

    }
    return $aGradeAproveitamento;
  }

  /**
   * Retorna o minino para a aprova??o do aluno
   * @return mixed|string
   */
  public function getMinimoParaAprovacao() {
     return $this->getDiarioDeClasse()->getMinimoAprovacao();
  }

  /**
4   * Retorna o procedimento de avaliacao
   * return ProcedimentoAvaliacao$aLegendas
   */
  public function getProcedimentoAvaliacao() {
    return $this->oProcedimentoAvaliacao;
  }

  /**
   * Seta se deve ser codificada uma string
   * @param boolean $lEncode
   */
  public function setUrlEncode($lEncode) {
    $this->lEncode = $lEncode;
  }

  public function setUtfEncode($lEncode) {
    $this->lEncodeUTF = $lEncode;
  }

  /**
   * Passa uma string para ser codificada, caso tenha sido setado $this->lEncode como true
   * @param string $sString
   * @return string
   */
  protected function encodeString($sString) {

    if ($this->lEncodeUTF) {
      $sString = utf8_encode($sString);
    }
    if ($this->lEncode) {

      $sString = urlencode($sString);
    }
    return $sString;
  }

  /**
   * Retorna o resultado final do aluno na turma
   * @return string
   */
  public function getResultadoFinalAluno() {
    return $this->getDiarioDeClasse()->getResultadoFinal();
  }

  /**
   * Retorna o amparo para disciplina se hover
   * @param Regencia $oRegencia
   * @return AmparoDisciplina|null
   */
  public function getAmparoDisciplina(Regencia $oRegencia) {
    return $this->getDiarioDeClasse()->getDisciplinasPorRegencia($oRegencia)->getAmparo();
  }

  /**
   * Controla se deve ser exibida a nota parcial
   * De acordo com o implementado na db_stdlibwebseller as notas parciais s? devem ser apresentadas quando:
   * - O Parâmetro Calcular m?dia parcial deve esta ativo (SIM)
   * - O aluno esta com situa??o da matr?cula igual a MATRICULADO;
   * - A matr?cula não esta conclu?da;
   * - A forma de obten??o do procedimento ? 'ME', 'MP', 'SO'
   *
   * @return bool
   */
  public function exibeNotaParcial() {

    $sWhere = " ed233_i_escola = {$this->oMatricula->getTurma()->getEscola()->getCodigo()}";
    $oDaoParametros = new cl_edu_parametros();
    $rsParametro    = db_query($oDaoParametros->sql_query_file(null, 'ed233_c_notabranca', null, $sWhere));

    if ( !$rsParametro ) {
      throw new Exception('Não foi encontrado os Parâmetros da escola.');
    }

    $lCalculaMediaParcial = false;
    if ( pg_num_rows($rsParametro) == 1 ) {
      $lCalculaMediaParcial = db_utils::fieldsMemory($rsParametro, 0)->ed233_c_notabranca == 'S';
    }

    if ( $lCalculaMediaParcial ) {

      if ( $this->oMatricula->isConcluida() || $this->oMatricula->getSituacao() != 'MATRICULADO' ) {
        return false;
      }

      foreach ($this->getProcedimentoAvaliacao()->getElementos() as $oElemento) {

        if ( $oElemento->isResultado() && $oElemento->geraResultadoFinal() ) {

          if ( in_array( $oElemento->getFormaDeObtencao(), array('ME', 'MP', 'SO') ) ) {
            return true;
          }
        }
      }
    }

    return false;
  }

  /**
   * Retorna se o aluno foi aprovado com progress?o parcial
   * @return bool
   */
  public function alunoAprovadoComProgressaoParcial() {
    return $this->getDiarioDeClasse()->aprovadoComProgressaoParcial();
  }


  /**
    * Autor: Uemerson Santana
    * Data: 30/07/2025
    * Demanda: 17633
    *
    * Corre??o: 17/12/2025 - Demanda: 17897
    * A regra de 75% mínimo s? deve ser aplicada para alunos que foram
    * efetivamente reclassificados por baixa frequ?ncia, não para todos.
    *
    * Corre??o: 19/01/2026 - Demanda: 18059
    * Raz?o: Para Anos Finais, a frequ?ncia deve considerar o turno da turma do aluno.
    *        Turno INTEGRAL deve usar 1522 horas/aula fixas, outros turnos usam 1000 horas/aula fixas.
    *        Antes, todos os Anos Finais usavam 1000h fixo, causando c?lculo incorreto para turmas integrais.
  */
  private function calcularFrequenciaAnosFinais($iCodigoAluno, $iCodigoEscola, $iCodigoCalendario) {

    // Autor: Uemerson Santana | Data: 19/01/2026 | Demanda: 18059
    // Raz?o: Consultar turno da turma do aluno para aplicar carga hor?ria correta
    //        (INTEGRAL = 1522h, outros = 1000h)
    //        A turma ? obtida atrav?s da reg?ncia (diario -> regencia -> turma -> turno)
    $sSqlTurno = "SELECT trim(tu.ed15_c_nome) as turno_nome
                  FROM diario d
                  INNER JOIN regencia r ON r.ed59_i_codigo = d.ed95_i_regencia
                  INNER JOIN turma t ON t.ed57_i_codigo = r.ed59_i_turma
                  INNER JOIN turno tu ON tu.ed15_i_codigo = t.ed57_i_turno
                  WHERE d.ed95_i_aluno = $iCodigoAluno
                  AND d.ed95_i_escola = $iCodigoEscola
                  AND d.ed95_i_calendario = $iCodigoCalendario
                  LIMIT 1";
    
    $rsTurno = db_query($sSqlTurno);
    $iTotalAulas = 1000; // Valor padr?o para outros turnos
    
    if (is_resource($rsTurno) && pg_num_rows($rsTurno) > 0) {
      $oTurno = db_utils::fieldsMemory($rsTurno, 0);
      $sTurnoNome = trim($oTurno->turno_nome);
      if (strtoupper($sTurnoNome) == 'INTEGRAL') {
        $iTotalAulas = 1522; // Turno INTEGRAL usa 1522 horas/aula
      }
    }

    $sSql = "SELECT sum(ed72_i_numfaltas) as total_faltas
             FROM diarioavaliacao
             INNER JOIN diario ON ed95_i_codigo = ed72_i_diario
             WHERE ed95_i_aluno = $iCodigoAluno
             AND ed95_i_escola = $iCodigoEscola
             AND ed95_i_calendario = $iCodigoCalendario
             AND ed72_c_amparo = 'N'";

    $rs = db_query($sSql);
    $iFaltas = pg_result($rs, 0, 'total_faltas') ?: 0;

    $nFrequencia = floor(($iTotalAulas - $iFaltas) / $iTotalAulas * 100);

    // Verifica se o aluno foi reclassificado por baixa frequ?ncia
    $lReclassificado = $this->getDiarioDeClasse()->reclassificadoPorBaixaFrequencia();

    // Regra 75% mínimo SOMENTE para alunos reclassificados
    if ($lReclassificado && $nFrequencia < 75) {
        $nFrequencia = 75;
    }

    return (object) [
        'nPercentualFrequencia' => $nFrequencia,
        'iTotalFaltas' => $iFaltas,
        'iTotalAulas' => $iTotalAulas,
        'iFaltasAbonadas' => 0,
        'lReclassificadoBaixaFrequencia' => $lReclassificado
    ];
}

}
