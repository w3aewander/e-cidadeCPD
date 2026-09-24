<?php

require_once(modification('model/configuracao/Task.model.php'));
require_once(modification('interfaces/iTarefa.interface.php'));

class AtualizaPatchTask extends Task implements iTarefa {

  /**
   * Inicia Execucao da Tarefa
   */
  public function iniciar() {

    parent::iniciar();

    require(modification('libs/db_conn.php'));
    $sConnection = "host=$DB_SERVIDOR dbname=$DB_BASE port=$DB_PORTA user=$DB_USUARIO password=$DB_SENHA";
    global $conn;
    $conn = pg_connect($sConnection);

    db_inicio_transacao();

    $oPatchService = new PatchService();

    try {

      $oDiffChangelog = $oPatchService->checkUpdates();

      $aDiffChangelog = (array) $oDiffChangelog;

      if ( !empty($aDiffChangelog) ) {
        $oPatchService->atualizar();
      }

      db_fim_transacao(false);
    } catch (Exception $e) {
      $this->log($e->getMessage());
      db_fim_transacao(true);
    }

    parent::terminar();
  }

  /**
   * Para execução da Tarefa
   */
  public function cancelar(){

  }
  
  /**
   * Aborta a Execução da Tarefa
   */
  public function abortar(){
    
  }
}
