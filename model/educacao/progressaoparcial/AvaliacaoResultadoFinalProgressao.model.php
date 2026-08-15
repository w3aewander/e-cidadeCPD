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
 * Avaliação de um elemento do procedimento de avaliação de um aluno em progressão parcial
 *
 *
 * @package    educacao
 * @subpackage progressaoparcial
 * @author     Andrio Costa <andrio.costa@dbseller.com.br>
 * @version    $Revision: 1.3 $
 */
class AvaliacaoResultadoFinalProgressao extends AvaliacaoResultadoFinal {


  public function __construct(DiarioProgressaoParcial $oDiarioAvaliacao) {

    $this->iCodigoDiario = $oDiarioAvaliacao->getCodigoDiario();

    $oDaoDiarioFinal    = new cl_diariofinalprogressao();
    $sSqlAvaliacaoFinal = $oDaoDiarioFinal->sql_query_file(null, "*", null, "ed995_diarioprogressao={$this->iCodigoDiario}");
    $rsAvaliacaoFinal   = db_query($sSqlAvaliacaoFinal);

    if( !is_resource( $rsAvaliacaoFinal ) ) {

      $oErro        = new stdClass();
      $oErro->sErro = pg_last_error();

      throw new DBException( _M( MENSAGENS_AVALIACAO_RESULTADO_FINAL . 'erro_buscar_diario_final', $oErro ) );
    }

    if( pg_num_rows( $rsAvaliacaoFinal ) > 0 ) {

      $oDados                      = db_utils::fieldsMemory($rsAvaliacaoFinal, 0);
      $this->iCodigoResultadoFinal = $oDados->ed995_sequencial;
      $this->iProcResultado        = $oDados->ed995_procresultadoaprov;
      $this->mValorAprovacao       = $oDados->ed995_valoraprov;
      $this->sResultadoAprovacao   = $oDados->ed995_resultadoaprov;
      $this->sResultadoFrequencia  = $oDados->ed995_resultadofreq;
      $this->nPercentualFrequencia = $oDados->ed995_percentualfreq;
      $this->sResultadoFinal       = $oDados->ed995_resultadofinal;
      $this->sObservacao           = $oDados->ed995_observacao;
      $this->oResultadoAvaliacao   = ResultadoAvaliacaoRepository::getResultadoAvaliacaoByCodigo($oDados->ed995_procresultadoaprov);
    }
  }


  /**
   * Persiste as informacoes do diario final
   */
  public function salvar() {

    if (!db_utils::inTransaction()) {
      throw new DBException("Não existe transação com o banco de dados ativa.");
    }
    $oDaoDiarioFinal    = new cl_diariofinalprogressao();

    /**
     * Caso o aluno está aprovado pelo conselho, o resultado final do mesmo deverá
     * ser sempre aprovado. Exceto quando reclassificado por baixa frequência
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

    $oDaoDiarioFinal->ed995_sequencial         = null;
    $oDaoDiarioFinal->ed995_diarioprogressao   = $this->getCodigoDiario();
    $oDaoDiarioFinal->ed995_procresultadoaprov = $this->getResultadoAvaliacao()->getCodigo();
    $oDaoDiarioFinal->ed995_valoraprov         = "{$this->getValorAprovacao()}";
    $oDaoDiarioFinal->ed995_resultadoaprov     = trim($this->getResultadoAprovacao());
    $oDaoDiarioFinal->ed995_procresultadofreq  = $this->getResultadoAvaliacao()->getCodigo();
    $oDaoDiarioFinal->ed995_percentualfreq     = "{$this->nPercentualFrequencia}";
    $oDaoDiarioFinal->ed995_resultadofreq      = $this->getResultadoFrequencia();
    $oDaoDiarioFinal->ed995_resultadofinal     = $this->getResultadoFinal();
    $oDaoDiarioFinal->ed995_observacao         = "{$this->getObservacao()}";

    if (!empty($this->iCodigoResultadoFinal)) {

      $oDaoDiarioFinal->ed995_sequencial = $this->getCodigoResultadoFinal();
      $oDaoDiarioFinal->alterar($oDaoDiarioFinal->ed74_i_codigo);
    } else {

      $oDaoDiarioFinal->incluir(null);
      $this->iCodigoResultadoFinal = $oDaoDiarioFinal->ed995_sequencial;
    }
    if ($oDaoDiarioFinal->erro_status == 0) {
      throw new BusinessException("Erro ao salvar o resultado final.\n{$oDaoDiarioFinal->erro_msg}");
    }

  }

  public function aprovadoPorProgressaoParcial() {
    return false;
  }

  /**
   * Retorna uma instancia de AprovacaoConselho, com os dados a aprovacao
   * @return AprovacaoConselho|null
   * @throws DBException
   */
  public function getFormaAprovacaoConselho() {

    if ( is_null($this->oAprovadoConcelho) && !$this->lValidouAlteracaoResultadoFinal ) {

      $this->lValidouAlteracaoResultadoFinal = true;
      $oDaoAprovConselho   = new cl_aprovadoconselhoprogressao();
      $sWhereAprovConselho = "ed996_diarioprogressao = {$this->iCodigoDiario}";
      $sSqlAprovConselho   = $oDaoAprovConselho->sql_query_file(null, "*", null, $sWhereAprovConselho);
      $rsAprovConselho     = db_query($sSqlAprovConselho);

      if( !is_resource( $rsAprovConselho ) ) {

        $oErro        = new stdClass();
        $oErro->sErro = pg_last_error();
        throw new DBException( _M( MENSAGENS_AVALIACAO_RESULTADO_FINAL . 'erro_buscar_aprovacao_conselho', $oErro ) );
      }

      $iTotalAprovConselho = pg_num_rows( $rsAprovConselho );

      if ($iTotalAprovConselho > 0) {

        $oAprovacaoConselho  = new AprovacaoConselhoProgressao($this);
        $oDadosAprovConselho = db_utils::fieldsMemory($rsAprovConselho, 0);

        $oAprovacaoConselho->setCodigo($oDadosAprovConselho->ed996_sequencial);
        $oAprovacaoConselho->setFormaAprovacao($oDadosAprovConselho->ed996_aprovconselhotipo);

        if (!empty($oDadosAprovConselho->ed996_rechumano)) {
          $oAprovacaoConselho->setRecursoHumano($oDadosAprovConselho->ed996_rechumano);
        }

        if (!empty($oDadosAprovConselho->ed996_observacao)) {
          $oAprovacaoConselho->setJustificativa($oDadosAprovConselho->ed996_observacao);
        }

        $oAprovacaoConselho->setData(new DBDate(date("Y-m-d", strtotime($oDadosAprovConselho->ed996_data))));
        $oAprovacaoConselho->setHora(date("H:i", strtotime($oDadosAprovConselho->ed996_data)));
        $oAprovacaoConselho->setUsuario(new UsuarioSistema($oDadosAprovConselho->ed996_usuario));
        $oAprovacaoConselho->setAlterarNotaFinal( $oDadosAprovConselho->ed996_alterarnotafinal );

        if ( $oDadosAprovConselho->ed996_avaliacaoconselho != '' ) {
          $oAprovacaoConselho->setAvaliacaoConselho( $oDadosAprovConselho->ed996_avaliacaoconselho );
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

    $oAprovacaoConselho = $this->getFormaAprovacaoConselho();

    if ($oAprovacaoConselho instanceof AprovacaoConselho) {
      $oAprovacaoConselho->remover();
    }

    $this->oAprovadoConcelho               = null;
    $this->lValidouAlteracaoResultadoFinal = false;
  }


  /**
   * Emcapsulado alteracao do resultado final por aprovação do conselho
   * @param  AprovacaoConselho $oAprovacaoConselho
   */
  public function adicionarAprovacaoConselho(AprovacaoConselho $oAprovacaoConselho) {

    $this->oAprovadoConcelho = $oAprovacaoConselho;
    $this->oAprovadoConcelho->salvar();
    $this->lValidouAlteracaoResultadoFinal = true;
  }
}
