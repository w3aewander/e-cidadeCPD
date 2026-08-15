<?php
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

/**
 * Classe responsável por controlar o amaparo de uma progressão
 */
class AmparoProgressao {

  /**
   * codigo do amparo
   * @var  integer
   */
  private $iCodigo;

  /**
   * Se o amparo é para todos os períodos de avaliação do procedimento ou parcial de alguns periodos de avaliação
   * @var boolean
   */
  private $lAmparoTotal;

  /**
   * Soma na carga horaria do aluno
   * true  -> periodo de avaliação irá contar na soma da carga horária para o histórico
   * false -> periodo de avaliação não irá contar na soma da carga horária para o histórico
   * @var Boolean
   */
  private $lSomaCargaHoraria;

  /**
   * Codigo da justificativa
   * @var integer
   */
  private $iCodigoJustificativa;

  /**
   * Codigo da convencao amparo
   * @var integer
   */
  private $iCodigoConvencaoAmparo;

  /**
   * Conveção para o amparo
   * @var Convencao
   */
  private $oConvencao = null;

  /**
   * Justificativa para o amparo
   * @var Justificativa
   */
  private $oJustificativa = null;

  /**
   *
   * @var DiarioProgressaoParcial
   */
  private $oDiarioProgressaoParcial;

  /**
   * Lista de periodos amparados
   * @var array
   */
  private $aPeriodosAmparados =  array();

  /**
   * O Amparo é feito por convencao
   * @var integer
   */
  const AMPARO_CONVENCAO     = 1;

  /**
   * O Amparo é feito por uma justificativa
   * @var integer
   */
  const AMPARO_JUSTIFICATIVA = 2;

  /**
   * tipo do amparo
   * @var integer
   */
  private $iTipoAmparo;

  public function __construct( DiarioProgressaoParcial $oDiarioProgressaoParcial  ) {

    $this->oDiarioProgressaoParcial = $oDiarioProgressaoParcial;
    $oDaoAmparoProgressao    = new cl_amparoprogressao();
    $sWhereAmparo            = "ed997_diarioprogressao = {$this->oDiarioProgressaoParcial->getCodigoDiario()}";
    $sSqlAmparoProgressao    = $oDaoAmparoProgressao->sql_query_file( null, '*', null, $sWhereAmparo );
    $rsAmparoProgressao      = db_query($sSqlAmparoProgressao);

    if ( !$rsAmparoProgressao ) {
      throw new DBException("Erro ao buscar os amparos da progressão.");
    }

    if ( pg_num_rows($rsAmparoProgressao) == 0 ) {
      return $this;
    }

    $oDadosAmparo                 = db_utils::fieldsMemory($rsAmparoProgressao, 0);
    $this->iCodigo                = $oDadosAmparo->ed997_sequencial;
    $this->iCodigoJustificativa   = $oDadosAmparo->ed997_justificativa;
    $this->iCodigoConvencaoAmparo = $oDadosAmparo->ed997_convencaoamp;
    $this->lAmparoTotal           = $oDadosAmparo->ed997_todoperiodo == "t";
    $this->lSomaCargaHoraria      = $oDadosAmparo->ed997_aproveitach == "t";

    if (!empty($oDadosAmparo->ed997_justificativa)) {
      $this->setJustificativa(new Justificativa($oDadosAmparo->ed997_justificativa));
    }
    if (!empty($oDadosAmparo->ed997_convencaoamp)) {
      $this->setConvencao(new Convencao($oDadosAmparo->ed997_convencaoamp));
    }
    $oDiarioProgressaoParcial->setAmparado(true);
  }

 /**
   * Verifica se o amparo é para todos os periodos do ano letivo
   * @return boolean
   */
  public function isTotal() {
    return $this->lAmparoTotal;
  }

  /**
   * Define se ampara todo período
   * @param $lAmparoTotal
   */
  private function amparaTodosPeriodos($lAmparoTotal) {
    $this->lAmparoTotal = $lAmparoTotal;
  }

  /**
   * Verfica se os periodos amparados devem ser somados na carga horaria do aluno
   * @return boolean
   */
  public function isAdicionadoNaCargaHoraria() {
    return $this->lSomaCargaHoraria;
  }

  /**
   * Define se SomaCargaHoraria
   * @param $lSomaCargaHoraria
   */
  public function setAproveitaCargaHoraria($lSomaCargaHoraria) {
    $this->lSomaCargaHoraria = $lSomaCargaHoraria;
  }

  /**
   * Retorna o Codigo do Amparo
   * Retorna o Codigo do amparo.
   * @return integer
   */
  public function getCodigo() {
    return $this->iCodigo;
  }

  /**
   * Retorna o codigo da Justificativa utilizado no amparo
   * @return number
   */
  public function getCodigoJustificativa() {
    return $this->iCodigoJustificativa;
  }

  /**
   * Retorna o codigo da convenção do amparo
   * @return number
   */
  public function getCodigoConvencaoAmparo() {
    return $this->iCodigoConvencaoAmparo;
  }

  /**
   * Retorna uma instancia de Justificativa para o amparo
   * @return Justificativa
   */
  public function getJustificativa() {
    return $this->oJustificativa;
  }

  /**
   * Seta Justificativa para o amparo
   * @param $oJustificativa
   */
  public function setJustificativa(Justificativa $oJustificativa) {

    $this->oJustificativa         = $oJustificativa;
    $this->iCodigoJustificativa   = $oJustificativa->getCodigo();
    $this->iTipoAmparo            = AmparoProgressao::AMPARO_JUSTIFICATIVA;
    $this->oConvencao             = null;
    $this->iCodigoConvencaoAmparo = null;
  }

  /**
   * Retorna uma instancia de convenção
   * @return Convencao
   */
  public function getConvencao() {
    return $this->oConvencao;
  }

  /**
   * Define uma convenção para o Amparo
   * @param $oConvencao
   */
  public function setConvencao(Convencao $oConvencao) {

    $this->oJustificativa         = null;
    $this->iCodigoJustificativa   = null;
    $this->oConvencao             = $oConvencao;
    $this->iCodigoConvencaoAmparo = $oConvencao->getCodigo();
    $this->iTipoAmparo            = AmparoProgressao::AMPARO_CONVENCAO;
  }

  /**
   * Retorna todos os períodos amparados
   * @param bool $lRetornarResultados Se deve retornar os períodos que sejam Resultados
   * @return AvaliacaoAproveitamentoProgressao[]
   */
  public function getPeriodosAmparados( $lRetornarResultados = false ) {

    if (count($this->aPeriodosAmparados) == 0) {

      foreach ($this->oDiarioProgressaoParcial->getAvaliacoes() as $oAvaliacaoAproveitamentoProgressao) {

        if ( !$lRetornarResultados && $oAvaliacaoAproveitamentoProgressao->getElementoAvaliacao()->isResultado() ) {
          continue;
        }
        if ($oAvaliacaoAproveitamentoProgressao->isAmparado()) {

          $iCodigoPeriodo                            = $oAvaliacaoAproveitamentoProgressao->getElementoAvaliacao()->getCodigo();
          $this->aPeriodosAmparados[$iCodigoPeriodo] = $oAvaliacaoAproveitamentoProgressao;
        }
      }
    }
    return $this->aPeriodosAmparados;
  }

  /**
   * Adiciona um periodo a ser amparado
   * @param AvaliacaoAproveitamentoProgressao $oAvaliacaoAproveitamentoProgressao
   */
  public function adicionarPeriodo(AvaliacaoAproveitamentoProgressao $oAvaliacaoAproveitamentoProgressao) {

    $iCodigoPeriodo = $oAvaliacaoAproveitamentoProgressao->getElementoAvaliacao()->getCodigo();

    if( !array_key_exists( $iCodigoPeriodo, $this->aPeriodosAmparados ) ) {
      $this->aPeriodosAmparados[$iCodigoPeriodo] = $oAvaliacaoAproveitamentoProgressao;
    }
  }

  /**
   * Salva um amparo
   *
   */
  public function salvar() {

    if (!db_utils::inTransaction()) {
      throw new DBException("Sem transação com banco de dados.");
    }

    $oDaoAmparo = new cl_amparoprogressao();
    /**
     * Verificamos se o diario já possiu amparo lançado
     */
    $sWhere     = "ed997_diarioprogressao = {$this->oDiarioProgressaoParcial->getCodigoDiario()}";
    $sSqlAmparo = $oDaoAmparo->sql_query_file(null, "ed997_sequencial", null, $sWhere);
    $rsAmparo   = db_query($sSqlAmparo);

    if ( !$rsAmparo ) {
      throw new DBException("Erro ao buscar o amparo da progressão.");
    }

    $oDaoAmparo->ed997_sequencial = null;
    if ( pg_num_rows($rsAmparo) > 0) {
      $oDaoAmparo->ed997_sequencial = db_utils::fieldsMemory($rsAmparo, 0)->ed997_sequencial;
    }

    $iJustificativa = 'null';
    $iConvencao     = 'null';
    if ( !empty($this->oJustificativa) ) {
      $iJustificativa = $this->oJustificativa->getCodigo();
    }
    if ( !empty($this->oConvencao) ) {

      $iConvencao = $this->oConvencao->getCodigo();
      $this->adicionarPeriodosConvencao();
    }

    $iNumeroDePeriodos = 0;

    /**
     * Removemos todos os amaparos dos periodos de avaliações
     */
    foreach ($this->oDiarioProgressaoParcial->getAvaliacoes() as $oAvaliacaoAproveitamentoProgressao) {

      if ( $oAvaliacaoAproveitamentoProgressao->getElementoAvaliacao()->isResultado() ) {

        /**
         * Remove amparo do Resultado... o Resultado final só será amparado se for todos os períodos amparados
         */
        $oAvaliacaoAproveitamentoProgressao->setAmparado(false);
        continue;
      }
      $oAvaliacaoAproveitamentoProgressao->setAmparado(false);
      $iNumeroDePeriodos ++;
    }

    if (count($this->aPeriodosAmparados) == $iNumeroDePeriodos) {
      $this->amparaTodosPeriodos(true);
    }

    /**
     * Quando todos períodos foram amparados, devemos amparar o resultado final
     */
    if ($this->isTotal()) {

      foreach ($this->oDiarioProgressaoParcial->getAvaliacoes() as $oAvaliacaoAproveitamentoProgressao) {

        if ( $oAvaliacaoAproveitamentoProgressao->getElementoAvaliacao()->isResultado() ) {
          $oAvaliacaoAproveitamentoProgressao->setAmparado(true);
        }
      }
    }

    $oDaoAmparo->ed997_diarioprogressao = $this->oDiarioProgressaoParcial->getCodigoDiario();
    $oDaoAmparo->ed997_justificativa    = $iJustificativa;
    $oDaoAmparo->ed997_todoperiodo      = $this->isTotal() ? "t" : "f";
    $oDaoAmparo->ed997_aproveitach      = $this->isAdicionadoNaCargaHoraria() ? "t" : "f";
    $oDaoAmparo->ed997_convencaoamp     = $iConvencao;

    /**
     * Incluimos ou alteramos
     */
    if (!empty($oDaoAmparo->ed997_sequencial)) {
      $oDaoAmparo->alterar($oDaoAmparo->ed997_sequencial);
    } else {

      $oDaoAmparo->incluir(null);
      $this->iCodigo = $oDaoAmparo->ed997_sequencial;
    }

    if ($oDaoAmparo->erro_status == 0) {

      $sMsgErro  = "Erro ao salvar amparo.\n";
      $sMsgErro .= str_replace('\\n', "\n", $oDaoAmparo->erro_msg);
      throw new BusinessException($sMsgErro);
    }

    /**
     * Seta como amparados os períodos informados
     */
    if (count($this->getPeriodosAmparados()) > 0) {

      foreach ($this->getPeriodosAmparados() as $oAvaliacaoAproveitamentoProgressao) {
        $oAvaliacaoAproveitamentoProgressao->setAmparado(true);
      }

      if ( !empty($this->oJustificativa) ) {

        foreach ($this->getResultadosQueDevemSeremAmparados() as $oAvaliacaoResultado) {
          $oAvaliacaoResultado->setAmparado(true);
        }
      }
    }

    $this->oDiarioProgressaoParcial->setAmparado( true );
    $this->oDiarioProgressaoParcial->salvar();
  }

  /**
   * Quando convenção, devemos incluir amparo para todos os períodos a partir do menor período selecionado.
   * @return void
   */
  private function adicionarPeriodosConvencao() {

    $iMenorPeriodo = null;

    if (count($this->getPeriodosAmparados()) > 0) {

      /**
       * Verifica qual o menor periodo adicionado
       */
      foreach ($this->getPeriodosAmparados() as $oAvaliacaoAmparada) {

        $iOrdemPeriodo = $oAvaliacaoAmparada->getElementoAvaliacao()->getOrdemSequencia();
        if (empty($iMenorPeriodo)) {

          $iMenorPeriodo = $iOrdemPeriodo;
        }

        if ($iOrdemPeriodo < $iMenorPeriodo) {
          $iMenorPeriodo = $iOrdemPeriodo;
        }
      }
    }

    /**
     * Percorre todos períodos de avaliação, buscando os períodos que são maiores que o menor período já adicionado
     */
    foreach ($this->oDiarioProgressaoParcial->getAvaliacoes() as $oAvaliacaoAproveitamentoProgressao) {

      if ( $oAvaliacaoAproveitamentoProgressao->getElementoAvaliacao()->isResultado() ) {
        continue;
      }

      if ($oAvaliacaoAproveitamentoProgressao->getElementoAvaliacao()->getOrdemSequencia() > $iMenorPeriodo) {
        $this->adicionarPeriodo($oAvaliacaoAproveitamentoProgressao);
      }
    }
  }

  /**
   * Exclui o amparo para uma disciplina
   * @throws DBException
   * @throws BusinessException
   */
  public function excluir() {

    if (!db_utils::inTransaction()) {
      throw new DBException("Sem transação com banco de dados.");
    }

    if ( is_null($this->iCodigo) ) {
      return;
    }

    /**
     * Remove o amparo de todas avaliações (Períodos e resultados)
     */
    foreach ($this->oDiarioProgressaoParcial->getAvaliacoes() as $oAvaliacaoAproveitamentoProgressao) {
      $oAvaliacaoAproveitamentoProgressao->setAmparado(false);
    }

    $this->oDiarioProgressaoParcial->setAmparado( false );
    $this->oDiarioProgressaoParcial->salvar();

    $oDaoAmparo           = new cl_amparoprogressao();
    $oDaoAmparo->excluir($this->iCodigo);
    if ($oDaoAmparo->erro_status == 0) {

      $sMsgErro  = "Erro ao remover amparo.\n";
      $sMsgErro .= str_replace('\\n', "\n", $oDaoAmparo->erro_msg);
      throw new BusinessException($sMsgErro);
    }
  }

  /**
   * Retorna o tipo do amparo
   */
  public function getTipoAmparo() {
    return $this->iTipoAmparo;
  }

  /**
   * Busca os Resultados que compõem a avaliação do aluno que devem ser amparados.
   * @return AvaliacaoAproveitamento[] Resultados que devem ser amparados
   */
  private function getResultadosQueDevemSeremAmparados() {

    // busca todos resultados que não gerem resultado final
    $aResultados = array();
    foreach ($this->oDiarioProgressaoParcial->getResultados() as $oAvaliacaoResultado) {

      if ( $oAvaliacaoResultado->getElementoAvaliacao()->geraResultadoFinal() ) {
        continue;
      }
      $aResultados[] = $oAvaliacaoResultado;
    }

    /**
     * Valida se todos os elementos de avaliação que compõem um resultado esta amparado.
     * Se sim amparamos os resultados
     */
    $aResultadosQueDevemSeremAmparados = array();
    foreach ($aResultados as $oAvaliacaoResultado) {

      $iElementosAmparadosDoResultado = 0;
      $iElementosCompoemResultado     = count($oAvaliacaoResultado->getElementoAvaliacao()->getElementosComposicaoResultado());
      //percorre os elementos que compõem o resultado e valida se estão amparados
      foreach ( $oAvaliacaoResultado->getElementoAvaliacao()->getElementosComposicaoResultado() as $oElemento ) {

        foreach ($this->aPeriodosAmparados as $oPeriodoAmparado) {

          if ( $oPeriodoAmparado->getOrdemSequencia() == $oElemento->getElementoAvaliacao()->getOrdemSequencia() ) {
            $iElementosAmparadosDoResultado ++;
          }
        }
      }

      if ($iElementosCompoemResultado == $iElementosAmparadosDoResultado) {
        $aResultadosQueDevemSeremAmparados[] = $oAvaliacaoResultado;
      }
    }

    return $aResultadosQueDevemSeremAmparados;
  }
}