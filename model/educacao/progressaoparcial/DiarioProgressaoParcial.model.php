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
 * Classe responsável por criar um diário para progressão parcial do aluno
 *
 * @package    educacao
 * @subpackage progressaoparcial
 * @author     Andrio Costa <andrio.costa@dbseller.com.br>
 * @version    $Revision: 1.32 $
 */
class DiarioProgressaoParcial extends DiarioDisciplina {

  const MSG_DIARIOPROGRESSAOPARCIAL = "educacao.escola.DiarioProgressaoParcial.";

  /**
   * Instância da progressão parcial
   * @var ProgressaoParcialAluno
   */
  private $oProgressao;


  /**
   * Ultimo Vínculo / matricula da progressão com uma regência
   * @var ProgressaoParcialVinculoDisciplina
   */
  private $oVinculoDisciplina;

  private $lEvadido       = false;
  private $lAmparado      = false;
  private $lEmRecuperacao = false;


  /**
   * Todas avaliações do aluno
   * @var AvaliacaoAproveitamentoProgressao[]
   */
  private $aAvaliacoes = array();

  /**
   * Guarda uma instância de AmparoProgressao
   * @var AmparoProgressao|null
   */
  protected $oAmparo = null;

  /**
   * Regencia da turma onde o aluno esta cursando a progressão
   * @var null
   */
  private $oRegenciaVinculo = null;

  private $ultimoResultaFinal = '';
  /**
   * construtor
   *
   * @param ProgressaoParcialAluno $oProgressao
   * @throws DBException
   * @throws BusinessException
   */
  function __construct(ProgressaoParcialAluno $oProgressao, $oRegenciaVinculo = null) {

    $this->oProgressao = $oProgressao;

    if ( $oRegenciaVinculo instanceof Regencia ) {
      $this->oRegenciaVinculo = $oRegenciaVinculo;
    }

    if ( !db_utils::inTransaction() ) {
      throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "sem_transacao") );
    }

    $oMatricula       = $this->buscaVinculoRegencia();

    $oMsgErro         = new stdClass();
    $oMsgErro->sAluno = $this->oProgressao->getAluno()->getNome();

    $sWhere     = "ed993_progressaoparcialalunoturmaregencia = {$oMatricula->getCodigoVinculo()}";
    $oDaoDiario = new cl_diarioprogressao();
    $sSqlDiario = $oDaoDiario->sql_query_file(null, "*", null, $sWhere);
    $rsDiario   = db_query($sSqlDiario);
    //var_dump($rsDiario);
    //$yyy = pg_fetch_all($rsDiario);
    //var_dump($yyy);
    //var_dump(!$rsDiario);
    //die("Erro acima?");
    //var_dump(pg_num_rows($rsDiario));

    //if (!$rsDiario) {
    /*if ( pg_num_rows($rsDiario) ==0 ) {
      $oMsgErro->sErroInesperado = " buscar o diario do aluno.";
      throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_inesperdado", $oMsgErro) );
    }*/

    if ( pg_num_rows($rsDiario) == 1) {

      $oDados          = db_utils::fieldsMemory($rsDiario, 0);
      $this->iCodigoDiario   = $oDados->ed993_sequencial;
      $this->lEvadido  = $oDados->ed993_evadido  == 't';
      $this->lAmparado = $oDados->ed993_amparado == 't';
    } else {
      $this->criarDiario();
    }
  }

  /**
   * Cria o diario da progressão parcial caso o aluno ainda não possua
   *
   * @throws DBException
   */
  private function criarDiario() {

    $oDaoDiario = new cl_diarioprogressao();

    $oDaoDiario->ed993_sequencial                          = null;
    $oDaoDiario->ed993_progressaoparcialalunoturmaregencia = $this->buscaVinculoRegencia()->getCodigoVinculo();
    $oDaoDiario->ed993_evadido                             = 'f';
    $oDaoDiario->ed993_amparado                            = 'f';
    $oDaoDiario->incluir(null);

    if ($oDaoDiario->erro_status == 0) {
      throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_incluir_diario") );
    }

    $this->iCodigoDiario = $oDaoDiario->ed993_sequencial;
    $oRegencia     = $this->buscaVinculoRegencia()->getRegencia();

    /**
     * Incluimos um registro em branco para os dados das avaliacoes da etapa de origem da matricula.
     */
    foreach ($oRegencia->getProcedimentoAvaliacao()->getElementos() as $oPeriodoAvaliacao) {

      if ($oPeriodoAvaliacao instanceof AvaliacaoPeriodica) {
        $this->salvarNovaAvaliacao( $oPeriodoAvaliacao );
      } else {
        $this->salvarNovoResultado( $oPeriodoAvaliacao );
      }
    }

  }

  /**
   * Inclui as avaliações ao diário do aluno
   *
   * @param  AvaliacaoPeriodica $oAvaliacao
   * @throws DBException
   */
  private function salvarNovaAvaliacao( AvaliacaoPeriodica $oAvaliacao) {

    $oDaoDiarioAvaliacao                         = new cl_diarioprogressaoavaliacao();
    $oDaoDiarioAvaliacao->ed999_sequencial       = null;
    $oDaoDiarioAvaliacao->ed999_diarioprogressao = $this->iCodigoDiario;
    $oDaoDiarioAvaliacao->ed999_procavaliacao    = $oAvaliacao->getCodigo();
    $oDaoDiarioAvaliacao->ed999_atingiominimo    = 'f';
    $oDaoDiarioAvaliacao->ed999_amparado         = 'f';

    if ($oAvaliacao->getFormaDeAvaliacao()->getTipo() == 'PARECER') {
      $oDaoDiarioAvaliacao->ed999_atingiominimo = 't';
    }

    $oDaoDiarioAvaliacao->incluir(null);

    if ($oDaoDiarioAvaliacao->erro_status == 0) {

      $oMsgErro         = new stdClass();
      $oMsgErro->sAluno = $this->oProgressao->getAluno()->getNome();
      throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_salvar_avaliacao", $oMsgErro) );
    }
  }

  /**
   * Inclui o(s) resultado(s) final(ais) vínculados ao procedimento de avaliação
   *
   * @param  ResultadoAvaliacao $oResultado
   * @throws DBException
   */
  private function salvarNovoResultado( ResultadoAvaliacao $oResultado) {

    $oDaoDiarioResultado                         = new cl_diarioprogressaoresultado();
    $oDaoDiarioResultado->ed998_sequencial       = null;
    $oDaoDiarioResultado->ed998_diarioprogressao = $this->iCodigoDiario;
    $oDaoDiarioResultado->ed998_procresultado    = $oResultado->getCodigo();
    $oDaoDiarioResultado->ed998_atingiominimo    = 'f';
    $oDaoDiarioResultado->ed998_amparado         = 'f';

    if ($oResultado->getFormaDeAvaliacao()->getTipo() == 'PARECER' ) {
      $oDaoDiarioResultado->ed998_atingiominimo = 'S';
    }

    $oDaoDiarioResultado->incluir(null);
    if ($oDaoDiarioResultado->erro_status == 0) {

      $oMsgErro         = new stdClass();
      $oMsgErro->sAluno = $this->oProgressao->getAluno()->getNome();
      throw new DBException(_M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_salvar_resultado", $oMsgErro));
    }

    if ($oResultado->geraResultadoFinal()) {
      $this->salvarDiarioFinal( $oResultado);
    }
  }

  /**
   * Cria o diário final, de acordo com o primeiro elemento que gera resultado final.
   * Foi mantida mesma lógica implementada em DiarioClasse::salvarDiarioFinal incluindo apenas o primeiro resultado,
   * mesmo havendo possíbilidade do procedimento possuir até 2.
   *
   * @param ResultadoAvaliacao $oResultado
   * @throws DBException
   */
  private function salvarDiarioFinal( ResultadoAvaliacao $oResultado) {

    /**
     * Verificado caso haja dois resultados, para incluir apenas o primeiro.
     */
    $sWhere          = "ed995_diarioprogressao = {$this->iCodigoDiario}";
    $oDaoDiarioFinal = new cl_diariofinalprogressao();
    $sSqlDiarioFinal = $oDaoDiarioFinal->sql_query_file(null, '1', null, $sWhere);
    $rsDiarioFinal   = db_query($sSqlDiarioFinal);

    if( $rsDiarioFinal && pg_num_rows($rsDiarioFinal) > 0 ) {
      return;
    }

    $oDaoDiarioFinal->ed995_sequencial         = null;
    $oDaoDiarioFinal->ed995_diarioprogressao   = $this->iCodigoDiario;
    $oDaoDiarioFinal->ed995_procresultadoaprov = $oResultado->getCodigo();
    $oDaoDiarioFinal->ed995_procresultadofreq  = $oResultado->getCodigo();

    $oDaoDiarioFinal->incluir(null);
    if ($oDaoDiarioFinal->erro_status == "0") {

      $oMsgErro         = new stdClass();
      $oMsgErro->sAluno = $this->oProgressao->getAluno()->getNome();
      throw new DBException(_M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_salvar_resultado", $oMsgErro));
    }
  }


  /**
   * Retorna as avaliações do aluno de acordo com seu diário
   *
   * @return AvaliacaoAproveitamentoProgressao[]
   */
  public function getAvaliacoes() {

    if (count($this->aAvaliacoes) == 0 && !empty($this->iCodigoDiario)) {

      $oDaoDiario     = new cl_diarioprogressao();
      $sSqlAvaliacoes = $oDaoDiario->sql_query_avaliacoes($this->iCodigoDiario);
      $rsAvaliacoes   = db_query( $sSqlAvaliacoes );
      //var_dump($sSqlAvaliacoes); die("Aqui agora");

      if ( !$rsAvaliacoes ) {
        throw new DBException(_M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_buscar_avaliacoes"));
      }

      $iLinhas = pg_num_rows( $rsAvaliacoes );
      for( $i = 0; $i < $iLinhas; $i++) {

        $oDadosDiario             = db_utils::fieldsMemory($rsAvaliacoes, $i);
        $oAvaliavaoAproveitamento = new AvaliacaoAproveitamentoProgressao($oDadosDiario->codigo);
        if ($oDadosDiario->tipo_elemento == "A") {
          $oElementoAvaliacao = AvaliacaoPeriodicaRepository::getAvaliacaoPeriodicaByCodigo($oDadosDiario->codigo_elemento);
        } else {
          $oElementoAvaliacao = ResultadoAvaliacaoRepository::getResultadoAvaliacaoByCodigo($oDadosDiario->codigo_elemento);
        }


        $oAvaliavaoAproveitamento->setDiarioProgressaoParcial($this);
        $oAvaliavaoAproveitamento->setElementoAvaliacao($oElementoAvaliacao);
        $oAvaliavaoAproveitamento->setNumeroFaltas($oDadosDiario->numero_faltas);
        $oAvaliavaoAproveitamento->setParecerPadronizado($oDadosDiario->parecerpadronizado);
        $oAvaliavaoAproveitamento->setAmparado(trim($oDadosDiario->amparo) == "t" ? true : false);
        $oAvaliavaoAproveitamento->setObservacao($oDadosDiario->observacao);


        $sTipoAvaliacao       = $oElementoAvaliacao->getFormaDeAvaliacao()->getTipo();
        $oValorAproveitamento = null;
        switch ($sTipoAvaliacao) {

          case 'NOTA' :

            $oValorAproveitamento = new ValorAproveitamentoNota($oDadosDiario->valor_nota);
            $oValorAproveitamento->setAproveitamentoReal( $oDadosDiario->valor_nota_real );
            $oAvaliavaoAproveitamento->setParecer($oDadosDiario->parecer);
            break;

          case 'PARECER' :

            $oValorAproveitamento = new ValorAproveitamentoParecer($oDadosDiario->parecer);
            break;

         case 'NIVEL' :

            $oValorAproveitamento = new ValorAproveitamentoNivel($oDadosDiario->valor_conceito, $oDadosDiario->ordem_conceito);
            $oAvaliavaoAproveitamento->setParecer($oDadosDiario->parecer);
            break;
        }

        $oAvaliavaoAproveitamento->setValorAproveitamento($oValorAproveitamento);
        $oAvaliavaoAproveitamento->setAproveitamentoMinimo($oDadosDiario->minimo == "t" ? true : false);
        $oAvaliavaoAproveitamento->setEmRecuperacao($oDadosDiario->em_recuperacao == "t");
        $oAvaliavaoAproveitamento->setTipo('M');

        $oRegencia = $this->buscaVinculoRegencia()->getRegencia();
        $oEscola   = $oRegencia->getTurma()->getEscola();
        $oAvaliavaoAproveitamento->setEscola($oEscola);

        $this->aAvaliacoes[] = $oAvaliavaoAproveitamento;
      }

    /**
     * @todo  nao foi implementada lógica que tem no fim do metodo  DiarioAvaliacaoDisciplina::getAvaliacoes()
     *        a principio não faz sentido... primeiro vamos tentar fazer o sistema entrar no código
     */
    }
    return $this->aAvaliacoes;
  }

  /**
   * Metodo para manter compatibilidade
   * @return boolean
   */
  public function proporcionalidadeComAmparoTotal() {
    return false;
  }

  /**
   * Se progressão já esta encerrada
   * @return boolean
   */
  public function isEncerrado() {
    return $this->buscaVinculoRegencia()->isEncerrado();
  }

  /**
   * Retorna o vínculo do aluno com a regência que esta cursando
   * @return ProgressaoParcialVinculoDisciplina
   */
  public function buscaVinculoRegencia() {

    if ( is_null($this->oVinculoDisciplina) ) {

      $iRegencia = null;
      if ( !is_null($this->oRegenciaVinculo) ) {
        $iRegencia = $this->oRegenciaVinculo->getCodigo();
      }
      $oVinculo = $this->oProgressao->getVinculoRegencia($iRegencia);

      $oMsgErro         = new stdClass();
      $oMsgErro->sAluno = $this->oProgressao->getAluno()->getNome();
      if ( is_null($oVinculo) ) {
        throw new BusinessException(_M(self::MSG_DIARIOPROGRESSAOPARCIAL . "sem_matricula", $oMsgErro));
      }
      $this->oVinculoDisciplina = $oVinculo;
    }

    return $this->oVinculoDisciplina;
  }

  /**
   * Salva as avaliações do aluno
   *
   * @return boolean
   */
  public function salvar() {

    if (!db_utils::inTransaction()) {
      throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "sem_transacao") );
    }

    if ($this->isEncerrado()) {
      return true;
    }

    if (!empty($this->iCodigoDiario)) {

      $oDaoDiario = new cl_diarioprogressao();

      $oDaoDiario->ed993_sequencial                          = $this->iCodigoDiario;
      $oDaoDiario->ed993_progressaoparcialalunoturmaregencia = $this->buscaVinculoRegencia()->getCodigoVinculo();
      $oDaoDiario->ed993_evadido                             = $this->lEvadido  ? 't' : 'f';
      $oDaoDiario->ed993_amparado                            = $this->lAmparado ? 't' : 'f';

      $oDaoDiario->alterar($this->iCodigoDiario);

      if ($oDaoDiario->erro_status == 0) {
        throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_salvar_diario") );
      }
    }

    foreach ($this->getAvaliacoes() as $oAvaliacao) {

      if ($oAvaliacao->getElementoAvaliacao()->isResultado()) {
        $this->salvarDadosResultado($oAvaliacao);
      } else {
        $this->salvarDadosAvaliacao($oAvaliacao);
      }
    }

    return true;
  }

  /**
   * Persiste as avaliações
   *
   * @param  AvaliacaoAproveitamentoProgressao $oAvaliacao
   * @return boolean
   */
  public function salvarDadosAvaliacao( AvaliacaoAproveitamentoProgressao $oAvaliacao ) {

    $oElementoAvaliacao = $oAvaliacao->getElementoAvaliacao();

    $oDaoAvaliacao = new cl_diarioprogressaoavaliacao();
    $oDaoAvaliacao->ed999_diarioprogressao   = $this->iCodigoDiario;
    $oDaoAvaliacao->ed999_procavaliacao      = $oElementoAvaliacao->getCodigo();
    $oDaoAvaliacao->ed999_faltas             = $oAvaliacao->getNumeroFaltas();
    $oDaoAvaliacao->ed999_atingiominimo      = $oAvaliacao->temAproveitamentoMinimo() ? 't' : 'f';
    $oDaoAvaliacao->ed999_amparado           = $oAvaliacao->isAmparado()              ? 't' : 'f';
    $oDaoAvaliacao->ed999_observacao         = $oAvaliacao->getObservacao();
    $oDaoAvaliacao->ed999_nota               = '';
    $oDaoAvaliacao->ed999_conceito           = '';
    $oDaoAvaliacao->ed999_parecer            = '';
    $oDaoAvaliacao->ed999_parecerpadronizado = '';

    $nValorAproveitamento = $oAvaliacao->getValorAproveitamento()->getAproveitamento();
    $sFormaAvaliacao      = $oElementoAvaliacao->getFormaDeAvaliacao()->getTipo();

    if ($oAvaliacao->getParecerPadronizado() != "") {
      $oDaoAvaliacao->ed999_parecerpadronizado = trim($oAvaliacao->getParecerPadronizado());
    }
    $oDaoAvaliacao->ed999_parecer = pg_escape_string(("{$oAvaliacao->getParecer()}"));
    switch ($sFormaAvaliacao) {

      case 'NOTA':

        $oDaoAvaliacao->ed999_nota = "{$nValorAproveitamento}";
        if ($nValorAproveitamento < $oElementoAvaliacao->getAproveitamentoMinimo()) {
          $oDaoAvaliacao->ed999_atingiominimo = 'f';
        }
        break;

      case 'NIVEL':

        $oDaoAvaliacao->ed999_conceito = "{$nValorAproveitamento}";
        $oAproveitamento               = $oElementoAvaliacao->getFormaDeAvaliacao()->getConceitoMinimo();
        if ( !is_null($oAproveitamento) ) {

          $iOrdemAvaliacao = $oElementoAvaliacao->getFormaDeAvaliacao()->getConceitoMinimo()->iOrdem;
          if ($oAvaliacao->getValorAproveitamento()->getOrdem() < $iOrdemAvaliacao) {
            $oDaoAvaliacao->ed999_atingiominimo = 'N';
          }
        }
        break;

      case 'PARECER':

        $oDaoAvaliacao->ed72_t_parecer = pg_escape_string(("{$nValorAproveitamento}"));
        break;
    }

    /**
     * Quando aluno amparado, sempre tem aproveitamento minimo;
     */
    if ($oAvaliacao->isAmparado()) {
      $oDaoAvaliacao->ed999_atingiominimo = 't';
    }

    if ($oAvaliacao->getCodigo() == '') {

      $oDaoAvaliacao->incluir(null);
      $oAvaliacao->setCodigo($oDaoAvaliacao->ed999_sequencial);
    } else {

      $oDaoAvaliacao->ed999_sequencial = $oAvaliacao->getCodigo();
      $oDaoAvaliacao->alterar($oDaoAvaliacao->ed999_sequencial);
    }

    if ($oDaoAvaliacao->erro_status == 0) {

      $oMsgErro = new stdClass();
      $oMsgErro->sAluno = $this->oProgressao->getAluno()->getNome();
      throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_salvar_avaliacao", $oMsgErro) );
    }
    return true;
  }

  /**
   * Persiste os resultados
   * @param  AvaliacaoAproveitamentoProgressao $oAvaliacao
   */
  public function salvarDadosResultado(AvaliacaoAproveitamentoProgressao $oAvaliacao) {

    $oRegencia = $this->buscaVinculoRegencia()->getRegencia();
    $iAno      = $oRegencia->getTurma()->getCalendario()->getAnoExecucao();

    $lCaracterReprobatorio = $oRegencia->possuiCaracterReprobatorio();
    $oElementoAvaliacao    = $oAvaliacao->getElementoAvaliacao();

    $oDaoResultado = new cl_diarioprogressaoresultado();

    $oDaoResultado->ed998_diarioprogressao   = $this->iCodigoDiario;
    $oDaoResultado->ed998_procresultado      = $oElementoAvaliacao->getCodigo();
    $oDaoResultado->ed998_atingiominimo      = $oAvaliacao->temAproveitamentoMinimo() ? 't' : 'f';
    $oDaoResultado->ed998_amparado           = $oAvaliacao->isAmparado()              ? 't' : 'f';
    $oDaoResultado->ed998_parecerpadronizado = trim($oAvaliacao->getParecerPadronizado());
    $oDaoResultado->ed998_nota               = '';
    $oDaoResultado->ed998_conceito           = '';
    $oDaoResultado->ed998_parecer            = '';
    $oDaoResultado->ed998_valorreal          = '';

    /**
     * nValorAproveitamento é o atual valor salvo no Resultado
     */
    $nValorAproveitamento = $oAvaliacao->getValorAproveitamento()->getAproveitamento();
    $nAproveitamentoReal  = $oAvaliacao->getValorAproveitamento()->getAproveitamentoReal();

    $lTemDireitoRecuperacao = true;
    $sFormaAvaliacao        = $oElementoAvaliacao->getFormaDeAvaliacao()->getTipo();

    /**
     * Disciplinas apenas com Frequencia nao tem direito a recuperacao
     */
    if (trim($oRegencia->getFrequenciaGlobal()) == 'F') {

      $lTemDireitoRecuperacao = false;
      $oAvaliacao->emRecuperacao(false);
      unset($oRecuperacao);
    }

    /**
     * Verifica se o elemento possui um elemento dependente ou seja uma recuperação
     */
    $oRecuperacao = AvaliacaoPeriodicaRepository::getAvaliacaoDependente($oElementoAvaliacao);

    if ( !empty($oRecuperacao) ) {

      /**
       * Valida se ja foi avaliado na recuperação, se foi não deve mais salvar como em recuperação
       */
      $oAvaliacaoAproveitamentoRecuperacao = $this->getAproveitamentosDoPeriodo($oRecuperacao->getCodigo());
      $oAproveitamentoNaRecuperacao        = $oAvaliacaoAproveitamentoRecuperacao->getValorAproveitamento();
      if ( !empty($oAproveitamentoNaRecuperacao) ) {

        if ($oAproveitamentoNaRecuperacao->getAproveitamento() !== "") {

          $lTemDireitoRecuperacao = false;
          $oAvaliacao->setEmRecuperacao(false);
        }
      }

      /**
       * Valida se o elemento de recuperação foi amparado. Se sim o resultado que estamos salvando não tem mais
       * direito a recuperação
       */
      if( $oAvaliacao->emRecuperacao() ) {

        if ($oAvaliacaoAproveitamentoRecuperacao->isAmparado() ) {

          $lTemDireitoRecuperacao = false;
          $oAvaliacao->emRecuperacao(false);
        }
      }
    }

    /**
     * Se ainda não esta encerrado, recalcula resultado
     */
    if ( !$this->isEncerrado() ) {

      $oResultadoAvaliacao = $oAvaliacao->getElementoAvaliacao();
      $nAproveitamento     = '';
      $oAproveitamento     = $oResultadoAvaliacao->getResultado( $this->getAvaliacoes(), false, $iAno );

      if (!empty($oAproveitamento) && is_null($oAproveitamento->getAproveitamentoReal()) ) {
        $oAproveitamento->setAproveitamentoReal( $oAproveitamento->getAproveitamento() );
      }

      $iEscola   = $oRegencia->getTurma()->getEscola()->getCodigo();
      $mNotaReal = DiarioProgressaoParcial::calcularResultadoReal( $oResultadoAvaliacao, $iEscola, $this->getAvaliacoes(), $iAno);
      if (!empty($oAproveitamento) && !is_null( $mNotaReal ) ) {
        $oAproveitamento->setAproveitamentoReal( $mNotaReal );
      }

      if ( !empty($oAproveitamento) ) {

        $nAproveitamento     = $oAproveitamento->getAproveitamento();
        $nAproveitamentoReal = $oAproveitamento->getAproveitamentoReal();
      }

      /**
       * Atualiza o valor calculado do resultado na classe
       */
      $oAvaliacao->setValorAproveitamento($oAproveitamento);
      $nValorAproveitamento = ArredondamentoNota::arredondar($nAproveitamento, $iAno);

      /**
       * @todo  pode ser um metodo
       * Valida se o aluno ficou abaixo do percentual mínimo de frequência.
       * Alunos reprovados por frequência não tem direito a recuperação
       */
      if(    $this->calcularPercentualFrequencia() < $this->getRegencia()->getProcedimentoAvaliacao()->getPercentualFrequencia()
          && !$this->reclassificadoPorBaixaFrequencia()
        ) {

        $oAvaliacao->setEmRecuperacao( false );
        $lTemDireitoRecuperacao = false;
      }

      /**
       * Validação necessária para tratamento do Resultado Final tratando o tipo de avaliação
       * @todo se plugin for incorporado ao e-cidade, pode-se mover essa lógica para um metodo em DiarioDisciplina
       *
       */
      switch ($sFormaAvaliacao) {

        /**
         * NOTA:> Temos que avaliar o valor definido para aproveitamento mínimo
         */
        case 'NOTA':

          $oAvaliacao->setAproveitamentoMinimo(true);
          $oAvaliacao->setEmRecuperacao(false);

          if (   !($nValorAproveitamento === '')
               && ( ((int) $nValorAproveitamento === 0)
                    || $nValorAproveitamento < $oResultadoAvaliacao->getAproveitamentoMinimo())
             ) {

            if ( $lTemDireitoRecuperacao && !empty($oRecuperacao) && $lCaracterReprobatorio) {
              $oAvaliacao->setEmRecuperacao(true);
            }

            $oAvaliacao->setAproveitamentoMinimo(false);
          }

          break;

        /**
         * NIVEL:> Temos que avaliar a ordem das avaliações
         */
        case 'NIVEL':

          $oAvaliacao->setAproveitamentoMinimo(true);
          $oAvaliacao->setEmRecuperacao(false);

          if (    $oAvaliacao->getValorAproveitamento()->hasOrdem()
               && $oAvaliacao->getValorAproveitamento()->getOrdem() < $oResultadoAvaliacao->getFormaDeAvaliacao()->getConceitoMinimo()->iOrdem
             ) {

            if (!empty($oRecuperacao) && $lTemDireitoRecuperacao && $lCaracterReprobatorio) {
              $oAvaliacao->setEmRecuperacao(true);
            }
            $oAvaliacao->setAproveitamentoMinimo(false);
          }

          break;

        /**
         * PARECER:> Sempre de acordo com informado
         */
        case 'PARECER':
          break;
      }
    } // fim if isEncerrado

    /**
     * Informa a avaliação calculada
     */
    switch ($sFormaAvaliacao) {

      case 'NOTA':

        $oDaoResultado->ed998_nota      = "{$nValorAproveitamento}";
        $nValorAproveitamento           = ArredondamentoNota::arredondar($nAproveitamentoReal, $iAno);
        $oDaoResultado->ed998_valorreal = "{$nValorAproveitamento}";
        break;

      case 'NIVEL':

        $oDaoResultado->ed998_conceito = $nValorAproveitamento;
        break;

     case 'PARECER':

        $oDaoResultado->ed998_parecer = $nValorAproveitamento;
        break;
    }

    $oDaoResultado->ed998_atingiominimo = $oAvaliacao->temAproveitamentoMinimo() ? "t" : "f";
    if ($oAvaliacao->getCodigo() == '') {

      $oDaoResultado->incluir(null);
      $oAvaliacao->setCodigo($oDaoResultado->ed998_sequencial);
    } else {

      $oDaoResultado->ed998_sequencial = $oAvaliacao->getCodigo();
      $oDaoResultado->alterar($oDaoResultado->ed998_sequencial);
    }

    $oMsgErro = new stdClass();
    $oMsgErro->sAluno = $this->oProgressao->getAluno()->getNome();
    if ($oDaoResultado->erro_status == 0) {
      throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_salvar_resultado", $oMsgErro) );
    }

    $this->atualizaDadosRecuperacao($oAvaliacao);

    if ($oAvaliacao->getElementoAvaliacao()->geraResultadoFinal()) {
      $this->salvarResultadoFinal($oAvaliacao);
    }
  }

  private function salvarResultadoFinal( AvaliacaoAproveitamentoProgressao $oAvaliacao ) {

    /*
     * Autor: Uemerson Santana
     * Data: 18/11/2025
     * Demanda: 17785
     * Razao: Logs temporários para análise detalhada do cálculo do resultado final
     */
    $iCodigoAluno = $this->oProgressao->getAluno()->getCodigoAluno();
    $sNomeAluno = $this->oProgressao->getAluno()->getNome();

    $oRegencia             = $this->getRegencia();
    $nValorAproveitamento  = $oAvaliacao->getValorAproveitamento()->getAproveitamento();
    $lCaracterReprobatorio = $oRegencia->possuiCaracterReprobatorio();

    $oAvaliacaoResultadoFinal = $this->getResultadoFinal();
    $nPercentualPresenca      = $this->calcularPercentualFrequencia();
    $sResultadoFrequencia     = 'A';

    $sFormaControleFrequenciaDisciplina = $oRegencia->getFrequenciaGlobal();

    if (   $oAvaliacao->getElementoAvaliacao()->reprovaPorFrequencia()
        && $sFormaControleFrequenciaDisciplina  <> 'A') {

      $nPercentualMinimoFrequencia = $oRegencia->getProcedimentoAvaliacao()->getPercentualFrequencia();

      if ( $nPercentualPresenca < $nPercentualMinimoFrequencia && !$this->reclassificadoPorBaixaFrequencia() ) {
        $sResultadoFrequencia = 'R';
      }
    }

    /**
     * Se o tipo da Avaliacao for PARECER não salvamos o aproveitamento e sim a palavra 'Parecer'
     */
    $sTipoAvaliacao      = $oAvaliacao->getElementoAvaliacao()->getFormaDeAvaliacao()->getTipo();
    $sResultadoFinal     = '';
    $sResultadoAprovacao = '';

    if ($sTipoAvaliacao == 'PARECER') {
      $nValorAproveitamento = 'Parecer';
      $sResultadoFinal      = 'A';
      $sResultadoAprovacao  = 'A';

      if ( !$oAvaliacao->temAproveitamentoMinimo() ) {
        $sResultadoFinal      = 'R';
        $sResultadoAprovacao  = 'R';
      }
    }

    if ( $nValorAproveitamento !== '' && $sTipoAvaliacao != 'PARECER' ) {
      $sResultadoAprovacao = "A";

      if( $lCaracterReprobatorio && !$oAvaliacao->temAproveitamentoMinimo() ) {
        $sResultadoAprovacao = "R";
      }

      if ($sFormaControleFrequenciaDisciplina == 'F') {
        $sResultadoAprovacao = '';
      }

      $sResultadoFinal = 'R';

      if ($sResultadoAprovacao <> 'R' && $sResultadoFrequencia <> 'R') {
        $sResultadoFinal = 'A';
      }
    }

    $iTotalFaltas = $this->getTotalFaltas();

    if ( $iTotalFaltas != 0  && $sResultadoFrequencia == 'R' && !$this->reclassificadoPorBaixaFrequencia() ) {
      $sResultadoFinal = 'R';
    }

    /**
     * Aluno não pode reprovar por frequencia, se disciplina não possue Caracter Reprobatorio
     */
    if ( !$lCaracterReprobatorio ) {
      $sResultadoFrequencia = "A";
      if ( !$this->temAproveitamentoLancado() ) {
        $sResultadoAprovacao  = "A";
      }
    }

    if ($sFormaControleFrequenciaDisciplina == 'F') {
      $sResultadoAprovacao = "A";
    }

    if ( $sResultadoAprovacao == 'A' && $sResultadoFrequencia == 'A') {
      $sResultadoFinal = 'A';
    }

    $oAmparo = $this->getAmparo();
    if( $oAmparo->getCodigo() != null && $oAmparo->isTotal()  ) {
      $sResultadoFinal = 'A';
    }

    $lAprovacaoAutomatica = false;
    foreach ( $oRegencia->getTurma()->getEtapas() as $oEtapaTurma ) {
      if ($oEtapaTurma->getEtapa()->getCodigo() == $oRegencia->getEtapa()->getCodigo()
           && $oEtapaTurma->temAprovacaoAutomatica() ) {
        $lAprovacaoAutomatica = true;
        $sResultadoAprovacao  = "A";
        $sResultadoFinal      = "A";
        $sResultadoFrequencia = "A";
        break;
      }
    }

    /**
     * Validado qual o último Resultado que gera Resultado Final e verifica se ele está sendo utilizado para gerar
     * o Resultado Final
     */
    $oUltimoResultado         = $this->getUltimoResultadoFinal();
    $sResultadoFinalJaLancado = '';
    $iOrdemUltimoResultado = $oUltimoResultado->getOrdemSequencia();
    $iOrdemAvaliacaoAtual = $oAvaliacao->getOrdemSequencia();
    $iElementosGeramResultadoFinal = count( $this->getElementosGeramResultadoFinal() );

    if ( $iOrdemUltimoResultado == $iOrdemAvaliacaoAtual ) {
      $sResultadoFinalJaLancado = $oAvaliacaoResultadoFinal->getResultadoFinal();

      if ( $iElementosGeramResultadoFinal > 1 && $sResultadoFinalJaLancado == "" ) {
        return true;
      }

      $lCondicaoReturn = ( ($sResultadoFinalJaLancado == 'A' || ($oAvaliacaoResultadoFinal->getResultadoFrequencia() == "R"
          && $oAvaliacaoResultadoFinal->getResultadoAprovacao() == "A") ) )
          && $oAvaliacaoResultadoFinal->getResultadoAvaliacao()->getCodigo() != $oAvaliacao->getElementoAvaliacao()->getCodigo();

      if ($lCondicaoReturn) {
        return true;
      }
    }

    /**
     * Caso haja 2 resultados que geram resultados finais, verifica se o Resultado Final já foi lançado e se o Valor
     * Aproveitamento do segundo resultado está em branco e retorna, fazendo com que não seja setado os valores vazios
     * para o Diario Final.
     */
    if ( $nValorAproveitamento === '' && !empty($sResultadoFinalJaLancado) && $iElementosGeramResultadoFinal > 1 ) {
      $this->ultimoResultaFinal = '';
      return true;
    }
    $this->ultimoResultaFinal = $sResultadoAprovacao;

    /*
     * Autor: Uemerson Santana
     * Data: 18/11/2025
     * Demanda: 17785
     * Razao: Corrigido para respeitar frequência mínima de 75%. Alunos com frequência abaixo de 75% devem reprovar,
     *        independente da nota. A aprovação só ocorre se nota >= 5.0 E frequência >= 75%.
     */
    if( $nValorAproveitamento >= '5.0' && $sResultadoFrequencia == 'A' ) {
      $sResultadoFinal = 'A';
    }

    $oAvaliacaoResultadoFinal->setResultadoAvaliacao($oAvaliacao->getElementoAvaliacao());
    $oAvaliacaoResultadoFinal->setValorAprovacao($nValorAproveitamento);
    $oAvaliacaoResultadoFinal->setResultadoAprovacao($sResultadoAprovacao);
    $oAvaliacaoResultadoFinal->setResultadoFinal($sResultadoFinal);
    $oAvaliacaoResultadoFinal->setPercentualFrequencia($nPercentualPresenca);
    $oAvaliacaoResultadoFinal->setResultadoFrequencia($sResultadoFrequencia);
    $oAvaliacaoResultadoFinal->salvar();


    $this->atualizarDiarioFinal();
  }

  /**
   * Inclui recuperação para o resultado do diario
   *
   *
   * @param  AvaliacaoAproveitamentoProgressao $oAvaliacao
   * @throws DBException
   */
  private function atualizaDadosRecuperacao( AvaliacaoAproveitamentoProgressao $oAvaliacao) {

    $oDaoDiarioRecuperacao = new cl_diarioprogressaoresultadorecuperacao();
    $oDaoDiarioRecuperacao->excluir(null, "ed994_diarioprogressaoresultado = {$oAvaliacao->getCodigo()}");

    if ($oDaoDiarioRecuperacao->erro_status == 0) {
      throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_salvar_resultado") );
    }

    if ($oAvaliacao->emRecuperacao()) {

      $oDaoDiarioRecuperacao                                  = new cl_diarioprogressaoresultadorecuperacao();
      $oDaoDiarioRecuperacao->ed994_diarioprogressaoresultado = $oAvaliacao->getCodigo();
      $oDaoDiarioRecuperacao->incluir(null);
      $this->lEmRecuperacao = true;

      if ($oDaoDiarioRecuperacao->erro_status == 0) {
        throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_salvar_resultado") );
      }
    }
  }



  /**
   * Retornamos os dados do resultado final
   *
   * @todo IMPLEMENTAR AvaliacaoResultadoFinalProgressao
   *
   * @return AvaliacaoResultadoFinalProgressao
   */
  public function getResultadoFinal() {

    if (empty($this->oResultadoFinal)) {
      $this->oResultadoFinal = new AvaliacaoResultadoFinalProgressao($this);
    }
    return $this->oResultadoFinal;
  }

  /**
   * Retorna a regencia
   * @return Regencia
   */
  public function getRegencia() {
    return $this->buscaVinculoRegencia()->getRegencia();
  }

  /**
   * Realiza o calculo da frequencia conforme o procedimento de avaliacao
   *
   * @throws BusinessException Regencia sem procedimento de Avaliacao
   * @return float
   */
  public function calcularPercentualFrequencia() {

    $oRegencia = $this->getRegencia();
    $iAno      = $oRegencia->getTurma()->getCalendario()->getAnoExecucao();

    $nPercentualFrequencia  = 100;
    $oProcedimentoAvaliacao = $oRegencia->getProcedimentoAvaliacao();

    if ( !$oProcedimentoAvaliacao ) {
      throw new BusinessException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "regencia_sem_procedimento") );
    }

    switch ($oProcedimentoAvaliacao->getFormaCalculoFrequencia()) {

      case 1:

        $nPercentualFrequencia = $this->calculoDeFrequenciaIndividual();
        break;

      case 2:

        $nPercentualFrequencia = $this->calculoDeFrequenciaGlobal();
        break;
    }

    return ArredondamentoFrequencia::arredondar($nPercentualFrequencia, $iAno);
  }



  /**
   * Calcula o aproveitamento Real de um aluno somente quando o sistema foi configurado para apresentar o resultado Real
   * --> Escola > Procedimentos > Parâmetros Globais > Apresentar nota proporcional: NÃO
   *
   * Neste caso sistema recalcula a avaliação do aluno aplicando SOMENTE a soma dos períodos com avaliação
   *
   * @param  ResultadoAvaliacao        $oResultadoAvaliacao
   * @param  integer                   $iEscola             código da escola
   * @param  AvaliacaoAproveitamento[] $aElementosCalcular
   * @param  integer                   $iAno
   * @return ValorAproveitamentoNota|null
   */
  static function calcularResultadoReal( $oResultadoAvaliacao, $iEscola, $aElementosCalcular, $iAno) {

    $mNotaReal = null;
    if ( $oResultadoAvaliacao->getFormaDeObtencao() == 'SO' && !Escola::apresentarNotaProporcional( $iEscola ) ) {

      $oFormaObtencaoSoma = new FormaObtencaoSoma();
      $oFormaObtencaoSoma->setResultadoAvaliacao($oResultadoAvaliacao);
      $mNotaReal = $oFormaObtencaoSoma->calcularResultado( $aElementosCalcular, $iAno );
    }
    return $mNotaReal;
  }

  public function getPeriodosAvaliacaoProporcionalidade() {
    return array();
  }

  /**
   * Define se há algum amparo lançado para o diário.
   * @param boolean $lAmparado
   */
  public function setAmparado( $lAmparado ) {
    $this->lAmparado = $lAmparado;
  }

  /**
   * Retorna o amparo da Disciplina
   * @return AmparoProgressao Amparo da Disciplina
   */
  public function getAmparo() {

    if( $this->getCodigoDiario() != "" && $this->oAmparo == null ) {
      $this->oAmparo = new AmparoProgressao( $this );
    }

    return $this->oAmparo;
  }

  /**
   * Retorna se o aluno esta evadido
   * @return boolean
   */
  public function isEvadido() {

    return $this->lEvadido;
  }

  /**
   * Informa se o aluno foi evadido
   * @param boolean $lEvadido
   */
  public function setEvadido($lEvadido = false) {

    $this->lEvadido = $lEvadido;
  }


  /**
   * Retorna o percentual de presenca do aluno quando o calculo for Global
   * @return float
   */
  protected function calculoDeFrequenciaGlobal() {

    if ($this->nPercentualGlobal == null) {

      $iTotalAulas     = $this->getTotalDeAulasParaCalculo();
      $iTotalFaltas    = $this->getTotalFaltas();
      $iFaltasAbonadas = $this->getTotalFaltasAbonadas();

      $nTotalFaltasSemAbono    = $iTotalFaltas - $iFaltasAbonadas;
      $iTotalAulasPresentes    = $iTotalAulas  - $nTotalFaltasSemAbono;
      $this->nPercentualGlobal = 0;
      if ($iTotalAulas > 0) {
        $this->nPercentualGlobal = ($iTotalAulasPresentes * 100) / $iTotalAulas;
      }
    }

    return $this->nPercentualGlobal;
  }

  public function hasAvaliacaoAlternativa() {
    return false;
  }

 /**
   * Exclui o diário do da progressão do aluno
   * @param  ProgressaoParcialAluno $oProgressao
   */
  static function excluirDiario( ProgressaoParcialAluno $oProgressao ) {

    $oVinculo   = $oProgressao->getVinculoRegencia();

    $sWhere     = "ed993_progressaoparcialalunoturmaregencia = {$oVinculo->getCodigoVinculo()}";
    $oDaoDiario = new cl_diarioprogressao();
    $sSqlDiario = $oDaoDiario->sql_query_file(null, "ed993_sequencial", null, $sWhere);
    $rsDiario   = db_query($sSqlDiario);

    if ( !$rsDiario ) {
      throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_buscar_diario") );
    }

    if ( pg_num_rows($rsDiario) == 0 ) {
      return true;
    }

    $iCodigoDiario = db_utils::fieldsMemory($rsDiario, 0)->ed993_sequencial;

    $oDaoResultado        = new cl_diarioprogressaoresultado();
    $oDaoAmparo           = new cl_amparoprogressao();
    $oDaoRecuperacao      = new cl_diarioprogressaoresultadorecuperacao();
    $oDaoAprovadoConselho = new cl_aprovadoconselhoprogressao();
    $oDaoDiarioFinal      = new cl_diariofinalprogressao();
    $oDaoAvaliacao        = new cl_diarioprogressaoavaliacao();

    /**
     * Busca os resultados vínculados ao diário para poder excluir as recuperações
     */
    $sSqlResultados = $oDaoResultado->sql_query(null, "ed998_sequencial " , null, "ed998_diarioprogressao = {$iCodigoDiario}");
    $rsResultados   = db_query($sSqlResultados);

    if ( !$rsResultados ) {
      throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_buscar_resultados") );
    }

    $aResultados = db_utils::getCollectionByRecord($rsResultados);
    foreach ($aResultados as $oDadoResultado) {

      $oDaoRecuperacao->excluir(null, " ed994_diarioprogressaoresultado = {$oDadoResultado->ed998_sequencial}");
      if ($oDaoRecuperacao->erro_status == 0) {
        throw new DBException( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_remover_recuperacao") );
      }
    }

    // exclui o amparo
    $oDaoAmparo->excluir(null, " ed997_diarioprogressao = {$iCodigoDiario}" );
    if ( $oDaoAmparo->erro_status == 0 ) {
      throw new DBException ( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_excluir_amparo" ) );
    }

    // exclui as alterações de resultado final
    $oDaoAprovadoConselho->excluir( null, " ed996_diarioprogressao = {$iCodigoDiario}");
    if ( $oDaoAprovadoConselho->erro_status == 0 ) {
      throw new DBException ( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_excluir_aprovacao_conselho" ) );
    }

    // exclui as avaliações
    $oDaoAvaliacao->excluir( null, " ed999_diarioprogressao = {$iCodigoDiario}");
    if ( $oDaoAvaliacao->erro_status == 0 ) {
      throw new DBException ( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_excluir_avaliacao" ) );
    }

    // exclui as avaliações
    $oDaoDiarioFinal->excluir( null, " ed995_diarioprogressao = {$iCodigoDiario}");
    if ( $oDaoDiarioFinal->erro_status == 0 ) {
      throw new DBException ( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_excluir_diario_final" ) );
    }

    // exclui os resultados
    $oDaoResultado->excluir( null, " ed998_diarioprogressao = {$iCodigoDiario}");
    if ( $oDaoResultado->erro_status == 0 ) {
      throw new DBException ( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_excluir_resultado" ) );
    }

    //exclui o diário
    $oDaoDiario->excluir($iCodigoDiario);
    if ( $oDaoDiario->erro_status == 0 ) {
      throw new DBException ( _M(self::MSG_DIARIOPROGRESSAOPARCIAL . "erro_excluir_diario" ) );
    }

    return true;
  }

  /**
   * Atualiza o resultado da progressão do aluno no e-cidade (progressaoparcialalunoresultadofinal) para que o
   * encerramento continue funcionando da mesma forma.
   */
  private function atualizarDiarioFinal() {
    $sResultadoFinal = $this->getResultadoFinal()->getResultadoFinal();
/*
    Divaldo 24/12/2024 demanda 16740
	Escola: E M DR JOAO PAULO PIO DE ABREU
	nas opções de menu: Procedimento->Diario de classe->Encerramento de Avaliaçôes->Progressao parcial->Encerrar
	                    Procedimento->Diario de classe->Progressão parcial/Dependencia->Por Aluno
	O sistema não permitia o encerramento dos alunos, citarei o caso da aluna "Ana Beatriz Martins Mendonça" Turma - DP- PLANOS DE ESTUDOS, dependencia em HISTORIA E LINGA INGLESA
    A aluna teve as notas: HISTORIA        1º bimestre = 3,5 e 2º bimestre = 1,2  total= 4,7
	                       LINGUA INGLESA  1º bimestre = 5,0 e 2º bimestre = 3,2  total= 8,2
	O sistema considerou que a aluna ficou em recuperação nas 2 matérias e quando chegava na condição abaixo(linha 1179) colocava a variavel $sResultadoFinal = ''
	e o campo ed121_resultadofinal da tabela escola.progressaoparcialalunoresultadofinal, ficava vazio e portanto o resultado final na tela de encerramento ficava: 'EM ANDAMENTO'
	e não encerrava
	Fazendo testes vi que o resultado só aparecia APROVADO ou REPROVADO e permitia o encerramento, se alterasse a nota para não ficar em recuperação ou se lançasse uma nota
	qualquer na recuperação.
	Mas na linha 875  a variavel $sResultadoFinal estava APROVADO, então na linha 870 coloquei uma variavel para buscar este resultado e trazer para condição
	onde o aluno não obteve media nos dois primeiros bimestres, não fez recuperação, mas conseguiu se recuperar no ultimos bimestres
*/

//    if ( $this->lEmRecuperacao ) {
//      $sResultadoFinal = '';
//    }

    if ( $this->lEmRecuperacao )  // se o aluno não obteve média nos 2 primeiros bimestres e deveria ter feito a recuperação
	{
		$sResultadoFinal = $this->ultimoResultaFinal; //  tem resultado final ou não
	}

    if ( $this->isEvadido() ) {
      $sResultadoFinal = 'R';
    }

    $iTotalFaltas    = $this->getTotalFaltas() - $this->getTotalFaltasAbonadas();
    $oResultadoFinal = new ProgressaoParcialAlunoResultadoFinal( $this->oVinculoDisciplina );
    $oResultadoFinal->setTotalFalta( $iTotalFaltas );
    $oResultadoFinal->setNota( $this->getResultadoFinal()->getValorAprovacao() );
    $oResultadoFinal->setResultado( $sResultadoFinal );
    $oResultadoFinal->salvar();
  }


  /**
   * Valida se esta disciplina esta em recuperação
   * @return boolean
   */
  public function emRecuperacao() {

    $oDaoRecuperacao = new cl_diarioprogressaoresultadorecuperacao();
    $sWhere          = "ed998_diarioprogressao = {$this->iCodigoDiario} ";
    $sSqlRecuperacao = $oDaoRecuperacao->sql_query(null, "1", null, $sWhere);
    $rsRecuperacao   = db_query( $sSqlRecuperacao );

    if ( !$rsRecuperacao ) {
      throw new DBException('Falha ao verificar se o aluno está em recuperação na disciplina.');
    }

    if (pg_num_rows( $rsRecuperacao ) > 0) {
      return true;
    }

    return false;
  }
}

