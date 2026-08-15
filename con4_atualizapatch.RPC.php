<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/JSON.php"));

$oJson                  = new services_json();
$oParam                 = $oJson->decode(str_replace("\\","",$_POST["json"]));
$oRetorno               = new stdClass();
$oRetorno->iStatus      = 1;
$oRetorno->sMessage     = '';

require(modification('libs/db_conn.php'));
$sConnection = "host=$DB_SERVIDOR dbname=$DB_BASE port=$DB_PORTA user=$DB_USUARIO password=$DB_SENHA";

global $conn;

$conn = pg_connect($sConnection);

try {

  db_inicio_transacao();

  switch ($oParam->exec) {

    case "getTarefasAtualizadas":

      $oRetorno->oChangelog = PatchService::getTarefasAtualizadas();

    break;

    case "checkUpdates";

      $oPatchService = new PatchService();

      $oRetorno->oNovasTarefas = $oPatchService->checkUpdates();

    break;

    case 'atualizar':

      $oPatchService = new PatchService();
      $oPatchService->atualizar();

      $oRetorno->sMessage = "Patch atualizado com sucesso.";

    break;

    case 'agendar':

      $oPatchService = new PatchService();
      $oPatchService->agendar($oParam->aHorarios);

      $oRetorno->sMessage = utf8_encode("Atualização de patch agendada com sucesso.");

    break;

    case 'buscarHorarios':

      $oPatchService = new PatchService();
      $oRetorno->aHorarios = $oPatchService->getHorarios();

    break;

  }

  db_fim_transacao(false);


} catch (Exception $eErro){

  db_fim_transacao(true);
  $oRetorno->iStatus  = 2;
  $oRetorno->sMessage = urlencode($eErro->getMessage());
}
echo $oJson->encode($oRetorno);

?>
