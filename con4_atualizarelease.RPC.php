<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/JSON.php"));

$oJson = new services_json();

$sMethod = $_GET["exec"];

$oRetorno               = new stdClass();
$oRetorno->iStatus      = 1;
$oRetorno->sMessage     = '';

try {

  if ($sMethod == "atualizar") {

    $oReleaseAtual = new Release(null, true);

    require_once(modification("model/configuracao/ReleaseService.service.php"));

    $oReleaseService = new ReleaseService();

    $oRetorno->sMessage = urlencode("O sistema não foi atualizado.");

    if ( !$oReleaseService->verificaAtualizacao($oReleaseAtual) ) {

      $oReleaseService->downloadPacote($oReleaseAtual);
      $oReleaseService->atualiza($oReleaseAtual);

      $oRetorno->sMessage = urlencode("Sistema atualizado com sucesso.");
    }

  } elseif ( $sMethod == "agendar" ) {


    $oParam = $oJson->decode(base64_decode($_GET['json']));

    $oJob = new Job();
    $oJob->setNome('AtualizaReleaseTask');
    $oJob->setCodigoUsuario(1);
    $oJob->setDescricao('Task de atualizacao da release');
    $oJob->setNomeClasse('AtualizaReleaseTask');
    $oJob->setCaminhoPrograma('model/configuracao/AtualizaReleaseTask.model.php');
    $oJob->setTipoPeriodicidade(Agenda::PERIODICIDADE_UNICA);
    $oJob->setMomentoCricao( strtotime(implode("-", array_reverse(explode("/", $oParam->sData))) . " " . $oParam->sHora )) ;
    $oJob->salvar();

    $oRetorno->sMessage = "Atualizacao agendada com sucesso: " . $oParam->sData . " " . $oParam->sHora;

  } elseif ( $sMethod == "getDadosIniciais") {

    $oReleaseAtual = new Release(null, true);
    $oRetorno->sVersaoAtual = $oReleaseAtual->getVersaoFormatada();

    $oReleaseService = new ReleaseService();
    $oReleaseService->verificaAtualizacao($oReleaseAtual);
    $oRetorno->sProximaVersao = $oReleaseAtual->getProximaVersao();

    
  }

} catch (Exception $eErro){

  $oRetorno->iStatus  = 2;
  $oRetorno->sMessage = urlencode($eErro->getMessage());
}

echo $oJson->encode($oRetorno);

?>
