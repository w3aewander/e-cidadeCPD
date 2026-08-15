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
 * Controla as informacoes sobre uma aprovacao pelo conselho da progressão
 */
class AprovacaoConselhoProgressao extends AprovacaoConselho {

  /**
   * Salvamos os dados da aprovacao pelo conselho da progressão.
   * @throws DBException
   */
  public function salvar() {

    $oDaoAprovadoConselhoProgressao = new cl_aprovadoconselhoprogressao();

    $sWhereAprovadoConselho = "ed996_diarioprogressao = {$this->oAvaliacaoResultadoFinal->getCodigoDiario()}";
    $sSqlAprovadoConselho   = $oDaoAprovadoConselhoProgressao->sql_query_file(null, "ed996_sequencial", null, $sWhereAprovadoConselho);
    $rsAprovadoConselho     = db_query($sSqlAprovadoConselho);

    if( !is_resource( $rsAprovadoConselho ) ) {

      $oErro        = new stdClass();
      $oErro->sErro = pg_last_error();
      throw new DBException( _M( URL_APROVCONSELHO . 'erro_buscar_aprovacao', $oErro ) );
    }

    $iLinhasAprovadoConselho = pg_num_rows( $rsAprovadoConselho );

    $oDaoAprovadoConselhoProgressao->ed996_diarioprogressao = $this->oAvaliacaoResultadoFinal->getCodigoDiario();
    $oDaoAprovadoConselhoProgressao->ed996_rechumano        = '';

    if ($this->iRecHumano != null) {
      $oDaoAprovadoConselhoProgressao->ed996_rechumano = $this->iRecHumano;
    }

    $aHora = explode(":", $this->sHora);

    $oDaoAprovadoConselhoProgressao->ed996_usuario           = $this->oUsuario->getIdUsuario();
    $oDaoAprovadoConselhoProgressao->ed996_observacao        = $this->sJustificativa;
    $oDaoAprovadoConselhoProgressao->ed996_data = "'{$this->oData->getDate()} {$this->sHora}'";
    $oDaoAprovadoConselhoProgressao->ed996_aprovconselhotipo = $this->iFormaAprovacao;
    $oDaoAprovadoConselhoProgressao->ed996_alterarnotafinal  = $this->iAlterarNotaFinal;
    $oDaoAprovadoConselhoProgressao->ed996_avaliacaoconselho = $this->sAvaliacaoConselho;

    if ($iLinhasAprovadoConselho > 0) {

      $iCodigoAprovConselho = db_utils::fieldsMemory($rsAprovadoConselho, 0)->ed996_sequencial;
      $oDaoAprovadoConselhoProgressao->ed996_sequencial = $iCodigoAprovConselho;
      $oDaoAprovadoConselhoProgressao->alterar($iCodigoAprovConselho);
    } else {
      $oDaoAprovadoConselhoProgressao->incluir(null);
    }

    if ( $oDaoAprovadoConselhoProgressao->erro_status == "0" ) {

      $oErro        = new stdClass();
      $oErro->sErro = $oDaoAprovadoConselhoProgressao->erro_msg;

      throw new DBException( _M( URL_APROVCONSELHO . 'erro_salvar_aprovacao', $oErro ) );
    }

    if( $this->iFormaAprovacao == AprovacaoConselho::RECLASSIFICACAO_BAIXA_FREQUENCIA ) {
      $this->oAvaliacaoResultadoFinal->setResultadoFrequencia('A');
    } else {
      $this->oAvaliacaoResultadoFinal->setResultadoFinal( 'A' );
    }

    $this->oAvaliacaoResultadoFinal->salvar();
  }
  /**
   * Remove a aprovacao pelo conselho da progressão
   * @throws DBException
   */
  public function remover() {

    $oDaoAprovadoConselhoProgressao   = new cl_aprovadoconselhoprogressao();
    $sWhereAprovadoConselhoProgressao = "ed996_diarioprogressao = {$this->oAvaliacaoResultadoFinal->getCodigoDiario()}";
    $oDaoAprovadoConselhoProgressao->excluir(null, $sWhereAprovadoConselhoProgressao);

    if ( $oDaoAprovadoConselhoProgressao->erro_status == "0" ) {
      throw new DBException($oDaoAprovadoConselhoProgressao->erro_msg);
    }

    $sResultadoFinal = 'R';

    if( $this->getFormaAprovacao() == AprovacaoConselho::RECLASSIFICACAO_BAIXA_FREQUENCIA ) {

      $this->oAvaliacaoResultadoFinal->setResultadoFrequencia( 'R' );
    }

    if(    $this->oAvaliacaoResultadoFinal->getResultadoFrequencia() == 'A'
        && $this->oAvaliacaoResultadoFinal->getResultadoAprovacao() == 'A'
      ) {
      $sResultadoFinal = 'A';
    }

    $this->oAvaliacaoResultadoFinal->setResultadoFinal($sResultadoFinal);
    $this->oAvaliacaoResultadoFinal->salvar();
  }
}