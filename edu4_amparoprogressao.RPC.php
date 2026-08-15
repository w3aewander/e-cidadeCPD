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

require_once (modification("libs/db_stdlib.php"));
require_once (modification("libs/db_utils.php"));
require_once (modification("libs/db_app.utils.php"));
require_once (modification("libs/db_con"."ecta.php"));
require_once (modification("libs/db_sessoes.php"));
require_once (modification("dbforms/db_funcoes.php"));
require_once (modification("libs/JSON.php"));

$oParam             = JSON::create()->parse(str_replace("\\","",$_POST["json"]));
$oRetorno           = new stdClass();
$oRetorno->iStatus  = 1;
$oRetorno->sMessage = '';

try {

  db_inicio_transacao();

  switch ($oParam->exec) {

    case 'buscaTipoAmparo':

      if ( empty($oParam->iProgressao) ) {
        throw new ParameterException( "Código da progressão não informado." );
      }

      $oProgressao = ProgressaoParcialAlunoRepository::getProgressaoParcialAlunoByCodigo($oParam->iProgressao);
      $oDiario     = new DiarioProgressaoParcial($oProgressao);
      $oAmparo     = $oDiario->getAmparo();

      $oJustificativaConvencao = $oAmparo->getConvencao();

      if (  $oAmparo->getTipoAmparo() == AmparoProgressao::AMPARO_JUSTIFICATIVA  ) {
        $oJustificativaConvencao = $oAmparo->getJustificativa();
      }

      $oRetorno->iJustificativaConvencao = $oJustificativaConvencao->getCodigo();
      $oRetorno->sJustificativaConvencao = $oJustificativaConvencao->getDescricao();
      $oRetorno->lCargaHoraria           = $oAmparo->isAdicionadoNaCargaHoraria();
      $oRetorno->sTipoAmparo             = $oAmparo->getTipoAmparo() == AmparoProgressao::AMPARO_JUSTIFICATIVA ? 'J' : 'C';
      break;

    case 'salvar':

      if ( empty($oParam->iProgressao) ) {
        throw new ParameterException("Código da progressão não informado.");
      }

      if ( empty($oParam->sTipoAmparo) ) {
        throw new ParameterException("Tipo de amparo não informado.");
      }

      if ( empty($oParam->iJustificativaConvencao) ) {
        throw new ParameterException("Justificativa ou Convenção não informado.");
      }

      if ( empty($oParam->aPeriodosAmparados) ) {
        throw new ParameterException("Nenhum período para amparo informado.");
      }

      if ( empty($oParam->iRegencia) ) {
        throw new ParameterException("Disciplina não informada.");
      }

      $oProgressao = ProgressaoParcialAlunoRepository::getProgressaoParcialAlunoByCodigo($oParam->iProgressao);
      $oRegencia   = RegenciaRepository::getRegenciaByCodigo($oParam->iRegencia);
      $oDiario     = new DiarioProgressaoParcial($oProgressao, $oRegencia);
      $oAmparo     = $oDiario->getAmparo();
      $oAmparo->setAproveitaCargaHoraria( false );

      if ( $oParam->sTipoAmparo == 'J' ) {
        $oAmparo->setJustificativa( new Justificativa( $oParam->iJustificativaConvencao ) );
      } else {
        $oAmparo->setConvencao( new Convencao( $oParam->iJustificativaConvencao ) );
      }

      foreach ( $oDiario->getAvaliacoes() as $oAvaliacoes ) {

        if ( in_array($oAvaliacoes->getElementoAvaliacao()->getCodigo(), $oParam->aPeriodosAmparados) ) {

          $oAvaliacoes->setAmparado(true);
          $oAmparo->adicionarPeriodo( $oAvaliacoes );
        }
      }

      $oAmparo->salvar();
      $oRetorno->sMessage = urlencode("Amparo salvo com sucesso.");
      break;

      case 'excluir':

        if ( empty($oParam->iProgressao) ) {
          throw new ParameterException( "Código da progressão não informado." );
        }
        if ( empty($oParam->iRegencia) ) {
          throw new ParameterException("Disciplina não informada.");
        }


        db_inicio_transacao();

        $oRegencia   = RegenciaRepository::getRegenciaByCodigo($oParam->iRegencia);
        $oProgressao = ProgressaoParcialAlunoRepository::getProgressaoParcialAlunoByCodigo($oParam->iProgressao);
        $oDiario     = new DiarioProgressaoParcial($oProgressao, $oRegencia);
        $oDiario->removerAmparo();

        db_fim_transacao();

        $oRetorno->sMessage = urlencode("Amparo excluído com sucesso.");
        break;

  }

  db_fim_transacao(false);


} catch (Exception $eErro){

  db_fim_transacao(true);
  $oRetorno->iStatus  = 2;
  $oRetorno->sMessage = $eErro->getMessage();
}
$oRetorno->erro = $oRetorno->iStatus == 2;
echo JSON::create()->stringify($oRetorno);
