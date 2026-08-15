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

    case 'buscar':

      if ( empty($oParam->iProgressao) ) {
        throw new ParameterException( "Código da progressão não informado." );
      }

      $oProgressao             = ProgressaoParcialAlunoRepository::getProgressaoParcialAlunoByCodigo($oParam->iProgressao);
      $oDiario                 = new DiarioProgressaoParcial($oProgressao);
      $oFormaAprovacaoConselho = $oDiario->getResultadoFinal()->getFormaAprovacaoConselho();

      if ( $oFormaAprovacaoConselho instanceof AprovacaoConselhoProgressao) {

        $iCodigoRecHumano     = $oFormaAprovacaoConselho->getRecursoHumano();
        $oRetorno->iProfessor = '';
        $oRetorno->sProfessor = '';

        if ( !empty($iCodigoRecHumano) ) {

          $oDocente = DocenteRepository::getDocenteByCodigoRecursosHumano( $oFormaAprovacaoConselho->getRecursoHumano() );
          $oRetorno->iProfessor = $oDocente->getCodigoDocente();
          $oRetorno->sProfessor = $oDocente->getNome();
        }
        $oRetorno->iFormaAprovacao         = $oFormaAprovacaoConselho->getFormaAprovacao();
        $oRetorno->sJustificativaResultado = $oFormaAprovacaoConselho->getJustificativa();
        $oRetorno->iAlterarNotaFinal       = $oFormaAprovacaoConselho->getAlterarNotaFinal();
        $oRetorno->sNovaAvaliacao          = $oFormaAprovacaoConselho->getAvaliacaoConselho();
      }

      break;

    case 'salvar':

      if ( empty($oParam->iProgressao) ) {
        throw new ParameterException("Código da progressão não informado.");
      }

      if ( empty($oParam->iFormaAprovacao) ) {
        throw new ParameterException("Forma de aprovação não informada.");
      }

      if ( $oParam->iFormaAprovacao == 1 && empty($oParam->iAlterarAvaliacao) ) {
        throw new ParameterException("Alterar avaliação final não informado.");
      }

      if ( empty($oParam->sJustificativa) ) {
        throw new ParameterException("Justificativa não informada.");
      }

      if ( empty($oParam->iRegencia) ) {
        throw new ParameterException("Disciplina não informada.");
      }

      $oRegencia   = RegenciaRepository::getRegenciaByCodigo($oParam->iRegencia);

      $oProgressao     = ProgressaoParcialAlunoRepository::getProgressaoParcialAlunoByCodigo($oParam->iProgressao);
      $oDiario         = new DiarioProgressaoParcial($oProgressao, $oRegencia);
      $oResultadoFinal = $oDiario->getResultadoFinal();

      $oAprovacaoConselho         = new AprovacaoConselhoProgressao($oResultadoFinal);
      $oAprovacaoConselho->setData(new DBDate(date("Y-m-d", time())));
      $oAprovacaoConselho->setHora(date("H:i", time()));

      if (!empty($oParam->iProfessor)) {
        $oAprovacaoConselho->setRecursoHumano($oParam->iProfessor);
      }
      $oAprovacaoConselho->setFormaAprovacao($oParam->iFormaAprovacao);
      $oAprovacaoConselho->setJustificativa($oParam->sJustificativa);
      $oAprovacaoConselho->setUsuario(new UsuarioSistema(db_getsession('DB_id_usuario')));

      if ($oParam->iFormaAprovacao == 1) {

        $oAprovacaoConselho->setAlterarNotaFinal($oParam->iAlterarAvaliacao);

        if ( $oParam->iAlterarAvaliacao == '' || $oParam->iAlterarAvaliacao == 1 ) {
          $oAprovacaoConselho->setAvaliacaoConselho('');
        } else {
          $oAprovacaoConselho->setAvaliacaoConselho($oParam->sNovaAvaliacao);
        }
      }

      $oResultadoFinal->adicionarAprovacaoConselho($oAprovacaoConselho);
      $oDiario->salvar();
      $oRetorno->sMessage = 'Alteração do resultado final salva com sucesso.';

      break;

    case 'excluir':

      if ( empty($oParam->iProgressao) ) {
        throw new ParameterException("Código da progressão não informado.");
      }
      if ( empty($oParam->iRegencia) ) {
        throw new ParameterException("Disciplina não informada.");
      }

      $oRegencia   = RegenciaRepository::getRegenciaByCodigo($oParam->iRegencia);

      $oProgressao     = ProgressaoParcialAlunoRepository::getProgressaoParcialAlunoByCodigo($oParam->iProgressao);
      $oDiario         = new DiarioProgressaoParcial($oProgressao, $oRegencia);
      $oResultadoFinal = $oDiario->getResultadoFinal();
      $oResultadoFinal->removerAprovacaoConselho();
      $oDiario->salvar();

      $oRetorno->sMessage = 'Alteração do resultado final excluída com sucesso.';
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
