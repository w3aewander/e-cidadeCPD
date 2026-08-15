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

define( 'MENSAGENS_AVALIACAO_RESULTADO_FINAL', 'educacao.avaliacao.AvaliacaoResultadoFinal.' );

/**
 * Resultado final da avaliacao - diariofinal
 * @author  Fabio Esteves - fabio.esteves@dbseller.com.br
 * @package educacao
 * @subpackage avaliacao
 * @version $Revision: 1.28 $
 */
class AvaliacaoResultadoFinal {

  /**
   * Codigo do resultado final
   * @var integer
   */
  protected $iCodigoResultadoFinal;

  /**
   * Codigo do diario
   * @var integer
   */
  protected $iCodigoDiario;

  /**
   * Instancia de ResultadoAvaliacao
   * @var ResultadoAvaliacao
   */
  protected $oResultadoAvaliacao;

  /**
   * Nota da avaliacao
   * @var mixed
   */
  protected $mValorAprovacao = '';

  /**
   * Observacao para o resultado final
   * @var string
   */
  protected $sObservacao;

  /**
   * Período de avaliação para alteração (parametrizável)
   * @var integer
   */
  protected $iPeriodoAlteracao;

  /**
   * Resultado da aprovacao
   * @var string
   */
  protected $sResultadoAprovacao = '';

  /**
   * Resultado da frequencia;
   * @var string
   */
  protected $sResultadoFrequencia;
  /**
   * Resultado final
   * @var string
   */
  protected $sResultadoFinal = '';

  protected $nPercentualFrequencia = 0;

  /**
   * Procresultado referente ao resultado final
   * @var integer
   */
  protected $iProcResultado = null;

  /**
   * Flag para evitar loop infinito no recálculo da média
   * @var boolean
   */
  protected static $lEmRecalculo = false;

  /**
   * Identifica se resultado final foi alterado
   * @var AprovadoConcelho
   */
  protected $oAprovadoConcelho = null;

  /**
   * Controle para saber se já validou busca por alteração do resultado final
   * @var boolean
   */
  protected $lValidouAlteracaoResultadoFinal = false;

  public function __construct(DiarioAvaliacaoDisciplina $oDiarioAvaliacaoDisciplina) {

    $this->iCodigoDiario = $oDiarioAvaliacaoDisciplina->getCodigoDiario();

    $oDaoDiarioFinal    = db_utils::getDao('diariofinal');
    $sSqlAvaliacaoFinal = $oDaoDiarioFinal->sql_query_file(null, "*", null, "ed74_i_diario={$this->iCodigoDiario}");
    $rsAvaliacaoFinal   = db_query($sSqlAvaliacaoFinal);

    if( !is_resource( $rsAvaliacaoFinal ) ) {

      $oErro        = new stdClass();
      $oErro->sErro = pg_last_error();

      throw new DBException( _M( MENSAGENS_AVALIACAO_RESULTADO_FINAL . 'erro_buscar_diario_final', $oErro ) );
    }

    if( pg_num_rows( $rsAvaliacaoFinal ) > 0 ) {

      $oDados                      = db_utils::fieldsMemory($rsAvaliacaoFinal, 0);
      $this->iCodigoDiario         = $oDados->ed74_i_diario;
      $this->iProcResultado        = $oDados->ed74_i_procresultadoaprov;
      $this->iCodigoResultadoFinal = $oDados->ed74_i_codigo;
      $this->mValorAprovacao       = $oDados->ed74_c_valoraprov;

      /**
       * Autor: Uemerson Santana
       * Data: 02/03/2026
       * Demanda: 18250
       * Razao: Quando ed74_c_valoraprov esta vazio (ex: aluno sem nota no 4o bimestre),
       *        busca a Nota Final (NF) calculada em diarioresultado. Sem isso, disciplinas
       *        ficam sem media na ATA, Ficha Individual e tela de encerramento, causando
       *        resultado final incorreto.
       */
      if (empty(trim($this->mValorAprovacao))) {
        $sSqlFallbackNF = "SELECT dr.ed73_i_valornota as valor_nf
                           FROM diarioresultado dr
                           INNER JOIN procresultado pr ON pr.ed43_i_codigo = dr.ed73_i_procresultado
                           INNER JOIN resultado r ON r.ed42_i_codigo = pr.ed43_i_resultado
                           WHERE dr.ed73_i_diario = {$this->iCodigoDiario}
                             AND trim(r.ed42_c_abrev) = 'NF'
                           LIMIT 1";
        $rsFallbackNF = db_query($sSqlFallbackNF);
        if (is_resource($rsFallbackNF) && pg_num_rows($rsFallbackNF) > 0) {
          $oFallbackNF = db_utils::fieldsMemory($rsFallbackNF, 0);
          $this->mValorAprovacao = $oFallbackNF->valor_nf;
        }
      }
      $this->sResultadoAprovacao   = $oDados->ed74_c_resultadoaprov;
      $this->sResultadoFrequencia  = $oDados->ed74_c_resultadofreq;
      $this->nPercentualFrequencia = $oDados->ed74_i_percfreq;
      $this->sResultadoFinal       = $oDados->ed74_c_resultadofinal;
      $this->sObservacao           = $oDados->ed74_t_obs;
      $this->oResultadoAvaliacao   = ResultadoAvaliacaoRepository::getResultadoAvaliacaoByCodigo($oDados->ed74_i_procresultadoaprov);
    }
  }

  /**
   * Retorna o codigo do resultado final
   * @return integer
   */
  public function getCodigoResultadoFinal() {
    return $this->iCodigoResultadoFinal;
  }

  /**
   * Retorna o codigo do diario
   * @return integer
   */
  public function getCodigoDiario() {
    return $this->iCodigoDiario;
  }

  /**
   * Retorna a instancia de ResultadoAvaliacao
   * @return ResultadoAvaliacao
   */
  public function getResultadoAvaliacao() {
    return $this->oResultadoAvaliacao;
  }

  /**
   * Atribui uma instancia de ResultadoAvaliacao
   * @param ResultadoAvaliacao $oResultadoAvaliacao
   */
  public function setResultadoAvaliacao(ResultadoAvaliacao $oResultadoAvaliacao) {
    $this->oResultadoAvaliacao = $oResultadoAvaliacao;
  }

  /**
   * Retorna o valor da aprovacao
   * @return mixed
   */
  public function getValorAprovacao() {
    return $this->mValorAprovacao;
  }

  /**
   * Atribui o valor da nota de aprovacao
   * @param mixed $mValorAprovacao
   */
  public function setValorAprovacao($mValorAprovacao) {
    $this->mValorAprovacao = $mValorAprovacao;
  }

  /**
   * Retorna o resultado da aprovacao
   * @return string
   */
  public function getResultadoAprovacao() {
    return $this->sResultadoAprovacao;
  }

  /**
   * Atribui um resultado de aprovacao
   * @param string $sResultadoAprovacao
   */
  public function setResultadoAprovacao($sResultadoAprovacao) {
    $this->sResultadoAprovacao = $sResultadoAprovacao;
  }

  /**
   * Define o resultado final da disciplina
   * OS valores validos para aprovacao : 'A'  Aprovado 'R' Reprovado'
   * @param string $sResultadoFinal resultado final da Disciplina
   */
  public function setResultadoFinal($sResultadoFinal) {
    $this->sResultadoFinal = $sResultadoFinal;
  }

  /**
   * Retorna o resultado final da disciplina
   * Os valores validos para aprovacao : 'A'  Aprovado 'R' Reprovado'
   * @return string Resultado final da disciplina
   */
  public function getResultadoFinal() {
    return $this->sResultadoFinal;
  }

  /**
   * Retorna o resultado da frequencia
   * Os valores validos para aprovacao : 'A'  Aprovado 'R' Reprovado'
   * @return string Resultado frequencia da disciplina
   */
  public function getResultadoFrequencia() {
    return $this->sResultadoFrequencia;
  }

  /**
   * Retorna a observacao referente ao resultado final
   * @return string
   */
  public function getObservacao() {
    return $this->sObservacao;
  }

  /**
   * Atribui uma observacao ao resultado final
   * @param string $sObservacao
   */
  public function setObservacao($sObservacao = '') {
    $this->sObservacao = $sObservacao;
  }

  /**
   * Retorna o período de alteração
   * @return integer
   */
  public function getPeriodoAlteracao() {
    return $this->iPeriodoAlteracao;
  }

  /**
   * Define o período de alteração
   * @param integer $iPeriodoAlteracao
   */
  public function setPeriodoAlteracao($iPeriodoAlteracao) {
    $this->iPeriodoAlteracao = $iPeriodoAlteracao;
  }

  /**
   * Retorna o percentual de frequencia
   * @return number
   */
  public function getPercentualFrequencia() {
    return $this->nPercentualFrequencia;
  }

  public function setPercentualFrequencia($nPercentual) {
    $this->nPercentualFrequencia = $nPercentual;
  }

  /**
   * Retorna o procresultado do Resultado Final
   * @return int
   */
  public function getProcResultado() {
    return $this->iProcResultado;
  }

  /**
   * Persiste as informacoes do diario final
   */
  public function salvar() {

  	if (!db_utils::inTransaction()) {
  		throw new DBException("Não existe transação com o banco de dados ativa");
  	}

    $oDaoDiarioFinal = new cl_diariofinal();

    if (empty($this->sResultadoFinal)) {
      $GLOBALS["HTTP_POST_VARS"]["ed74_c_resultadofinal"] = '';
    }

    if (empty($this->sResultadoFrequencia)) {
      $GLOBALS["HTTP_POST_VARS"]["ed74_c_resultadofreq"] = '';
    }

    if (empty($this->sResultadoAprovacao)) {
      $GLOBALS["HTTP_POST_VARS"]["ed74_c_resultadoaprov"] = '';
    }

    if (empty($this->ed74_c_valoraprov)) {
      $GLOBALS["HTTP_POST_VARS"]["ed74_c_valoraprov"] = '';
    }
    $GLOBALS["HTTP_POST_VARS"]["diarioAluno"] = $this->iCodigoDiario;
    /**
     * Caso o aluno está aprovado pelo conselho, o resultado final do mesmo deverá
     * ser sempre aprovado.
     */
    if( $this->getFormaAprovacaoConselho() != "" ) {

      if( $this->getFormaAprovacaoConselho()->getFormaAprovacao() == AprovacaoConselho::RECLASSIFICACAO_BAIXA_FREQUENCIA ) {

        $this->setResultadoFrequencia('A');

        if( $this->getResultadoAprovacao() == 'A' ) {
          $this->setResultadoFinal('A');
        }
      } else {
        $this->setResultadoFinal('A');
      }
    }

    $GLOBALS["HTTP_POST_VARS"]["diarioAluno"] = $this->iCodigoDiario;

	// Usar período parametrizado se definido, senão usar padrão (8 ou 4)
	$sPeriodoFiltro = "(ed09_i_codigo = 8 or ed09_i_codigo = 4)";
	if (!empty($this->iPeriodoAlteracao)) {
		$sPeriodoFiltro = "ed09_i_codigo = " . intval($this->iPeriodoAlteracao);
	}

	$sql = "
			select
			ed09_i_codigo,
			ed09_c_descr,
			ed72_i_valornota,
            ed72_c_valorconceito,
			ed47_i_codigo
			from
			diarioavaliacao
			inner join procavaliacao    on ed41_i_codigo = ed72_i_procavaliacao
			inner join periodoavaliacao on ed09_i_codigo = ed41_i_periodoavaliacao
			inner join diario           on ed95_i_codigo = ed72_i_diario
			inner join aluno            on ed47_i_codigo = ed95_i_aluno
			where
			{$sPeriodoFiltro}
			and
			ed95_i_codigo = ".$this->iCodigoDiario;

	$result  = pg_query($sql);
	$periodo = db_utils::fieldsMemory($result,0);
    $oDaoDiarioFinal->ed74_i_procresultadoaprov = $this->getResultadoAvaliacao()->getCodigo();
	if($periodo->ed72_i_valornota == null and $periodo->ed72_c_valorconceito == '' )
	{
		$oDaoDiarioFinal->ed74_c_valoraprov         = '';
	}else{
        $oDaoDiarioFinal->ed74_c_valoraprov         = "{$this->getValorAprovacao()}";
	}

    $oDaoDiarioFinal->ed74_c_resultadoaprov     = trim($this->getResultadoAprovacao());
    $oDaoDiarioFinal->ed74_i_procresultadofreq  = $this->getResultadoAvaliacao()->getCodigo();
    $oDaoDiarioFinal->ed74_i_percfreq           = "{$this->nPercentualFrequencia}";
    $oDaoDiarioFinal->ed74_c_resultadofreq      = $this->getResultadoFrequencia();
	// Aceitar resultado 'N' (NÃO AVALIADO) para alunos com necessidades especiais
	if ($this->getResultadoFinal() == 'N') {
		$oDaoDiarioFinal->ed74_c_resultadofinal = 'N';
	} elseif($periodo->ed72_i_valornota == null and $periodo->ed72_c_valorconceito == '' ) {
		$oDaoDiarioFinal->ed74_c_resultadofinal = '';
	} else {
        $oDaoDiarioFinal->ed74_c_resultadofinal = $this->getResultadoFinal();
	}
    $oDaoDiarioFinal->ed74_i_calcfreq           = '1';

    if (empty($this->sObservacao)) {
      $GLOBALS["HTTP_POST_VARS"]["ed74_t_obs"] = '';
    }
    $oDaoDiarioFinal->ed74_t_obs = "{$this->getObservacao()}";

    $oDaoDiarioFinal->ed74_i_diario = $this->getCodigoDiario();
    if (!empty($this->iCodigoResultadoFinal)) {

      $oDaoDiarioFinal->ed74_i_codigo = $this->getCodigoResultadoFinal();
      $oDaoDiarioFinal->alterar($oDaoDiarioFinal->ed74_i_codigo);
    } else {

      $oDaoDiarioFinal->incluir(null);
      $this->iCodigoResultadoFinal = $oDaoDiarioFinal->ed74_i_codigo;
    }
    if ($oDaoDiarioFinal->erro_status == 0) {
      throw new BusinessException("Erro ao salvar o resultado final.\n{$oDaoDiarioFinal->erro_msg}");
    }

    // Se período específico foi definido E existe aprovação pelo conselho ativa, alterar a nota desse período e recalcular
    // IMPORTANTE: Verificar se existe aprovconselho no banco para evitar re-alterar após exclusão
    /**
     * Autor: Uemerson Santana
     * Data: 29/09/2025
     * Demanda: 17872
     */

    // IMPORTANTE: Evitar loop infinito - não recalcular se já estamos em recálculo
    /**
     * Autor: Uemerson Santana
     * Data: 06/10/2025
     * Demanda: 17872 - Evitar loop infinito no recálculo da média
     */
    if (!empty($this->iPeriodoAlteracao) && $this->getFormaAprovacaoConselho() != "" && !self::$lEmRecalculo) {

        // Verificar se ainda existe registro de aprovconselho no banco
        $oDaoAprovConselho = db_utils::getDao('aprovconselho');
        $sSqlVerifica = $oDaoAprovConselho->sql_query_file(null, "ed253_i_codigo", null, "ed253_i_diario = {$this->iCodigoDiario}");
        $rsVerifica = db_query($sSqlVerifica);


        // Só alterar se existe aprovconselho ativo
        if (is_resource($rsVerifica) && pg_num_rows($rsVerifica) > 0) {
            $this->alterarNotaPeriodoEspecificoERecalcular();
        }
    }
  }

  /**
   * Verifica se o aluno foi aprovado na disciplina atraves de progressao parcial
   * @return bool
   * @throws DBException
   */
  public function aprovadoPorProgressaoParcial() {

    if ( $this->getCodigoResultadoFinal() == null ) {
      return false;
    }

    $oDaoProgressaoParcialAluno = new cl_progressaoparcialalunodiariofinalorigem();
    $sWhere                     = "     ed107_diariofinal = {$this->getCodigoResultadoFinal()}";
    $sWhere                    .= " and ed114_situacaoeducacao <> ".ProgressaoParcialAluno::INATIVA;
    $sSqlProgressaoParcial      = $oDaoProgressaoParcialAluno->sql_query( null, '1', null, $sWhere );
    $rsProgressaoParcial        = db_query( $sSqlProgressaoParcial );

    if( !is_resource( $rsProgressaoParcial ) ) {

      $oErro        = new stdClass();
      $oErro->sErro = pg_last_error();

      throw new DBException( _M( MENSAGENS_AVALIACAO_RESULTADO_FINAL . 'erro_buscar_progressao_parcial', $oErro ) );
    }

    if( pg_num_rows( $rsProgressaoParcial ) > 0 ) {
      return true;
    }

    return false;
  }

  /**
   * Retorna se o aluno foi aprovado
   * @return boolean|string
   */
  public function isAprovado() {

    switch (trim($this->sResultadoFinal)) {

      case 'R':

        return false;
        break;
      case 'A':

        return true;
        break;
      default:

        return '';
        break;
    }
  }

  /**
   * Define o resultado final da frequencia
   * @param string $sResultadoFrequencia resultado da frequencia = A para aprovado R para reprovado
   */
  public function setResultadoFrequencia ($sResultadoFrequencia) {
     $this->sResultadoFrequencia = $sResultadoFrequencia;
  }

  /**
   * Retorna uma instancia de AprovacaoConselho, com os dados a aprovacao
   * @return AprovacaoConselho|null
   * @throws DBException
   */
  public function getFormaAprovacaoConselho() {

    if ( is_null($this->oAprovadoConcelho) && !$this->lValidouAlteracaoResultadoFinal ) {

      $this->lValidouAlteracaoResultadoFinal = true;
    	$oDaoAprovConselho   = new cl_aprovconselho();
    	$sWhereAprovConselho = "ed253_i_diario = {$this->iCodigoDiario}";
    	$sSqlAprovConselho   = $oDaoAprovConselho->sql_query_file(null, "*", null, $sWhereAprovConselho);
    	$rsAprovConselho     = db_query($sSqlAprovConselho);

      if( !is_resource( $rsAprovConselho ) ) {

        $oErro        = new stdClass();
        $oErro->sErro = pg_last_error();

        throw new DBException( _M( MENSAGENS_AVALIACAO_RESULTADO_FINAL . 'erro_buscar_aprovacao_conselho', $oErro ) );
      }

    	$iTotalAprovConselho = pg_num_rows( $rsAprovConselho );

    	if ($iTotalAprovConselho > 0) {

    		$oAprovacaoConselho  = AprovacaoConselhoRepository::getByAvaliacaoResultadoFinal( $this );
    		$oDadosAprovConselho = db_utils::fieldsMemory($rsAprovConselho, 0);

    		$oAprovacaoConselho->setCodigo($oDadosAprovConselho->ed253_i_codigo);
    		$oAprovacaoConselho->setFormaAprovacao($oDadosAprovConselho->ed253_aprovconselhotipo);

    		if (!empty($oDadosAprovConselho->ed253_i_rechumano)) {
    		  $oAprovacaoConselho->setRecursoHumano($oDadosAprovConselho->ed253_i_rechumano);
    		}

    		if (!empty($oDadosAprovConselho->ed253_t_obs)) {
    		  $oAprovacaoConselho->setJustificativa($oDadosAprovConselho->ed253_t_obs);
    		}

    		$oAprovacaoConselho->setData(new DBDate(date("Y-m-d", $oDadosAprovConselho->ed253_i_data)));
    		$oAprovacaoConselho->setHora(date("H:i", $oDadosAprovConselho->ed253_i_data));
    		$oAprovacaoConselho->setUsuario(new UsuarioSistema($oDadosAprovConselho->ed253_i_usuario));
        $oAprovacaoConselho->setAlterarNotaFinal( $oDadosAprovConselho->ed253_alterarnotafinal );

        if ( $oDadosAprovConselho->ed253_avaliacaoconselho != '' ) {
          $oAprovacaoConselho->setAvaliacaoConselho( $oDadosAprovConselho->ed253_avaliacaoconselho );
        }
        $this->oAprovadoConcelho = $oAprovacaoConselho;
    	}
    }
  	return $this->oAprovadoConcelho;
  }

  /**
   * Remove a aprovação do conselho
   */
  public function removerAprovacaoConselho() {

    /**
     * Autor: Uemerson Santana
     * Data: 29/09/2025
     * Demanda: 17872
     */

    $oAprovacaoConselho = $this->getFormaAprovacaoConselho();

    if ($oAprovacaoConselho instanceof AprovacaoConselho) {
      $oAprovacaoConselho->remover();
    } else {
    }

    $this->oAprovadoConcelho               = null;
    $this->lValidouAlteracaoResultadoFinal = false;
  }

  /**
   * Limpa a aprovação do conselho da propriedade (sem chamar remover)
   * Usado quando o AprovacaoConselho->remover() já foi chamado
   */
  public function limparAprovacaoConselho() {
    $this->oAprovadoConcelho = null;
    $this->lValidouAlteracaoResultadoFinal = false;
  }


  /**
   * Emcapsulado alteracao do resultado final por aprovação do conselho
   * @param  AprovacaoConselho $oAprovacaoConselho
   */
  public function adicionarAprovacaoConselho(AprovacaoConselho $oAprovacaoConselho) {

    $this->oAprovadoConcelho = $oAprovacaoConselho;

    // IMPORTANTE: Capturar valor anterior ANTES de salvar (se for alteração específica)
    if (!empty($this->iPeriodoAlteracao) &&
        $oAprovacaoConselho->getAlterarNotaFinal() == 2 &&
        !empty($oAprovacaoConselho->getAvaliacaoConselho())) {

      /**
       * Autor: Uemerson Santana
       * Data: 14/10/2025
       * Demanda: 17872
       *
       * IMPORTANTE: Verificar se já existe aprovação pelo conselho para este diário
       * Isso evita que a nota anterior seja sobrescrita em caso de dupla requisição
       * (workaround para problema de cache que requer 2 requisições para Prova Final)
       */
      $sqlVerifica = "
        SELECT COUNT(*) as total
        FROM aprovconselho
        WHERE ed253_i_diario = {$this->iCodigoDiario}";

      $rsVerifica = pg_query($sqlVerifica);
      $oVerifica = db_utils::fieldsMemory($rsVerifica, 0);

      // Só capturar nota anterior se NÃO existir aprovação (primeira requisição)
      if ($oVerifica->total == 0) {

        // Buscar dados atuais do período ANTES de qualquer alteração
        $sql = "
          SELECT da.ed72_i_valornota, da.ed72_c_valorconceito, da.ed72_t_parecer, da.ed72_i_procavaliacao
          FROM diarioavaliacao da
          INNER JOIN procavaliacao pa ON pa.ed41_i_codigo = da.ed72_i_procavaliacao
          INNER JOIN periodoavaliacao pav ON pav.ed09_i_codigo = pa.ed41_i_periodoavaliacao
          WHERE da.ed72_i_diario = {$this->iCodigoDiario}
            AND pav.ed09_i_codigo = " . intval($this->iPeriodoAlteracao);

        $result = pg_query($sql);

      if (is_resource($result) && pg_num_rows($result) > 0) {
        $oDados = db_utils::fieldsMemory($result, 0);

        // Detectar tipo de avaliação baseado na forma de avaliação
        $aTipoAvaliacao = $this->detectarTipoAvaliacao($oDados->ed72_i_procavaliacao);

        if ($aTipoAvaliacao !== null) {
          $sTipo = $aTipoAvaliacao['tipo'];

          // Armazenar dados baseados no tipo de avaliação
          $oAprovacaoConselho->setPeriodoAlterado($this->iPeriodoAlteracao);

          // Converter tipo para código de 1 caractere
          $sTipoCodigo = 'N'; // Default para NOTA
          switch ($sTipo) {
            case 'NOTA':
              $sTipoCodigo = 'N';
              $oAprovacaoConselho->setNotaAnterior($oDados->ed72_i_valornota);
              break;
            case 'NIVEL':
              $sTipoCodigo = 'C';
              $oAprovacaoConselho->setConceitoAnterior($oDados->ed72_c_valorconceito);
              break;
            case 'PARECER':
              $sTipoCodigo = 'P';
              $oAprovacaoConselho->setParecerAnterior($oDados->ed72_t_parecer);
              break;
          }

          $oAprovacaoConselho->setTipoAlteracao($sTipoCodigo);
        }
      }
      } // Fim do if ($oVerifica->total == 0)
    }

    $this->oAprovadoConcelho->salvar();
    $this->lValidouAlteracaoResultadoFinal = true;
  }

  /**
   * Altera a nota específica do período selecionado e recalcula a média
   * @throws DBException
   */
  private function alterarNotaPeriodoEspecificoERecalcular() {

    $oAprovacaoConselho = $this->getFormaAprovacaoConselho();

    // Verificar se deve alterar nota e se foi informada avaliação
    if ($oAprovacaoConselho->getAlterarNotaFinal() == 2 && !empty($oAprovacaoConselho->getAvaliacaoConselho())) {

      // Buscar o registro específico da diarioavaliacao para o período
      // NOTA: A captura da nota anterior já foi feita em adicionarAprovacaoConselho()
      $sql = "
        SELECT da.ed72_i_codigo
        FROM diarioavaliacao da
        INNER JOIN procavaliacao pa ON pa.ed41_i_codigo = da.ed72_i_procavaliacao
        INNER JOIN periodoavaliacao pav ON pav.ed09_i_codigo = pa.ed41_i_periodoavaliacao
        WHERE da.ed72_i_diario = {$this->iCodigoDiario}
          AND pav.ed09_i_codigo = " . intval($this->iPeriodoAlteracao);

      $result = pg_query($sql);

      if (!is_resource($result)) {
        throw new DBException("Erro ao buscar avaliação do período específico: " . pg_last_error());
      }

      if (pg_num_rows($result) > 0) {
        $oDados = db_utils::fieldsMemory($result, 0);
        $iCodigoDiarioAvaliacao = $oDados->ed72_i_codigo;

        // Atualizar o valor específico do período baseado no tipo de avaliação
        $sNovaAvaliacao = pg_escape_string($oAprovacaoConselho->getAvaliacaoConselho());
        $sTipoAlteracao = $oAprovacaoConselho->getTipoAlteracao();

        $sqlUpdate = "UPDATE diarioavaliacao SET ";

        switch ($sTipoAlteracao) {
          case 'N': // NOTA
            $sqlUpdate .= "ed72_i_valornota = {$sNovaAvaliacao}";
            break;
          case 'C': // CONCEITO (NIVEL)
            $sqlUpdate .= "ed72_c_valorconceito = '{$sNovaAvaliacao}'";
            break;
          case 'P': // PARECER
            $sqlUpdate .= "ed72_t_parecer = '{$sNovaAvaliacao}'";
            break;
          default:
            // Fallback para nota (compatibilidade)
            $sqlUpdate .= "ed72_i_valornota = {$sNovaAvaliacao}";
            break;
        }

        $sqlUpdate .= " WHERE ed72_i_codigo = {$iCodigoDiarioAvaliacao}";

        $resultUpdate = pg_query($sqlUpdate);

        if (!$resultUpdate) {
          throw new DBException("Erro ao alterar valor do período específico: " . pg_last_error());
        }

        /**
         * Autor: Uemerson Santana
         * Data: 06/10/2025
         * Demanda: 17872 - Forçar atualização da nota da Prova Final na tabela diarioavaliacao
         *
         * PROBLEMA IDENTIFICADO:
         * A Prova Final (código 10) NÃO é um "resultado final" que gera recálculo automático.
         * O DiarioAvaliacaoDisciplina->salvar() só recalcula para períodos que "geram resultado final".
         * Por isso, precisamos forçar a atualização da nota da Prova Final ANTES do recálculo.
         */

        // IMPORTANTE: Para Prova Final (código 10), forçar atualização da nota na tabela diarioavaliacao
        if (intval($this->iPeriodoAlteracao) === 10) {

          // Buscar o registro da Prova Final na tabela diarioavaliacao
          $sqlProvaFinal = "
            SELECT da.ed72_i_codigo as codigo_avaliacao
            FROM diarioavaliacao da
            INNER JOIN procavaliacao pa ON pa.ed41_i_codigo = da.ed72_i_procavaliacao
            INNER JOIN periodoavaliacao pav ON pav.ed09_i_codigo = pa.ed41_i_periodoavaliacao
            WHERE da.ed72_i_diario = {$this->iCodigoDiario}
              AND pav.ed09_i_codigo = 10
            LIMIT 1";

          $rsProvaFinal = pg_query($sqlProvaFinal);

          if (is_resource($rsProvaFinal) && pg_num_rows($rsProvaFinal) > 0) {
            $oProvaFinal = db_utils::fieldsMemory($rsProvaFinal, 0);
            $iCodigoProvaFinal = $oProvaFinal->codigo_avaliacao;

            // Atualizar a nota da Prova Final na tabela diarioavaliacao
            $sNovaAvaliacao = pg_escape_string($oAprovacaoConselho->getAvaliacaoConselho());
            $sTipoAlteracao = $oAprovacaoConselho->getTipoAlteracao();

            $sqlUpdateProvaFinal = "UPDATE diarioavaliacao SET ";

            switch ($sTipoAlteracao) {
              case 'N': // NOTA
                $sqlUpdateProvaFinal .= "ed72_i_valornota = {$sNovaAvaliacao}";
                break;
              case 'C': // CONCEITO (NIVEL)
                $sqlUpdateProvaFinal .= "ed72_c_valorconceito = '{$sNovaAvaliacao}'";
                break;
              case 'P': // PARECER
                $sqlUpdateProvaFinal .= "ed72_t_parecer = '{$sNovaAvaliacao}'";
                break;
              default:
                // Fallback para nota (compatibilidade)
                $sqlUpdateProvaFinal .= "ed72_i_valornota = {$sNovaAvaliacao}";
                break;
            }

            $sqlUpdateProvaFinal .= " WHERE ed72_i_codigo = {$iCodigoProvaFinal}";

            $resultUpdateProvaFinal = pg_query($sqlUpdateProvaFinal);

            if (!$resultUpdateProvaFinal) {
              throw new DBException("Erro ao atualizar nota da Prova Final na tabela diarioavaliacao: " . pg_last_error());
            }
          }
        }
        // Recalcular a média chamando o DiarioAvaliacaoDisciplina->salvar()
        $this->recalcularMediaAposAlteracaoNota();

        /**
         * Autor: Uemerson Santana
         * Data: 06/10/2025
         * Demanda: 17872 - Validação de média mínima para aprovação pelo conselho
         *
         * REGRA DE NEGÓCIO:
         * 1. Para 4º BIMESTRE: Média dos 4 bimestres deve ser >= 5.0
         * 2. Para PROVA FINAL: (Média dos 4 bimestres + Prova Final) / 2 deve ser >= 5.0
         */

        // Validação específica para 4º bimestre (ed09_i_codigo = 4)
        if (intval($this->iPeriodoAlteracao) === 4) {

          $sqlMedia = "
            SELECT df.ed74_c_valoraprov
              FROM diariofinal df
             WHERE df.ed74_i_diario = {$this->iCodigoDiario}
             LIMIT 1";

          $rsMedia = pg_query($sqlMedia);

          if (!is_resource($rsMedia)) {
            throw new DBException("Erro ao validar média após alteração do 4º bimestre: " . pg_last_error());
          }

          if (pg_num_rows($rsMedia) > 0) {
            $oMedia = db_utils::fieldsMemory($rsMedia, 0);

            // Converter para número (aceita ponto ou vírgula)
            $nMediaRecalculada = 0.0;
            if (isset($oMedia->ed74_c_valoraprov) && $oMedia->ed74_c_valoraprov !== '') {
              $sValorMedia = str_replace(',', '.', (string)$oMedia->ed74_c_valoraprov);
              $nMediaRecalculada = (float)$sValorMedia;
            }

            if ($nMediaRecalculada < 5.0) {
              throw new BusinessException(
                "O lançamento da aprovação do conselho no 4º bimestre não poderá ser efetivado, pois a nota da avaliação não foi suficiente para o aluno alcançar média igual ou superior a 5.0. Neste caso, será necessário realizar a prova final."
              );
            }
          }
        }
        // Validação específica para Prova Final (ed09_i_codigo = 10)
        elseif (intval($this->iPeriodoAlteracao) === 10) {

            // LIMPAR CACHE ANTES DE PROCESSAR PROVA FINAL
            // EducacaoSessionManager::limpar();
            // $this->recalcularMediaAposAlteracaoNota();
          /**
           * REGRA: Média Final = (Média dos 4 bimestres + Prova Final) / 2
           * Deve ser >= 5.0 para aprovação
           */

           $sqlMediaFinal = "
           SELECT
             TRUNC(AVG(da_bimestres.ed72_i_valornota::numeric), 1) as media_anual,
             da_prova_final.ed72_i_valornota as prova_final
           FROM diarioavaliacao da_bimestres
           INNER JOIN procavaliacao pa_bimestres ON pa_bimestres.ed41_i_codigo = da_bimestres.ed72_i_procavaliacao
           INNER JOIN periodoavaliacao pav_bimestres ON pav_bimestres.ed09_i_codigo = pa_bimestres.ed41_i_periodoavaliacao
           LEFT JOIN diarioavaliacao da_prova_final ON da_prova_final.ed72_i_diario = da_bimestres.ed72_i_diario
           LEFT JOIN procavaliacao pa_prova_final ON pa_prova_final.ed41_i_codigo = da_prova_final.ed72_i_procavaliacao
           LEFT JOIN periodoavaliacao pav_prova_final ON pav_prova_final.ed09_i_codigo = pa_prova_final.ed41_i_periodoavaliacao
           WHERE da_bimestres.ed72_i_diario = {$this->iCodigoDiario}
             AND pav_bimestres.ed09_i_codigo IN (1, 2, 3, 4)
             AND pav_prova_final.ed09_i_codigo = 10
           GROUP BY da_prova_final.ed72_i_valornota";

          $rsMediaFinal = pg_query($sqlMediaFinal);

          if (!is_resource($rsMediaFinal)) {
            throw new DBException("Erro ao validar média após alteração da Prova Final: " . pg_last_error());
          }

          if (pg_num_rows($rsMediaFinal) > 0) {
            $oDadosMedia = db_utils::fieldsMemory($rsMediaFinal, 0);

            // Converter média anual para número
            $nMediaAnual = 0.0;
            if (isset($oDadosMedia->media_anual) && $oDadosMedia->media_anual !== '') {
              $sMediaAnual = str_replace(',', '.', (string)$oDadosMedia->media_anual);
              $nMediaAnual = (float)$sMediaAnual;
            }

            // Converter prova final para número
            $nProvaFinal = 0.0;
            if (isset($oDadosMedia->prova_final) && $oDadosMedia->prova_final !== '') {
              $nProvaFinal = (float)$oDadosMedia->prova_final;
            }

            // Calcular média final: (média anual + prova final) / 2
            $nMediaFinalCalculada = 0.0;
            if ($nMediaAnual > 0 || $nProvaFinal > 0) {
              $nMediaFinalCalculada = ($nMediaAnual + $nProvaFinal) / 2;
            }

            if ($nMediaFinalCalculada < 5.0) {
              throw new BusinessException(
                "O lançamento da aprovação do conselho na Prova Final não poderá ser efetivado, pois a nota da avaliação não foi suficiente para o aluno alcançar média final igual ou superior a 5.0. Média dos 4 bimestres: " . number_format($nMediaAnual, 1, ',', '.') . " + Prova Final: " . number_format($nProvaFinal, 1, ',', '.') . " = Média Final: " . number_format($nMediaFinalCalculada, 2, ',', '.') . "."
              );
            }

            /**
             * Autor: Uemerson Santana
             * Data: 06/10/2025
             * Demanda: 17872 - Recalcular e atualizar média final após alterar Prova Final
             *
             * REGRA DE NEGÓCIO:
             * Após alterar a nota da Prova Final e validar que a média >= 5.0,
             * precisamos ATUALIZAR a tabela diariofinal com a nova média final calculada.
             *
             * FÓRMULA: Média Final = (Média Anual + Prova Final) / 2
             *
             * MOTIVO: A Prova Final (código 10) NÃO é um "resultado final" que gera
             * recálculo automático pelo DiarioAvaliacaoDisciplina->salvar().
             * Por isso, precisamos forçar a atualização da média final aqui.
             */

            // Formatar média final calculada para string com 1 casa decimal
            $sMediaFinalFormatada = number_format(floor($nMediaFinalCalculada * 10) / 10, 1, ',', '.');

            // Determinar resultado da aprovação baseado na média final
            $sResultadoAprovacao = ($nMediaFinalCalculada >= 5.0) ? 'A' : 'R';

            // Atualizar diariofinal com a nova média final
            $sqlUpdateMediaFinal = "
              UPDATE diariofinal
              SET
                ed74_c_valoraprov = '{$sMediaFinalFormatada}',
                ed74_c_resultadoaprov = '{$sResultadoAprovacao}'
              WHERE ed74_i_diario = {$this->iCodigoDiario}";

            $rsUpdateMediaFinal = pg_query($sqlUpdateMediaFinal);

            if (!$rsUpdateMediaFinal) {
              throw new DBException("Erro ao atualizar média final após alteração da Prova Final: " . pg_last_error());
            }
          }
        }
      }
    }
  }

  /**
   * Recalcula a média após alterar nota específica do período
   * @throws DBException
   */
  private function recalcularMediaAposAlteracaoNota() {
    /**
     * Autor: Uemerson Santana
     * Data: 29/09/2025
     * Demanda: 17872
     */

    // Buscar a matrícula e regência para recalcular
    $sqlMatricula = "
      SELECT m.ed60_i_codigo as matricula_codigo, d.ed95_i_regencia as regencia_codigo
      FROM diario d
      INNER JOIN matricula m ON m.ed60_i_aluno = d.ed95_i_aluno
      WHERE d.ed95_i_codigo = {$this->iCodigoDiario}
      ORDER BY m.ed60_i_codigo DESC
      LIMIT 1";

    $result = pg_query($sqlMatricula);

    if (!is_resource($result) || pg_num_rows($result) == 0) {
      throw new DBException("Erro ao buscar dados para recálculo da média: " . pg_last_error());
    }

    $oDados = db_utils::fieldsMemory($result, 0);

    // Carregar a matrícula e recalcular via DiarioAvaliacaoDisciplina
    require_once(modification("model/educacao/MatriculaRepository.model.php"));
    require_once(modification("model/educacao/RegenciaRepository.model.php"));
    $oMatricula = MatriculaRepository::getMatriculaByCodigo($oDados->matricula_codigo);
    $oRegencia = RegenciaRepository::getRegenciaByCodigo($oDados->regencia_codigo);

    $oDiarioClasse = $oMatricula->getDiarioDeClasse();
    $oDiarioAvaliacaoDisciplina = $oDiarioClasse->getDisciplinasPorRegencia($oRegencia);

    // IMPORTANTE: Definir flag para evitar loop infinito antes de salvar
    // e garantir que seja limpa mesmo se houver erro
    try {
      self::$lEmRecalculo = true;

      if (!empty($oDiarioAvaliacaoDisciplina)) {
        $oDiarioAvaliacaoDisciplina->salvar();
      }

    } finally {
      self::$lEmRecalculo = false;
    }
  }

  /**
   * Restaura a nota anterior do período após exclusão da aprovação pelo conselho
   * @param float|null $nNotaAnterior
   * @param integer $iPeriodoAlterado
   * @throws DBException
   */
  public function restaurarNotaAnterior($nNotaAnterior, $iPeriodoAlterado) {

    /**
     * Autor: Uemerson Santana
     * Data: 29/09/2025
     * Demanda: 17872
     */

    // Buscar o registro específico da diarioavaliacao para o período
    $sql = "
      SELECT da.ed72_i_codigo
      FROM diarioavaliacao da
      INNER JOIN procavaliacao pa ON pa.ed41_i_codigo = da.ed72_i_procavaliacao
      INNER JOIN periodoavaliacao pav ON pav.ed09_i_codigo = pa.ed41_i_periodoavaliacao
      WHERE da.ed72_i_diario = {$this->iCodigoDiario}
        AND pav.ed09_i_codigo = " . intval($iPeriodoAlterado);

    $result = pg_query($sql);

    if (!is_resource($result)) {
      throw new DBException("Erro ao buscar avaliação do período para restauração: " . pg_last_error());
    }

    if (pg_num_rows($result) > 0) {
      $oDados = db_utils::fieldsMemory($result, 0);
      $iCodigoDiarioAvaliacao = $oDados->ed72_i_codigo;

      // Restaurar a nota anterior (pode ser NULL se estava em branco)
      $sValorNota = ($nNotaAnterior === null || $nNotaAnterior === '') ? 'NULL' : $nNotaAnterior;

      $sqlUpdate = "
        UPDATE diarioavaliacao
        SET ed72_i_valornota = {$sValorNota}
        WHERE ed72_i_codigo = {$iCodigoDiarioAvaliacao}";
      $resultUpdate = pg_query($sqlUpdate);

      if (!$resultUpdate) {
        throw new DBException("Erro ao restaurar nota do período: " . pg_last_error());
      }

      // IMPORTANTE: Agora SIM recalcular a média com a nota restaurada
      // Mas fazer isso ANTES do salvar() final para garantir que a média seja recalculada com o valor correto
      $this->recalcularMediaAposAlteracaoNota();
    } else {
    }
  }

  /**
   * Detecta o tipo de avaliação (nota, conceito ou parecer) baseado na forma de avaliação
   * @param integer $iCodigoProcAvaliacao Código do procedimento de avaliação
   * @return array|null Array com tipo e parecer ou null se não encontrado
   */
  private function detectarTipoAvaliacao($iCodigoProcAvaliacao) {
    /**
     * Autor: Uemerson Santana
     * Data: 29/09/2025
     * Demanda: 17872
     */

    $sql = "SELECT fa.ed37_c_tipo, fa.ed37_c_parecerarmaz
            FROM formaavaliacao fa
            INNER JOIN procavaliacao pr ON pr.ed41_i_formaavaliacao = fa.ed37_i_codigo
            WHERE pr.ed41_i_codigo = " . intval($iCodigoProcAvaliacao);

    $result = pg_query($sql);
    if (is_resource($result) && pg_num_rows($result) > 0) {
      $oDados = db_utils::fieldsMemory($result, 0);
      return [
        'tipo' => trim($oDados->ed37_c_tipo),
        'parecer' => trim($oDados->ed37_c_parecerarmaz)
      ];
    }
    return null;
  }

  /**
   * Restaura o conceito anterior do período específico
   * @param string $sConceitoAnterior Conceito anterior a ser restaurado
   * @param integer $iPeriodoAlterado Código do período alterado
   * @throws DBException
   */
  public function restaurarConceitoAnterior($sConceitoAnterior, $iPeriodoAlterado) {
    /**
     * Autor: Uemerson Santana
     * Data: 29/09/2025
     * Demanda: 17872
     */

    // Buscar o código da avaliação para o período específico
    $sql = "SELECT da.ed72_i_codigo
            FROM diarioavaliacao da
            INNER JOIN procavaliacao pa ON pa.ed41_i_codigo = da.ed72_i_procavaliacao
            INNER JOIN periodoavaliacao pav ON pav.ed09_i_codigo = pa.ed41_i_periodoavaliacao
            WHERE da.ed72_i_diario = {$this->iCodigoDiario}
            AND pav.ed09_i_codigo = " . intval($iPeriodoAlterado);

    $result = pg_query($sql);
    if (!is_resource($result) || pg_num_rows($result) == 0) {
      throw new DBException("Avaliação não encontrada para o período especificado");
    }

    $oDados = db_utils::fieldsMemory($result, 0);
    $iCodigoAvaliacao = $oDados->ed72_i_codigo;

    // Restaurar o conceito anterior
    $sqlUpdate = "UPDATE diarioavaliacao
                  SET ed72_c_valorconceito = " . ($sConceitoAnterior === null ? "NULL" : "'" . pg_escape_string($sConceitoAnterior) . "'") . "
                  WHERE ed72_i_codigo = " . intval($iCodigoAvaliacao);

    $resultUpdate = pg_query($sqlUpdate);

    if (!$resultUpdate) {
      throw new DBException("Erro ao restaurar conceito do período: " . pg_last_error());
    }

    // Recalcular a média com o conceito restaurado
    $this->recalcularMediaAposAlteracaoNota();
  }

  /**
   * Restaura o parecer anterior do período específico
   * @param string $sParecerAnterior Parecer anterior a ser restaurado
   * @param integer $iPeriodoAlterado Código do período alterado
   * @throws DBException
   */
  public function restaurarParecerAnterior($sParecerAnterior, $iPeriodoAlterado) {
    /**
     * Autor: Uemerson Santana
     * Data: 29/09/2025
     * Demanda: 17872
     */

    // Buscar o código da avaliação para o período específico
    $sql = "SELECT da.ed72_i_codigo
            FROM diarioavaliacao da
            INNER JOIN procavaliacao pa ON pa.ed41_i_codigo = da.ed72_i_procavaliacao
            INNER JOIN periodoavaliacao pav ON pav.ed09_i_codigo = pa.ed41_i_periodoavaliacao
            WHERE da.ed72_i_diario = {$this->iCodigoDiario}
            AND pav.ed09_i_codigo = " . intval($iPeriodoAlterado);

    $result = pg_query($sql);
    if (!is_resource($result) || pg_num_rows($result) == 0) {
      throw new DBException("Avaliação não encontrada para o período especificado");
    }

    $oDados = db_utils::fieldsMemory($result, 0);
    $iCodigoAvaliacao = $oDados->ed72_i_codigo;

    // Restaurar o parecer anterior
    $sqlUpdate = "UPDATE diarioavaliacao
                  SET ed72_t_parecer = " . ($sParecerAnterior === null ? "NULL" : "'" . pg_escape_string($sParecerAnterior) . "'") . "
                  WHERE ed72_i_codigo = " . intval($iCodigoAvaliacao);

    $resultUpdate = pg_query($sqlUpdate);

    if (!$resultUpdate) {
      throw new DBException("Erro ao restaurar parecer do período: " . pg_last_error());
    }

    // Recalcular a média com o parecer restaurado
    $this->recalcularMediaAposAlteracaoNota();
  }

}
