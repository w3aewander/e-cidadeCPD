<?php

/**
 * E-cidade Software Publico para Gestão Municipal
 *   Copyright (C) 2014 DBSeller Serviços de Informática Ltda
 *                          www.dbseller.com.br
 *                          e-cidade@dbseller.com.br
 *   Este programa é software livre; você pode redistribuí-lo e/ou
 *   modificá-lo sob os termos da Licença Pública Geral GNU, conforme
 *   publicada pela Free Software Foundation; tanto a versão 2 da
 *   Licença como (a seu critério) qualquer versão mais nova.
 *   Este programa e distribuído na expectativa de ser útil, mas SEM
 *   QUALQUER GARANTIA; sem mesmo a garantia implícita de
 *   COMERCIALIZAÇÃO ou de ADEQUAÇÃO A QUALQUER PROPÓSITO EM
 *   PARTICULAR. Consulte a Licença Pública Geral GNU para obter mais
 *   detalhes.
 *   Você deve ter recebido uma cópia da Licença Pública Geral GNU
 *   junto com este programa; se não, escreva para a Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *   02111-1307, USA.
 *   Cópia da licença no diretório licenca/licenca_en.txt
 *                                 licenca/licenca_pt.txt
 */

require_once (modification("libs/db_stdlib.php"));
require_once (modification("libs/db_utils.php"));
require_once (modification("libs/db_app.utils.php"));
require_once (modification("libs/db_conecta_plugin.php"));
require_once (modification("libs/db_sessoes.php"));
require_once (modification("dbforms/db_funcoes.php"));
require_once (modification("libs/JSON.php"));

define("MSG_LANCAMENTOSHORASAULA", "educacao.escola.LancamentoHorasAulaRPC.");

$oParam             = JSON::create()->parse(str_replace("\\","",$_POST["json"]));
$oRetorno           = new stdClass();
$oRetorno->iStatus  = 1;
$oRetorno->sMessage = '';

db_inicio_transacao();
try {

  switch ($oParam->exec) {

    /*
     * Busca as horas aulas lançadas para a turma informada.
     */
    case 'buscar':

      if ( empty($oParam->iTurma) ) {
        throw new ParameterException( _M( MSG_LANCAMENTOSHORASAULA . 'informe_turma') );
      }

      if ( empty($oParam->iEtapa) ) {
        throw new ParameterException( _M( MSG_LANCAMENTOSHORASAULA . 'informe_etapa') );
      }


      $oDaoRegencias = new cl_progressaoparcialalunoturmaregencia();
      $sWhere        = " ed59_i_turma = {$oParam->iTurma} ";
      $sWhere       .= " and ed59_i_serie = {$oParam->iEtapa} ";

      $sSqlRegencias = $oDaoRegencias->sql_query(null, 'distinct ed59_i_codigo', null, $sWhere);
      $rsRegencias   = db_query($sSqlRegencias);

      if ( !$rsRegencias ) {
        throw new DBException( _M( MSG_LANCAMENTOSHORASAULA . "erro_buscar_horas_aula" ) );
      }

      $iLinhas = pg_num_rows($rsRegencias);
      if ( $iLinhas == 0 ) {
        throw new BusinessException( _M( MSG_LANCAMENTOSHORASAULA . "erro_nenhuma_regencia") );
      }

      $oRetorno->aDisciplinas = array();
      for ($i = 0; $i < $iLinhas; $i++ ) {

        $oRegencia = RegenciaRepository::getRegenciaByCodigo(db_utils::fieldsMemory($rsRegencias, $i)->ed59_i_codigo);

        $oDadosDisciplina                    = new stdClass();
        $oDadosDisciplina->iCodigo           = $oRegencia->getCodigo();
        $oDadosDisciplina->sDescricao        = $oRegencia->getDisciplina()->getNomeDisciplina();
        $oDadosDisciplina->sAbrev            = $oRegencia->getDisciplina()->getAbreviatura();
        $oDadosDisciplina->lEncerrada        = $oRegencia->isEncerrada();
        $oDadosDisciplina->lSomenteAvaliacao = $oRegencia->getFrequenciaGlobal() == "A";
        $oDadosDisciplina->lObrigatoria      = $oRegencia->isObrigatoria();
        $oDadosDisciplina->aPeriodos         = array();
        $oProcedimento                       = $oRegencia->getProcedimentoAvaliacao();

        foreach ($oProcedimento->getElementos() as $oAvaliacao) {

          if ($oAvaliacao instanceof AvaliacaoPeriodica && $oAvaliacao->getPeriodoAvaliacao()->hasControlaFrequencia()) {

            $oPeriodo          = new stdClass();
            $oPeriodo->iCodigo = $oAvaliacao->getPeriodoAvaliacao()->getCodigo();
            $oPeriodo->sNome   = $oAvaliacao->getPeriodoAvaliacao()->getDescricaoAbreviada();
            $oPeriodo->iOrdem  = $oAvaliacao->getPeriodoAvaliacao()->getOrdemPeriodo();

            $iTotalDeAulas = $oRegencia->getTotalDeAulasNoPeriodo($oAvaliacao->getPeriodoAvaliacao());
            if (empty($iTotalDeAulas)) {
              $iTotalDeAulas = '';
            }
            $iAulas           = $iTotalDeAulas;
            $oPeriodo->iAulas = $iAulas;

            $oDadosDisciplina->aPeriodos[] = $oPeriodo;
          }
        }

        $oRetorno->aDisciplinas[] = $oDadosDisciplina;
      }
    break;
  }

  db_fim_transacao(false);

} catch (Exception $eErro) {

  db_fim_transacao(true);
  $oRetorno->iStatus  = 2;
  $oRetorno->sMessage = urlencode($eErro->getMessage());
}

$oRetorno->erro = $oRetorno->iStatus == 2;
echo JSON::create()->stringify($oRetorno);